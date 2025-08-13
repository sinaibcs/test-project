<?php

namespace App\Http\Services\Mobile\PMTScore;

use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VariableService
{


    /* -------------------------------------------------------------------------- */
    /*                            Variable Service                              */
    /* -------------------------------------------------------------------------- */

    public function createVariable(Request $request)
    {

        DB::beginTransaction();
        try {

            $Variable                         = new Variable();
            $Variable->name_en                = $request->name_en;
            $Variable->name_bn                = $request->name_bn;
            $Variable->score                  = $request->score;
            $Variable->field_type             = $request->field_type;

            $Variable->save();
            $sub_variable=$request->field_value;
            if($sub_variable){

                foreach($sub_variable as $item){
                $sub = new Variable();
                $sub->parent_id= $Variable->id;
                $sub->name_en= $item['value_en'];
                $sub->name_bn= $item['value_bn'];
                $sub->score= $item['score'];
                $sub->save();
            }
            }


            DB::commit();
            return $Variable;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateVariable(Request $request)
    {

        DB::beginTransaction();
        try {

            $Variable                         = Variable::find($request->id);;
            $Variable->name_en                = $request->name_en;
            $Variable->name_bn                = $request->name_bn;
            $Variable->score                  = $request->score;
            $Variable->field_type             = $request->field_type;

           $israt=Variable::where('parent_id',"=",$request->id)->forceDelete();

            $sub_variable=$request->field_value;

            if($sub_variable){
                $Variable->score                  = NULL;

                foreach($sub_variable as $item){
                $sub = new Variable();
                $sub->parent_id= $Variable->id;
                $sub->name_en= $item['value_en'];
                $sub->name_bn= $item['value_bn'];
                $sub->score= $item['score'];
                $sub->save();
            }
            }
             $Variable->save();

            DB::commit();
            return $Variable;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                            Sub - Variable Service                              */
    /* -------------------------------------------------------------------------- */

    public function createSubVariable(Request $request)
    {

        DB::beginTransaction();
        try {

            $Variable                         = new Variable();
            $Variable->name_en                   = $request->name_en;
            $Variable->parent_id                   = $request->variable_id;
            $Variable->score              = $request->score;

            $Variable->save();

            DB::commit();
            return $Variable;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateSubVariable(Request $request)
    {

        DB::beginTransaction();
        try {

            $Variable                         = Variable::find($request->id);;
            $Variable->name_en                   = $request->name_en;
            $Variable->parent_id                   = $request->variable_id;
            $Variable->score              = $request->score;

            $Variable->save();

            DB::commit();
            return $Variable;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
