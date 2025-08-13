<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Location;

class AreaWiseBudgetFormatExport implements WithMultipleSheets
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
            $sheets[$division->name_bn] = new AreaWiseBudgetFormatSheetExport($division, $this->program);
        }

        return $sheets;
    }
}
