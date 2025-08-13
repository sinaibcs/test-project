<?php

namespace App\Http\Services\Messaging;

use App\Http\Traits\SettingsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Twilio\Rest\Client;

class SmsService
{
    use SettingsTrait;
    /**
     * Strip a Phone number of all non alphanumeric characters
     *
     * @param  string phone
     * @return string
     */
    public function stripCharFromPhone($phone)
    {
        return preg_replace('/(\W*)/', '', $phone);
    }



    public function sendSMSNew($to, $text){
        $url = "bulksmsbd.net/api/smsapi";

        $data = [
            'api_key' => "xp143oLW8GJtKk3ggwxW",
            'type' => "text",
            'message' => $text,
            'number' => $to,
            'senderid' => "8809617617434",
        ];

        $response = Http::contentType('application/json')
            ->post($url, $data);

        return $response->json();

    }

    public function sendSMS($to, $text){

        $api_key=config('envvars.sms_api_key');
        $api_secret=config('envvars.sms_api_secret');
        if($this->activeSmsProvider=="alpha_net"){

        $url='https://api.sms.net.bd/sendsms';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('api_key' => $api_key, 'msg' => $text, 'to' => $to),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return response()->json(['success'=>$response]);

        }
        elseif($this->activeSmsProvider=="nexmo"){
            $params = [
                "api_key" => $api_key,
                "api_secret" => $api_secret,
                "text" => $text,
                "to" => $to
            ];

            $url = "https://rest.nexmo.com/sms/json";
            $params = json_encode($params);

            $ch = curl_init(); // Initialize cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($params),
                'accept:application/json'
            ));
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        elseif($this->activeSmsProvider=="bulk_sms_bd"){
            $url = "http://api.greenweb.com.bd/api.php?json";
            $data= array(
                'to'=>"$to",
                'message'=>"$text",
                'token'=>"$api_key"
                ); // Add parameters in key value
                $ch = curl_init(); // Initialize cURL
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_ENCODING, '');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
            return $response;
        }
        elseif($this->activeSmsProvider=="twilo"){
            $account_sid = config('envvars.twilio_sid');
            $auth_token = config('envvars.twilio_auth_token');
            $twilio_number = config('envvars.twilio_number');



            $client = new Client($account_sid, $auth_token);

            //send otp
            $client->messages->create(
                $to,
                array(
                    'from' => $twilio_number,
                    'body' => $text
                    )
                );
                return 'SMS Sent Successfully.';


        }else{
            return response()->json('something want Wrong');
        }

        }
}
