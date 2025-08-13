<?php

namespace App\Http\Services\Admin\GrievanceManagement;
use App\Http\Traits\RoleTrait;
use App\Models\GrievanceSubject;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;



Class GrievanceSubjectService{
    use RoleTrait;

    public function store($request){

       DB::beginTransaction();
       try {
       $grievanceSubject = new GrievanceSubject();
       $grievanceSubject->title_en = $request->title_en;
       $grievanceSubject->title_bn = $request->title_bn;
       $grievanceSubject->grievance_type_id =$request->grievance_type_id;
       if ($request->status == null) {
        $grievanceSubject->status = '0';
       } else {
         $grievanceSubject->status = $request->status;
       }
       $grievanceSubject->save();
       DB::commit();
       return $grievanceSubject;
       } catch (\Throwable $th) {
           DB::rollBack();
           throw $th;
       }
      
    }

    public function edit($id)
    {
       $grievanceSubject= GrievanceSubject::where('id',$id)->first();
       return $grievanceSubject;
    }

     public function update(Request $request)
    {
     DB::beginTransaction();
       try {
       $grievanceSubject = GrievanceSubject::where('id',$request->id)->first();
       $grievanceSubject->title_en = $request->title_en;
       $grievanceSubject->title_bn = $request->title_bn;
       $grievanceSubject->grievance_type_id = $request->grievance_type_id;
       if ($request->status == null) {
         $grievanceSubject->status = '0';
      } else {
       $grievanceSubject->status = $request->status;
       }

       $grievanceSubject->update();
       DB::commit();
       return $grievanceSubject;
       } catch (\Throwable $th) {
           DB::rollBack();
           throw $th;
       }
    }

    public function destroy($id)
    {
    DB::beginTransaction();
       try {
       $grievanceSubject = GrievanceSubject::where('id',$id)->first();
       $grievanceSubject->delete();
       DB::commit();
       return $grievanceSubject;
       } catch (\Throwable $th) {
           DB::rollBack();
           throw $th;
       }
    }


}