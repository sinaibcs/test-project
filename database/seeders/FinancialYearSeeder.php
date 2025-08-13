<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FinancialYear;

class FinancialYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        // create financial year data in current financial year to previouse 5 year financial years
        $currentDate = now();
        $startOfFinancialYear = $currentDate->month >= 4 ? $currentDate->year : $currentDate->year - 1;
        $endOfFinancialYear = $startOfFinancialYear + 1;
        for ($i = 0; $i < 5; $i++) {

    $financial_year = new FinancialYear;
    $financial_year->financial_year    = "{$startOfFinancialYear}-{$endOfFinancialYear}";
    $financial_year->start_date = \Illuminate\Support\Carbon::parse("{$startOfFinancialYear}-07-01");
    $financial_year->end_date          = \Illuminate\Support\Carbon::parse("{$endOfFinancialYear}-06-30");
    $financial_year->status            = $i == 0 ? true : false;
    $financial_year->version           = 1;
    $financial_year->save();
    $startOfFinancialYear--;
    $endOfFinancialYear--;
        }

    }
}
