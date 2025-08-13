<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BeneficiariesImport implements ToCollection, WithChunkReading, WithHeadingRow
{
    public $data = [];
    public $columns = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->columns = array_keys($row->toArray());

            if (isset($row['date_of_birth']) && is_numeric($row['date_of_birth'])) {
                $row['date_of_birth'] = ExcelDate::excelToDateTimeObject($row['date_of_birth'])->format('Y-m-d');
            }

            $this->data[] = $row->toArray();
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
