<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Office;
use App\Models\OfficeHasWard;
use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    public $divisionId, $districtId, $upazilaId, $cityPouroId;

    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            $this->setDivisionId(auth()->user());
            $this->setDistrictId(auth()->user());
            $this->setUpazilaId(auth()->user());

            return $next($request);
        });
    }


    public function getDivisions()
    {
        $data = Location::whereParentId(null)
            ->when($this->divisionId, function ($q, $v) {
                $q->where('id', $v);
            })->get();

        return $this->sendResponse($data);
    }

    public function getDistricts($divisionId)
    {
        $data = Location::whereParentId($divisionId)
            ->when($this->districtId, function ($q, $v) {
                $q->where('id', $v);
            })->get();

        return $this->sendResponse($data);
    }



    public function getUpazilas($districtId)
    {
        $data = [];
        $officeType = auth()->user()->office_type;

        if ($officeType == 35 || $officeType ==9) {
            return $this->sendResponse($data);
        }


        $data = Location::whereParentId($districtId)
            ->whereType("thana")
            ->whereLocationType(2)
            ->when($this->upazilaId, function ($q, $v) {
                $q->where('id', $v);
            })
            ->get();

        return $this->sendResponse($data);
    }


    public function getCityPouroshavaList($districtId, $locationType)
    {
        $data = [];
        $user = auth()->user();

        // if (in_array($user->office_type, [4,5,6,7,9,35]) || $user->user_type == 1) {
            $data = Location::whereParentId($districtId)
                ->whereType("city")
                ->whereLocationType($locationType)
                // ->when($user->assign_location_id, function ($q, $v) {
                //     $q->where('id', $v);
                // })
                ->get();

            return $this->sendResponse($data);
        // }

        // return $this->sendResponse($data);
    }




    public function getOfficeList(Request $request)
    {
        $query = Office::query();

        $selectedOffices = OfficeHasWard::whereIn('ward_id', auth()->user()
            ->userWards()->pluck('id'))
            ->pluck('office_id');

        $query->whereOfficeType($request->office_type_id)
            ->when($request->location_id, function ($q, $v) {
                $q->where('assign_location_id', $v);
            });

        $query->when(auth()->user()->userWards()->exists(), function ($q) use ($selectedOffices){
            $q->whereIn('id', $selectedOffices);
        });

        return $this->sendResponse($query->get());
    }


    public function getOfficeWardList($officeId)
    {
        $officeWards = [];

        if (auth()->user()->userWards()->exists()) {
             $officeWards = auth()->user()->userWards()->pluck('id');
        }


        $wards = OfficeHasWard::where('office_id', $officeId)
            ->when($officeWards, function ($q, $v) {
                $q->whereIn('ward_id', $v);
            })
            ->pluck('ward_id');

        return $this->sendResponse(Location::whereIn('id', $wards)->with('parent')->get());
    }
    
    public function getOfficeUnionList($officeId)
    {
        $office = Office::find($officeId);
        if($office->office_type == 8){
            $unionIds = $office->wards()->whereNotNull('union_id')->distinct('union_id')->pluck('union_id');
            // $unions = Location::where('parent_id', $office->assign_location_id)->get();
            $unions = Location::whereIn('id', $unionIds)->get();
        }else{
            $unions = [];
        }

        return $this->sendResponse($unions);
    }

    public function setUpazilaId($user)
    {
        $this->upazilaId = match ($user->office_type) {
            8, 10, 11 => $user->assign_location_id,
            default => null
        };
    }


    public function setDistrictId($user)
    {
        $this->districtId = match ($user->office_type) {
            7 => $user->assign_location_id,
            8, 9, 10, 11, 35 => $user->assign_location->parent_id,
            default => null
        };
    }


    public function setDivisionId($user)
    {
        $this->divisionId = match ($user->office_type) {
            6 => $user->assign_location_id,
            7 => $user->assign_location->parent_id,
            8, 9, 10, 11, 35 => $user->assign_location->parent->parent_id,
            default => null
        };
    }

    public function getUnionOrThana($upazilaId,$locationType){
        $data = [];
        $user = auth()->user();

        // if (in_array($user->office_type, [4,5,6,7,9,35]) || $user->user_type == 1) {
            if($locationType == 2){ // if upazila then get union
                $data = Location::whereParentId($upazilaId)
                ->whereType("union")
                // ->whereLocationType($locationType)
                ->when($user->assign_location_id, function ($q, $v) {
                    $q->where('id', $v);
                })->get();
            }else{ // get city corporation wise thana
                $data = Location::whereParentId($upazilaId)
                ->whereType("thana")
                // ->whereLocationType($locationType)
                ->when($user->assign_location_id, function ($q, $v) {
                    $q->where('id', $v);
                })->get();
            // }


            return $this->sendResponse($data);
        }

        return $this->sendResponse($data);
    }

    public function getWards($parentId,$locationTypeId){
       return Location::whereType('ward')->where('parent_id',$parentId)->where('location_type',$locationTypeId)->get();
    }






}
