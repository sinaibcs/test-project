<?php

namespace App\Exports;

use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class AreaWiseBudgetFormatSheetExport implements FromCollection, WithHeadings, WithEvents, WithTitle
{
    protected $division;
    private $totalRows = 0;

    public function __construct($division, private $program)
    {
        $this->division = $division;
    }

    public function collection()
    {
        $locations = DB::select("
            SELECT 
                l1.name_bn AS ward_name, 
                l2.name_bn AS union_name,
                l3.name_bn AS pourashava_name,
                l4.name_bn AS thana_name,
                l5.name_bn AS dist_pura_name,
                l6.name_bn AS upazilla_name,
                l7.name_bn AS city_corp_name,
                l8.name_bn AS district_name,
                allotment_areas_view.*
            FROM allotment_areas_view
            LEFT JOIN locations AS l1 ON l1.id = ward_id
            LEFT JOIN locations AS l2 ON l2.id = union_id
            LEFT JOIN locations AS l3 ON l3.id = pourashava_id
            LEFT JOIN locations AS l4 ON l4.id = thana_id
            LEFT JOIN locations AS l5 ON l5.id = district_pourashava_id
            LEFT JOIN locations AS l6 ON l6.id = upazila_id
            LEFT JOIN locations AS l7 ON l7.id = city_corp_id
            LEFT JOIN locations AS l8 ON l8.id = district_id
            WHERE division_id = ?
            ORDER BY district_id, city_corp_id, district_pourashava_id, thana_id, 'upazila_id', pourashava_id, union_id, ward_id ASC
        ", [$this->division->id]);
        $this->totalRows = count($locations);

        $currentDistrict = null;
        $i = 0;

        return collect($locations)->map(function ($item) use(&$i, &$currentDistrict){
            if ($currentDistrict !== $item->district_name) {
                $currentDistrict = $item->district_name;
                $i = 0;
            }
            $ids = [
                'location_id' => $item->locatoin_id,
                'location_type' => $item->location_type,
                'ward_id' => $item->ward_id,
                'union_id' => $item->union_id,
                'pourashava_id' => $item->pourashava_id,
                'thana_id' => $item->thana_id,
                'district_pourashava_id' => $item->district_pourashava_id,
                'upazila_id' => $item->upazila_id,
                'city_corp_id' => $item->city_corp_id,
                'district_id' => $item->district_id,
                'division_id' => $item->division_id
            ];

            $uwp[] = $item->ward_name;
            $uwp[] = $item->union_name;
            $uwp[] = $item->pourashava_name;
            // $ucdp[] = $item->thana_name;
            $ucdp[] = $item->dist_pura_name;
            $ucdp[] = $item->thana_name?? $item->upazilla_name;
            $ucdp[] = $item->city_corp_name;

            return [
                'Key' => Crypt::encrypt(json_encode($ids)),
                'সিরিয়াল' => Helper::englishToBangla(++$i),
                'জেলা' => $item->district_name,
                'উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা' => implode(', ',array_filter($ucdp, function($area){ return $area != null;})),
                'ইউনিয়ন/ওয়ার্ড/পৌরসভা' => implode(', ',array_filter($uwp, function($area){ return $area != null;})),
                'কার্যক্রম' => $this->program->name_bn,
                'মোট উপকারভোগী' => '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Key',
            // 'জেলা',
            // 'সিটি কর্পোরেশন',
            // 'উপজেলা',
            // 'জেলা পৌরসভা',
            // 'থানা',
            // 'পৌরসভা',
            // 'ইউনিয়ন',
            // 'ওয়ার্ড',
            'সিরিয়াল',
            'জেলা',
            'উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা',
            'ইউনিয়ন/ওয়ার্ড/পৌরসভা',
            'কার্যক্রম',
            'মোট উপকারভোগী',
        ];
    }

    public function title(): string
    {
        return $this->division->name_bn; // Set the sheet name as the division name
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Hide the "Key" column (A)
                $event->sheet->getColumnDimension('A')->setVisible(false);

                // Set column widths
                $columnWidths = [
                    'C' => 15,  // জেলা
                    'D' => 40,  // উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা
                    'E' => 30,  // অফিস
                    'F' => 50,  // শ্রেণী
                    'G' => 25,  // মোট উপকারভোগী
                ];
                foreach ($columnWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                // Increase row height for better spacing
                $rowCount = $this->totalRows + 1;
                for ($i = 1; $i <= $rowCount; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(25); // Adjust row height
                }

                // Style the header row
                $headerRange = 'B1:G1'; // Header row
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['size' => 12],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'], // Centered text
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9EAD3'], // Light green background
                    ],
                ]);

                // Apply borders to all data cells
                $dataRange = "B1:G$rowCount";
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'], // Black border
                        ],
                    ],
                ]);

                // Apply alternating row colors with #F0F0F0
                for ($i = 2; $i <= $rowCount; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("B$i:G$i")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F0F0F0'], // Light grey
                            ],
                        ]);
                    }
                }

                // Align all text to center vertically
                $sheet->getStyle("B1:G$rowCount")->applyFromArray([
                    'alignment' => [
                        'vertical' => 'center', // Vertically center text
                    ],
                ]);

                // Freeze top row
                $sheet->freezePane('B2');

                // Unlock the "মোট উপকারভোগী" column (G)
                $sheet->getStyle("G1:G$rowCount")->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

                // Protect entire sheet except unlocked cells
                $sheet->getProtection()->setSheet(true);
            },
        ];
    }

}
