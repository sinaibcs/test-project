<?php

namespace App\Http\Services\Admin\Office;

use App\Http\Traits\RoleTrait;
use App\Models\Location;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;

class OfficeListService
{
    use RoleTrait;

    public function getOfficesUnderUser($query)
    {
        $user = Auth::user();

        $type = $user->assign_location?->type;

        return match ($type) {
            'division' => $this->getDivisionOffices($user->assign_location_id, $query),
            'district' => $this->getDistrictOffices($user->assign_location_id, $query),
            'city' => $this->getCityPouroshovaUpazilaOffices($user->assign_location_id, $query),
            default => $this->getAllOffices($user, $query),
        };

        return $offices ? $offices->pluck('id') : [];
    }


    public function getDivisionOffices($locationId, $query)
    {
        $districts = Location::whereParentId($locationId)->get();

        $cities = Location::whereIn('parent_id', $districts->pluck('id'))->get();

        $assignedLocIds = $districts->pluck('id')->merge($cities->pluck('id'))->merge([$locationId]);

        return $query->whereIn('assign_location_id', $assignedLocIds);
    }


    public function getDistrictOffices($locationId, $query)
    {
        $cities = Location::whereParentId($locationId)->get();

        $assignedLocIds = $cities->pluck('id')->merge([$locationId]);

        return $query->whereIn('assign_location_id', $assignedLocIds);
    }



    public function getCityPouroshovaUpazilaOffices($locationId, $query)
    {
        return $query->where('assign_location_id', $locationId);
    }



    public function getAllOffices($user, $query)
    {
        $canSeeAll = $user->user_type == 1 || $user->office_type == 4 || $user->office_type == 5;

        if (!$canSeeAll) {
           return $query->whereNull('id');
        }

    }












}
