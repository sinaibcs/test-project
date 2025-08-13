<?php

namespace App\Http\Services\Mobile\GlobalSetting;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GlobalSettingService
{

        public function createGlobalSetting(Request $request){

        DB::beginTransaction();
        try {

            $globalsetting                     = new GlobalSetting;
            $globalsetting->area_type          = $request->area_type;
            $globalsetting->value              = $request->value;
            $globalsetting->save();
            DB::commit();
            return $globalsetting;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


      public function updateGlobalSetting(Request $request){

        DB::beginTransaction();
        try {

            $globalsetting                     = GlobalSetting::find($request->id);
            $globalsetting->area_type          = $request->area_type;
            $globalsetting->value              = $request->value;
            $globalsetting->save();
            DB::commit();
            return $globalsetting;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
