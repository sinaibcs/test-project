<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\Http\Traits\PermissionTrait;
use App\Http\Traits\RoleTrait;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesSeeder extends Seeder
{
    use RoleTrait, PermissionTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'sanctum';

        $role = Role::create([
            'guard_name' => $guard,
            'code' => "278932",
            'default' => 1,
            'status'  => 1,
            'name_en' => $this->superAdmin,
            'name_bn' => $this->superAdmin,
            'name' => $this->superAdmin
        ]);
        $role->givePermissionTo(Permission::all());

        $officeHeadRole = Role::create([
            'guard_name' => $guard,
            'code' => "10001",
            'default' => 1,
            'status'  => 1,
            'name_en' => $this->officeHead,
            'name_bn' => $this->officeHead,
            'name' => $this->officeHead
        ]);
        $officeHeadRole->givePermissionTo(Permission::all());

        // $applicationListRole = Role::create([
        //     'guard_name' => $guard,
        //     'code' => "20001",
        //     'default' => 1,
        //     'status'  => 1,
        //     'name_en' => $this->applicationListRole,
        //     'name_bn' => $this->applicationListRole,
        //     'name' => $this->applicationListRole
        // ]);
        // $applicationListRole->givePermissionTo(Permission::all());



        $dataEntryOperatorRole = Role::create([
            'guard_name' => $guard,
            'code' => "10002",
            'default' => 1,
            'status'  => 1,
            'name_en' => $this->dataEntryOperator,
            'name_bn' => $this->dataEntryOperator,
            'name' => $this->dataEntryOperator
        ]);
        $dataEntryOperatorRole->givePermissionTo(Permission::all());


        $committeeRole = Role::create([
            'guard_name' => $guard,
            'code' => "10003",
            'default' => 1,
            'status'  => 1,
            'name_en' => $this->committee,
            'name_bn' => $this->committee,
            'name' => $this->committee
        ]);
        $committeeRole->givePermissionTo(
            Permission::where('module_name', $this->modulePermissionApplicationSelection)
                ->get()
        );

        $trainingRole = Role::create([
            'guard_name' => $guard,
            'code' => "10004",
            'default' => 1,
            'status'  => 1,
            'name_en' => $this->trainer,
            'name_bn' => $this->trainer,
            'name' => $this->trainer
        ]);
        $trainingRole->givePermissionTo(
            Permission::where('module_name', $this->modulePermissionTrainingManagement)
                ->get()
        );

        $participantRole = Role::create([
            'guard_name' => $guard,
            'code' => "10005",
            'default' => 1,
            'status'  => 1,
            'name_en' => $this->participant,
            'name_bn' => $this->participant,
            'name' => $this->participant
        ]);
        $participantRole->givePermissionTo(
            Permission::where('module_name', $this->modulePermissionTrainingManagement)
                ->get()
        );

        $salt = Helper::generateSalt();
        $admin = User::create(
            [
                'full_name'            => 'CTM',
                'username'            => 'ctm-01',
                'user_id'            => 1,
                'email'                 => 'admin@ctm.com',
                'salt'                  => $salt,
                'password'              => bcrypt($salt . '12345678'),
                'user_type'               => $this->superAdminId,
                'remember_token'        => Str::random(10),
                'status'            => 1,
                'is_default_password'            => 0,
                'email_verified_at'     => now(),
            ]
        );

        $admin->assignRole([$role->id]);
    }
}
