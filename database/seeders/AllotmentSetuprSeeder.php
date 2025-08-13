<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\GlobalSetting;

class AllotmentSetuprSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $global_settings = [
            ['id' => 1, 'area_type' => '1', 'value' => 'Ward','default' => '0'],
            ['id' => 2, 'value' => '2', 'value' => 'Ward','default' => '0'],
            ['id' => 3,'default' => '3', 'value' => 'Ward','default' => '0'],
         
        ];
        foreach ($global_settings as $value) {
            $global_settings = new GlobalSetting;
            $global_settings->id                                 = $value['id'];
            $global_settings->area_type                          = $value['area_type'];
            $global_settings->value                              = $value['value'];
            $global_settings->default                            = $value['default'];
            $global_settings ->save();
        }
    }
}
