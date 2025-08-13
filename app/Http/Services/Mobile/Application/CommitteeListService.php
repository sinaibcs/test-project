<?php

namespace App\Http\Services\Mobile\Application;




use App\Models\Committee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommitteeListService
{

    /**
     * @param Builder $query
     * @param User $user
     * @return mixed
     */
    public function applyCommitteeListFilter($query, $user)
    {
        if ($user->office_type) {
            return match ($user->office_type) {
                8, 10, 11 => $this->forUpazilaOffice($user, $query),
                9 => $this->forCityCorporationOffice($user, $query),
                35 => $this->forDistrictPouroshavaOffice($user, $query),

                //Exclude all
                default => $query->whereNull('committees.id')
            };
        }


        if ($user->committee_type_id) {
            return match ($user->committee_type_id) {
                12 => $this->forUnionCommittee($user, $query),
                13 => $this->forWardCommittee($user, $query),

                //Exclude all
                default => $query->whereNull('committees.id')
            };
        }




    }


    //get ward committees under city id
    public function forCityCorporationOffice($user, $query)
    {
        $cityCorpId = $user->assign_location_id;

        $thanasId = Location::whereParentId($cityCorpId)->pluck('id');

        return $query->whereIn('locations.parent_id', $thanasId);
    }



    //get ward committees under dis pouro
    public function forDistrictPouroshavaOffice($user, $query)
    {
        $distPouroId = $user->assign_location_id;

        return $query->where('locations.parent_id', $distPouroId);
    }


    /**
     * get union committee list
     * @param User $user
     * @param Builder $query
     * @return mixed
     *
     */
    public function forUpazilaOffice($user, $query)
    {
        $upazilaId = $user->assign_location_id;

        return $query->where('locations.parent_id', $upazilaId);

        return $query->get();

        $unions = Location::where('parent_id', $upazilaId)->pluck('id');

//        return $user;
        return [$unions, Committee::whereIn('location_id', $unions)->get(['id', 'location_id'])];

        return $query->whereIn('location_id', $unions)->get();
    }


    //getUpazilaCommitteList
    public function forUnionCommittee($user, $query)
    {
        $upazilaId = $user->assign_location?->parent_id;

        return $query->where('locations.id', $upazilaId);
    }



    //getCityCommitteList
    public function forWardCommittee($user, $query)
    {
        $cityId = $user->assign_location?->parent?->parent_id;

        return $query->where('locations.id', $cityId);
    }



}
