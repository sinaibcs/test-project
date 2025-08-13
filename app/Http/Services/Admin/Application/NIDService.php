<?php

// namespace App\Services;
namespace App\Http\Services\Admin\Application;


use App\Models\NidServiceApiRequestLog;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class NIDService
{
    private function getToken($loginUrl, $credentials){
        $accessToken = Cache::get('nid_token');
        if($accessToken == null){
            $log = new NidServiceApiRequestLog;
            $log->endpoint = $loginUrl;
            $log->type = 'LOGIN';
            $log->payload = json_encode($credentials);

            $loginResponse = Http::post($loginUrl, $credentials);
            // Check if login was successful
            if ($loginResponse->status() !== 200 || $loginResponse->json('statusCode') !== 'SUCCESS') {
                $log->error = json_encode($loginResponse->body());
                $log->save();
                throw new Exception('Login failed. Please check your credentials.');
            }
            // Get the access token from the response
            $log->response = json_encode($loginResponse->body());
            $log->save();
            $accessToken = $loginResponse->json('success.data.access_token');
            Cache::put('nid_token', $accessToken, now()->addMinutes(10));
        }
        return $accessToken;
    }
    // Method to get NID information
    public function getInfo($data)
    {
        $loginUrl = env('NID_PORTAL_LOGIN','https://prportal.nidw.gov.bd/partner-service/rest/auth/login'); // URL to login to the portal
        // $nidInfoUrl = env('NID_PORTAL_DETAILS','https://prportal.nidw.gov.bd/partner-service/rest/voter/details'); // URL to get NID info
        $nidInfoUrl = env('NID_PORTAL_DETAILS','https://prportal.nidw.gov.bd/partner-service/rest/voter/demographic/verification'); // URL to get NID info
        // $logoutUrl = env('NID_PORTAL_LOGOUT','https://prportal.nidw.gov.bd/partner-service/rest/auth/logout'); // URL to logout from the portal        $loginUrl = env('NID_PORTAL_LOGIN'); // URL to login to the portal

        // Login credentials
        $credentials = [
            // 'username' => env('NID_PORTAL_USERNAME'), // Set your username in .env
            // 'password' => env('NID_PORTAL_PASSWORD'), // Set your password in .env
            'username' => "dss_systemadmin", // Set your username in .env
            'password' => "nidwAPI@%#202508", // Set your password in .env
        ];

        try {
            // Step 1: Login and get access token
            $accessToken = $this->getToken($loginUrl, $credentials);

            // Step 2: Use the access token to retrieve NID information
            $nidRequestBody = [
                "verify" => [
                    'dateOfBirth' => $data['dob'],
                ]
            ];
            if(($data['name']?? null) != null){
                $nidRequestBody['verify']['name'] = $data['name'];
            }
            if(($data['nameEn']?? null) != null){
                $nidRequestBody['verify']['nameEn'] = $data['nameEn'];
            }

            $log = new NidServiceApiRequestLog;
            $log->type = 'FETCH';
            $log->endpoint = $nidInfoUrl;

            // Check if it's a 17-digit or 10-digit NID and adjust the request body accordingly
            if (strlen($data['nid']) === 17) {
                $nidRequestBody['identify']['nid17Digit'] = $data['nid'];
            } elseif (strlen($data['nid']) === 10) {
                $nidRequestBody['identify']['nid10Digit'] = $data['nid'];
            } else {
                throw new Exception('Invalid NID length. It should be either 10 or 17 digits.');
            }

            // Make a request to get NID information
            $nidInfoResponse = Http::withToken($accessToken)->post($nidInfoUrl, $nidRequestBody);
            $log->payload = json_encode($nidRequestBody);


            // Check if the NID retrieval was successful
            if($nidInfoResponse->status() == 401){
                Cache::forget('nid_token');
                $accessToken = $this->getToken($loginUrl, $credentials);
                $nidInfoResponse = Http::withToken($accessToken)->post($nidInfoUrl, $nidRequestBody);

            }

            if ($nidInfoResponse->status() !== 200 || $nidInfoResponse->json('status') !== 'OK') {
                $log->error = json_encode($nidInfoResponse->body());
                $log->save();
                // throw new Exception('Failed to retrieve NID information.');
            }else{
                $log->response = json_encode($nidInfoResponse->body());
                $log->save();
            }

            // // Step 3: Logout after retrieving NID information
            // Http::withToken($accessToken)->post($logoutUrl);

            // Return the retrieved NID information
            return $nidInfoResponse;

        } catch (Exception $e) {
            // throw $e;
            return false;
            // Handle any exceptions that occur during the process
            // return ['error' => $e->getMessage()];
        }
    }
}
