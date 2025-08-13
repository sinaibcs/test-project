<?php

namespace App\Http\Services\Mobile\Application;




use App\Models\CommitteeApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CommitteeApplicationService
{

    /**
     * @param Builder $query
     * @param User $user
     * @return mixed
     */
    public function getApplications($query, $user)
    {
        if ($user->committee_type_id) {

            $assignedApplicationsId = CommitteeApplication::whereCommitteeId($user->committee_id)->pluck('application_id');

            if ($user->committee) {
                $query->where('program_id', $user->committee->program_id);
            }

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
        $districtId= $user->assign_location_id;
        $divisionId = $user->assign_location?->parent?->id;

        return $this->applyLocationTypeFilter($query, $divisionId, $districtId);
    }

    public function applyLocationTypeFilter($query, $divisionId, $districtId)
    {
        if ($divisionId) {
            $query->where('permanent_division_id', $divisionId);

            if ($districtId) {
                // dd($districtId);
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


    public function getDistrictPouroshava($user, $query, $assignedApplicationsId)
    {
        $distPouroId = $user->assign_location_id;

        $query->where('permanent_district_pourashava_id', $distPouroId)
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


    //see not selected too
    public function getCityCorporationApplications($user, $query, $assignedApplicationsId)
    {
        $cityCorpId = $user->assign_location_id;

//        $query->whereIn('id', $assignedApplicationsId);

        $query->where('permanent_city_corp_id', $cityCorpId);

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
    public function getUpazilaApplications($user, $query, $assignedApplicationsId)
    {
        $upazilaId = $user->assign_location_id;
        $districtId = $user->assign_location?->parent?->id;
        $divisionId = $user->assign_location?->parent?->parent_id;

//        $query->whereIn('id', $assignedApplicationsId);

        $query->where('permanent_division_id', $divisionId)
            ->where('permanent_district_id', $districtId)
            ->where('permanent_upazila_id', $upazilaId)
            ;

        return $this->applySubLocationFilter($query);
    }


    public function getUnionApplications($user, $query, $assignedApplicationsId)
    {
        $unionId = $user->assign_location_id;

        $query->whereIn('id', $assignedApplicationsId);

        $query->where('permanent_union_id', $unionId);

        return $this->applyWardIdFilter($query);
    }

    public function getWardApplications($user, $query, $assignedApplicationsId)
    {
        $wardId = $user->assign_location_id;

        $query->whereIn('id', $assignedApplicationsId);

        return $query->where('permanent_ward_id', $wardId);
    }

}
