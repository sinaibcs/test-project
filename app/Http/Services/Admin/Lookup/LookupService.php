<?php

namespace App\Http\Services\Admin\Lookup;

use App\Http\Traits\LookupTrait;
use App\Models\Lookup;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Response;

class LookupService
{
    use LookupTrait;

    /* -------------------------------------------------------------------------- */
    /*                              Lookup Service                              */
    /* -------------------------------------------------------------------------- */

    public function createLookup(Request $request){
        $lookupType = LookupTrait::getLookUpTypes()->where('id', $request->type)->first();
        if(!$lookupType){
            throw new \Exception('Error: lookup type not found');

        }
        DB::beginTransaction();
        try {

            $lookup                         = new Lookup;
            $lookup->type                   = $request->type;
            $lookup->value_en               = $request->value_en;
            $lookup->value_bn               = $request->value_bn;
            $lookup->keyword                = $request->keyword;
            $lookup ->save();
            DB::commit();
            return $lookup;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

    }

    public function updateLookup(Request $request){
        $lookupType = LookupTrait::getLookUpTypes()->where('id', $request->type)->first();
        if(!$lookupType){
            throw new \Exception('Error: lookup type not found');

        }
        DB::beginTransaction();
        try {
            $lookup                         = Lookup::find($request->id);
            $lookup->type                   = $request->type;
            $lookup->value_en               = $request->value_en;
            $lookup->value_bn               = $request->value_bn;
            $lookup->keyword                = $request->keyword;

            $lookup->version                = $lookup->version+1;
            $lookup->save();
            DB::commit();
            return $lookup;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


}
