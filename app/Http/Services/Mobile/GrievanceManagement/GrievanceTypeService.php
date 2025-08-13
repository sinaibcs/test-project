<?php

namespace App\Http\Services\Mobile\GrievanceManagement;
use App\Http\Requests\Admin\GrievanceManagement\GrievacneType;
use App\Http\Traits\RoleTrait;
use App\Models\GrievanceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;



Class GrievanceTypeService{
    use RoleTrait;

//    public function getAll()
//     {
//        $grievanceType= GrievanceType::where('status',1)->get();
//        return $grievanceType;
//     }

    public function store($request){
        // dd($request->status);
       DB::beginTransaction();
       try {
       $grievanceType = new GrievanceType();
       $grievanceType->title_en = $request->title_en;
       $grievanceType->title_bn = $request->title_bn;
       if($request->status==null){
         $grievanceType->status='0';
       }else{
        $grievanceType->status = $request->status;
       }
       $grievanceType->save();
       DB::commit();
       return $grievanceType;
       } catch (\Throwable $th) {
           DB::rollBack();
           throw $th;
       }

    }

    public function edit($id)
    {
       $grievanceType= GrievanceType::where('id',$id)->first();
       return $grievanceType;
    }

     public function update(Request $request)
    {
     DB::beginTransaction();
       try {
       $grievanceType = GrievanceType::where('id',$request->id)->first();
       $grievanceType->title_en = $request->title_en;
       $grievanceType->title_bn = $request->title_bn;
        if ($request->status == null) {
       $grievanceType->status = '0';
       } else {
       $grievanceType->status = $request->status;
       }

       $grievanceType->update();
       DB::commit();
       return $grievanceType;
       } catch (\Throwable $th) {
           DB::rollBack();
           throw $th;
       }
    }

    public function destroy($id)
    {
    DB::beginTransaction();
       try {
       $grievanceType = GrievanceType::where('id',$id)->first();
       $grievanceType->delete();
       DB::commit();
       return $grievanceType;
       } catch (\Throwable $th) {
           DB::rollBack();
           throw $th;
       }
    }


}
