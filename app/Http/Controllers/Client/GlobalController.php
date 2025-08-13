<?php

namespace App\Http\Controllers\Client;

use App\Constants\ApiKey;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Beneficiary\AccountRequest;
use App\Http\Requests\Client\Beneficiary\GetListRequest;
use App\Http\Requests\Client\Beneficiary\NomineeRequest;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryResource;
use App\Http\Resources\Admin\Geographic\DistrictResource;
use App\Http\Resources\Admin\Geographic\DivisionResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Http\Services\Client\ApiService;
use App\Http\Services\Client\BeneficiaryService;
use App\Http\Traits\LocationTrait;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\UserTrait;
use App\Models\AllowanceProgram;
use App\Models\ApiDataReceive;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GlobalController extends Controller
{
    use MessageTrait, UserTrait, LocationTrait;


    /**
     * Program list
     *
     *Fetches list of all active allowance programs along with associated additional fields.
     * @return AnonymousResourceCollection
     */
    public function getAllProgram()
    {
        $data = AllowanceProgram::where('is_active',1)->with('lookup','addtionalfield.additional_field_value')->get();

        return AllowanceResource::collection($data)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    /**
     * Division list
     *
     * Fetches list of all divisions.
     * @return AnonymousResourceCollection
     */
    public function getAllDivision()
    {
        $division = Location::query()
            ->whereParentId(null)
            ->get();

        return DivisionResource::collection($division)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    /**
     * District list
     *
     * Fetches list of districts belongs to a specific division.
     *
     * @param  int  $division_id The ID of the division for which districts are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved districts.
     */
    public function getAllDistrictByDivisionId($division_id)
    {
        $district = Location::whereParentId($division_id)->whereType($this->district)->get();

        return DistrictResource::collection($district)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }



    /**
     * City corporation list
     *
     * Fetches list of city corporation belonging to a specified district.
     *
     * @param  int  $district_id The ID of the district for which cities are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved cities.
     */
    public function getAllCityByDistrictId($district_id)
    {
        $cities = Location::whereParentId($district_id)
            ->whereType($this->city)
            ->whereLocationType(3)
            ->get();

        return DistrictResource::collection($cities)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }



    /**
     * Upazila list
     *
     * Fetches list of upazilas belongs to a specific district.
     *
     * @param  int  $district_id The ID of the district for which upazilas are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved districts.
     */
    public function getAllThanaByDistrictId($district_id)
    {
        $thanas = Location::whereParentId($district_id)
            ->whereType($this->thana)
            ->whereLocationType(2)
            ->get();

        return DistrictResource::collection($thanas)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    /**
     * District pourashava list
     *
     * Fetches list of District pourashavas belongs to a specific district.
     *
     * @param  int  $district_id The ID of the district for which district pourashava are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved districts.
     */
    public function getAllDistrictPourashavaByDistrictId($district_id)
    {
        $cities = Location::whereParentId($district_id)
            ->whereType($this->city)
            ->whereLocationType(1)->get();

        return DistrictResource::collection($cities)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }





    /**
     * Union list
     *
     * Fetches list of unions belongs to a specific upazila.
     *
     * @param  int  $upazila_id The ID of the upazila for which unions are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved districts.
     */
    public function getAllUnionByThanaId($upazila_id)
    {
        $unions = Location::whereParentId($upazila_id)->whereType($this->union)->get();

        return DistrictResource::collection($unions)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    /**
     * Pourashava list
     *
     * Fetches list of pourashava belongs to a specific upazila.
     *
     * @param  int  $upazila_id The ID of the upazila for which pourashavas are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved districts.
     */
    public function getAllPouroByThanaId($upazila_id)
    {
        $pouros = Location::whereParentId($upazila_id)->whereType($this->pouro)->get();

        return DistrictResource::collection($pouros)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }










    /**
     * Thana list
     *
     * Fetches list of all thanas belonging to a specified city corporation.
     *
     * @param  int  $city_id The ID of the city corporation for which thanas are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved thanas.
     */
    public function getAllThanaByCityId($city_id)
    {
        $thanas = Location::whereParentId($city_id)->whereType($this->thana)->whereLocationType(3)->get();
        return DistrictResource::collection($thanas)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }





    /**
     * Ward list
     *
     * Fetches list of all wards belonging to a specific location.
     *
     * @param  int  $city_id The ID of the city corporation for which thanas are retrieved.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection Returns a collection of DistrictResource objects representing the retrieved thanas.
     */
    public function getAllWardByUnionId($location_id)
    {
        $wards = Location::whereParentId($location_id)->whereType($this->ward)->get();

        return DistrictResource::collection($wards)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


}
