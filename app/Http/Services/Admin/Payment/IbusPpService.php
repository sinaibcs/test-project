<?php

namespace App\Http\Services\Admin\Payment;

use App\Models\IbusApiRequestLog;
use Illuminate\Support\Facades\Http;

class IbusPpService
{
    private $baseUrl;

    public function __construct()
    {
        // $this->baseUrl = config('services.ibuspp.base_url');
        $this->baseUrl = env("IBAS_ROOT");
    }

    /**
     * Retrieve the access token from the API.
     *
     * @return string|null
     */
    private function getAccessToken(): ?string
    {
        $data = [
            'userName' => env('IBAS_USERNAME'),
            'password' => env('IBAS_PASSWORD'),
        ];
        $log = new IbusApiRequestLog;
        $log->endpoint = "/spbmu/authenticate/getToken";
        $log->payload = json_encode($data);

        try{
            $response = Http::post("{$this->baseUrl}/spbmu/authenticate/getToken", $data);

            if ($response->successful()) {
                $log->response = json_encode($response->json());
                $log->save();
                return $response->json('token');
            }
        }catch (\Exception $e){
            $log->error = $e->getMessage();
            $log->save();
            throw $e;
        }


        return null;
    }

    /**
     * Create a payment cycle.
     *
     * @param array $data
     * @return mixed
     */
    public function createPaymentCycle(array $data)
    {   
        $token = $this->getAccessToken();
        $data['token'] = $token;

        $log = new IbusApiRequestLog;
        $log->endpoint = "/spbmu/payment/cycle/create";
        $log->payload = json_encode($data);

        try{
            $response = Http::post("{$this->baseUrl}/spbmu/payment/cycle/create?token=$token", $data);
            if ($response->successful()) {
                $log->response = json_encode($response->json());
                $log->save();
                return $response->json();
            }else{
                 $log->error = $response->body();
            }
        }catch (\Exception $e){
            $log->error = $e->getMessage();
        }
        $log->save();

        return false;
    }

    /**
     * Add bulk payments.
     *
     * @param array $data
     * @return mixed
     */
    public function addBulkPayment(array $data) 
    {
        $token = $this->getAccessToken();
        $data['token'] = $token;

        $log = new IbusApiRequestLog;
        $log->endpoint = "/spbmu/payment/addBulk";
        $log->payload = json_encode($data);

        try{
            $response = Http::post("{$this->baseUrl}/spbmu/payment/addBulk?token=$token", $data);

            if ($response->successful()) {
                $log->response = json_encode($response->json());
                $log->save();
                return $response->json();
            }else{
                $log->error = $response->body();
            }
            
        }catch (\Exception $e){
            $log->error = $e->getMessage();
        }
        $log->save();

        return false;
    }

    /**
     * Pull reconciliation data.
     *
     * @param array $data
     * @param string $token
     * @return mixed
     */
    public function pullReconciliationData(array $data)
    {
        $token = $this->getAccessToken();
        
        $log = new IbusApiRequestLog;
        $log->endpoint = "/spbmu/payment/lm/getReturnedBeneficiariesForLM";
        $log->payload = json_encode($data);
        
        try{
            $response = Http::withToken($token)->post("{$this->baseUrl}/spbmu/payment/lm/getReturnedBeneficiariesForLM?token=$token", $data);
        
            if ($response->successful()) {
                $log->response = json_encode($response->json());
                $log->save();
                return $response->json();
            }else{
                $log->error = $response->body();
            }
        }catch (\Exception $e){
            $log->error = $e->getMessage();
        }
        $log->save();

        return false;
    }
}
