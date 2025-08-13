<?php
namespace App\Services\ApiClients;

use Exception;
use Carbon\Carbon;
use App\Models\ApiOAuthToken;
use Illuminate\Support\Facades\Http;
use App\Interfaces\OAuthTokenRepositoryInterface;

class NagadClient
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected OAuthTokenRepositoryInterface $tokenRepository;

    public function __construct(OAuthTokenRepositoryInterface $tokenRepository)
    {
        $this->baseUrl = env('NAGAD_BASE_URL');
        $this->clientId = env('NAGAD_CLIENT_ID');
        $this->clientSecret = env('NAGAD_CLIENT_SECRET');
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Get a valid Nagad OAuth token (either from refresh or new login)
     * @return ApiOAuthToken
     * @throws Exception
     */
    public function getValidToken(): ApiOAuthToken
    {
        // Check for existing token in database
        $existingToken = $this->tokenRepository->getToken('Nagad');

        if ($existingToken && !$this->isTokenExpired($existingToken)) {
            return $existingToken;
        }

        // If refresh token exists, try to refresh
        if ($existingToken && $existingToken->refresh_token) {
            return $this->refreshToken($existingToken->refresh_token);
        }

        // If no refresh token, perform initial login
        return $this->login();
    }

    /**
     * Login to Nagad API to obtain access & refresh tokens
     * @return ApiOAuthToken
     * @throws Exception
     */
    public function login(): ApiOAuthToken
    {
        $endpoint = "{$this->baseUrl}/nagad-wallet-validation/api/token";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($endpoint, [
                'grant_type' => 'password',
                'username' => $this->clientId,
                'password' => $this->clientSecret,
            ]);

            $data = $response->json();

            if (!$response->successful() || !isset($data['accessToken'])) {
                throw new Exception("Failed to obtain Nagad access token.");
            }

            $tokenData = [
                'api_type' => 'Nagad',
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => Carbon::now()->addSeconds($data['expires_in']),
            ];

            // Save to database
            return $this->tokenRepository->saveToken($tokenData);

        } catch (Exception $e) {
            throw new Exception("Nagad API error: " . $e->getMessage());
        }
    }

    /**
     * Refresh OAuth token using the refresh token
     * @param string $refreshToken
     * @return ApiOAuthToken
     * @throws Exception
     */
    public function refreshToken(string $refreshToken): ApiOAuthToken
    {
        $endpoint = "{$this->baseUrl}/nagad-wallet-validation/api/token";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($endpoint, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken
            ]);

            $data = $response->json();

            if (!$response->successful() || !isset($data['accessToken'])) {
                throw new Exception("Failed to refresh Nagad access token.");
            }

            $tokenData = [
                'api_type' => 'Nagad',
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => Carbon::now()->addSeconds($data['expires_in']),
            ];

            // Update in database
            $existingToken = $this->tokenRepository->getToken('Nagad');
            if ($existingToken) {
                return $this->tokenRepository->updateToken($existingToken, $tokenData);
            } else {
                return $this->tokenRepository->saveToken($tokenData);
            }

        } catch (Exception $e) {
            throw new Exception("Nagad API error: " . $e->getMessage());
        }
    }

    /**
     * Check if token is expired
     * @param object $token
     * @return bool
     */
    private function isTokenExpired($token): bool
    {
        return $token->expires_at ? Carbon::now()->greaterThan($token->expires_at) : true;
    }
}
