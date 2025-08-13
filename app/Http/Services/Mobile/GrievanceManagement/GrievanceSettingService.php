<?php

namespace App\Http\Services\Mobile\GrievanceManagement;

use App\Http\Traits\RoleTrait;
use App\Models\GrievanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrievanceSettingService
{
    use RoleTrait;

    public function store($request)
    {


        DB::beginTransaction();
        try {
            $grievanceSettings = new GrievanceSetting();
            $grievanceSettings->grievance_type_id = $request->grievance_type_id;
            $grievanceSettings->grievance_subject_id = $request->grievance_subject_id;
            $grievanceSettings->first_tire_officer = $request['OfficerForm'][0]['first_tire_officer'];
            $grievanceSettings->first_tire_solution_time = $request['OfficerForm'][0]['first_tire_solution_time'];
            if (isset($request['OfficerForm'][1])) {
                $grievanceSettings->secound_tire_officer = $request['OfficerForm'][1]['first_tire_officer'];
                $grievanceSettings->secound_tire_solution_time = $request['OfficerForm'][1]['first_tire_solution_time'];
            }
            if (isset($request['OfficerForm'][2])) {
                $grievanceSettings->third_tire_officer = $request['OfficerForm'][2]['first_tire_officer'];
                $grievanceSettings->third_tire_solution_time = $request['OfficerForm'][2]['first_tire_solution_time'];
            }
            $grievanceSettings->save();
            DB::commit();
            return $grievanceSettings;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function edit($id)
    {
        $grievanceSubject = GrievanceSetting::where('id', $id)->first();
        return $grievanceSubject;
    }

    public function update(Request $request)
    {
        // return $request->all();
        DB::beginTransaction();
        try {
            $grievanceSetting = GrievanceSetting::where('id', $request->id)->first(); // Use first() instead of get()
            if (!$grievanceSetting) {
                throw new \Exception("Grievance Setting not found for the given ID.");
            }

            $grievanceSetting->grievance_type_id = $request->grievance_type_id;
            $grievanceSetting->grievance_subject_id = $request->grievance_subject_id;
            $grievanceSetting->first_tire_officer = $request['OfficerForm'][0]['first_tire_officer'];
            $grievanceSetting->first_tire_solution_time = $request['OfficerForm'][0]['first_tire_solution_time'];

            if (isset($request['OfficerForm'][1])) {
                $grievanceSetting->secound_tire_officer = $request['OfficerForm'][1]['first_tire_officer'];
                $grievanceSetting->secound_tire_solution_time = $request['OfficerForm'][1]['first_tire_solution_time'];
            }else{
               $grievanceSetting->secound_tire_officer = null;
               $grievanceSetting->secound_tire_solution_time = null;

            }

            if (isset($request['OfficerForm'][2])) {
                $grievanceSetting->third_tire_officer = $request['OfficerForm'][2]['first_tire_officer'];
                $grievanceSetting->third_tire_solution_time = $request['OfficerForm'][2]['first_tire_solution_time'];
            }else{
              $grievanceSetting->third_tire_officer =null;
              $grievanceSetting->third_tire_solution_time = null;

            }

            $grievanceSetting->save(); // Use save() to update the record

            DB::commit();
            return $grievanceSetting;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $grievanceSubject = GrievanceSetting::where('id', $id)->first();
            $grievanceSubject->delete();
            DB::commit();
            return $grievanceSubject;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

}
