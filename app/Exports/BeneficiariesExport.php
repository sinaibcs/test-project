<?php

namespace App\Exports;

use Log;
use Carbon\Carbon;
use App\Models\Lookup;
use App\Models\Office;
use App\Helpers\Helper;
use App\Models\Beneficiaries;
use App\Models\AdditionalFields;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromIterator;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class BeneficiariesExport implements FromIterator, WithMapping , WithHeadings, WithChunkReading
{
    use Exportable;
    protected $beneficiariesQuery;

    public function __construct($beneficiariesQuery)
    {
        $this->beneficiariesQuery = $beneficiariesQuery;
    }

    private function headerHas($val){
        return in_array($val, request()->visibleHeaders??[]);
    }

    // public function query()
    // {
    //     // d($this->beneficiariesQuery);
    //     $this->beneficiariesQuery->with('get_office_id_from_wards.office.officeType');
    //     if(request()->has('additionalFieldIds')){
    //         $this->beneficiariesQuery->with(['application' => function($q){
    //             $q->with(['applicationAllowanceValues' => function($q){
    //                 $q->whereIn('allow_addi_fields_id', request()->additionalFieldIds)->with('additionalFieldValue');
    //             }]);
    //         }]);
    //     }
    //     // Log::debug($this->beneficiariesQuery->toRawSql());
    //     return $this->beneficiariesQuery;
    // }

    public function iterator(): \Iterator
    {
        $lastBeneficiaryId = 0; // Initialize with 0 or the lowest possible ID
        $perPage = 20000;

        return (function () use ($perPage, &$lastBeneficiaryId) {
            do {
                $query = clone $this->beneficiariesQuery;

                // Apply the cursor condition
                $query->where('beneficiary_id', '>', $lastBeneficiaryId)
                    // ->useIndex('idx_all_filters')
                    ->take($perPage); // Limit the number of results per "page"

                $items = $query->with('get_office_id_from_wards.office.officeType');

                if (request()->has('additionalFieldIds')) {
                    $items->with(['application' => function ($q) {
                        $q->with(['applicationAllowanceValues' => function ($q) {
                            $q->whereIn('allow_addi_fields_id', request()->additionalFieldIds)->with('additionalFieldValue');
                        }]);
                    }]);
                }

                $results = $items->get();

                if($results->isEmpty()){
                    break;
                }

                foreach ($results as $row) {
                    yield $row;
                    // Update the last ID seen for the next iteration
                    $lastBeneficiaryId = $row->beneficiary_id;
                }

            } while ($results->count() === $perPage); // Continue if we got a full page, meaning there might be more
        })();
    }


    public function map($beneficiary): array
    {
        $officeCnt = $beneficiary->get_office_id_from_wards->count();
        // $get_office_id_from_wards = $beneficiary->get_office_id_from_wards->count() > 0 ? $beneficiary->get_office_id_from_wards[0] : null;
            $get_office = $officeCnt == 0 ? null : $beneficiary->get_office_id_from_wards[0]?->office;
            $get_office_type = $officeCnt == 0 ? null : $beneficiary->get_office_id_from_wards[0]?->office?->officeType;
            // if($get_office_id_from_wards){
            //     $get_office_id_from_wards = $get_office_id_from_wards['office_id'];
            //     $get_office = Office::find($get_office_id_from_wards);
            //     $get_office_type = Lookup::find($get_office->office_type);
            // }
            $currentDate = Carbon::now();
            $age = Carbon::parse($beneficiary->date_of_birth)->diffInYears($currentDate);
            $addedData = [];
            $additionalData = $this->getAdditionalData($beneficiary);
            foreach($this->getAdditionalFields() as $addi){
                $addedData[] = $additionalData[$addi->id]?? '';
            }
            $res = [];
            if($this->headerHas('beneficiary_id')){
                $res[] = $beneficiary->beneficiary_id;
            }
            if($this->headerHas('name_en')){
                $res[] = $beneficiary->name_en;
            }
            if($this->headerHas('name_bn')){
                $res[] = $beneficiary->name_bn;
            }
            if($this->headerHas('gender.value_en')){
                $res[] = Helper::lang($beneficiary->gender?->value_en, $beneficiary->gender?->value_bn);
            }
            if($this->headerHas('date_of_birth')){
                $res[] = $beneficiary->date_of_birth == null? '': Carbon::parse($beneficiary->date_of_birth)->format('d-m-Y');
            }
            if($this->headerHas('father_name_en')){
                $res[] = $beneficiary->father_name_en;
            }
            if($this->headerHas('father_name_bn')){
                $res[] = $beneficiary->father_name_bn;
            }
            if($this->headerHas('mother_name_en')){
                $res[] = $beneficiary->mother_name_en;
            }
            if($this->headerHas('mother_name_bn')){
                $res[] = $beneficiary->mother_name_bn;
            }
            if($this->headerHas('spouse_name_en')){
                $res[] = $beneficiary->spouse_name_en;
            }
            if($this->headerHas('spouse_name_bn')){
                $res[] = $beneficiary->spouse_name_bn;
            }
            if($this->headerHas('main_program')){
                $res[] = Helper::lang($beneficiary->mainProgram->name_en, $beneficiary->mainProgram->name_bn);
            }
            if($this->headerHas('program')){
                if($beneficiary->mainProgram?->id == $beneficiary->program?->id){
                    $res[] = "";
                }else{
                    $res[] = Helper::lang($beneficiary->program->name_en, $beneficiary->program->name_bn);
                }
            }
            if($this->headerHas('verification_number')){
                $res[] = "'".$beneficiary->verification_number;
            }
            $res[] =  Helper::lang($beneficiary->getStatus('en'), $beneficiary->getStatus('bn'));
            if($this->headerHas('mobile')){
                $res[] = "'".$beneficiary->mobile;
            }
            if($this->headerHas('bank_mfs_name')){
                $res[] = $beneficiary->mfs_name ?: $beneficiary->bank_name ?: '';
            }
            if($this->headerHas('account_number')){
                $res[] = "'".$beneficiary->account_number;
            }
            if($this->headerHas('age_calculated')){
                $res[] = $age;
            }
            if($this->headerHas('permanentDivision.name_en') || $this->headerHas('permanentDivision.name_bn')){
                $res[] = Helper::lang($beneficiary->permanentDivision->name_en, $beneficiary->permanentDivision->name_bn);
            }
            if($this->headerHas('permanentDistrict.name_en') || $this->headerHas('permanentDistrict.name_bn')){
                $res[] = Helper::lang($beneficiary->permanentDistrict->name_en, $beneficiary->permanentDistrict->name_bn);
            }
            if($this->headerHas('location_upccd')){
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
            if($this->headerHas('location_unpoth')){
                $res[] = Helper::lang(
                    optional($beneficiary->permanentUnion)->name_en
                ?:  optional($beneficiary->permanentPourashava)->name_en
                ?:  optional($beneficiary->permanentThana)->name_en ?: "",
                optional($beneficiary->permanentUnion)->name_bn
                ?:  optional($beneficiary->permanentPourashava)->name_bn
                ?:  optional($beneficiary->permanentThana)->name_bn ?: "");
            }
            if($this->headerHas('permanentWard.name_en') || $this->headerHas('permanentWard.name_bn')){
                $res[] = Helper::lang(optional($beneficiary->permanentWard)->name_en ?: "",optional($beneficiary->permanentWard)->name_bn ?: "");
            }
            if($this->headerHas('permanent_address')){
                $res[] = $beneficiary->permanent_address;
            }
            if($this->headerHas('get_office.name_en') || $this->headerHas('get_office.name_bn')){
                $res[] = Helper::lang(optional($get_office)->name_en ?: "",optional($get_office)->name_bn ?: "");
            }
            if($this->headerHas('get_office_type.value_en')){
                $res[] = Helper::lang(optional($get_office_type)->value_en ?: "",optional($get_office_type)->value_bn ?: "");
            }
            if($this->headerHas('status_name') || $this->headerHas('verify_action')){
                $res[] = $beneficiary->verify_logs_count == 0? Helper::lang('Non-verified', 'যাচাই করা হয়নি') : Helper::lang('Verified', 'যাচাইকৃত');
            }
            if($this->headerHas('disability_id')){
                $res[] = "'".$beneficiary->DISABILITY_ID;
            }
            if($this->headerHas('class_level')){
                $res[] = "'".Helper::lang($beneficiary->allowance_class?->value_en, $beneficiary->allowance_class?->value_bn);
            }
            return [
                ...$res,
                ...$addedData

            ];


    }

    private function getAdditionalData($ben){
        if(request()->has('additionalFieldIds')){
            $values = $ben->application?->applicationAllowanceValues??[];
            $data = [];
            foreach($values as $value){
                if($value->additionalFieldValue){
                    $data[$value->allow_addi_fields_id] = Helper::lang($value->additionalFieldValue->value_en,$value->additionalFieldValue->value_bn);
                }else{
                    $data[$value->allow_addi_fields_id] = $value->value;
                }
            }
            return $data;
        }

        return [];
    }

    private $additionals = null;

    private function getAdditionalFields(){
        if($this->additionals) return $this->additionals;
        if(request()->has('additionalFieldIds')){
            $this->additionals = AdditionalFields::whereIn('id', request()->additionalFieldIds)->get();
            return $this->additionals;
        }
        return [];
    }

    public function headings(): array
    {
        $addis = [];
        foreach($this->getAdditionalFields() as $addi){
            $addis[] = Helper::lang($addi->name_en,$addi->name_bn);
        }
        $res = [];
        if($this->headerHas('beneficiary_id')){
            $res[] = Helper::lang('MIS Number', 'এম আই এস নাম্বার');
        }
        if($this->headerHas('name_en')){
            $res[] = Helper::lang('Name (en)', 'নাম (ইংরেজি)');
        }
        if($this->headerHas('name_bn')){
            $res[] = Helper::lang('Name (bn)', 'নাম (বাংলা)');
        }
        if($this->headerHas('gender.value_en')){
            $res[] = Helper::lang('Gender', 'লিঙ্গ');
        }
        if($this->headerHas('date_of_birth')){
            $res[] = Helper::lang('Date of birth', 'জন্ম তারিখ');
        }
        if($this->headerHas('father_name_en')){
            $res[] = Helper::lang("Father's Name (en)", 'পিতার নাম (ইংরেজি)');
        }
        if($this->headerHas('father_name_bn')){
            $res[] = Helper::lang("Father's Name (bn)", 'পিতার নাম (বাংলা)');
        }
        if($this->headerHas('mother_name_en')){
            $res[] = Helper::lang("Mother's Name (en)", 'মাতার নাম (ইংরেজি)');
        }
        if($this->headerHas('mother_name_bn')){
            $res[] = Helper::lang("Mother's Name (bn)", 'মাতার নাম (বাংলা)');
        }
        if($this->headerHas('spouse_name_en')){
            $res[] = Helper::lang("Spouse's Name (en)", 'স্বামী/স্ত্রীর নাম (ইংরেজি)');
        }
        if($this->headerHas('spouse_name_bn')){
            $res[] = Helper::lang("Spouse's Name (bn)", 'স্বামী/স্ত্রীর নাম (বাংলা)');
        }
        if($this->headerHas('main_program')){
            $res[] = Helper::lang('Allowance Program', 'ভাতা কার্যক্রম');
        }
        if($this->headerHas('program')){
            $res[] = Helper::lang('Sub Allowance Program', 'উপ ভাতা কার্যক্রম');
        }
        if($this->headerHas('verification_number')){
            $res[] = Helper::lang('NID Number', 'জাতীয় পরিচয়পত্র নাম্বার');
        }
        $res[] = Helper::lang('Status', 'স্ট্যাটাস');
        // $res[] = Helper::lang('Verification Status', 'ভ্যারিফিকেশন স্ট্যাটাস');
        if($this->headerHas('mobile')){
            $res[] = Helper::lang('Mobile', 'মোবাইল');
        }
        if($this->headerHas('bank_mfs_name')){
            $res[] = Helper::lang('Bank/MFS', 'ব্যাংক/এমএফএস');
        }
        if($this->headerHas('account_number')){
            $res[] = Helper::lang('Account Number', 'এ্যাকাউন্ট নাম্বার');
        }
        if($this->headerHas('age_calculated')){
            $res[] = Helper::lang('Age', 'বয়স');
        }
        if($this->headerHas('permanentDivision.name_en') || $this->headerHas('permanentDivision.name_bn')){
            $res[] = Helper::lang('Division', 'বিভাগ');
        }
        if($this->headerHas('permanentDistrict.name_en') || $this->headerHas('permanentDistrict.name_bn')){
            $res[] = Helper::lang('District', 'জেলা');
        }
        if($this->headerHas('location_upccd')){
            $res[] = Helper::lang('Upazila/City Corp./Zilla Pouroshava', 'উপজেলা/সিটি কর্প/জেলা পৌরসভা');
        }
        if($this->headerHas('location_unpoth')){
            $res[] = Helper::lang('Union/Thana/Pouroshava', 'ইউনিয়ন/থানা/পৌরসভা');
        }
        if($this->headerHas('permanentWard.name_en') || $this->headerHas('permanentWard.name_bn')){
            $res[] = Helper::lang('Ward', 'ওয়ার্ড');
        }
        if($this->headerHas('permanent_address')){
            $res[] = Helper::lang('Permanent Address', 'স্থায়ী ঠিকানা');
        }
        if($this->headerHas('get_office.name_en') || $this->headerHas('get_office.name_bn')){
            $res[] = Helper::lang('Office Name', 'অফিসের নাম');
        }
        if($this->headerHas('get_office_type.value_en')){
            $res[] = Helper::lang('Office Type', 'অফিসের ধরন');
        }
        if($this->headerHas('status_name') || $this->headerHas('verify_action')){
            $res[] = Helper::lang('Verification Status', 'ভ্যারিফিকেশন স্ট্যাটাস');
        }
        if($this->headerHas('disability_id')){
            $res[] = Helper::lang('Disability ID', 'ডিজাবিলিটি আইডি');
        }
        if($this->headerHas('class_level')){
            $res[] = Helper::lang('Class Level', 'ক্লাস স্তর');
        }
        return [
            ...$res,
            ...$addis
        ];
    }

    public function chunkSize(): int
    {
        return 2500;  // Process 1000 records at a time
    }
}
