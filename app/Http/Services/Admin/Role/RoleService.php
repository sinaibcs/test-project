<?php

namespace App\Http\Services\Admin\Role;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Create a new role based on the given request.
     *
     * @param  Request  $request
     * @return Role
     * @throws \Throwable
     */
    public function createRole(Request $request){
        DB::beginTransaction();
        try {
            // store role
            $role= new Role;
            $role->guard_name="sanctum";
            $role->name=$request->name_en;
            $role->name_en=$request->name_en;
            $role->name_bn=$request->name_bn;
            $role->code=$request->code;
            $role->status= $request->status;
            $role->comment = $request->comment;

            $role->save();
            db::commit();
            return $role;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Update the role with the given request data.
     *
     * @param  Request  $request
     * @return Role
     * @throws \Throwable
     */
    public function updateRole(Request $request){
        DB::beginTransaction();
        try {
            // update role
            $role= Role::find($request->id);
            $role->name=$request->name_en;
            $role->name_en=$request->name_en;
            $role->name_bn=$request->name_bn;
            $role->code=$request->code;

            if (!$request->status)
            {
                $role->status = $role->status;
            }

            if ($request->status == "true")
            {
                $role->status= 1;
            }else{
                $role->status= 0;
            }


            $role->comment = $request->comment;
            $role->save();
            db::commit();
            return $role;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /* -------------------------------------------------------------------------- */
    /*                             Permission Services                            */
    /* -------------------------------------------------------------------------- */


    /**
     * Assigns permissions to a role.
     *
     * @param Request $request The request object containing the role ID and permissions.
     * @return Role The updated role object.
     * @throws \Throwable If an error occurs during the assignment process.
     */
    public function AssignPermissionToRole(Request $request){
        DB::beginTransaction();
        try {
            $role= Role::find($request->role_id);
            // assign permissions
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
            db::commit();
            return $role;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
