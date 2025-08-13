<?php

namespace App\Services\MfsValidationServices;

use Http;
use Exception;
use App\Services\OAuthTokenService;

class NagadValidationService{

    private $baseUrl;
    function __construct(private OAuthTokenService $oAuthTokenService){
        $this->baseUrl = env('NAGAD_BASE_URL');
    }

    private function validateByPhoneNumber($phoneNumber){

        $endpoint = "{$this->baseUrl}/nagad-wallet-validation/api/getWalletStatus";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$this->oAuthTokenService->getValidToken('Nagad')}"
            ])->post($endpoint, [
                'mobileNo' => $phoneNumber,
            ]);

            $data = $response->json();

            if (!$response->successful()) {
                throw new Exception("Failed to obtain Nagad access token.");
            }

            return $data['disbursementEligibility'] == 'Eligible' && $data['customerProfile'] == 'Registered';


        } catch (Exception $e) {
            throw new Exception("Nagad API error: " . $e->getMessage());
        }
    }

    public function validate ($phone): bool{
        return $this->validateByPhoneNumber($phone);
    }
}