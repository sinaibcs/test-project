<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Device;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $devices = [
        //     [
        //         'id' => 1,
        //         'user_id' => 1000,
        //         'name' => 'juned_laptop',
        //         'device_name' => 'MSI',
        //         'device_id' => 'f0c07ad114b7c3bc55ddf6046394ce61',
        //         'ip_address' => '127.0.0.1',
        //         'createdBy' => 1,
        //         'device_type' => 1,
        //         'status' => 1,
        //     ],
        //     [
        //         'id' => 2,
        //         'user_id' => 1000,
        //         'name' => 'tarikul',
        //         'device_name' => 'laptop',
        //         'device_id' => '82431752d5f6807aeeaa87a24eb0251b',
        //         'ip_address' => '127.0.0.1',
        //         'createdBy' => 1,
        //         'device_type' => 1,
        //         'status' => 1,
        //     ],
        //     [
        //         'id' => 3,
        //         'user_id' => 1000,
        //         'name' => 'sujon',
        //         'device_name' => 'laptop',
        //         'device_id' => '29c6aeadc0aad291085383b56dcedad8',
        //         'ip_address' => '127.0.0.1',
        //         'createdBy' => 1,
        //         'device_type' => 1,
        //         'status' => 1,
        //     ],
        //     [
        //         'id' => 4,
        //         'user_id' => 1000,
        //         'name' => 'Jesmin',
        //         'device_name' => 'laptop',
        //         'device_id' => '8526c03dbf24cc59691a63e4523856bc',
        //         'ip_address' => '127.0.0.1',
        //         'createdBy' => 1,
        //         'device_type' => 1,
        //         'status' => 1,
        //     ],
        //     [
        //         'id' => 5,
        //         'user_id' => 1000,
        //         'name' => 'MD',
        //         'device_name' => 'laptop',
        //         'device_id' => 'd0e84bcb428d71a92ef936cb17638608',
        //         'ip_address' => '127.0.0.1',
        //         'createdBy' => 1,
        //         'device_type' => 1,
        //         'status' => 1,
        //     ],
        // ];


        $devices = [
            [
                'id' => 1,
                'user_id' => 1000,
                'name' => 'juned_laptop',
                'device_name' => 'MSI',
                'device_id' => 'f3c0d3b3f5dab581b31591861ac8e29b',
                'ip_address' => '127.0.0.1',
                'device_details' => null,
                'createdBy' => 1,
                'device_type' => 1,
                'status' => 1,
                'purpose_use' => null,
                'created_at' => '2023-11-20 17:02:27',
                'updated_at' => '2023-11-20 17:02:27',
            ],
            [
                'id' => 2,
                'user_id' => 1000,
                'name' => 'tarikul',
                'device_name' => 'laptop',
                'device_id' => '6ddae5ded9d59f41974dd6ced75b130e',
                'ip_address' => '127.0.0.1',
                'device_details' => null,
                'createdBy' => 1,
                'device_type' => 1,
                'status' => 1,
                'purpose_use' => null,
                'created_at' => '2023-11-20 17:02:27',
                'updated_at' => '2023-11-20 17:02:27',
            ],
            [
                'id' => 3,
                'user_id' => 1000,
                'name' => 'sujon',
                'device_name' => 'laptop',
                'device_id' => '2d8abf3cdd904a4cf785fcbc786d79ad',
                'ip_address' => '127.0.0.1',
                'device_details' => null,
                'createdBy' => 1,
                'device_type' => 1,
                'status' => 1,
                'purpose_use' => null,
                'created_at' => '2023-11-20 17:02:27',
                'updated_at' => '2023-11-20 17:02:27',
            ],
            [
                'id' => 4,
                'user_id' => 1000,
                'name' => 'Jesmin',
                'device_name' => 'laptop',
                'device_id' => '851a768068cddfb102a685a2d6232ba7',
                'ip_address' => '127.0.0.1',
                'device_details' => null,
                'createdBy' => 1,
                'device_type' => 1,
                'status' => 1,
                'purpose_use' => null,
                'created_at' => '2023-11-20 17:02:27',
                'updated_at' => '2023-11-20 17:02:27',
            ],
            [
                'id' => 5,
                'user_id' => 1000,
                'name' => 'Jamal Ahmed',
                'device_name' => 'laptop',
                'device_id' => '5eb1e84edfbd681ee39cddaf7cfabb08',
                'ip_address' => '127.0.0.1',
                'device_details' => null,
                'createdBy' => 1,
                'device_type' => 1,
                'status' => 1,
                'purpose_use' => null,
                'created_at' => '2023-11-20 17:02:27',
                'updated_at' => '2023-11-20 17:02:27',
            ],
            [
                'id' => 6,
                'user_id' => 1000,
                'name' => 'Ishrat Laptop',
                'device_name' => null,
                'device_id' => 'e1e05776a0644fb0dccef25432f2a4f3',
                'ip_address' => '192.168.1.25',
                'device_details' => null,
                'createdBy' => 1,
                'device_type' => 4,
                'status' => 1,
                'purpose_use' => 'NA',
                'created_at' => '2023-11-21 10:57:56',
                'updated_at' => '2023-11-21 10:57:56',
            ],
            [
                'id' => 7,
                'user_id' => 1000,
                'name' => 'Selim Laptop',
                'device_name' => null,
                'device_id' => 'e1e05776a0644fb0dccef25432f2a4f3',
                'ip_address' => null,
                'device_details' => null,
                'createdBy' => 1,
                'device_type' => null,
                'status' => 1,
                'purpose_use' => null,
                'created_at' => '2023-12-04 10:05:39',
                'updated_at' => '2023-12-04 10:05:39',
            ],
            [
                'id' => 8,
                'user_id' => 1000,
                'name' => 'juned_laptop',
                'device_name' => 'MSI',
                'device_id' => 'f0c07ad114b7c3bc55ddf6046394ce61',
                'ip_address' => '127.0.0.1',
                'createdBy' => 1,
                'device_type' => 1,
                'status' => 1,
            ],
        ];

        $devices = [];

        foreach ($devices as $value) {
            $device = new Device;
            $device->id              = $value['id'];
            $device->user_id         = $value['user_id'];
            $device->name            = $value['name'];
            $device->device_name     = $value['device_name'];
            $device->device_id       = $value['device_id'];
            $device->ip_address      = $value['ip_address'];
            $device->createdBy       = $value['createdBy'];
            $device->device_type     = $value['device_type'];
            $device->status          = $value['status'];
            $device->save();
        }
    }
}
