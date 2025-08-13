<?php

namespace App\Http\Services\Admin\Application;

use App\Models\DisServiceApiRequestLog;
use Exception;
use Illuminate\Support\Facades\Http;

class DISService
{
    public function getInfo($data)
    {

        $disSendUrl = env('DIS_URL', 'https://www.dis.gov.bd/api/ApiGetPwdForMis');

        try {
            $response = Http::withToken(env('DIS_AUTHORIZED_KEY'))
                ->withHeaders([
                    'X-ApiKey'     => env('DIS_API_KEY'),
                    'Content-Type' => 'application/json',
                ])->post($disSendUrl, [
                    'DateOfBirth' => $data['DateOfBirth'],
                    'DisId'       => $data['DisId'],
                ]);


//            dd($response->status(), $response->body());

//            $log = new DisServiceApiRequestLog();
//            $log->endpoint = $disSendUrl;
//            $log->type = 'FETCH';
//            $log->payload = json_encode($data);

//            if ($response->status() !== 200) {
//                $log->error = $response->body();
//            } else {
//                $log->response = $response->body();
//            }
//            $log->save();

            if ($response->successful()) {
                $res = $response->json();

                if (is_string($res)) {
                    $res = json_decode($res, true);
                }

                if (isset($res['Status']) && $res['Status'] == 200) {
                    return $res;
                }
            }

            return false;

        } catch (Exception $e) {
            // Log or handle exception
            return false;
        }

    }
}
