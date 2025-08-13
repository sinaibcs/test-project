<?php

namespace App\Exports;

use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Log;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\Protection;

class OfficeWiseBudgetFormatSheetExport implements FromCollection, WithHeadings, WithEvents, WithTitle
{
    protected $division;
    private $totalRows = 0;

    public function __construct($division, private $program, private $classes)
    {
        $this->division = $division;
    }

    public function collection()
    {
        $locations = DB::select("
            SELECT 
                o.name_bn AS office_name,
                l1.name_bn AS dist_pura_name,
                l2.name_bn AS upazilla_name,
                l3.name_bn AS city_corp_name,
                l4.name_bn AS district_name,
                allotment_areas_1st_level_view.*
            FROM allotment_areas_1st_level_view
            LEFT JOIN offices AS o ON o.id = office_id
            LEFT JOIN locations AS l1 ON l1.id = district_pourashava_id
            LEFT JOIN locations AS l2 ON l2.id = upazila_id
            LEFT JOIN locations AS l3 ON l3.id = city_corp_id
            LEFT JOIN locations AS l4 ON l4.id = district_id
            WHERE division_id = ?
            ORDER BY district_id, city_corp_id, district_pourashava_id, upazila_id ASC
        ", [$this->division->id]);
        $this->totalRows = count($locations) + 1;
        if($this->program->is_disable_class == 1){
            $this->totalRows *= count($this->classes);
            $data = [];
            $i = 0;
            $currentDistrict = null;
            foreach($locations as $item){
                $areas = [];
                $areas[] = $item->dist_pura_name;
                $areas[] = $item->upazilla_name;
                $areas[] = $item->city_corp_name;

                foreach($this->classes as $class){
                    if ($currentDistrict !== $item->district_name) {
                        $currentDistrict = $item->district_name;
                        $i = 0;
                    }
                    $ids = [
                        'type_id' => $class->type_id,
                        'office_id' => $item->office_id,
                        'district_pourashava_id' => $item->district_pourashava_id,
                        'upazila_id' => $item->upazila_id,
                        'city_corp_id' => $item->city_corp_id,
                        'district_id' => $item->district_id,
                        'division_id' => $item->division_id,
                        'location_id' => $item->locatoin_id,
                        'location_type' => $item->location_type_id,
                    ];
        
                    $data[] = [
                        'Key' => Crypt::encrypt(json_encode($ids)),
                        'সিরিয়াল' => Helper::englishToBangla(++$i),
                        'জেলা' => $item->district_name,
                        'উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা' => implode(', ',array_filter($areas, function($area){ return $area != null;})),
                        'অফিস' => $item->office_name,
                        'শ্রেণী' => $class->type->value_bn,
                        'কার্যক্রম' => $this->program->name_bn,
                        'মোট উপকারভোগী' => '',
                    ];
                }
            }
            return collect($data);
        }

        $currentDistrict = null;
        $i = 0;

        return collect($locations)->map(function ($item) use(&$i, &$currentDistrict){
            if ($currentDistrict !== $item->district_name) {
                $currentDistrict = $item->district_name;
                $i = 0;
            }
            $ids = [
                'type_id' => null,
                'office_id' => $item->office_id,
                'district_pourashava_id' => $item->district_pourashava_id,
                'upazila_id' => $item->upazila_id,
                'city_corp_id' => $item->city_corp_id,
                'district_id' => $item->district_id,
                'division_id' => $item->division_id,
                'location_id' => $item->locatoin_id,
                'location_type' => $item->location_type_id
            ];

            $areas[] = $item->dist_pura_name;
            $areas[] = $item->upazilla_name;
            $areas[] = $item->city_corp_name;

            return [
                'Key' => Crypt::encrypt(json_encode($ids)),
                'সিরিয়াল' => Helper::englishToBangla(++$i),
                'জেলা' => $item->district_name,
                'উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা' => implode(', ',array_filter($areas, function($area){ return $area != null;})),
                'অফিস' => $item->office_name,
                'শ্রেণী' => '',
                'কার্যক্রম' => $this->program->name_bn,
                'মোট উপকারভোগী' => '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Key',
            'সিরিয়াল',
            'জেলা',
            'উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা',
            'অফিস',
            'শ্রেণী',
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

                // Hide the "Key" column
                $event->sheet->getColumnDimension('A')->setVisible(false);
                if($this->program->is_disable_class != 1){
                    $event->sheet->getColumnDimension('F')->setVisible(false);
                }

                // Set column widths for better readability
                $columnWidths = [
                    'C' => 15,  // জেলা
                    'D' => 40,  // উপজেলা/সিটি কর্পোরেশন/জেলা পৌরসভা
                    'E' => 30,  // অফিস
                    'F' => 55,  // শ্রেণী (if applicable)
                    'G' => 50,  // কার্যক্রম
                    'H' => 25,  // মোট উপকারভোগী
                ];
                foreach ($columnWidths as $column => $width) {
                    if($column == 'F' && $this->program->is_disable_class != 1){
                        continue;
                    }
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                // Increase row height for better spacing
                $rowCount = $this->totalRows;
                for ($i = 1; $i <= $rowCount; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(25); // Increase row height
                }

                // Set bold and center-aligned headers with background color
                $headerRange = 'B1:H1'; // Header row
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['size' => 12],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'], // Vertical center
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9EAD3'], // Light green background
                    ],
                ]);

                // Apply borders to all data cells
                $dataRange = "B1:H$rowCount";
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'], // Black border
                        ],
                    ],
                ]);

                // Apply alternating row colors with #F0F0F0 for better readability
                for ($i = 2; $i <= $rowCount; $i++) {
                    if ($i % 2 == 0) {
                        $sheet->getStyle("B$i:H$i")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F0F0F0'], // Light grey
                            ],
                        ]);
                    }
                }

                // Align all text to center vertically
                $sheet->getStyle("B1:H$rowCount")->applyFromArray([
                    'alignment' => [
                        'vertical' => 'center', // Vertically center text
                    ],
                ]);

                // Freeze top row for easy scrolling
                $sheet->freezePane('B2');

                // Unlock the "মোট উপকারভোগী" column (H)
                $sheet->getStyle('H1:H10000')->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);

                // Protect entire sheet except unlocked cells
                $sheet->getProtection()->setSheet(true);
            },
        ];
    }


}
