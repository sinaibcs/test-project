<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\GrievanceManagement\GrievanceSettingResource;
use App\Http\Resources\Admin\GrievanceManagement\GrievanceSubjectResource;
use App\Http\Services\Admin\GrievanceManagement\GrievanceSettingService;
use App\Http\Traits\MessageTrait;
use App\Models\GrievanceSetting;
use App\Models\GrievanceSubject;
use App\Models\GrievanceType;
use Illuminate\Http\Request;
use App\Helpers\Helper;


class GrievanceSettingController extends Controller
{
    use MessageTrait;
    private $grievanceSetting;
    public function __construct(GrievanceSettingService $GrievanceSettingService)
    {
        $this->grievanceSetting = $GrievanceSettingService;

    }

    public function grievanceSubjectType($id){
           $grievanceSubject = GrievanceSubject::where('grievance_type_id',$id)->where('status', 1)->get();
           return  $grievanceSubject;
 
    }  
    
    public function grievanceSubject($id){
          $grievanceSubject = GrievanceSetting::with('subjects')
           ->where('grievance_type_id', $id)
          ->get(); // Orders the query results by the 'created_at' column in descending order

        return $grievanceSubject;

 
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
        if($status=='active'){
            $grievanceSetting = GrievanceSetting::with('grievanceType','grievanceSubject')->get();
            $uniqueGrievanceTypes = $grievanceSetting->pluck('grievanceType')->unique();

            return  $uniqueGrievanceTypes;
        }
        $grievanceSetting = GrievanceSetting::query()
            ->with(['grievanceType', 'grievanceSubject', 'firstOfficer', 'secoundOfficer', 'thirdOfficer'])
            ->where(function ($query) use ($searchText) {
                $query->where('first_tire_solution_time', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('secound_tire_solution_time', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('third_tire_solution_time', 'LIKE', '%' . $searchText . '%');
            })
            ->orWhereHas('grievanceType', function ($query) use ($searchText) {
                $query->where('title_en', 'LIKE', '%' . $searchText . '%');
            })
            ->orWhereHas('grievanceSubject', function ($query) use ($searchText) {
                $query->where('title_en', 'LIKE', '%' . $searchText . '%');
            })  
             ->orWhereHas('firstOfficer', function ($query) use ($searchText) {
                $query->where('name', 'LIKE', '%' . $searchText . '%');
            })
            // ->orWhereHas('secoundOfficer', function ($query) use ($searchText) {
            //     $query->where('name', 'LIKE', '%' . $searchText . '%');
            // }) 
            // ->orWhereHas('thirdOfficer', function ($query) use ($searchText) {
            //     $query->where('name', 'LIKE', '%' . $searchText . '%');
            // })
            
            ->orderBy('id', 'asc')
            ->latest()
            ->paginate($perPage, ['*'], 'page');
           
        return GrievanceSettingResource::collection($grievanceSetting)->additional([
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
      // Manually check if the grievance_subject_id already exists
         $existingGrievanceSetting = GrievanceSetting::where('grievance_subject_id', $request->grievance_subject_id)->first();
            if ($existingGrievanceSetting) {
                return response()->json(['success' => '201']);
            }

        try {
            $grievanceSetting = $this->grievanceSetting->store($request);
            Helper::activityLogInsert($grievanceSetting, '', 'Grievance Setting', 'Grievance Setting Created !');

            return GrievanceSettingResource::make($grievanceSetting)->additional([
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
            $grievanceSetting = $this->grievanceSetting->edit($id);
            return GrievanceSettingResource::make($grievanceSetting)->additional([
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
        // return  $request->id;
        try {
            $beforeUpdate = GrievanceSetting::find($request->id);
            $grievanceSetting = $this->grievanceSetting->update($request);
            Helper::activityLogUpdate($grievanceSetting, $beforeUpdate, 'Grievance Setting', 'Grievance Setting Updated !');
            return $grievanceSetting;
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
            $grievanceSetting = $this->grievanceSetting->destroy($id);
            Helper::activityLogDelete($grievanceSetting, '', 'Grievance Setting', 'Grievance Setting Deleted !');
            return GrievanceSettingResource::make($grievanceSetting)->additional([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}