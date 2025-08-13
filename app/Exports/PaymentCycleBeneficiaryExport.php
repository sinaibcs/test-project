<?php

namespace App\Exports;

use Log;
use Carbon\Carbon;
use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromIterator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PaymentCycleBeneficiaryExport implements FromIterator, WithMapping , WithHeadings, WithChunkReading
{
    use Exportable;
    protected $beneficiariesQuery;

    public function __construct($beneficiariesQuery)
    {
        $this->beneficiariesQuery = $beneficiariesQuery;
    }

    private function headerHas($val){
        // Ensure request()->visibleHeaders is an array to prevent errors if it's null
        return in_array($val, request()->visibleHeaders ?? []);
    }

    public function iterator(): \Iterator
    {
        $beneficiary_id = 0; // Initialize with 0 or the lowest possible ID from the payroll_details table
        $perPage = 20000; // Chunk size for iteration

        return (function () use ($perPage, &$beneficiary_id) {
            do {
                $query = clone $this->beneficiariesQuery;
                $query->orderBy('ppcd.beneficiary_id');

                // Apply the cursor condition based on 'id' from the current table (likely payroll_details)
                $query->where('ppcd.beneficiary_id', '>', $beneficiary_id)
                      ->take($perPage); // Limit the number of results per "page"

                // No need for deep eager loading like before, as data is flattened
                // If there are still related models that are NOT flattened, they would be added here.
                $results = $query->get();

                if($results->isEmpty()){
                    break; // No more results, exit the loop
                }

                foreach ($results as $row) {
                    yield $row;
                    // Update the last ID seen for the next iteration (from the current table)
                    $beneficiary_id = $row->beneficiary_id;
                }

            } while ($results->count() === $perPage); // Continue if we got a full page, meaning there might be more
        })();
    }


    public function map($item): array
    {
        $res = [];

        if($this->headerHas('office')){
            $res[] = Helper::lang($item->office_name_en, $item->office_name_bn);
        }
        if($this->headerHas('financial_year')){
            $res[] = $item->financial_year; // Directly accessible now
        }
        if($this->headerHas('program')){
            $res[] = Helper::lang($item->program_name_en, $item->program_name_bn);
        }
        if($this->headerHas('beneficiary_id')){
            $res[] = $item->beneficiary_id;
        }
        if($this->headerHas('name')){
            $res[] = Helper::lang($item->beneficiary_name_en, $item->beneficiary_name_bn);
        }
        if($this->headerHas('cycle_id')){
            $res[] = Helper::lang($item->payroll_payment_cycle_id, $item->payroll_payment_cycle_id);
        }
        if($this->headerHas('installment_no')){
            $res[] = Helper::lang($item->installment_name, $item->installment_name_bn);
        }
        if($this->headerHas('amount_of_money')){
            $res[] = $item->amount;
        }
        if($this->headerHas('total_charge')){
            $res[] = $item->charge;
        }
        if($this->headerHas('total_amount')){
            $res[] = $item->total_amount;
        }
        if($this->headerHas('status')){
            $res[] = Helper::lang($item->payment_status_name_en, $item->payment_status_name_bn);
        }
        
        return $res;
    }

    public function headings(): array
    {
        $res = [];
        if($this->headerHas('office')){
            $res[] = Helper::lang('Office', 'কার্যালয়');
        }
        if($this->headerHas('financial_year')){
            $res[] = Helper::lang('Financial Year', 'অর্থ বছর');
        }
        if($this->headerHas('program')){
            $res[] = Helper::lang('Program', 'কর্মসূচি');
        }
        if($this->headerHas('beneficiary_id')){
            $res[] = Helper::lang('Beneficiary ID', 'উপকারভোগী আইডি');
        }
        if($this->headerHas('name')){
            $res[] = Helper::lang('Beneficiary Name', 'উপকারভোগীর নাম');
        }
        if($this->headerHas('cycle_id')){
            $res[] = Helper::lang('Payment Cycle ID', 'পেমেন্ট সাইকেল আইডি');
        }
        if($this->headerHas('installment_no')){
            $res[] = Helper::lang('Installment', 'কিস্তি');
        }
        if($this->headerHas('amount_of_money')){
            $res[] = Helper::lang('Amount of Money', 'টাকার পরিমাণ');
        }
        if($this->headerHas('total_charge')){
            $res[] = Helper::lang('Total Charge', 'মোট চার্জ');
        }
        if($this->headerHas('total_amount')){
            $res[] = Helper::lang('Total Amount', 'মোট পরিমাণ');
        }
        if($this->headerHas('status')){
            $res[] = Helper::lang('Status', 'স্ট্যাটাস');
        }
        return $res;
    }

    public function chunkSize(): int
    {
        return 2500;  // Process 2500 records at a time
    }
}