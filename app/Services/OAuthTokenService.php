<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\ApiOAuthToken;
use App\Services\ApiClients\NagadClient;
use App\Interfaces\OAuthTokenServiceInterface;
use App\Interfaces\OAuthTokenRepositoryInterface;

class OAuthTokenService implements OAuthTokenServiceInterface
{
    protected OAuthTokenRepositoryInterface $tokenRepository;
    protected array $apiClients;

    public function __construct(
        OAuthTokenRepositoryInterface $tokenRepository,
        NagadClient $nagadClient
        // NIDClient $nidClient,
        // DISClient $disClient
    ) {
        $this->tokenRepository = $tokenRepository;

        // Mapping API types to their respective clients
        $this->apiClients = [
            'Nagad' => $nagadClient,
            // 'NID' => $nidClient,
            // 'DIS' => $disClient
        ];
    }

    /**
     * Get a valid OAuth token for a given API type.
     * @param string $apiType
     * @return ApiOAuthToken|null
     * @throws Exception
     */
    public function getValidToken(string $apiType): ? ApiOAuthToken
    {
        // Ensure API type is valid
        if (!isset($this->apiClients[$apiType])) {
            throw new Exception("Invalid API type: {$apiType}");
        }

        $existingToken = $this->tokenRepository->getToken($apiType);

        // If token exists and is still valid, return it
        if ($existingToken && !$this->isTokenExpired($existingToken)) {
            return $existingToken;
        }

        // If refresh token exists, attempt to refresh the token
        if ($existingToken && $existingToken->refresh_token) {
            return $this->refreshToken($apiType);
        }

        // Otherwise, perform an initial login to get a new token
        return $this->login($apiType);
    }

    /**
     * Refresh an OAuth token using the refresh token.
     * @param string $apiType
     * @return ApiOAuthToken
     * @throws Exception
     */
    public function refreshToken(string $apiType): ApiOAuthToken
    {
        if (!isset($this->apiClients[$apiType])) {
            throw new Exception("Invalid API type: {$apiType}");
        }

        $existingToken = $this->tokenRepository->getToken($apiType);

        if (!$existingToken || !$existingToken->refresh_token) {
            throw new Exception("No refresh token available for {$apiType}");
        }

        // Use API client to refresh the token
        $newTokenData = $this->apiClients[$apiType]->refreshToken($existingToken->refresh_token);

        // Update the token in the database
        $this->tokenRepository->updateToken($existingToken, $newTokenData);

        return $this->tokenRepository->getToken($apiType);
    }

    /**
     * Perform an initial login to obtain access and refresh tokens.
     * @param string $apiType
     * @return object|null
     * @throws Exception
     */
    public function login(string $apiType): ?object
    {
        if (!isset($this->apiClients[$apiType])) {
            throw new Exception("Invalid API type: {$apiType}");
        }

        // Use API client to perform login
        $newTokenData = $this->apiClients[$apiType]->login();

        // Save token in the database
        $this->tokenRepository->saveToken($newTokenData);

        return $this->tokenRepository->getToken($apiType);
    }

    /**
     * Check if the given token is expired.
     * @param object $token
     * @return bool
     */
    private function isTokenExpired(object $token): bool
    {
        return $token->expires_at ? Carbon::now()->greaterThan($token->expires_at) : true;
    }
}

