<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FinancialYear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:financial-year';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() : void
    {
        // financial year auto active when this financial year is current financial year
        $currentYear = date('Y');
        $currentMonth = date('m');
        $currentDay = date('d');
        $currentDate = $currentYear.'-'.$currentMonth.'-'.$currentDay;
        $financialYear = \App\Models\FinancialYear::where('startDate','<=',$currentDate)->where('end_date','>=',$currentDate)->first();
        if($financialYear){
            $financialYear->status = 1;
            $financialYear->save();
        }
        // financial year auto inactive when this financial year is not current financial year
        $financialYears = \App\Models\FinancialYear::where('start_date','>',$currentDate)->orWhere('end_date','<',$currentDate)->get();
        foreach($financialYears as $financialYear){
            $financialYear->is_active = 0;
            $financialYear->save();
        }









    }
}
