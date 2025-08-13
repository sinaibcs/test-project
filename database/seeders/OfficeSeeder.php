<?php

namespace Database\Seeders;

use App\Http\Traits\OfficeTrait;
use App\Models\Office;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use OfficeTrait;

    public function run(): void
    {

        $offices = [
            /////////////////// UCD - Upazila Office
            [
                "id" => 1,
                "name_en" => "Barisal Sadar, UCD Office",
                "name_bn" => "Barisal Sadar, UCD Office",
                "assign_location_id" => 353,
                "office_type" => $this->ucdUpazilaType,
                "office_address" => "Barisal Sadar, UCD Office",
                "status" => 1,
            ],
            /////////////////// END UCD - Upazila Office

            /////////////////// UCD - District Pauroshava Office
            // [
            //     "id" => 2,
            //     "name_en" => "Barisal Sadar, UCD Office",
            //     "name_bn" => "Barisal Sadar, UCD Office",
            //     "assigned_location_id" => 353,
            //     "office_type" => 9,
            //     "office_address" => "Barisal Sadar, UCD Office",
            //     "status" => 1,
            // ],
            /////////////////// END UCD - Upazila Office

        ];

        foreach ($offices as $value) {
            $office = new Office;
            $office->id                   = $value['id'];
            $office->name_en              = $value['name_en'];
            $office->name_bn              = $value['name_bn'];
            $office->assign_location_id   = $value['assign_location_id'];
            $office->office_type          = $value['office_type'];
            $office->office_address       = $value['office_address'];
            $office->status               = $value['status'];

            $office->save();
        }
    }
}
