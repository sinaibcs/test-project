<?php

namespace App\Exports;

use Log;
use Carbon\Carbon;
use App\Models\Lookup; // Assuming this is still used by Helper or other parts
use App\Models\Office;  // Assuming this is still used by Helper or other parts
use App\Helpers\Helper;
use App\Models\Beneficiaries; // Assuming this is the type of $beneficiariesQuery items
use App\Models\AdditionalFields;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Database\Eloquent\Builder; // For type hinting the query

class BeneficiariesSpatieExport
{
    protected Builder $beneficiariesQuery;
    private mixed $additionals = null; // Keep for caching additional fields

    public function __construct(Builder $beneficiariesQuery)
    {
        $this->beneficiariesQuery = $beneficiariesQuery;
    }

    private function headerHas(string $val): bool
    {
        return in_array($val, request()->input('visibleHeaders', []));
    }

    private function prepareQuery(): Builder
    {
        $query = clone $this->beneficiariesQuery; // Clone to avoid modifying the original instance if reused
        $query->with('get_office_id_from_wards.office.officeType');

        if (request()->has('additionalFieldIds')) {
            $query->with(['application' => function ($q) {
                $q->with(['applicationAllowanceValues' => function ($q) {
                    $q->whereIn('allow_addi_fields_id', request()->input('additionalFieldIds', []))
                      ->with('additionalFieldValue');
                }]);
            }]);
        }
        // Log::debug($query->toRawSql());
        return $query;
    }

    private function getHeaders(): array
    {
        $addis = [];
        foreach ($this->getAdditionalFields() as $addi) {
            $addis[] = Helper::lang($addi->name_en, $addi->name_bn);
        }

        $res = [];
        if ($this->headerHas('beneficiary_id')) {
            $res[] = Helper::lang('MIS Number', 'এম আই এস নাম্বার');
        }
        if ($this->headerHas('name_en')) {
            $res[] = Helper::lang('Name (en)', 'নাম (ইংরেজি)');
        }
        if ($this->headerHas('name_bn')) {
            $res[] = Helper::lang('Name (bn)', 'নাম (বাংলা)');
        }
        if ($this->headerHas('gender.value_en')) {
            $res[] = Helper::lang('Gender', 'লিঙ্গ');
        }
        if ($this->headerHas('date_of_birth')) {
            $res[] = Helper::lang('Date of birth', 'জন্ম তারিখ');
        }
        if ($this->headerHas('father_name_en')) {
            $res[] = Helper::lang("Father's Name (en)", 'পিতার নাম (ইংরেজি)');
        }
        if ($this->headerHas('father_name_bn')) {
            $res[] = Helper::lang("Father's Name (bn)", 'পিতার নাম (বাংলা)');
        }
        if ($this->headerHas('mother_name_en')) {
            $res[] = Helper::lang("Mother's Name (en)", 'মাতার নাম (ইংরেজি)');
        }
        if ($this->headerHas('mother_name_bn')) {
            $res[] = Helper::lang("Mother's Name (bn)", 'মাতার নাম (বাংলা)');
        }
        if ($this->headerHas('spouse_name_en')) {
            $res[] = Helper::lang("Spouse's Name (en)", 'স্বামী/স্ত্রীর নাম (ইংরেজি)');
        }
        if ($this->headerHas('spouse_name_bn')) {
            $res[] = Helper::lang("Spouse's Name (bn)", 'স্বামী/স্ত্রীর নাম (বাংলা)');
        }
        if ($this->headerHas('program_name')) {
            $res[] = Helper::lang('Allowance Program', 'ভাতা কার্যক্রম');
        }
        if ($this->headerHas('verification_number')) {
            $res[] = Helper::lang('NID Number', 'জাতীয় পরিচয়পত্র নাম্বার');
        }
        $res[] = Helper::lang('Status', 'স্ট্যাটাস'); // This was always added
        if ($this->headerHas('mobile')) {
            $res[] = Helper::lang('Mobile', 'মোবাইল');
        }
        if ($this->headerHas('bank_mfs_name')) {
            $res[] = Helper::lang('Bank/MFS', 'ব্যাংক/এমএফএস');
        }
        if ($this->headerHas('account_number')) {
            $res[] = Helper::lang('Account Number', 'এ্যাকাউন্ট নাম্বার');
        }
        if ($this->headerHas('age_calculated')) {
            $res[] = Helper::lang('Age', 'বয়স');
        }
        if ($this->headerHas('permanentDivision.name_en') || $this->headerHas('permanentDivision.name_bn')) {
            $res[] = Helper::lang('Division', 'বিভাগ');
        }
        if ($this->headerHas('permanentDistrict.name_en') || $this->headerHas('permanentDistrict.name_bn')) {
            $res[] = Helper::lang('District', 'জেলা');
        }
        if ($this->headerHas('location_upccd')) {
            $res[] = Helper::lang('Upazila/City Corp./Zilla Pouroshava', 'উপজেলা/সিটি কর্প/জেলা পৌরসভা');
        }
        if ($this->headerHas('location_unpoth')) {
            $res[] = Helper::lang('Union/Thana/Pouroshava', 'ইউনিয়ন/থানা/পৌরসভা');
        }
        if ($this->headerHas('permanentWard.name_en') || $this->headerHas('permanentWard.name_bn')) {
            $res[] = Helper::lang('Ward', 'ওয়ার্ড');
        }
        if ($this->headerHas('permanent_address')) {
            $res[] = Helper::lang('Permanent Address', 'স্থায়ী ঠিকানা');
        }
        if ($this->headerHas('get_office.name_en') || $this->headerHas('get_office.name_bn')) {
            $res[] = Helper::lang('Office Name', 'অফিসের নাম');
        }
        if ($this->headerHas('get_office_type.value_en')) {
            $res[] = Helper::lang('Office Type', 'অফিসের ধরন');
        }
        if ($this->headerHas('status_name') || $this->headerHas('verify_action')) {
            $res[] = Helper::lang('Verification Status', 'ভ্যারিফিকেশন স্ট্যাটাস');
        }
        if ($this->headerHas('disability_id')) {
            $res[] = Helper::lang('Disability ID', 'ডিজাবিলিটি আইডি');
        }

        return [
            ...$res,
            ...$addis
        ];
    }

    private function mapBeneficiaryToArray($beneficiary): array // Changed type hint if Beneficiaries is your model
    {
        $officeCnt = $beneficiary->get_office_id_from_wards->count();
        $get_office = $officeCnt == 0 ? null : $beneficiary->get_office_id_from_wards[0]?->office;
        $get_office_type = $officeCnt == 0 ? null : $beneficiary->get_office_id_from_wards[0]?->office?->officeType;

        $currentDate = Carbon::now();
        $age = $beneficiary->date_of_birth ? Carbon::parse($beneficiary->date_of_birth)->diffInYears($currentDate) : null; // Handle null DOB

        $addedData = [];
        $additionalDataValues = $this->getAdditionalDataValues($beneficiary);
        foreach ($this->getAdditionalFields() as $addi) {
            $addedData[] = $additionalDataValues[$addi->id] ?? '';
        }

        $res = [];
        if ($this->headerHas('beneficiary_id')) {
            $res[] = $beneficiary->beneficiary_id;
        }
        if ($this->headerHas('name_en')) {
            $res[] = $beneficiary->name_en;
        }
        if ($this->headerHas('name_bn')) {
            $res[] = $beneficiary->name_bn;
        }
        if ($this->headerHas('gender.value_en')) {
            $res[] = Helper::lang($beneficiary->gender?->value_en, $beneficiary->gender?->value_bn);
        }
        if ($this->headerHas('date_of_birth')) {
            $res[] = $beneficiary->date_of_birth == null ? '' : Carbon::parse($beneficiary->date_of_birth)->format('d-m-Y');
        }
        if ($this->headerHas('father_name_en')) {
            $res[] = $beneficiary->father_name_en;
        }
        if ($this->headerHas('father_name_bn')) {
            $res[] = $beneficiary->father_name_bn;
        }
        if ($this->headerHas('mother_name_en')) {
            $res[] = $beneficiary->mother_name_en;
        }
        if ($this->headerHas('mother_name_bn')) {
            $res[] = $beneficiary->mother_name_bn;
        }
        if ($this->headerHas('spouse_name_en')) {
            $res[] = $beneficiary->spouse_name_en;
        }
        if ($this->headerHas('spouse_name_bn')) {
            $res[] = $beneficiary->spouse_name_bn;
        }
        if ($this->headerHas('program_name')) {
            $res[] = Helper::lang($beneficiary->program->name_en, $beneficiary->program->name_bn);
        }
        if ($this->headerHas('verification_number')) {
            // For Excel to treat it as text, especially for numbers that might look like dates or be very long
            $res[] = $beneficiary->verification_number ? "'" . $beneficiary->verification_number : '';
        }
        // This status was always added
        $res[] =  Helper::lang($beneficiary->getStatus('en'), $beneficiary->getStatus('bn'));

        if ($this->headerHas('mobile')) {
            $res[] = $beneficiary->mobile ? "'" . $beneficiary->mobile : '';
        }
        if ($this->headerHas('bank_mfs_name')) {
            $res[] = $beneficiary->mfs_name ?: $beneficiary->bank_name ?: '';
        }
        if ($this->headerHas('account_number')) {
            $res[] = $beneficiary->account_number ? "'" . $beneficiary->account_number : '';
        }
        if ($this->headerHas('age_calculated')) {
            $res[] = $age;
        }
        if ($this->headerHas('permanentDivision.name_en') || $this->headerHas('permanentDivision.name_bn')) {
            $res[] = Helper::lang(optional($beneficiary->permanentDivision)->name_en, optional($beneficiary->permanentDivision)->name_bn);
        }
        if ($this->headerHas('permanentDistrict.name_en') || $this->headerHas('permanentDistrict.name_bn')) {
            $res[] = Helper::lang(optional($beneficiary->permanentDistrict)->name_en, optional($beneficiary->permanentDistrict)->name_bn);
        }
        if ($this->headerHas('location_upccd')) {
            $res[] = Helper::lang(
                optional($beneficiary->permanentDistrictPourashava)->name_en
                    ?: optional($beneficiary->permanentCityCorporation)->name_en
                    ?: optional($beneficiary->permanentUpazila)->name_en
                    ?: "",
                optional($beneficiary->permanentDistrictPourashava)->name_bn
                    ?: optional($beneficiary->permanentCityCorporation)->name_bn
                    ?: optional($beneficiary->permanentUpazila)->name_bn
                    ?: ""
            );
        }
        if ($this->headerHas('location_unpoth')) {
            $res[] = Helper::lang(
                optional($beneficiary->permanentUnion)->name_en
                    ?: optional($beneficiary->permanentPourashava)->name_en
                    ?: optional($beneficiary->permanentThana)->name_en ?: "",
                optional($beneficiary->permanentUnion)->name_bn
                    ?: optional($beneficiary->permanentPourashava)->name_bn
                    ?: optional($beneficiary->permanentThana)->name_bn ?: ""
            );
        }
        if ($this->headerHas('permanentWard.name_en') || $this->headerHas('permanentWard.name_bn')) {
            $res[] = Helper::lang(optional($beneficiary->permanentWard)->name_en ?: "", optional($beneficiary->permanentWard)->name_bn ?: "");
        }
        if ($this->headerHas('permanent_address')) {
            $res[] = $beneficiary->permanent_address;
        }
        if ($this->headerHas('get_office.name_en') || $this->headerHas('get_office.name_bn')) {
            $res[] = Helper::lang(optional($get_office)->name_en ?: "", optional($get_office)->name_bn ?: "");
        }
        if ($this->headerHas('get_office_type.value_en')) {
            $res[] = Helper::lang(optional($get_office_type)->value_en ?: "", optional($get_office_type)->value_bn ?: "");
        }
        if ($this->headerHas('status_name') || $this->headerHas('verify_action')) {
            $res[] = $beneficiary->verify_logs_count == 0 ? Helper::lang('Non-verified', 'যাচাই করা হয়নি') : Helper::lang('Verified', 'যাচাইকৃত');
        }
        if ($this->headerHas('disability_id')) {
             $res[] = $beneficiary->DISABILITY_ID ? "'" . $beneficiary->DISABILITY_ID : '';
        }

        return [
            ...$res,
            ...$addedData
        ];
    }

    private function getAdditionalDataValues($ben): array
    {
        if (request()->has('additionalFieldIds')) {
            $values = $ben->application?->applicationAllowanceValues ?? [];
            $data = [];
            foreach ($values as $value) {
                if ($value->additionalFieldValue) {
                    $data[$value->allow_addi_fields_id] = Helper::lang($value->additionalFieldValue->value_en, $value->additionalFieldValue->value_bn);
                } else {
                    $data[$value->allow_addi_fields_id] = $value->value;
                }
            }
            return $data;
        }
        return [];
    }

    private function getAdditionalFields() // No type hint, returns Collection or array
    {
        if ($this->additionals !== null) {
            return $this->additionals;
        }
        if (request()->has('additionalFieldIds')) {
            $this->additionals = AdditionalFields::whereIn('id', request()->input('additionalFieldIds', []))->get();
            return $this->additionals;
        }
        return collect(); // Return an empty collection if no IDs
    }

    public function download(string $filename = 'beneficiaries.xlsx')
    {
        $writer = SimpleExcelWriter::create(storage_path('app/' . $filename)); // Or directly to browser
        // Or: $writer = SimpleExcelWriter::streamDownload($filename);


        // 1. Add Headers
        $headers = $this->getHeaders();
        $writer->addHeader($headers);

        // 2. Prepare Query
        $query = $this->prepareQuery();

        // 3. Process data in chunks
        $chunkSize = 2500; // Same as your Maatwebsite example
        $query->chunkById($chunkSize, function ($beneficiariesChunk) use ($writer) {
            $rows = [];
            foreach ($beneficiariesChunk as $beneficiary) {
                $rows[] = $this->mapBeneficiaryToArray($beneficiary);
            }
            $writer->addRows($rows);

            // If processing very large files and memory is still an issue with
            // keeping the writer in memory for too long, you might need to
            // write to disk, close, and then re-open for appending,
            // but SimpleExcelWriter itself is designed to be memory efficient.
            // For most cases, this direct addRows within chunk is fine.
        });

        // Option 1: Download directly to browser
        // For this to work, you must not have outputted anything before (like headers from Laravel)
        // And the SimpleExcelWriter must be created with `streamDownload()`
        // Example:
        // $writer = SimpleExcelWriter::streamDownload($filename);
        // ... (add header, add rows in chunk)
        // $writer->close(); // This will trigger the download.
        // return null; // Or an appropriate response if your controller handles it.

        // Option 2: Save to file and then offer download
        $filePath = storage_path('app/' . $filename);
        $writer->toFile($filePath); // This closes the writer
        
        // Make sure to clear the cached additional fields if this instance might be reused
        // though typically for an export, it's a one-off process.
        $this->additionals = null;

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}