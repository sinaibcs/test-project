<?php

namespace App\Http\Services\Mobile\Application;




use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OfficeApplicationService
{

    /**
     * @param $query
     * @param User $user
     * @return mixed
     */
    public function getApplications($query, $user)
    {
        if ($user->office_type) {
            return match ($user->office_type) {
                4, 5 => $this->applyLocationTypeFilter($query, request('division_id'), request('district_id')),
                6 => $this->getDivision($user, $query),
                7 => $this->getDistrict($user, $query),

                8, 10, 11 => $this->getUpazilaApplications($user, $query),
                9 => $this->getCityCorporation($user, $query),
                35 => $this->getDistrictPouroshava($user, $query),

                //Exclude all applications
                default => $query->whereId(null)

            };
        }

    }


    /**
     * @param $user
     * @param $query
     * @return void
     */
    public function getDivision($user, $query)
    {
        $divisionId = $user->assign_location_id;

        return $this->applyLocationTypeFilter($query, $divisionId, request('district_id'));
    }



    public function getDistrict($user, $query)
    {
        $districtId = $user->assign_location_id;
        $divisionId = $user->assign_location?->parent?->id;

        return $this->applyLocationTypeFilter($query, $divisionId, $districtId);
    }

    public function applyLocationTypeFilter($query, $divisionId, $districtId)
    {

        if ($divisionId) {
            $query->where('permanent_division_id', $divisionId);

            if ($districtId) {
                $query->where('permanent_district_id', $districtId);

                if ($type = request('location_type_id')) {

                    //Dis pouro
                    if ($type == 1) {

                        if ($disPouroId = request('district_pouro_id')) {
                            $query->where('permanent_district_pourashava_id', $disPouroId);

                            return $this->applyWardIdFilter($query);

                        }

                    }


                    //upazila
                    if ($type == 2) {
                        if ($upazilaId = request('thana_id')) {
                            $query->where('permanent_upazila_id', $upazilaId);

                            return $this->applySubLocationFilter($query);
                        }
                    }

                    //city corporation
                    if ($type == 3) {
                        if ($cityCorpId = request('city_id')) {
                            $query->where('permanent_city_corp_id', $cityCorpId);

                            if ($thanaId = request('city_thana_id')) {
                                $query->where('permanent_thana_id', $thanaId);

                                return $this->applyWardIdFilter($query);
                            }

                        }
                    }


                }

            }

        }


    }


    public function getDistrictPouroshava($user, $query)
    {
        $distPouroId = $user->assign_location_id;
        $districtId = $user->assign_location?->parent?->id;
        $divisionId = $user->assign_location?->parent?->parent_id;

        $query->where('permanent_division_id', $divisionId)
            ->where('permanent_district_id', $districtId)
            ->where('permanent_district_pourashava_id', $distPouroId)
        ;

        return $this->applyWardIdFilter($query);
    }





    public function applyWardIdFilter($query)
    {
        return $query->when(request('ward_id'), function ($q, $v) {
            $q->where('permanent_ward_id', $v);
        });
    }



    public function applySubLocationFilter($query)
    {
        if ($subType = request('sub_location_type')) {

            //pourashava
            if ($subType == 1) {
                $query->when(request('pouro_id'), function ($q, $v) {
                    $q->where('permanent_pourashava_id', $v);
                });
            }


            //union
            if ($subType == 2) {
                $query->when(request('union_id'), function ($q, $v) {
                    $q->where('permanent_union_id', $v);
                });
            }


            return $this->applyWardIdFilter($query);

        }

    }


    public function getCityCorporation($user, $query)
    {
        $cityCorpId = $user->assign_location_id;
        $districtId = $user->assign_location?->parent?->id;
        $divisionId = $user->assign_location?->parent?->parent_id;

        $query->where('permanent_division_id', $divisionId)
            ->where('permanent_district_id', $districtId)
            ->where('permanent_city_corp_id', $cityCorpId)
        ;

        $query->whereIn('permanent_ward_id', $user->userWards()->pluck('id'));

        $query->when(request('city_thana_id'), function ($q, $v) {
            $q->where('thana_id', $v);
        });

        return $this->applyWardIdFilter($query);
    }


    /**
     * @param User $user
     * @param Builder $query
     * @return void
     */
    public function getUpazilaApplications($user, $query)
    {
        $user->load( 'assign_location.parent.parent.parent.parent');

        $upazilaId = $user->assign_location_id;
        $districtId = $user->assign_location?->parent?->id;
        $divisionId = $user->assign_location?->parent?->parent_id;

        $query->where('permanent_division_id', $divisionId)
            ->where('permanent_district_id', $districtId)
            ->where('permanent_upazila_id', $upazilaId)
            ;

        if ($user->userWards()->exists()) {
            $query->whereIn('permanent_ward_id', $user->userWards()->pluck('id'));
        }

        return $this->applySubLocationFilter($query);

    }

}
