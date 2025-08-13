<?php

namespace Database\Seeders;

use App\Helpers\Helper;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
//             [
//                 'full_name'            => 'CTM',
//                 'username'            => 'ctm-01',
//                 'user_id'            => 1000,
//                 'email'                 => 'admin@ctm.com',
//                  'salt'                  => $salt,
//                  'password'              => bcrypt($salt . '12345678'),
//                  'user_type'               => $this->superAdminId,
//                 // 'remember_token'        => Str::random(10),
//                 'status'            => 1,
//                 'is_default_password'            => 0,
//                 'email_verified_at'     => now(),
//             ],
            [
                'full_name' => 'Juned Ahmed Chowdhury',
                'username' => 'JDUU',
                'mobile' => '01967876320',
                'email' => 'junedahmed848@gmail.com',
                'status' => '1',
                'office_type' => '10',
                'assign_location_id' => '353',
                'office_id' => 1, // UCD Upazila Office - For Testing
                'user_type' => 2,
                'salt' => 'JlX08DZz1LobpyEy6jg7GiWV0lUzxS',
                'password' => 'y$h80.v5vlSTcpI0Lpx5xdRuGyYOGeOm0MZdoiC/QP./F8bgAqa/iom',
                'user_id' => 2,
                'email_verified_at' => '2023-12-04 11:43:51',
                'updated_at' => '2023-12-04 11:43:51',
                'created_at' => '2023-12-04 11:43:51'
            ]
        ];

        $users = [];

        foreach ($users as $value) {
            $salt = Helper::generateSalt();

            $users = new User;
            $users->full_name = $value['full_name'];
            $users->username = $value['username'];
            $users->user_id = $value['user_id'];
            $users->email = $value['email'];
            $users->salt = $salt;
            $users->password = bcrypt($salt . '12345678');
            // $users->user_type = $this->superAdminId;
            // $users->remember_token = $value['remember_token'];
            $users->status = $value['status'];
            $users->password = $value['password'];
            $users->email_verified_at = $value['email_verified_at'];

            $users->save();
        }
    }
}
