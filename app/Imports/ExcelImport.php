<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class ExcelImport implements ToCollection
{
    // public function sheets(): array
    // {
    //     return [
    //         '*' => new SheetImport(), // '*' means it will process all sheets dynamically
    //     ];
    // }

    public function collection(Collection $rows)
    {
        dd(1);
        $filteredRows = $rows->filter(function ($row) {
            // Filter out rows where all cells are empty
            return $row->toArray()[0] == null;
        });

        // Log or return the filtered rows for debugging
        // You can save the data to the database or process it as needed
        // return $filteredRows;
        // return $rows; // This will return data for each sheet dynamically
    }
}

class SheetImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        return $rows; // This will return data for each sheet dynamically
    }
}

