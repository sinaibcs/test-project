<?php

namespace App\Http\Services\Client;

use App\Helpers\Helper;
use App\Models\ApiDataReceive;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApiService
{
    public function hasPermission($request, $apiName)
    {
        $apiLog = ApiLog::create(['request_time' => now(), 'status' => 1]);

        try {
            $apiDataReceive = ApiDataReceive::where([
                'username' => $request->auth_key,
                'api_key' => $request->auth_secret
            ])->first();

            abort_if(!$apiDataReceive,403, 'Unauthorized action');

            $apiDataReceive->increment('total_hit');

            $apiLog->update(['api_data_receive_id' => $apiDataReceive->id]);

            if ($apiDataReceive->whitelist_ip) {
                abort_if($apiDataReceive->whitelist_ip != Helper::clientIp(), 403, 'Access denied');
            }

            abort_if(now()->lt($apiDataReceive->start_date), 422, 'Access denied! Endpoint is not active yet.');

            if ($apiDataReceive->end_date) {
                abort_if(now()->subDay()->gt($apiDataReceive->end_date), 422, 'Access denied! Endpoint is no longer active.');
            }

            $apiList = $apiDataReceive->apiList()->where('api_unique_id', $apiName)->first();

            abort_if(!$apiList,403, 'You are no authorized to access this endpoint!');

            $apiLog->update(['api_list_id' => $apiList->id]);

            return $apiList->selected_columns;
        } catch (\Throwable $throwable) {
            $apiLog->update(['status' => 0]);

            throw $throwable;
        }
    }


    /**
     * @param Request $request
     * @param $columns
     * @return void
     */
    public function validateColumnSearch($request, $columns)
    {
        $errors = [];

        foreach ($request->except('auth_key', 'auth_secret') as $key => $i) {
            if (!in_array($key, $columns)){
                $errors[$key] = "You are not authorized to access $key column.";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }




}
