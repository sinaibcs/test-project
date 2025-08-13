<?php

namespace App\Http\Services\Admin\PMTScore;
use App\Models\PMTScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PMTScoreService
{


    /* -------------------------------------------------------------------------- */
    /*                            PMTScore Service                              */
    /* -------------------------------------------------------------------------- */

    public function createPMTScore(Request $request){

        DB::beginTransaction();
        try {

            $PMTScore                         = new PMTScore();
            $PMTScore->type                   = $request->type;
            $PMTScore->location_id             = $request->location_id;
            $PMTScore->score              = $request->score;

            $PMTScore ->save();

            DB::commit();
            return $PMTScore;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function updatePMTScore(Request $request){

        DB::beginTransaction();
        try {

            $PMTScore                         = PMTScore::find($request->id);;
            $PMTScore->type                   = $request->type;
            $PMTScore->location_id             = $request->location_id;
            $PMTScore->score              = $request->score;

            $PMTScore ->save();

            DB::commit();
            return $PMTScore;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }



}
