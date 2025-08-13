<?php

namespace App\Exports;

use Log;

use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromIterator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PayrollBeneficiariesExport implements FromCollection, WithMapping , WithHeadings, WithChunkReading
{
    use Exportable;
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    private function headerHas($val){
        return in_array($val, request()->visibleHeaders??[]);
    }

    public function collection(){
        return $this->items;
    }

    // public function iterator(): \Iterator
    // {
    //     $lastBeneficiaryId = 0; // Initialize with 0 or the lowest possible ID
    //     $perPage = 20000;

    //     return (function () use ($perPage, &$lastBeneficiaryId) {
    //         do {
    //             $query = clone $this->beneficiariesQuery;

    //             // Apply the cursor condition
    //             $query->where('beneficiary_id', '>', $lastBeneficiaryId)
    //                 ->take($perPage); // Limit the number of results per "page"

    //             $items = $query->with('get_office_id_from_wards.office.officeType');

    //             $results = $items->get();

    //             if($results->isEmpty()){
    //                 break;
    //             }

    //             foreach ($results as $row) {
    //                 yield $row;
    //                 // Update the last ID seen for the next iteration
    //                 $lastBeneficiaryId = $row->beneficiary_id;
    //             }

    //         } while ($results->count() === $perPage); // Continue if we got a full page, meaning there might be more
    //     })();
    // }


    public function map($beneficiary): array
    {
        $res = [];
        if($this->headerHas('beneficiary_id')){
            $res[] = $beneficiary->beneficiary_id;
        }
        if($this->headerHas('beneficiary_name')){
            $res[] = Helper::lang($beneficiary->name_en, $beneficiary->name_bn);
        }
        if($this->headerHas('father_name')){
            $res[] = Helper::lang($beneficiary->father_name_en, $beneficiary->father_name_bn);
        }
        if($this->headerHas('mother_name')){
            $res[] = Helper::lang($beneficiary->mother_name_en, $beneficiary->mother_name_bn);
        }
        if($this->headerHas('bank_name')){
            // Log::info($beneficiary->mfs);
            $res[] = Helper::lang($beneficiary->mfs?->name_en ?: $beneficiary->bank?->name_en ?: '', $beneficiary->mfs?->name_bn ?: $beneficiary->bank?->name_bn ?: '');
        }
        if($this->headerHas('upazilaCityDistPourosova')){
            $res[] = Helper::lang(
                optional($beneficiary->permanentDistrictPourashava)->name_en
                ?: optional($beneficiary->permanentCityCorporation)->name_en
                ?: optional($beneficiary->permanentUpazila)->name_en
                    ?: "",
                optional($beneficiary->permanentDistrictPourashava)->name_bn
                ?: optional($beneficiary->permanentCityCorporation)->name_bn
                ?: optional($beneficiary->permanentUpazila)->name_bn
                    ?: "");
        }
        if($this->headerHas('union')){
            $res[] = Helper::lang(
                optional($beneficiary->permanentUnion)->name_en ?: "",
                optional($beneficiary->permanentUnion)->name_bn ?: ""
            );
        }
        if($this->headerHas('ward')){
            $res[] = Helper::lang(
                optional($beneficiary->permanentWard)->name_en ?: "",
                optional($beneficiary->permanentWard)->name_bn ?: ""
            );
        }
        if($this->headerHas('account_number')){
            $res[] = $beneficiary->account_number;
        }
        if($this->headerHas('mobile')){
            $res[] = $beneficiary->mobile;
        }
        if($this->headerHas('amount')){
            $res[] = $beneficiary->amount;
        }
        if($this->headerHas('charge')){
            $res[] = $beneficiary->charge;
        }
        if($this->headerHas('total_allowance_amount')){
            $res[] = $beneficiary->total_allowance_amount;
        }
        
        return $res;
    }

    public function headings(): array
    {
        $res = [];
        if($this->headerHas('beneficiary_id')){
            $res[] = Helper::lang('MIS Number', 'এমআইএস নাম্বার');
        }
        if($this->headerHas('beneficiary_name')){
            $res[] = Helper::lang('Beneficiary Name', 'উপকারভোগীর নাম');
        }
        if($this->headerHas('father_name')){
            $res[] = Helper::lang('Father Name', 'বাবার নাম');
        }
        if($this->headerHas('mother_name')){
            $res[] = Helper::lang('Mohter Name', 'মায়ের নাম');
        }
        if($this->headerHas('bank_name')){
            $res[] = Helper::lang('Bank/MFS Name', 'ব্যাংক/এমএফএস নাম');
        }
        if($this->headerHas('upazilaCityDistPourosova')){
            $res[] = Helper::lang('Upazila/City Corp./Zilla Pouroshava', 'উপজেলা/সিটি কর্প/জেলা পৌরসভা');
        }
        if($this->headerHas('union')){
            $res[] = Helper::lang('Union', 'ইউনিয়ন');
        }
        if($this->headerHas('ward')){
            $res[] = Helper::lang('Ward', 'ওয়ার্ড');
        }
        if($this->headerHas('account_number')){
            $res[] = Helper::lang('Account Number', 'এ্যাকাউন্ট নাম্বার');
        }
        if($this->headerHas('mobile')){
            $res[] = Helper::lang('Mobile', 'মোবাইল');
        }
        if($this->headerHas('amount')){
            $res[] = Helper::lang('Amount', 'পরিমাণ');
        }
        if($this->headerHas('charge')){
            $res[] = Helper::lang('Charge', 'চার্জ');
        }
        if($this->headerHas('total_allowance_amount')){
            $res[] = Helper::lang('Total Allowance Amount', 'মোট ভাতার পরিমাণ');
        }
        return $res;
    }

    public function chunkSize(): int
    {
        return 2500;  // Process 2500 records at a time
    }
}