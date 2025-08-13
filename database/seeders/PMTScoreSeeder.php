<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PMTScore;

class PMTScoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $poverty_score_cut_offs = [
            [
                'id' => 1,
                'type' => 0,
                'location_id' => null, //Bangladesh
                'financial_year_id' => 1, //Bangladesh
                'default' => 0, //Bangladesh
                'score' => '795',
            ],

            [
                'id' => 2,
                'type' => 1,
                'location_id' => 4, //Barishal
                'financial_year_id' => 1, //Barishal
                'default' => 0, //Barishal
                'score' => '795',
            ],

            [
                'id' => 3,
                'type' => 1,
                'location_id' => 1, //Chattagram
                'financial_year_id' => 1, //Chattagram
                'default' => 0, //Chattagram
                'score' => '800',
            ],

            [
                'id' => 4,
                'type' => 1,
                'location_id' => 6, //Dhaka
                'financial_year_id' => 1, //Dhaka
                'default' => 0, //Dhaka
                'score' => '810',
            ],

            [
                'id' => 5,
                'type' => 1,
                'location_id' => 3, //Khulna
                'financial_year_id' => 1, //Khulna
                'default' => 0, //Khulna
                'score' => '785',
            ],

            [
                'id' => 6,
                'type' => 1,
                'location_id' => 8, //Mymensignh
                'financial_year_id' => 1, //Mymensignh
                'default' => 0, //Mymensignh
                'score' => '780',
            ],

            [
                'id' => 7,
                'type' => 1,
                'location_id' => 2, //Rajshahi
                'financial_year_id' => 1, //Rajshahi
                'default' => 0, //Rajshahi
                'score' => '790',
            ],

            [
                'id' => 8,
                'type' => 1,
                'location_id' => 7, //Rangpur
                'financial_year_id' => 1, //Rangpur
                'default' => 0, //Rangpur
                'score' => '775',
            ],

        ];
        foreach ($poverty_score_cut_offs as $value) {
            $poverty_score_cut_offs = new PMTScore;
            $poverty_score_cut_offs->id           = $value['id'];
            $poverty_score_cut_offs->type         = $value['type'];
            $poverty_score_cut_offs->location_id  = $value['location_id'];
            $poverty_score_cut_offs->financial_year_id  = $value['financial_year_id'];
            $poverty_score_cut_offs->score        = $value['score'];
            $poverty_score_cut_offs->save();
        }
    }
}
