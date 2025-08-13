<?php

namespace App\Http\Services\Mobile\GrievanceManagement;

use App\Models\CommitteeApplication;
use App\Models\GrievanceSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GrievanceComitteeService
{

    /**
     * @param Builder $query
     * @param User $user
     * @return mixed
     */
    public function getGrievance($query, $user)
    {

        if ($user->committee_type_id) {
            $assignedApplicationsId = CommitteeApplication::whereCommitteeId($user->committee_id)->pluck('application_id');

            return match ($user->committee_type_id) {
                12 => $this->getUnionApplications($user, $query, $assignedApplicationsId),
                13 => $this->getWardApplications($user, $query, $assignedApplicationsId),
                14 => $this->getUpazilaApplications($user, $query, $assignedApplicationsId),
                15 => $this->getCityCorporationApplications($user, $query, $assignedApplicationsId),
                16 => $this->getDistrictPouroshava($user, $query, $assignedApplicationsId),

                17 => $this->getDistrictApplications($user, $query),

                18, 19 => $this->applyLocationTypeFilter($query, request('division_id'), request('district_id')),

            //Exclude all applications
                default => $query->whereNull('id')

            };
        }

    }

    public function getDistrictApplications($user, $query)
    {
        $districtId = $user->assign_location_id;
        $divisionId = $user->assign_location?->parent?->id;

        return $this->applyLocationTypeFilter($query, $divisionId, $districtId);
    }

    public function applyLocationTypeFilter($query, $divisionId, $districtId)
    {
        if ($divisionId) {
            $query->where('division_id', $divisionId);

            if ($districtId) {
                // dd($districtId);
                $query->where('district_id', $districtId);

                if ($type = request('type_id')) {

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
                                $query->where('city_thana_id', $thanaId);

                                return $this->applyWardIdFilter($query);
                            }

                        }
                    }

                }

            }

        }

    }

    public function getDistrictPouroshava($user, $query, $assignedApplicationsId)
    {
        $distPouroId = $user->assign_location_id;
        // $userRoleId = $user->assign_location_id;

        $query->where('district_pouro_id', $distPouroId);

        return $this->applyWardIdFilter($query);
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

    //see not selected too
    public function getCityCorporationApplications($user, $query, $assignedApplicationsId)
    {
        // $settings = GrievanceSetting::all();
        $cityCorpId = $user->assign_location_id;
        // $roles = [$user->roles->pluck('id')->first()];
        // $query->whereIn('resolver_id', [$roles]);
        $query->where('city_id', $cityCorpId);


        // dd($query->get());
        $query->when(request('city_thana_id'), function ($q, $v) {
            $q->where('city_thana_id', $v);
        });

        return $this->applyWardIdFilter($query);
    }

    /**
     * @param User $user
     * @param Builder $query
     * @return void
     */
    public function getUpazilaApplications($user, $query, $assignedApplicationsId)
    {
        $upazilaId = $user->assign_location_id;
        $districtId = $user->assign_location?->parent?->id;
        $divisionId = $user->assign_location?->parent?->parent_id;

//        $query->whereIn('id', $assignedApplicationsId);

        $query->where('division_id', $divisionId)
            ->where('district_id', $districtId)
            ->where('upazila_id', $upazilaId)
        ;

        return $this->applySubLocationFilter($query);
    }

    public function getUnionApplications($user, $query, $assignedApplicationsId)
    {
        $unionId = $user->assign_location_id;

        $query->whereIn('id', $assignedApplicationsId);

        $query->where('union_id', $unionId);

        return $this->applyWardIdFilter($query);
    }

    public function getWardApplications($user, $query)
    {
        // dd( $query->get());
        $wardId = $user->assign_location_id;

        // $query->whereIn('id', $assignedApplicationsId);

        return $query->where('ward_id_city', $wardId);
    }

}
