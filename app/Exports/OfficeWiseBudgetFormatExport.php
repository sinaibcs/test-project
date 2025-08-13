<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Location;

class OfficeWiseBudgetFormatExport implements WithMultipleSheets
{
    private $program;
    private $classes;
    function __construct($program){
        $this->program = $program;
        $this->classes = $program->classAmounts()->with('type')->get();
    }
    public function sheets(): array
    {
        $sheets = [];
        $divisions = Location::whereNull('parent_id')->get();

        foreach ($divisions as $division) {
            $sheets[$division->name_bn] = new OfficeWiseBudgetFormatSheetExport($division, $this->program, $this->classes);
        }

        return $sheets;
    }
}
