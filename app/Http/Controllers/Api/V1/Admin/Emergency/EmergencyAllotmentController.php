<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Emergency\EmergencyAllotmentRequest;
use App\Http\Resources\Admin\Emergency\EmergencyAllotmentResource;
use App\Http\Services\Admin\Emergency\EmergencyAllotmentService;
use App\Http\Traits\MessageTrait;
use App\Models\AllowanceProgram;
use App\Models\EmergencyAllotment;
use App\Models\User;
use Illuminate\Http\Request;

class EmergencyAllotmentController extends Controller
{
    use MessageTrait;

    protected EmergencyAllotmentService $emergencyAllotmentService;

    public function __construct(EmergencyAllotmentService $emergencyAllotmentService)
    {
        $this->emergencyAllotmentService = $emergencyAllotmentService;
    }

    public function getEmergencyAllotments(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $allotments = $this->emergencyAllotmentService->getListData($request);

        return EmergencyAllotmentResource::collection($allotments)->additional([
            'success' => true,
            'message' => $this->fetchDataSuccessMessage,
        ]);
    }

    public function getAllotmentWiseProgram(Request $request, $allotment_id): array
    {
        $data = array();
        $emergencyAllotments = EmergencyAllotment::with('programs')->where('id', $allotment_id)->get();
        foreach ($emergencyAllotments as $emergencyAllotment) {
            $programs = $emergencyAllotment->programs;
            foreach ($programs as $program) {
                $data[] = [
                    'id' => $program->id,
                    'name_en' => $program->name_en,
                    'name_bn' => $program->name_bn,
                ];
            }
        }
        return $data;
    }

    public function getUserWisePrograms(Request $request): \Illuminate\Database\Eloquent\Collection|array|\Illuminate\Support\Collection
    {
        $data = array();
        $userInfo = auth()->user();
        $user = User::find($userInfo->id); // $userId is the ID of the user you want to fetch
        if ($user->user_type == 1) {
            $data = AllowanceProgram::get();
        } else {
            $programs = $user->getPrograms();
            if (!empty($programs)) {
                $data = AllowanceProgram::whereIn('id', $programs)->get();
            }
        }
        return $data;
    }

    public function getAllAllotmentPrograms(Request $request): array
    {
        $data = array();
        $emergencyAllotments = EmergencyAllotment::with('programs')->get();
        foreach ($emergencyAllotments as $emergencyAllotment) {
            $programs = $emergencyAllotment->programs;
            foreach ($programs as $program) {
                $data[] = [
                    'id' => $program->id,
                    'name_en' => $program->name_en,
                    'name_bn' => $program->name_bn,
                ];
            }
        }
        return $data;
    }


    public function store(EmergencyAllotmentRequest $request)
    {
        try {
            $allotment = $this->emergencyAllotmentService->store($request);
            Helper::activityLogInsert($allotment, '', 'Emergency Allotment', 'Emergency Allotment Created !');
            return EmergencyAllotmentResource::make($allotment)->additional([
                'success' => true,
                'message' => "Emergency Allotment Created Successfully",
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function edit($id)
    {

        try {
            $allotment = $this->emergencyAllotmentService->edit($id);
            return EmergencyAllotmentResource::make($allotment)->additional([
                'success' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function update(EmergencyAllotmentRequest $request, $id)
    {
        try {
            $beforeUpdate = EmergencyAllotment::find($id);
            $allotment = $this->emergencyAllotmentService->update($request, $id);
            Helper::activityLogUpdate($allotment, $beforeUpdate, 'Emergency Allotment', 'Emergency Allotment Updated !');

            return EmergencyAllotmentResource::make($allotment)->additional([
                'success' => true,
                'message' => "Emergency Allotment Updated Successfully",
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->emergencyAllotmentService->destroy($id);
            Helper::activityLogDelete($data, '', 'Emergency Allotment', 'Emergency Allotment Deleted !');
            return handleResponse($data, "Emergency Allotment Deleted Successfully");
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }

    }

    public function getFinancialId(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->emergencyAllotmentService->getFinancialId($request->start_date, $request->end_date);
            return handleResponse($data, "Data Fetch Successfully");
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }

    }
}
