<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MfsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mfs')->delete();

        $banks = [
            ['id' => 1, 'name_en' => 'Bkash', 'name_bn' => 'বিকাশ'],
            ['id' => 2, 'name_en' => 'Nagod', 'name_bn' => 'নগদ'],
            ['id' => 3, 'name_en' => 'Rocket', 'name_bn' => 'রকেট'],
            ['id' => 4, 'name_en' => 'Upay', 'name_bn' => 'উপায়'],
        ];

        DB::table('mfs')->insert($banks);
    }
}
