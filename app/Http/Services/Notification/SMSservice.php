<?php

namespace App\Http\Services\Notification;

use Illuminate\Support\Facades\Http;
use Infobip\Api\SmsApi;
use Infobip\Configuration;
use Infobip\ApiException;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsSendingSpeedLimit;
use Infobip\Model\SmsTextualMessage;
class SMSservice
{
    // send limit 1000 sms per minute
    private $api;



    public function sendSmsBulkBd($mobile, $message)
    {
        $url = "bulksmsbd.net/api/smsapi";

        $data = [
            'api_key' => "xp143oLW8GJtKk3ggwxW",
            'type' => "text",
            'message' => $message,
            'number' => $mobile,
            'senderid' => "8809617617434",
        ];

        $response = Http::contentType('application/json')
            ->post($url, $data);

        return $response->json();
    }


    //bulksmsbd, Infobip
    public function sendSms($mobile, $message)
    {
        try{
            // $gateway = strtolower(env('SMS_GATEWAY', 'Infobip'));
            $gateway = strtolower(env('SMS_GATEWAY', 'smsTeletalk'));
            //  dd($gateway);

            if ($gateway == 'infobip') {
                return $this->sendSmsInfobip($mobile, $message);
            }

            if ($gateway == 'bulksmsbd') {
                return $this->sendSmsBulkBd($mobile, $message);
            } 
            if ($gateway == 'smsteletalk') {
            
                return $this->sendSmsTeletalk($mobile, $message);
            }
        }catch(\Throwable $t){
            throw $t;
            // return true;
        }
    }
    
    //bulksmsbd, Infobip
    public function sendOtpSms($mobile, $message)
    {
        try{
            // $gateway = strtolower(env('SMS_GATEWAY', 'Infobip'));
            $gateway = strtolower(env('SMS_GATEWAY', 'smsTeletalk'));
            //  dd($gateway);

            if ($gateway == 'infobip') {
                return $this->sendSmsInfobip($mobile, $message);
            }

            if ($gateway == 'bulksmsbd') {
                return $this->sendSmsBulkBd($mobile, $message);
            } 
            if ($gateway == 'smsteletalk') {
            
                return $this->sendSmsTeletalk($mobile, $message, "OTP");
            }
        }catch(\Throwable $t){
            throw $t;
            // return true;
        }
    }
    
    //bulksmsbd, Infobip
    public function sendConfirmationSms($mobile, $message)
    {
        try{
            // $gateway = strtolower(env('SMS_GATEWAY', 'Infobip'));
            $gateway = strtolower(env('SMS_GATEWAY', 'smsTeletalk'));
            //  dd($gateway);

            if ($gateway == 'infobip') {
                return $this->sendSmsInfobip($mobile, $message);
            }

            if ($gateway == 'bulksmsbd') {
                return $this->sendSmsBulkBd($mobile, $message);
            } 
            if ($gateway == 'smsteletalk') {
            
                return $this->sendSmsTeletalk($mobile, $message, "CONFIRMATION");
            }
        }catch(\Throwable $t){
            return false;
        }
    }



    public function sendSmsInfobip($mobile, $message)
    {
        // Fetch host and apiKey from the .env file
        $host = env('INFOBIP_HOST');
        $apiKey = env('INFOBIP_API_KEY');

        $configuration = new Configuration(
            host: $host,
            apiKey: $apiKey
        );
        $sendSmsApi = new SmsApi(config: $configuration);
        // check mobile number is valid or not has 88 or not if not add 88
        if (substr($mobile, 0, 2) != '88') {
            $mobile = '88' . $mobile;
        }



        $message = new SmsTextualMessage(
            destinations: [
                new SmsDestination(to: $mobile)
            ],
            from: 'InfoSMS',
            text: $message
        );

        // infobip sms sending speed limit
        $speedLimit = new SmsSendingSpeedLimit(
            amount: 1000,
            timeUnit: 'MINUTE'
        );

        $request = new SmsAdvancedTextualRequest(messages: [$message], bulkId: null, sendingSpeedLimit: $speedLimit);

        try {
            $smsResponse = $sendSmsApi->sendSmsMessage($request);
            $data['bulkId'] = $smsResponse->getBulkId() . PHP_EOL;
            // $data['bulkId'] = $smsResponse->getBulkId();
            $data['messages'] = $smsResponse->getMessages();
            $msg=[];
            foreach ($smsResponse->getMessages() ?? [] as $message) {
                $msg[]= sprintf('Message ID: %s, status: %s, Discriminator: %s ', $message->getMessageId(), $message->getStatus()?->getName(),$message->getTo()) . PHP_EOL;
            }
            $data['messageId'] = (!empty($data['messages'])) ? current($data['messages'])->getMessageId() : null;
            return $msg;
        } catch (ApiException $apiException) {
            $code['getCode']=$apiException->getCode();
            $code['getResponseHeaders']=$apiException->getResponseHeaders();
            $code['getResponseBody']=$apiException->getResponseBody();
            $code['getResponseObject']=$apiException->getResponseObject();
            throw New \Exception(json_encode($code));
            // throw New \Exception($apiException->getMessage());
        }
    }

public function sendSmsTeletalk($mobile, $message, $type = "GENERAL")
{
    // Fetch necessary credentials from the .env file
    $apiUrl = env('TELETALK_BULK_SMS_API_URL', 'http://bulksms1.teletalk.com.bd:8091/jlinktbls.php');
    $user = env('TELETALK_USER');
    $pass = md5(env('TELETALK_PASS')); // Hash the password using MD5
    $encrkey = env('TELETALK_ENCR_KEY'); // Encryption key from .env
    $cid = env('TELETALK_CID', '1234567890'); // Common ID
    $userId = env('TELETALK_USER_ID');

    // Generate p_key: a 16-digit unique number
    $p_key = mt_rand(1000000000000000, 9999999999999999);

    // Generate a_key using the custom function
    $a_key = $this->generateApiKey($p_key, $userId, $encrkey);

    // Prepend '88' to the mobile number if it doesn't start with '88'
    if (substr($mobile, 0, 2) != '88') {
        $mobile = '88' . $mobile;
    }

    // Set up the request data
    $data = [
        'op' => 'SMS',
        'user' => $user,
        'pass' => $pass,
        'a_key' => $a_key,
        'p_key' => $p_key,
        'chunk' => 'S', // 'S' for single message, 'G' for group message
        'sms' => $message,
        'mobile' => $mobile,
        'cid' => $cid,
        'smsclass' => $type, // Change if needed, e.g., OTP, CODE, etc.
        'charset' => 'ASCII', // Use 'UTF-8' for Unicode messages
        'validity' => '1440', // Message validity in minutes
    ];

    $response = Http::timeout(60)
    ->withHeaders([
        'Content-Type' => 'application/json',
    ])
    ->post($apiUrl, $data);

    // Check for HTTP error (connection-level issues)
    if ($response->failed()) {
        throw new \Exception("API request failed with HTTP code " . $response->status());
    }

    $responseData = $response->json();

    // If status is FAILED, throw exception with `details` message
    if (isset($responseData['status']) && strtoupper($responseData['status']) === 'FAILED') {
        $details = $responseData['details'] ?? 'Unknown error occurred.';
        throw new \Exception("SMS sending failed: $details");
    }

    // If status is SUCCESS, return the parsed result
    if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
        return explode('|', $responseData['details']);
    }

    // Handle unexpected response format
    throw new \Exception("Unexpected API response: " . json_encode($responseData));
}

// Function to generate the a_key
private function generateApiKey($p_key, $userId, $encrkey)
{
    // Perform mathematical addition of p_key and userId
    $sum = (int)$p_key + (int)$userId;

    // Concatenate the sum with the encrkey
    $concatenatedString = $sum . $encrkey;

    // Generate an MD5 hash for the concatenated string
    return md5($concatenatedString);
}




}