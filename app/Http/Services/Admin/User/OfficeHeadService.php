<?php

namespace App\Http\Services\Admin\User;

use App\Http\Traits\LocationTrait;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Log;

class OfficeHeadService
{
    use LocationTrait;

    /*
     * If office head/ super admin get users list
     * No assignLocation type means user is != [Super admin, head office, ministry]
     * */
    public function getUsersUnderOffice()
    {
        $user = Auth::user();

        $type = $user->assign_location?->type;

        $users = match ($type) {
            'division' => $this->getDivisionUsers($user),
            'district' => $this->getDistrictUsers($user),
            'thana' => $this->getUpazilaUsers($user),
            'city' => $this->getWardUsers($user),
            default => $this->grantAllUsersList($user)
        };

        return $users ? $users->pluck('id') : [];
    }


    public function grantAllUsersList($user)
    {
        $weight = auth()->user()->roles()->min('weight');
        $query = User::query();

        $query->select('users.id', 'username', 'full_name', 'office_type', 'committee_type_id', 'assign_location_id',
            'name_en', 'locations.id as id2', 'type');

        $query->leftJoin('locations', 'users.assign_location_id', '=', 'locations.id');

        //Head office, ministry
        if ($user->office_type == 4 || $user->office_type == 5) {
//            $query->whereNotIn('office_type', [5, 4])
                //Exclude super admin
                $query->whereRaw("(select MIN(roles.weight) from roles JOIN `model_has_roles` ON `roles`.`id` = `model_has_roles`.`role_id`
                WHERE
                `users`.`id` = `model_has_roles`.`model_id` AND `model_has_roles`.`model_type` = 'App\\\Models\\\User') > $weight");
        }

        //Exclude own id
//        $query->whereNot('users.id', $user->id);

        return $query->get();
    }




    /*
 * District office type = 7
 * District committee type = 17
 * */
    public function getDivisionUsers($user)
    {
        $officeTypes = [7];
        $committeeTypes = [17];
        $assignedIds = [$user->assign_location_id];

        return $this->getLocationWiseUsers($officeTypes, $committeeTypes, $assignedIds);
    }



    /*
    * Committee types
    * City corp = 15
    * Pauroshava = 16
    * Upazila = 14
    * officeType
    * Circle social service = 11
    * UCD = 9
    * UCD Upazila = 10
    * Upazila = 8
    * */
    public function getDistrictUsers($user)
    {
        $officeTypes = [8, 9, 10, 11, 35];
        $committeeTypes = [14, 15, 16];
        $assignedIds = [$user->assign_location_id];


        return $this->getLocationWiseUsers($officeTypes, $committeeTypes, $assignedIds);
    }



    public function getUpazilaUsers($user)
    {
        $officeTypes = [];
        $committeeTypes = [12];
        $assignedIds = [$user->assign_location_id];

        return $this->getLocationWiseUsers($officeTypes, $committeeTypes, $assignedIds);
    }


    /*
     * City -> Thana -> Ward
     * */
    public function getWardUsers($user)
    {
        $officeTypes = [];
        $committeeTypes = [13];

        $thanas = Location::where('parent_id', $user->assign_location_id)
            ->whereType($this->thana)
            ->whereLocationType(3)
            ->get();


        return $this->getLocationWiseUsers(
            officeTypes: $officeTypes,
            committeeTypes: $committeeTypes,
            assignedIds: $thanas->pluck('id')
        );
    }





    public function getLocationWiseUsers($officeTypes = [], $committeeTypes = [], $assignedIds = [])
    {
        $weight = auth()->user()->roles()->min('weight');
        $query = User::leftJoin('locations', 'users.assign_location_id', '=', 'locations.id')
            ->where(function (Builder $query) use ($officeTypes, $committeeTypes){
                $query->whereIn('office_type', $officeTypes)
                    ->orWhereIn('committee_type_id', $committeeTypes);
            })
            ->whereIn('parent_id', $assignedIds)
            ->orWhereIn('locations.id', $assignedIds)
            ->whereRaw("(select MIN(roles.weight) from roles JOIN `model_has_roles` ON `roles`.`id` = `model_has_roles`.`role_id`
            WHERE
            `users`.`id` = `model_has_roles`.`model_id` AND `model_has_roles`.`model_type` = 'App\\\Models\\\User') > $weight")
            ->select('users.id', 'username', 'full_name', 'office_type', 'committee_type_id',
                'assign_location_id', 'name_en', 'locations.id as id2', 'type'
            );

            // Log::info($query->toRawSql());

            return $query->get();
    }





}
