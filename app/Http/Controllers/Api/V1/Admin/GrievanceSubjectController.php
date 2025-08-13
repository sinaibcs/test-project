<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\GrievanceManagement\GrievanceSubjectResource;
use App\Http\Services\Admin\GrievanceManagement\GrievanceSubjectService;
use App\Http\Traits\MessageTrait;
use App\Models\GrievanceSubject;
use App\Models\GrievanceType;
use Illuminate\Http\Request;
use App\Helpers\Helper;


class GrievanceSubjectController extends Controller
{
    use MessageTrait;
    private $grievanceSubject;
    public function __construct(GrievanceSubjectService $GrievanceSubjectService)
    {
        $this->grievanceSubject = $GrievanceSubjectService;

    }
    /**
     * Display a listing of the resource.
     */
    public function getAll(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');
        $status = $request->query('status');

        if ($status == 'active') {
            $grievanceType = GrievanceSubject::where('status', 1)->get();
            return GrievanceSubjectResource::collection($grievanceType)->additional([
                'success' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);

        }

        $filterArrayTitleEn = [];
        $filterArrayTitileBn = [];
        $filterArrayKeyStatus = [];

        if ($searchText) {
            $filterArrayTitleEn[] = ['title_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayTitileBn[] = ['title_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayKeyStatus[] = ['status', 'LIKE', '%' . $searchText . '%'];
            // $filterArrayKeyWord[] = ['grievanceType', 'LIKE', '%' . $searchText . '%'];
        }
        $grievanceSubject = GrievanceSubject::query()
            ->with('grievanceType')
            ->where(function ($query) use ($filterArrayTitleEn, $filterArrayTitileBn, $filterArrayKeyStatus) {
                $query->where($filterArrayTitleEn)
                    ->orWhere($filterArrayTitileBn)
                    ->orWhere($filterArrayKeyStatus);
            })
            ->orderBy('title_en', 'asc')
            ->latest()
            ->paginate($perPage, ['*'], 'page');
        //    dd($grievanceSubject);

        return GrievanceSubjectResource::collection($grievanceSubject)->additional([
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
    public function store(Request $request)
    {

        try {
            $grievanceSubject = $this->grievanceSubject->store($request);
            Helper::activityLogInsert($grievanceSubject, '', 'Grievance Subject', 'Grievance Subject Created !');
            return GrievanceSubjectResource::make($grievanceSubject)->additional([
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
            $grievanceSubject = $this->grievanceSubject->edit($id);
            return GrievanceSubjectResource::make($grievanceSubject)->additional([
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
    public function update(Request $request)
    {
        try {
            $beforeUpdate = GrievanceSubject::find($request->id);
            $grievanceSubject = $this->grievanceSubject->update($request);
            Helper::activityLogUpdate($grievanceSubject, $beforeUpdate, 'Grievance Subject', 'Grievance Subject Updated !');
            return GrievanceSubjectResource::make($grievanceSubject)->additional([
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
            $grievanceSubject = $this->grievanceSubject->destroy($id);
            Helper::activityLogDelete($grievanceSubject, '', 'Grievance Subejct', 'Grievance Subejct Deleted !');
            return GrievanceSubjectResource::make($grievanceSubject)->additional([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}