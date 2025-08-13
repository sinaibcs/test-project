<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\FinancialYear;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


class AddFinancialYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'financial-year:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new financial year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
//    $financialYear = $this->calculateFinancialYear();
    $budgetYear = $this->calculateBudgetYear();

//   $financialYearArray = explode('-', $financialYear);
        $seventhMonth = 7;
        $sixthMonth = 7;
//        $startDate = Carbon::create($financialYearArray[0], $seventhMonth, 1);
//        $lastDate = Carbon::create($financialYearArray[1], $sixthMonth , 1)->subDay();
        // For Budget year
        $budgetYearArray = explode('-', $budgetYear);
        $startDate_budget = Carbon::create($budgetYearArray[0], $seventhMonth, 1);
        $lastDate_budget= Carbon::create($budgetYearArray[1], $sixthMonth , 1)->subDay();



//    $financialYearData = [
//        'financial_year' => $financialYear,
//        'start_date' => $startDate,
//        'end_date' => $lastDate,
//        'status' => 1
//
//    ];
    //Budget year Array
        $budgetYearData = [
            'financial_year' => $budgetYear,
            'start_date' => $startDate_budget,
            'end_date' => $lastDate_budget,
            'status' => 2

        ];

    $existingFinancialYear = FinancialYear::where('financial_year', $budgetYear)->first();
    if ($existingFinancialYear) {
        $this->info('Budget year already exists.');
        return;
    }
    FinancialYear::where('status', 1)->update(['status' => 0]);
    FinancialYear::where('status', 2)->update(['status' => 1]);
//    FinancialYear::where('financial_year', $financialYear)->update(['status' => 1]);


//    FinancialYear::create($financialYearData);
    // Create Budget year
        FinancialYear::create($budgetYearData);

    $this->info("Budget year inserted successfully.");
    }
//      private function calculateFinancialYear()
//    {
//        $currentDate = now();
//        $startOfFinancialYear = $currentDate->year;
//        $endOfFinancialYear = $startOfFinancialYear + 1;
//
//        return "{$startOfFinancialYear}-{$endOfFinancialYear}";
//    }
    private function calculateBudgetYear()
    {
        $currentDate = now();
        $startOfFinancialYear = $currentDate->year+1;
        $endOfFinancialYear = date("y") + 2;

        return "{$startOfFinancialYear}-{$endOfFinancialYear}";
    }
    }

