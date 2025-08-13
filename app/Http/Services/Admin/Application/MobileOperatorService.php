<?php

namespace App\Http\Services\Admin\Application;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\MobileOperator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MobileOperatorService
{

     public function createMobileOperator(Request $request){

        DB::beginTransaction();
        try {

            $mobile                       = new MobileOperator;
            $mobile->operator             = $request->operator;
            $mobile->save();
            DB::commit();
            return $mobile;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function updateMobileOperator(Request $request){

        DB::beginTransaction();
        try {

            $mobile                       = MobileOperator::find($request->id);
            $mobile->operator             = $request->operator;
            $mobile->save();
            DB::commit();
            return $mobile;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    
}
