<?php

namespace App\Http\Controllers\Client;

use App\Constants\ApiKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Application\GetListRequest;
use App\Http\Services\Client\ApiService;
use App\Models\ApiDataReceive;
use App\Models\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LaravelIdea\Helper\App\Models\_IH_Application_C;

class ApplicationController extends Controller
{

    public function __construct(public ApiService $apiService)
    {
    }

    /**
     * Get application list
     *
     * @param GetListRequest $request
     * @return Application[]|LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator|_IH_Application_C
     * @throws \Throwable
     */
    public function getAllApplicationPaginated(GetListRequest $request)
    {
        $columns = $this->apiService->hasPermission($request, ApiKey::APPLICATION_LIST);
        $this->apiService->validateColumnSearch($request, $columns);

        $searchText = $request->query('searchText');
        $application_id = $request->query('application_id');
        $nominee_name = $request->query('nominee_name');
        $account_no = $request->query('account_no');
        $nid_no = $request->query('nid_no');
        $list_type_id = $request->query('list_type_id');
        $program_id = $request->query('program_id');
        $location_type_id = $request->query('location_type_id');
        $perPage = in_array('perPage', $columns) ? $request->query('perPage') : 15;
        $page = in_array('page', $columns) ? $request->query('page') : 1;

        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayFatherNameEn = [];
        $filterArrayFatherNameBn = [];
        $filterArrayMotherNameEn = [];
        $filterArrayMotherNameBn = [];
        $filterArrayApplicationId = [];
        $filterArrayNomineeNameEn = [];
        $filterArrayNomineeNameBn = [];
        $filterArrayAccountNo = [];
        $filterArrayNidNo = [];
        $filterArrayListTypeId = [];
        $filterArrayProgramId = [];

        if($searchText && in_array('searchText', $columns)){
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayMotherNameEn[] = ['mother_name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayMotherNameBn[] = ['mother_name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayFatherNameEn[] = ['father_name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayFatherNameBn[] = ['father_name_bn', 'LIKE', '%' . $searchText . '%'];
            $page = 1;

        }

        if($application_id && in_array('application_id', $columns)){
            $filterArrayApplicationId[] = ['application_id', 'LIKE', '%' . $application_id . '%'];
            $page = 1;

        }

        if($nominee_name && in_array('nominee_name', $columns)){
            $filterArrayNomineeNameEn[] = ['nominee_en', 'LIKE', '%' . $nominee_name . '%'];
            $filterArrayNomineeNameBn[] = ['nominee_bn', 'LIKE', '%' . $nominee_name . '%'];
            $page = 1;

        }

        if($account_no && in_array('account_no', $columns)){
            $filterArrayAccountNo[] = ['account_number', 'LIKE', '%' . $account_no . '%'];
            $page = 1;

        }

        if($nid_no && in_array('nid_no', $columns)){
            $filterArrayNidNo[] = ['verification_number', 'LIKE', '%' . $nid_no . '%'];
            $page = 1;

        }

        if($program_id && in_array('program_id', $columns)){
            $filterArrayProgramId[] = ['program_id', '=', $program_id];
            $page = 1;

        }




        $query = Application::query();

        $query->when($searchText, function ($q) use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayMotherNameEn, $filterArrayMotherNameBn, $filterArrayFatherNameEn, $filterArrayFatherNameBn) {
            $q->where($filterArrayNameEn)
                ->orWhere($filterArrayNameBn)
                ->orWhere($filterArrayMotherNameEn)
                ->orWhere($filterArrayMotherNameBn)
                ->orWhere($filterArrayFatherNameEn)
                ->orWhere($filterArrayFatherNameBn)
            ;
        });


        $query->when($nominee_name, function ($q) use ($filterArrayNomineeNameBn, $filterArrayNomineeNameEn) {
            $q->where($filterArrayNomineeNameEn)
                ->orWhere($filterArrayNomineeNameBn)
            ;
        });

        $query->when($application_id, function ($q) use ($filterArrayApplicationId) {
            $q->where($filterArrayApplicationId);
        });


        $query->when($nid_no, function ($q) use ($filterArrayNidNo) {
            $q->where($filterArrayNidNo);
        });


        $query->when($nid_no, function ($q) use ($filterArrayNidNo) {
            $q->where($filterArrayNidNo);
        });


        $query->when($program_id, function ($q) use ($filterArrayProgramId) {
            $q->where($filterArrayProgramId);
        });

        $query->when($account_no, function ($q) use ($filterArrayAccountNo) {
            $q->where($filterArrayAccountNo);
        });



        if ($request->has('status')) {
            $query->where('status', $request->status);
        }


        $query->with('current_location', 'permanent_location.parent.parent.parent.parent', 'program',
            'gender', 'pmtScore'
        )
            ->orderBy('score')
        ;


        return $query->paginate($perPage, ['*'], 'page',$page);
    }


    /**
     * Find application by application id
     *
     * Find application by application id
     * @param $id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function getApplicationById(Request $request, $id)
    {
        $request->validate([
            //Auth key
            'auth_key' => 'required',
            //Secret key
            'auth_secret' => 'required',
        ]);

        $columns = $this->apiService->hasPermission(request(), ApiKey::APPLICATION_BY_ID);

        $application = Application::where('application_id', '=', $id)
            ->with([
                'current_location.parent.parent.parent.parent',
                'permanent_location.parent.parent.parent.parent',
                'program',
                'allowAddiFields',
                'allowAddiFieldValue.allowAddiField',
                'variable',
                'subvariable'
            ])->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        $image = asset('storage/' . $application->image);

        $signature = asset('storage/' . $application->signature);

        $nominee_image = asset('storage/' . $application->nominee_image);


        $nominee_signature = asset('storage/' . $application->nominee_signature);
        $groupedAllowAddiFields = $application->allowAddiFields->groupBy('id')->values();
        $groupedAllowAddiFields = $application->allowAddiFields->groupBy('pivot.allow_addi_fields_id');

        // Get the first item from each group (assuming it's the same for each 'allow_addi_fields_id')
        $distinctAllowAddiFields = $groupedAllowAddiFields->map(function ($group) {
            return $group->first();
        });

        return response()->json([
            'application' => $application,
            'unique_additional_fields' => $distinctAllowAddiFields,
            'image' => $image,
            'signature' => $signature,
            'nominee_image' => $nominee_image,
            'nominee_signature' => $nominee_signature,

        ], Response::HTTP_OK);
    }












}
