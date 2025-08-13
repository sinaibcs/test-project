<?php

namespace App\Http\Controllers\Client;

use App\Constants\ApiKey;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Beneficiary\AccountRequest;
use App\Http\Requests\Client\Beneficiary\BeneficiaryIdEligibilityRequest;
use App\Http\Requests\Client\Beneficiary\BeneficiaryVerifyRequest;
use App\Http\Requests\Client\Beneficiary\GetListRequest;
use App\Http\Requests\Client\Beneficiary\NomineeRequest;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryResource;
use App\Http\Services\Client\ApiService;
use App\Http\Services\Client\BeneficiaryService;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\ApplicationTrait;
use App\Models\ApiDataReceive;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\BeneficiaryPovertyValue;
use App\Models\Variable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BeneficiaryController extends Controller
{
    use MessageTrait;
    use ApplicationTrait;

    public function __construct(public ApiService $apiService, public BeneficiaryService $beneficiaryService)
    {
    }


    /**
     * Get beneficiaries list
     *
     * Retrieves list of beneficiaries based on the provided request parameters.
     * @param GetListRequest $request
     * @return AnonymousResourceCollection
     * @throws \Throwable
     */
    public function getBeneficiariesList(GetListRequest $request)
    {
        $columns = $this->apiService->hasPermission($request, ApiKey::BENEFICIARIES_LIST);

        $this->apiService->validateColumnSearch($request, $columns);

        $beneficiaryList = $this->beneficiaryService->getList($request, $columns);

        return BeneficiaryResource::collection($beneficiaryList)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    /**
     * Get beneficiary by tracking id
     *
     * Fetch beneficiary details by beneficiary tracking id
     * @param Request $request
     * @param $beneficiary_id
     * @return BeneficiaryResource|JsonResponse
     * @throws \Throwable
     */
    public function getBeneficiaryById(Request $request, $beneficiary_id)
    {
        $request->validate([
            //Auth key
            'auth_key' => 'required',
            //Secret key
            'auth_secret' => 'required',
        ]);

        $columns = $this->apiService->hasPermission($request, ApiKey::BENEFICIARY_BY_BENEFICIARY_ID);

        $beneficiary = Beneficiary::with('program')
            ->where('beneficiary_id', $beneficiary_id)
            ->first();

        if ($beneficiary) {
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $this->notFoundMessage,
        ], 404);

    }


    /**
     * Update beneficiary nominee information
     *
     * Update beneficiary nominee information
     * @param NomineeRequest $request
     * @param $beneficiary_id
     * @return BeneficiaryResource|JsonResponse
     * @throws \Throwable
     */
    public function updateNomineeInfo(NomineeRequest $request, $beneficiary_id)
    {
        $columns = $this->apiService->hasPermission($request, ApiKey::BENEFICIARY_NOMINEE_UPDATE);

        $beneficiary = Beneficiary::where('beneficiary_id', $beneficiary_id)
            ->first();

        $beforeUpdate = $beneficiary->replicate();


        if ($beneficiary) {
            $beneficiary = $this->beneficiaryService->updateNominee($request, $beneficiary, $columns);

            Helper::activityLogUpdate($beneficiary, $beforeUpdate,'Beneficiary - External','Nominee Info Updated !');

            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $this->notFoundMessage,
        ], 404);

    }


    /**
     * Update beneficiary account information
     *
     * Update beneficiary account information
     * @param AccountRequest $request
     * @param $beneficiary_id
     * @return BeneficiaryResource|JsonResponse
     * @throws \Throwable
     */
    public function updateAccountInfo(AccountRequest $request, $beneficiary_id)
    {
        $columns = $this->apiService->hasPermission($request, ApiKey::BENEFICIARY_ACCOUNT_UPDATE);

        $beneficiary = Beneficiary::where('beneficiary_id', $beneficiary_id)
            ->first();

        $beforeUpdate = $beneficiary->replicate();

        if ($beneficiary) {
            $beneficiary = $this->beneficiaryService->updateAccount($request, $beneficiary, $columns);

            Helper::activityLogUpdate($beneficiary, $beforeUpdate,'Beneficiary - External','Account Info Updated !');

            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $this->notFoundMessage,
        ], 404);

    }

    public function beneficiaryVerify(BeneficiaryVerifyRequest $request)
    {
//        return $request;

        $id_type = $request['id_type'];
        $id_value = $request['id_value'];
        $date_of_birth = $request['date_of_birth'];

        $query = Beneficiary::query();
        if(!empty($id_value) && !empty($date_of_birth)){
            $query->where('date_of_birth', $date_of_birth);

            if ($id_type === 'nid_birthid') {
                $query->where('verification_number', $id_value);
            } elseif ($id_type === 'beneficiaryId') {
                $query->where('beneficiary_id', $id_value);
            }
        }

        $beneficiary = $query->first();

        if (!$beneficiary) {
            return response()->json([
                'status' => 404,
                'success' => false,
                'beneficiary' => null,
                'message' => 'Beneficiary Not Found.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'success' => true,
            'beneficiary' => $beneficiary,
            'message' => 'Beneficiary Found.'
        ], 200);

    }

    public function beneficiaryPmtSubmit(BeneficiaryIdEligibilityRequest $request)
    {
        try {
            $beneficiary_id = $request->input('beneficiary_id');

            $pmtData = $request->input('pmt_data', []);

            if (empty($pmtData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No PMT data provided.',
                ], 400);
            }

            $values = collect($pmtData)
                ->mapWithKeys(function ($item) {
                    return [$item['variable_id'] => $item['sub_variable_ids']];
                })
                ->toJson();

            DB::table('beneficiary_poverty_values')->updateOrInsert(
                ['beneficiary_id' => $beneficiary_id],
                [
                    'values' => $values,
                    'updated_at' => now(),
                    'created_at' => now(), // only if your table has created_at
                ]
            );

            $pmt_score = $this->calculateBeneficiaryPMTScore($beneficiary_id, $request->per_room_score);

            Log::info('Beneficiary PMT score:', ['pmt_score' => $pmt_score]);

            $roundedScore = (int) round($pmt_score);
            Beneficiary::where('beneficiary_id', $beneficiary_id)
                ->update(['score' => $roundedScore]);

            return response()->json([
                'success' => true,
                'message' => 'PMT data saved successfully.',
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function calculateBeneficiaryPMTScore($beneficiary_id, $per_room_score = 0)
    {
        $record = BeneficiaryPovertyValue::where('beneficiary_id', $beneficiary_id)->first();

        if (!$record) {
            return 0;
        }

        // Decode stored JSON
        $decoded = json_decode($record->values, true);
        $values = $decoded['values'] ?? $decoded;

        if (empty($values) || !is_array($values)) {
            return 0;
        }

        // Get all selected sub-variable IDs
        $allSubVariableIds = collect($values)
            ->flatten()
            ->unique()
            ->toArray();

        if (empty($allSubVariableIds)) {
            return 0;
        }

        // Sum their scores
        $totalVariableScore = Variable::whereIn('id', $allSubVariableIds)->sum('score');

        // Get district FE for this beneficiary
        $beneficiary_data = Beneficiary::select('beneficiary_id', 'permanent_district_id')
            ->where('beneficiary_id', $beneficiary_id)->first();

        $districtFE = $this->getDistrictFEForBeneficiary($beneficiary_data->permanent_district_id);

        // Apply the final formula
        $constant = $this->povertyConstant ?? 0;

//        Log::info('Beneficiary districtFE: ' . json_encode($districtFE));
//        Log::info('Beneficiary constant: ' . json_encode($constant));
//        Log::info('Beneficiary totalVariableScore: ' . json_encode($totalVariableScore));
//        Log::info('Beneficiary per_room_score: ' . json_encode($per_room_score));

        $povertyScore = ($constant + $totalVariableScore + $per_room_score + $districtFE) * 100;

        return $povertyScore;
    }

    private function getDistrictFEForBeneficiary($districtId)
    {
        if (!$districtId) {
            return 0;
        }

        $districtFE = DB::select("
        SELECT poverty_score_cut_offs.*, financial_years.financial_year AS financial_year, financial_years.end_date
        FROM poverty_score_cut_offs
        JOIN financial_years ON financial_years.id = poverty_score_cut_offs.financial_year_id
        WHERE poverty_score_cut_offs.location_id = ? AND poverty_score_cut_offs.type = 2
        ORDER BY financial_years.end_date DESC LIMIT 1", [$districtId]);

        if (isset($districtFE[0])) {
//            Log::info('Beneficiary districtFE: ' . json_encode($districtFE[0]));
            return $districtFE[0]->score;
        } else {
            return 0;
        }
    }


}
