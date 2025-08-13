<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NidsTableSeeder extends Seeder
{
    public function run()
    {
        // Truncate the table before seeding
        DB::table('nids')->truncate();

        // Insert new data
        DB::table('nids')->insert([
            [
                'nid' => '8240387558',
                'dob' => Carbon::createFromFormat('d-m-Y', '01-06-1951')->format('Y-m-d'),
                'name' => 'মোঃ রহিম',
                'gender' => 'পুরুষ',
                'nameEn' => 'MD: Rohim',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '4160120889',
                'dob' => Carbon::createFromFormat('d-m-Y', '28-07-1953')->format('Y-m-d'),
                'name' => 'মোঃ করিম',
                'gender' => 'পুরুষ',
                'nameEn' => 'MD: Korim',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '3290489032',
                'dob' => Carbon::createFromFormat('d-m-Y', '01-08-1949')->format('Y-m-d'),
                'name' => 'রবিন রায়',
                'gender' => 'পুরুষ',
                'nameEn' => 'Robin Roy',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '19791511285425973',
                'dob' => Carbon::createFromFormat('d-m-Y', '02-05-1979')->format('Y-m-d'),
                'name' => 'সুমন আহমেদ',
                'gender' => 'পুরুষ',
                'nameEn' => 'Suman Ahmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '2841917558',
                'dob' => Carbon::createFromFormat('d-m-Y', '04-05-1987')->format('Y-m-d'),
                'name' => 'মাহবুব হোসেন',
                'gender' => 'পুরুষ',
                'nameEn' => 'Mahbub Hossain',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '19621527003241800',
                'dob' => Carbon::createFromFormat('d-m-Y', '18-11-1962')->format('Y-m-d'),
                'name' => 'রবিউল ইসলাম',
                'gender' => 'পুরুষ',
                'nameEn' => 'Rabiul Islam',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '19492613825105050',
                'dob' => Carbon::createFromFormat('d-m-Y', '15-08-1949')->format('Y-m-d'),
                'name' => 'আব্দুল কাদের',
                'gender' => 'পুরুষ',
                'nameEn' => 'Abdul Kader',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '19792621809626266',
                'dob' => Carbon::createFromFormat('d-m-Y', '02-03-1979')->format('Y-m-d'),
                'name' => 'তাহমিদ হাসান',
                'gender' => 'পুরুষ',
                'nameEn' => 'Tahmid Hasan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nid' => '19592613825114568',
                'dob' => Carbon::createFromFormat('d-m-Y', '07-01-1959')->format('Y-m-d'),
                'name' => 'আবুল কাশেম',
                'gender' => 'পুরুষ',
                'nameEn' => 'Abul Kashem',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
