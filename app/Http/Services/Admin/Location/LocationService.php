<?php

namespace App\Http\Services\Admin\Location;

use App\Http\Traits\LocationTrait;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationService
{
    use LocationTrait;
    /**
     * Create a new division in the database.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function createDivision(Request $request){

        DB::beginTransaction();
        try {
            $maxId = DB::table('locations')->max('id');
            $newId = $maxId + 1;

            $location                       = new Location;
            $location->id                   = $newId;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->type                   = $this->division;
            $location->created_by                   = Auth()->user()->id;
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * Update the division record in the database.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function updateDivision(Request $request){

        DB::beginTransaction();
        try {

            $location                       = Location::find($request->id);
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->version                 = $location->version+1;
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /* -------------------------------------------------------------------------- */
    /*                              District Service                              */
    /* -------------------------------------------------------------------------- */


    /**
     * Create a new district location based on the given request.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function createDistrict(Request $request){

        DB::beginTransaction();
        try {
            $maxId = DB::table('locations')->max('id');
            $newId = $maxId + 1;

            $location                         = new Location;
            $location->id                     = $newId;
            $location->parent_id              = $request->division_id;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->type                   = $this->district;
            $location->created_by             = Auth()->user()->id;
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Update the district information in the database.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function updateDistrict(Request $request){

        DB::beginTransaction();
        try {

            $location                       = Location::find($request->id);
            $location->name_en                = $request->name_en;
            $location->parent_id              = $request->division_id;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->version                  = $location->version+1;

            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /* -------------------------------------------------------------------------- */
    /*                                City Services                               */
    /* -------------------------------------------------------------------------- */
    /**
     * Create a new city in the database.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function createCity(Request $request){

        DB::beginTransaction();
        try {
            $maxId = DB::table('locations')->max('id');
            $newId = $maxId + 1;

            $location                         = new Location;
            $location->id                     = $newId;
            $location->parent_id              = $request->district_id;
            $location->location_type          = $request->location_type;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->type                   = $this->city;
            // $location->type                   = $request->location_type==3?$this->city:$this->districtPouroshava;
            $location->created_by             = Auth()->user()->id;
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * Update the city information in the database.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function updateCity(Request $request){

        DB::beginTransaction();
        try {

            $location                       = Location::find($request->id);
            $location->parent_id              = $request->district_id;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->version                = $location->version+1;

            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /* -------------------------------------------------------------------------- */
    /*                               Thana Services                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Create a new Thana (location) based on the given request data.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function createThana(Request $request){

        DB::beginTransaction();
        try {

            $location                         = new Location;
            $location->parent_id              = $request->has('city_corporation_id')?$request->city_corporation_id:$request->district_id;
            $location->location_type          = $request->location_type;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->type                   = $this->thana;
            $location->created_by             = Auth()->user()->id;
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateThana(Request $request){

        DB::beginTransaction();
        try {

            $location                       = Location::find($request->id);
            $location->parent_id              = $request->district_id;
            if ($request->location_type == 2 ) {
                $location->type =  $this->thana;
            }
            $location->location_type          = $request->location_type;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->version                = $location->version+1;

            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /* -------------------------------------------------------------------------- */
    /*                               Union Services                               */
    /* -------------------------------------------------------------------------- */


    /**
     * Create a new union location based on the given request.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function createUnion(Request $request){

        DB::beginTransaction();
        try {
            $maxId = DB::table('locations')->max('id');
            $newId = $maxId + 1;

            $location                         = new Location;
            $location->id                     = $newId;
            $location->parent_id              = $request->thana_id;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;

            //Pouro
            if ($request->location_type == 1) {
                $location->type                   = $this->pouro;
                $location->location_type                   = 2;
            }

            //City
            if ($request->location_type == 2) {
                $location->type                   = $this->thana;
                $location->location_type                   = 3;
            }
            //Thana

            if ($request->location_type == 3) {
                $location->type                   = $this->union;
                $location->location_type                   = 2;
            }

            $location->created_by             = Auth()->user()->id;
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Update the union location with the given request data.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function updateUnion(Request $request){

        DB::beginTransaction();
        try {

            $location                       = Location::find($request->id);
            $location->parent_id              = $request->thana_id;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->version                = $location->version+1;

            //Pouro
            if ($request->location_type == 1) {
                $location->type                   = $this->pouro;
                $location->location_type                   = 2;
            }

            //Thana
            if ($request->location_type == 2) {
                // $location->parent_id              = $request->city_id;
                $location->type                   = $this->thana;
                $location->location_type                   = 3;
            }
            //Union

            if ($request->location_type == 3) {
                $location->type                   = $this->union;
                $location->location_type                   = 2;
            }
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                               Ward Services                               */
    /* -------------------------------------------------------------------------- */


    /**
     * Create a new ward location based on the given request.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function createWard(Request $request){

        DB::beginTransaction();
        try {
            $maxId = DB::table('locations')->max('id');
            $newId = $maxId + 1;

            $location                         = new Location;
            if($request->has('city_thana_id')){
                $location->parent_id              = $request->city_thana_id;
            }
            if($request->has('district_pouro_id')){
                $location->parent_id              = $request->district_pouro_id;
            }
            if($request->has('union_id')){
                $location->parent_id              = $request->union_id;
            }
            if($request->has('pouro_id')){
                $location->parent_id              = $request->pouro_id;
            }
            $location->id = $newId;
            $location->location_type          = $request->location_type;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->type                   = $this->ward;
            $location->version                = 1;
            $location->created_by             = Auth()->user()->id;
            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * Update the ward information in the database.
     *
     * @param  Request  $request
     * @return Location
     * @throws \Throwable
     */
    public function updateWard(Request $request){

        DB::beginTransaction();
        try {

            $location                       = Location::find($request->id);
            if($request->has('city_thana_id')){
            $location->parent_id              = $request->city_thana_id;
            }
            if($request->has('district_pouro_id')){
            $location->parent_id              = $request->district_pouro_id;
            }
            if($request->has('union_id')){
            $location->parent_id              = $request->union_id;
            }
            $location->location_type          = $request->location_type;
            $location->name_en                = $request->name_en;
            $location->name_bn                = $request->name_bn;
            $location->code                   = $request->code;
            $location->version                = $location->version+1;

            $location->save();
            DB::commit();
            return $location;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

}
