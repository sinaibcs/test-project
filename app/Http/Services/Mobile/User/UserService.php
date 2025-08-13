<?php

namespace App\Http\Services\Mobile\User;

use App\Helpers\Helper;
use App\Http\Traits\RoleTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserService
{
    use RoleTrait;
    public function createUser(Request $request, $password)
    {
        DB::beginTransaction();
         try {

            $user                       = new User();
            $user->full_name = $request->full_name;
            $user->username = $request->username;
            $user->mobile = $request->mobile;
            $user->email = $request->email;
//            $user->status = $request->status;
            // check request has division_id, district_id, thana_id, city_corpo_id

            if ($request->has('office_type')) {
                if ($request->office_type != 4 || $request->office_type != 5) {
                    if ($request->office_type == 6) {
                        if ($request->has('division_id')) {
                            $user->assign_location_id = $request->division_id;
                        }
                    } elseif ($request->office_type == 7) {
                        if ($request->has('district_id')) {
                            $user->assign_location_id = $request->district_id;
                        }
                    } elseif ($request->office_type == 8 || $request->office_type == 10 || $request->office_type == 11) {
                        if ($request->has('thana_id')) {
                            $user->assign_location_id = $request->thana_id;
                        }
                    } elseif ($request->office_type == 9) {
                        if ($request->has('city_corpo_id')) {
                            $user->assign_location_id = $request->city_corpo_id;
                        }
                    } elseif ($request->office_type == 35) {
                        $user->assign_location_id = $request->paurashava_id;
                    }
                } else {
                    $user->assign_location_id = null;
                }

                $user->office_type = $request->office_type;
                $user->office_id = $request->office_id;

            } elseif ((int)$committeeType = $request->committee_type) {
                $user->assign_location_id = match ((int)$committeeType) {
                    12 => $request->union_id,
                    13 => $request->ward_id,
                    14 => $request->upazila_id,
                    15 => $request->city_corpo_id,
                    16 => $request->paurashava_id,
                    17 => $request->district_id,
                    default => null
                };

                $user->committee_type_id = $request->committee_type;
                $user->committee_id = $request->committee_id;
            } else {
                abort(500, 'Internal server error');
            }


            $user->user_type = $this->staffId;
            $user->salt = Helper::generateSalt();

            // password encryption with salt
            $user->password = bcrypt($user->salt . $password);

            $user->email_verified_at = now();
            $user->save();

            $user->user_id = $user->id;
            $user->save();

            if ($request->office_type == 9 || $request->office_type == 10) {
                $this->saveUserWards($user, $request);
            }

            if ($request->user_type == 1) {
                $user->assignRole(Arr::wrap($request->role_id));
            } else {
                $user->assignRole('committee');
            }

            DB::commit();
            return $user;
         } catch (\Throwable $th) {
             DB::rollBack();
             throw $th;
         }
    }


    /**
     * @param User $user
     * @param $request
     * @return string[]
     */
    public function saveUserWards($user, $request)
    {
        $wards = $request->office_ward_id ? explode(',', $request->office_ward_id) : [];

        $user->userWards()->sync($wards);

        return $user->userWards;
    }



    public function approveUser($id) {
        $user = User::findOrFail($id);
        $user->status = !$user->status;
        $user->save();

        return $user;
    }



    public function upddateUser(Request $request, $id)
    {
        DB::beginTransaction();
        try {

            $user            = User::findOrFail($id);
            $user->full_name = $request->full_name;
            $user->username = $request->username;
            $user->mobile = $request->mobile;
            $user->email = $request->email;
//            $user->status = $request->status;
            // check request has division_id, district_id, thana_id, city_corpo_id

            if($request->office_type) {

                if($request->office_type!=4 || $request->office_type!=5){

                    if($request->office_type==6){
                        $user->assign_location_id = $request->division_id;

                    }elseif ($request->office_type==7) {
                        $user->assign_location_id = $request->district_id;

                    }elseif ($request->office_type==8 || $request->office_type==10 || $request->office_type==11) {
                        $user->assign_location_id = $request->thana_id;

                    }elseif ($request->office_type==9) {
                        $user->assign_location_id = $request->city_corpo_id;

                    } elseif ($request->office_type == 35) {
                        $user->assign_location_id = $request->paurashava_id;
                    }
                } else {
                    $user->assign_location_id = null;
                }

                $user->office_type = $request->office_type;
                $user->office_id = $request->office_id;

                $user->committee_id = null;
                $user->committee_type_id = null;

            } elseif ((int)$committeeType = $request->committee_type) {
                $user->assign_location_id = match ((int)$committeeType) {
                    12 => $request->union_id,
                    13 => $request->ward_id,
                    14 => $request->upazila_id,
                    15 => $request->city_corpo_id,
                    16 => $request->paurashava_id,
                    17 => $request->district_id,
                    default => null
                };

                $user->office_type = null;
                $user->office_id = null;

                $user->committee_id = $request->committee_id;
                $user->committee_type_id = $request->committee_type;
            } else {
                abort(500, 'Internal server error');
            }

            $user->save();
            // assign role to the user

            if ($request->user_type == 1) {
                $user->syncRoles(Arr::wrap($request->role_id));

                if ($user->hasRole('committee')) {
                    $user->roles()->detach(
                        Role::whereName('committee')->value('id')
                    );

                }


            } else {
                $user->syncRoles([]);
                $user->assignRole('committee');
            }

            $this->saveUserWards($user, $request);

            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // public function upddateUser(Request $request, $id)
    // {

    //     DB::beginTransaction();
    //     try {
    //         $user = User::findOrFail($id);

    //         // Update user attributes
    //         $user->fill([
    //             'full_name' => $request->full_name,
    //             'username' => $request->username,
    //             'mobile' => $request->mobile,
    //             'email' => $request->email,
    //             'status' => $request->status,
    //             'office_id' => $request->office_id,
    //         ]);

    //         // Handle office_type and assign_location_id based on conditions
    //         // if ($request->has('office_type')) {
    //         //     $user->office_type = $request->office_type;

    //         //     if ($request->office_type != 4 && $request->office_type != 5) {
    //         //         if ($request->office_type == 6 && $request->has('division_id')) {
    //         //             $user->assign_location_id = $request->division_id;
    //         //         } elseif ($request->office_type == 7 && $request->has('district_id')) {
    //         //             $user->assign_location_id = $request->district_id;
    //         //         } elseif (in_array($request->office_type, [8, 10, 11]) && $request->has('thana_id')) {
    //         //             $user->assign_location_id = $request->thana_id;
    //         //         } elseif ($request->office_type == 9 && $request->has('city_corpo_id')) {
    //         //             $user->assign_location_id = $request->city_corpo_id;
    //         //         }
    //         //     }
    //         // }

    //         // Save the updated user record
    //         $user->save();

    //         // Assign a role to the user
    //         $user->syncRoles([$request->role_id]);

    //         DB::commit();

    //         return $user;
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         throw $th;
    //     }
    // }

    public function generateUserId()
    {
        return DB::table('users')->latest('id')->value('id') + 1;

        return User::orderByDesc('id')->value('id') + 1;
        $user = User::where('user_id', $user_id)->first();
        if ($user) {
            $this->generateUserId();
        }
        return $user_id;
    }
}
