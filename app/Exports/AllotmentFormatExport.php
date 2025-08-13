<?php

namespace App\Exports;

use App\Models\Location;
use App\Exports\AllotmentFormatSheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllotmentFormatExport implements WithMultipleSheets
{
    private $program;
    private $financial_year_id;
    function __construct($program, $financial_year_id){
        $this->program = $program;
        $this->financial_year_id = $financial_year_id;
    }

    public function sheets(): array
    {
        $sheets = [];
        $divisions = Location::whereNull('parent_id')->get();

        foreach ($divisions as $division) {
            $sheets[$division->name_bn] = new AllotmentFormatSheetExport($division, $this->program, $this->financial_year_id);
        }

        return $sheets;
    }
}
