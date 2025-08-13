<?php

namespace App\Http\Services\Admin\GrievanceManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GrievanceListService
{

    /**
     * @param $query
     * @param User $user
     * @return mixed
     */
    public function getGrievance($query, $user)
    {
        //   dd($user->office_type);
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
        // dd($divisionId);

        return $this->applyLocationTypeFilter($query, $divisionId, request('district_id'));
        // dd($data->get());
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
            $query->where('division_id', $divisionId);
            return $query;
            //  dd($query->get());
            if ($districtId) {
                $query->where('district_id', $districtId);

                if ($type = request('location_type_id')) {

                    //Dis pouro
                    if ($type == 1) {

                        if ($disPouroId = request('district_pouro_id')) {
                            $query->where('district_pouro_id', $disPouroId);

                            return $this->applyWardIdFilter($query);

                        }

                    }

                    //upazila
                    if ($type == 2) {
                        if ($upazilaId = request('thana_id')) {
                            $query->where('upazila_id', $upazilaId);

                            return $this->applySubLocationFilter($query);
                        }
                    }

                    //city corporation
                    if ($type == 3) {
                        if ($cityCorpId = request('city_id')) {
                            $query->where('city_id', $cityCorpId);

                            if ($thanaId = request('city_thana_id')) {
                                $query->where('thana_id', $thanaId);

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

        $query->where('division_id', $divisionId)
            ->where('district_id', $districtId)
            ->where('district_pouro_id', $distPouroId)
        ;
        return $user->office_type;
        if($user->office_type==35){
           return $query->when(request('ward_id'), function ($q, $v) {
              $q->where('ward_id_dist', $v);
          });
 
        }

        // return $this->applyWardIdFilter($query);
    }

    public function applyWardIdFilter($query)
    {
        return $query->when(request('ward_id'), function ($q, $v) {
            $q->where('ward_id', $v);
        });
    }

    public function applySubLocationFilter($query)
    {
        if ($subType = request('sub_location_type')) {

            //pourashava
            if ($subType == 1) {
                $query->when(request('pouro_id'), function ($q, $v) {
                    $q->where('pouro_id', $v);
                });
            }

            //union
            if ($subType == 2) {
                $query->when(request('union_id'), function ($q, $v) {
                    $q->where('union_id', $v);
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

        $query->where('division_id', $divisionId)
            ->where('district_id', $districtId)
            ->where('city_id', $cityCorpId)
        ;
        // dd( $user->userWards()->pluck('id'));
        // dd( $user);

        $query->whereIn('ward_id_city', $user->userWards()->pluck('id'));
        //    dd($query->get());
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
        $user->load('assign_location.parent.parent.parent.parent');

        $upazilaId = $user->assign_location_id;
        $districtId = $user->assign_location?->parent?->id;
        $divisionId = $user->assign_location?->parent?->parent_id;

        $query->where('division_id', $divisionId)
            ->where('district_id', $districtId)
            ->where('thana_id', $upazilaId)
        ;
     
       $userWardIds = $user->userWards()->pluck('id');
       if ($userWardIds->isNotEmpty()) {
          $query->whereIn('ward_id', $userWardIds);
        }
        // dd($query->get());

        return $this->applySubLocationFilter($query);

    }

}
