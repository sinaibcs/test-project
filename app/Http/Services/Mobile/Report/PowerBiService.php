<?php

namespace App\Http\Services\Mobile\Report;

use App\Helpers\Helper;
use App\Models\PowerBiReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PowerBiService
{
    public function createinfo(Request $request){

        DB::beginTransaction();
        try {
            $data                       = new PowerBiReport();
            $data->name_en                = $request->name_en;
            $data->name_bn                = $request->name_bn;
            $data->embaded_code           = $request->embaded_code;
            if ($request->image) {
                $data->image = $request->file('image')->store('public');
            }
            $data->save();
            DB::commit();
            Helper::activityLogInsert($data, '','Power BI','Power BI Report Created !');
            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateInfo(Request $request){

        DB::beginTransaction();
        try {
            $previousValues = PowerBiReport::find($request->id);
            $data                       = PowerBiReport::find($request->id);
            $data->name_en                = $request->name_en;
            $data->name_bn                = $request->name_bn;
            $data->embaded_code           = $request->embaded_code;
            if ($request->hasFile('image')) {
                if ($data->image && Storage::exists($data->image)) {
                    Storage::delete($data->image);
                }
                $data->image = $request->file('image')->store('public');
            }
            $data->save();
            DB::commit();
            Helper::activityLogUpdate($data, $previousValues,'Power BI','Power BI Report Updated !');
            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
