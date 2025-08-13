<?php

namespace App\Http\Services\Admin\Systemconfig;

use App\Helpers\Helper;
use App\Models\Office;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Http\Traits\OfficeTrait;
use App\Models\AllowanceAdditionalField;
use App\Models\AllowanceAdditionalFieldValue;
use App\Models\AllowanceProgramAdditionalField;
use App\Models\AllowanceProgram;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Schema;

class SystemconfigService
{


    /* -------------------------------------------------------------------------- */
    /*                              Allowance Additional Field Service                              */
    /* -------------------------------------------------------------------------- */


    public function createAllowanceAdditionalField(Request $request)
    {
        // print_r($request->all());
        DB::beginTransaction();
        try {
            $allowanceAdditionalField                         = new AllowanceAdditionalField;
            $allowanceAdditionalField->name_en                = $request->name_en;
            $allowanceAdditionalField->name_bn                = $request->name_bn;
            $allowanceAdditionalField->type                   = $request->type;
            $allowanceAdditionalField->option                 = $request->option;
            $allowanceAdditionalField->save();
            $allowanceAdditionalField->preAssignedPrograms()->sync($request->program_ids);
            DB::commit();
            return $allowanceAdditionalField;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateAllowanceAdditionalField($request)
    {
        // print_r($request->all());
        DB::beginTransaction();
    

      
        try {
            $allowanceAdditionalField                         = AllowanceAdditionalField::find($request->id);
            $allowanceAdditionalField->name_en                = $request->name_en;
            $allowanceAdditionalField->name_bn                = $request->name_bn;
            $allowanceAdditionalField->type                   = $request->type;
            $allowanceAdditionalField->option                 = $request->option;
            $allowanceAdditionalField->save();
            $allowanceAdditionalField->preAssignedPrograms()->sync($request->program_ids);
        if ($request->has('field_value') && is_array($request->field_value) && count($request->field_value) > 0) {
                       
                   
            
            $input = $request->field_value;
           
            AllowanceAdditionalFieldValue::where('additional_field_id', $request->id)->delete();
            
       
            for ($i = 0; $i < count($input); $i++) {
                // print_r($input[$i]['value_en']);
            $field_value = new AllowanceAdditionalFieldValue;
            $field_value->additional_field_id = $request->id;
            $field_value->value_en = $input[$i]['value_en'];
            $field_value->value_bn = $input[$i]['value_bn'];
            $field_value->save();
            
       } 


}
         else if ($request->date) {
    AllowanceAdditionalFieldValue::where('additional_field_id', $request->id)->delete();
    $field_value = new AllowanceAdditionalFieldValue;
    $field_value->additional_field_id = $request->id;
    $field_value->value_en = $request->date;
     $field_value->save();

 }
        else if ($request->text) {
    AllowanceAdditionalFieldValue::where('additional_field_id', $request->id)->delete();
    $field_value = new AllowanceAdditionalFieldValue;
    $field_value->additional_field_id = $request->id;
    $field_value->value = $request->text;
     $field_value->save();

 }
         else if ($request->number) {
    AllowanceAdditionalFieldValue::where('additional_field_id', $request->id)->delete();
    $field_value = new AllowanceAdditionalFieldValue;
    $field_value->additional_field_id = $request->id;
    $field_value->value = $request->number;
     $field_value->save();

 }

            DB::commit();
            return $allowanceAdditionalField;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                              Allowance Service                              */
    /* -------------------------------------------------------------------------- */

    public function createallowance(Request $request)
    {

        DB::beginTransaction();
        try {

            $allowance                         = new AllowanceProgram;
            $allowance->name_en                = $request->name_en;
            $allowance->name_bn                = $request->name_bn;
            $allowance->guideline              = $request->guideline;
            $allowance->description            = $request->description;
            $allowance->service_type           = $request->service_type;
            $allowance->version                = $allowance->version + 1;
            $allowance->save();
            DB::commit();
            return $allowance;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateallowance(Request $request)
    {

        DB::beginTransaction();
        try {
            $allowance                         = AllowanceProgram::find($request->id);
            $allowance->name_en                = $request->name_en;
            $allowance->name_bn                = $request->name_bn;
            $allowance->guideline              = $request->guideline;
            $allowance->description            = $request->description;
            $allowance->service_type           = $request->service_type;

            $allowance->version                = $allowance->version + 1;
            $allowance->save();
            DB::commit();
            return $allowance;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



    /* -------------------------------------------------------------------------- */
    /*                               financial Year                               */
    /* -------------------------------------------------------------------------- */
    public function createFinancialYear(Request $request)
    {
        $financialYear = $request->financial_year;
        $financialYearArray = explode('-', $financialYear);
        $seventhMonth = 7;
        $sixthMonth = 6;
        $startDate = Carbon::create($financialYearArray[0], $seventhMonth, 1);
        $lastDate = Carbon::create($financialYearArray[1], $sixthMonth + 1, 1)->subDay();
        DB::beginTransaction();
        try {
            $financial                         = new FinancialYear;
            $financial->financial_year         = $financialYear;
            $financial->start_date             = $startDate;
            $financial->end_date               = $lastDate;
            $financial->status                  = Helper::FinancialYear() == $financialYear ? true : false;
            $financial->version                = $financial->version + 1;
            $financial->save();
            DB::commit();
            return $financial;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}