<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\Admin\GrievanceManagement\GrievacneType;
use App\Http\Resources\Admin\GrievanceManagement\GrievanceTypeResource;
use App\Http\Services\Admin\GrievanceManagement\GrievanceTypeService;
use App\Models\GrievanceType;
use Illuminate\Http\Request;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;


class GrievanceTypeController extends Controller
{
    use MessageTrait;
    private $grievanceType;
    public function __construct(GrievanceTypeService $grievanceTypeService)
    {
        $this->grievanceType = $grievanceTypeService;
    }
    /**
     * Display a listing of the resource.
     */
    public function getAllTypePaginated(Request $request)
    {

        // Retrieve the query parameters
        $searchText = $request->searchText;
        $perPage = $request->perPage;
        $page = $request->page;
        $status = $request->status;
        if ($status == 'active') {
            $grievanceType = GrievanceType::where('status', 1)->get();
            return GrievanceTypeResource::collection($grievanceType)->additional([
                'success' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);
        }

        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayKeyWord = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['title_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['title_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayKeyWord[] = ['status', 'LIKE', '%' . $searchText . '%'];
        }
        $grievanceType = GrievanceType::query()
            ->where(function ($query) use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayKeyWord) {
                $query->where($filterArrayNameEn)
                    ->orWhere($filterArrayNameBn)
                    ->orWhere($filterArrayKeyWord);
            })
            ->orderBy('title_en', 'asc')
            ->latest()
            ->paginate($perPage, ['*'], 'page');

        return GrievanceTypeResource::collection($grievanceType)->additional([
            'success' => true,
            'message' => $this->fetchDataSuccessMessage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GrievacneType $request)
    {
        try {
            $grievanceType = $this->grievanceType->store($request);
            Helper::activityLogInsert($grievanceType, '', 'Grievance Type', 'Grievance Type Created !');
            return GrievanceTypeResource::make($grievanceType)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GrievanceType $grievanceType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $grievanceType = $this->grievanceType->edit($id);
            return GrievanceTypeResource::make($grievanceType)->additional([
                'sucess' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GrievacneType $request)
    {
        try {
            $beforeUpdate = GrievanceType::find($request->id);
            $grievanceType = $this->grievanceType->update($request);
            Helper::activityLogUpdate($grievanceType, $beforeUpdate, 'Grievance Type', 'Grievance Type Updated !');

            return GrievanceTypeResource::make($grievanceType)->additional([
                'sucess' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $grievanceType = $this->grievanceType->destroy($id);
            Helper::activityLogDelete($grievanceType, '', 'Grievance Type', 'Grievance Type Deleted !');
            return GrievanceTypeResource::make($grievanceType)->additional([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}