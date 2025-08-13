<?php

namespace App\Services\MfsValidationServices;

use Illuminate\Support\Facades\Http;

class BkashValidationService
{
    protected string $endpoint;
    protected string $appKey;
    protected string $appSecret;
    protected string $resultUrl;

    private array $types = [
        'Sync', 'Beneficiary'
    ];
    
    public function __construct()
    {
        $this->endpoint = env('BKASH_VALIDATION_ENDPOINT');
        $this->appKey = env('BKASH_APP_KEY');
        $this->appSecret = env('BKASH_APP_SECRECT');
        // $this->resultUrl = route('bkash.validation_result');
    }

    public function validateBeneficiary(string $receiverIdentifier, $type = 'Sync')
    {
        if(!in_array($type, $this->types)){
            throw new \Exception('Invalid argument passed as type');
        }
        $nonce = base64_encode(random_bytes(16));
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $passwordDigest = base64_encode(hash('sha256', $nonce . $timestamp . $this->appSecret, true));

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'WSSE realm="SDP", profile="UsernameToken", type="Appkey"',
            'X-WSSE' => "UsernameToken Username=\"{$this->appKey}\", PasswordDigest=\"{$passwordDigest}\", Nonce=\"{$nonce}\", Created=\"{$timestamp}\""
        ];

        $resultUrl = null;

        switch($type){
            case 'Sync':
                $resultUrl = route('bkash.validation_result');
                break;
            case 'Beneficiary':
                $resultUrl = route('bkash.validation_result.beneficiary_account');
                break;
            default:
            $resultUrl = route('bkash.validation_result');
        }

        $body = [
            'Header' => [
                'Version' => '1.0',
                'CommandID' => 'PreValidation',
                'OriginatorConversationID' => uniqid('CustomerValidation_'),
                'Caller' => [
                    'ThirdParty' => 'B2C Prevalidation AEP',
                    'Password' => 'YourEncryptedPasswordHere',
                    'ResultURL' => $resultUrl
                ]
            ],
            'Body' => [
                'PrimaryParty' => [
                    'IdentifierType' => 4,
                    'Identifier' => 'AEP'
                ],
                'ReceiverParty' => [
                    'IdentifierType' => 1,
                    'Identifier' => $receiverIdentifier
                ],
                'PreValidationRequest' => [
                    ['Key' => 'Service', 'Value' => 'B2C Disbursement customer validation by AEP'],
                    ['Key' => 'Currency', 'Value' => 'BDT'],
                    ['Key' => 'Amount', 'Value' => '25']
                ]
            ]
        ];

        $response = Http::withHeaders($headers)->post($this->endpoint, $body);
        
        return $response->json();
    }
}
