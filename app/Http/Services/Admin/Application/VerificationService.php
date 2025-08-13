<?php

namespace App\Http\Services\Admin\Application;

use App\Exceptions\AuthBasicErrorException;
use App\Models\AllowanceProgram;
use App\Models\FinancialYear;
use App\Models\Nid;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Http;

class VerificationService
{

    public const BASE_URL = "https://mis.bhata.gov.bd";

    public const NID_API = "/test-nid-data";

    public function callVerificationApi($data)
    {
        // Convert the date of birth to the format used in the database
        $data['dob'] = Carbon::parse($data['dob'])->format('Y-m-d');

        // Find the record in the Nid model
        $nidInfo = Nid::where('nid', $data['nid'])
            ->where('dob', $data['dob'])
            ->first();


        // Check if the record exists
        if (!$nidInfo) {
            throw new HttpResponseException(
                response()->json([
                    'error' => 'invalid_nid',
                    'message' => "NID or Date of birth information doesn't match",
                ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            );
        }
        $nidInfo = $nidInfo->toArray();

        // Calculate age
        $nidInfo['age'] = $this->calculateAge($nidInfo);
        // Convert the model instance to an array to return as response
        return $data = $nidInfo;
    }


//    public function callVerificationApi($data)
//    {
//        $data['dob'] = Carbon::parse($data['dob'])->format('d-m-Y');
//
//        $response = Http::withoutVerifying()->contentType('application/json')
//            ->post(self::BASE_URL . self::NID_API, $data);
//
//
//        if ($response->failed()) {
//            throw new AuthBasicErrorException(
//                HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
//                'invalid_nid',
//                "NID or Date of birth information doesn't match",
//            );
//        }
//
//        $nidInfo = $response->json('success.data');
//        $nidInfo['age'] = $this->calculateAge($data);
//
//        return $nidInfo;
//    }


    public function callNomineeVerificationApi($data)
    {
        // Convert the date of birth to the format used in the database
        $data['dob'] = Carbon::parse($data['dob'])->format('Y-m-d');

        // Find the record in the Nid model
        $nidInfo = Nid::where('nid', $data['nid'])
            ->where('dob', $data['dob'])
            ->first();

        // Check if the record exists
        if (!$nidInfo) {
            throw new HttpResponseException(
                response()->json([
                    'error' => 'invalid_nominee_nid',
                    'message' => 'Nominee NID or Date of birth is invalid',
                ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        // Return the NID information
        return $nidInfo;
    }


//    public function callNomineeVerificationApi($data)
//    {
//        $data['dob'] = Carbon::parse($data['dob'])->format('d-m-Y');
//
//        $response = Http::withoutVerifying()->contentType('application/json')
//            ->post(self::BASE_URL . self::NID_API, $data);
//
//
//        if ($response->failed()) {
//            throw new AuthBasicErrorException(
//                HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
//                'invalid_nominee_nid',
//                "Nominee NID or Date of birth is invalid",
//            );
//        }
//
//        $nidInfo = $response->json('success.data');
//
//        return $nidInfo;
//    }



    public function calculateAge($data)
    {
        $finYear = FinancialYear::whereStatus(1)->first();

        if (!$finYear) {
            throw new AuthBasicErrorException(
                HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
                'internal_error',
                'No financial year found',
            );
        }


        return Carbon::parse($data['dob'])->diff($finYear->end_date)->format('%y.%m');
    }












    public function formatData()
    {

    }






}
