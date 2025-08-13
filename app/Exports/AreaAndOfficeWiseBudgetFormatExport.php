<?php

namespace App\Exports;

use App\Models\Location;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\AreaAndOfficeWiseBudgetFormatSheetExport;

class AreaAndOfficeWiseBudgetFormatExport implements WithMultipleSheets
{
    private $program;
    function __construct($program){
        $this->program = $program;
    }

    public function sheets(): array
    {
        $sheets = [];
        $divisions = Location::whereNull('parent_id')->get();

        foreach ($divisions as $division) {
            $sheets[$division->name_bn] = new AreaAndOfficeWiseBudgetFormatSheetExport($division, $this->program);
        }

        return $sheets;
    }
}
