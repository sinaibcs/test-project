<?php

namespace App\Http\Controllers\Api\V1\Admin\Datamigration\BeneficiaryCsvData;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;


class CsvUploadController extends Controller
{
    public function upload(Request $request){

//        ini_set('memory_limit', '512M');
       ini_set('memory_limit', '-1');
       ini_set('max_execution_time', '0');

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,csv,xls|max:204800',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid file upload.',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = Excel::toCollection(null,$request->file('file'));

        $uploadfileColumn =  $data->first()[0]->toArray();
        $currentbeneficiaryTableColumn = Schema::getColumnListing('beneficiaries');
        $diffcolumn = array_diff($uploadfileColumn,$currentbeneficiaryTableColumn);
        $rowCount = $data->first()->count();
        $valueData = $data->first();
        if($rowCount > 0){
            $heads = $data->first()[0]->toArray();
            $dobIndex = array_search('date_of_birth', $heads);
            foreach($valueData as $i => $val){
                if($i == 0) continue;
                if($dobIndex){
                    if(is_numeric($val[$dobIndex])){
                        $valueData[$i][$dobIndex] = ExcelDate::excelToDateTimeObject($val[$dobIndex])->format('Y-m-d');
                    }
                }
            }
        }
        return response()->json([
            'status' =>'success',
            'message' => 'File uploaded successfully.',
            'data' => $valueData,
            'rowCount' => $rowCount,
            'column_diff' => $diffcolumn,
        ], 200);
    }

    public function store(Request $request)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2G');

        \DB::beginTransaction();
        try{
            $response = $this->uploadData($request);
            \DB::commit();
            return $response;
        }catch(\Exception $e){
            \DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'errors' => []
            ], 500);
        }
    }

    public function uploadData($request)
    {
        // Get current columns in the beneficiaries table
        $currentbeneficiaryTableColumn = Schema::getColumnListing('beneficiaries');

        // Decode the JSON input from the request
        $finalData = json_decode($request->input('final_data'), true);

        // Extract column headers
        $keys = $finalData[0];

        // Convert input to associative arrays
        $convertData = [];
        foreach (array_slice($finalData, 1) as $row) {
            $record = [];
            foreach ($keys as $index => $columnName) {
                $record[$columnName] = $row[$index] ?? null;
            }
            $convertData[] = $record;
        }

        $duplicateBeneficiaryIds = [];
        $storedCount = 0;

        // Chunk into batches of 500
        foreach (array_chunk($convertData, 500) as $batch) {
            // Extract beneficiary IDs and verification numbers
            $beneficiaryIds = array_column($batch, 'beneficiary_id');
            $verificationNumbers = array_column($batch, 'verification_number');

            // Run separate, faster queries for duplicates
            $existingIds = Beneficiary::withTrashed()->whereIn('beneficiary_id', $beneficiaryIds)
                ->pluck('beneficiary_id')
                ->toArray();

            $existingVerificationNumbers = Beneficiary::withTrashed()->whereIn('verification_number', $verificationNumbers)
                ->pluck('verification_number')
                ->toArray();

            $batchToInsert = [];

            foreach ($batch as $column) {
                $beneficiaryId = $column['beneficiary_id'] ?? null;
                $verificationNumber = $column['verification_number'] ?? null;

                // Skip empty
                if (!$beneficiaryId || !$verificationNumber) {
                    continue;
                }

                // Skip duplicates
                if (in_array($beneficiaryId, $existingIds) || in_array($verificationNumber, $existingVerificationNumbers)) {
                    $duplicateBeneficiaryIds[] = $beneficiaryId;
                    continue;
                }

                $dataToSave = [];

                foreach ($column as $infoKey => $infoValue) {
                    if (in_array($infoKey, $currentbeneficiaryTableColumn)) {
                        if (!empty($infoValue)) {
                            if (in_array($infoKey, ['date_of_birth', 'nominee_date_of_birth', 'application_date', 'approve_date'])) {
                                $formattedDate = date('Y-m-d', strtotime(str_replace('/', '-', $infoValue)));
                                $dataToSave[$infoKey] = $formattedDate ?? null;
                            } else {
                                $dataToSave[$infoKey] = $infoValue;
                            }
                        } else {
                            $dataToSave[$infoKey] = null;
                        }
                    }
                }

                // Normalize mobile numbers
                if (isset($dataToSave['mobile']) && ((string) $dataToSave['mobile'])[0] !== '0') {
                    $dataToSave['mobile'] = '0' . $dataToSave['mobile'];
                }
                if (isset($dataToSave['permanent_mobile']) && ((string) $dataToSave['permanent_mobile'])[0] !== '0') {
                    $dataToSave['permanent_mobile'] = '0' . $dataToSave['permanent_mobile'];
                }

                $batchToInsert[] = $dataToSave;
            }

            // Bulk insert the clean batch
            if (!empty($batchToInsert)) {
                Beneficiary::insert($batchToInsert);
                $storedCount += count($batchToInsert);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'File processed successfully.',
            'duplicate_beneficiary_ids' => $duplicateBeneficiaryIds,
            'duplicate_count' => count($duplicateBeneficiaryIds),
            'stored_count' => $storedCount
        ], 200);
    }




//    public function uploadData($request)
//    {
//        // Get current columns in the beneficiaries table
//        $currentbeneficiaryTableColumn = Schema::getColumnListing('beneficiaries');
//
//        // Decode the JSON input from the request
//        $finalData = json_decode($request->input('final_data'), true);
//
//        // Initialize an array to hold matched columns
//        $matchedData = [];
//
//        // Match the keys from the input with table columns
//        foreach ($finalData[0] as $key => $value) {
//            if (in_array($value, $currentbeneficiaryTableColumn)) {
//                $matchedData[$key] = $value;
//            }
//        }
//
//        // Convert the final data to a format suitable for database insertion
//        $convertData = [];
//        foreach ($finalData as $key => $value) {
//            if ($key === 0) {
//                $keys = $value;
//                continue;
//            }
//            $record = [];
//            foreach ($keys as $index => $columnName) {
//                $record[$columnName] = $value[$index];
//            }
//            $convertData[] = $record;
//        }
//
//        // Remove the header row from the data
//        $finalData = array_slice($finalData, 1);
//
//        // Initialize array to hold duplicate beneficiary IDs and counters
//        $duplicateBeneficiaryIds = [];
//        $storedCount = 0;
//
//        // Process each record
//        foreach ($convertData as $column) {
//            $id = isset($column['id']) ? $column['id'] : null;
//            $beneficiaryId = isset($column['beneficiary_id']) ? $column['beneficiary_id'] : null;
//
//            // Ignore records with a null or empty beneficiary_id
//            if (is_null($beneficiaryId) || $beneficiaryId === '') {
//                continue;
//            }
//
//            // Check for duplicates
//            if (Beneficiary::where('beneficiary_id', $beneficiaryId)->exists()) {
//                // Add duplicate beneficiary ID to the array
//                $duplicateBeneficiaryIds[] = $beneficiaryId;
//                continue;
//            }
//
//            $dataToSave = [];
//
//            // Prepare data for saving
//            foreach ($column as $infoKey => $infoValue) {
//                if (in_array($infoKey, $currentbeneficiaryTableColumn)) {
//                    if (!empty($infoValue) && $infoValue !== '') {
//                        if (in_array($infoKey, ['date_of_birth', 'nominee_date_of_birth', 'application_date', 'approve_date'])) {
//                            $formattedDate = date('Y-m-d', strtotime(str_replace('/', '-', $infoValue)));
//                            $dataToSave[$infoKey] = $formattedDate ?? null;
//                        } else {
//                            $dataToSave[$infoKey] = $infoValue;
//                        }
//                    } else {
//                        $dataToSave[$infoKey] = null;
//                    }
//                }
//            }
//
//            // Insert or update the record
//            if ($id !== null) {
//                Beneficiary::updateOrCreate(
//                    ['id' => $id],
//                    $dataToSave
//                );
//            } else {
//                Beneficiary::create($dataToSave);
//            }
//
//            // Increment stored count
//            $storedCount++;
//        }
//
//        // Return response with duplicate beneficiary IDs and counts
//        return response()->json([
//            'status' => 'success',
//            'message' => 'File processed successfully.',
//            'duplicate_beneficiary_ids' => $duplicateBeneficiaryIds,
//            'duplicate_count' => count($duplicateBeneficiaryIds),
//            'stored_count' => $storedCount
//        ], 200);
//    }

}
