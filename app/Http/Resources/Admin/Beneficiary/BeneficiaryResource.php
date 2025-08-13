<?php

namespace App\Http\Resources\Admin\Beneficiary;

use Log;
use Storage;
use Carbon\Carbon;
use App\Models\Mfs;
use App\Models\Bank;
use App\Models\Lookup;
use App\Models\Office;
use App\Models\BankBranch;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;

class BeneficiaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $getBeneficiaryAccountChange = $this->getBeneficiaryChangeTrackingAccountChange
                                            ->select('change_type_id', 'change_value', 'previous_value' , 'status')
                                            ->where('status', 1)
                                            ->first();
        if($getBeneficiaryAccountChange){
            $getBeneficiaryAccountChange["change_value"] = json_decode($getBeneficiaryAccountChange["change_value"], true);
            if($getBeneficiaryAccountChange["change_value"]['account_type'] == 1){
                $getBeneficiaryAccountChange["change_value"]['bank'] =  Bank::find($getBeneficiaryAccountChange["change_value"]["bank_id"]);
                $getBeneficiaryAccountChange["change_value"]['branch'] =  BankBranch::find($getBeneficiaryAccountChange["change_value"]["bank_branch_id"]);
                $getBeneficiaryAccountChange["change_value"]['mfs'] = null;
            }else{
                $getBeneficiaryAccountChange["change_value"]['bank'] = null;
                $getBeneficiaryAccountChange["change_value"]['branch'] = null;
                $getBeneficiaryAccountChange["change_value"]['mfs'] =  Mfs::find($getBeneficiaryAccountChange["change_value"]["mfs_id"]);
            }
            $getBeneficiaryAccountChange["previous_value"] = json_decode($getBeneficiaryAccountChange["previous_value"], true);
            if($getBeneficiaryAccountChange["previous_value"]['account_type'] == 1){
                $getBeneficiaryAccountChange["previous_value"]['bank'] =  Bank::find($getBeneficiaryAccountChange["previous_value"]["bank_id"]);
                $getBeneficiaryAccountChange["previous_value"]['branch'] =  BankBranch::find($getBeneficiaryAccountChange["previous_value"]["bank_branch_id"]);
                $getBeneficiaryAccountChange["previous_value"]['mfs'] = null;
            }else{
                $getBeneficiaryAccountChange["previous_value"]['bank'] = null;
                $getBeneficiaryAccountChange["previous_value"]['branch'] = null;
                $getBeneficiaryAccountChange["previous_value"]['mfs'] =  Mfs::find($getBeneficiaryAccountChange["previous_value"]["mfs_id"]);
            }
            $changeValue = $getBeneficiaryAccountChange["change_value"];

            $account_type = (int) ($changeValue["account_type"] ?? $this->account_type);
            $account_name = $changeValue["account_name"] ?? $this->account_name;
            $account_owner = (int) ($changeValue["account_owner"] ?? $this->account_owner);
            $account_number = $changeValue["account_number"] ?? $this->account_number;
            $mfs_id = (int) ($changeValue["mfs_id"] ?? $this->mfs_id);
            $bank_id = (int) ($changeValue["bank_id"] ?? $this->bank_id);
            $bank_branch_id = (int) ($changeValue["bank_branch_id"] ?? $this->bank_branch_id);
        } else {
            $account_type = (int) $this->account_type;
            $account_name = $this->account_name;
            $account_owner = (int) $this->account_owner;
            $account_number =  $this->account_number;

            $mfs_id = (int) $this->mfs_id;
            $bank_id = (int) $this->bank_id;
            $bank_branch_id = (int) $this->bank_branch_id;
        }

        $get_office_id_from_wards = $this->get_office_id_from_wards ? $this->get_office_id_from_wards->first() : null;
        $get_office = null;
        $get_office_type = null;
        if($get_office_id_from_wards){
            $get_office_id_from_wards = $get_office_id_from_wards['office_id'];
            $get_office = Office::find($get_office_id_from_wards);
            $get_office_type = Lookup::find($get_office->office_type);
        }

        $accountOwnerLookup = Lookup::find($account_owner);

        return [
            "id" => $this->id,
            "program_id" => $this->program_id,
            'main_program' => $this->mainProgram,
            'program' => AllowanceResource::make($this->whenLoaded('program')),
            "beneficiary_id" => $this->beneficiary_id,
            "application_id" => $this->application_id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "spouse_name_en" => $this->spouse_name_en,
            "spouse_name_bn" => $this->spouse_name_bn,
            "beneficiary_address" => $this->beneficiary_address(),
            "identification_mark" => $this->identification_mark,
            "age" => $this->age,
            "date_of_birth" => $this->date_of_birth == null? '' : Carbon::parse($this->date_of_birth)->format('d-m-Y'),
            "age_calculated" => Carbon::parse($this->date_of_birth)->diffInYears(Carbon::now()),
            "gender" => LookupResource::make($this->gender),
            // "gender" => LookupResource::make($this->whenLoaded('gender')),
            "nationality" => LookupResource::make($this->whenLoaded('ben_nationality')),
            "education_status" => LookupResource::make($this->whenLoaded('ben_education_status')),
            "profession" => LookupResource::make($this->whenLoaded('ben_profession')),
            "religion" => LookupResource::make($this->whenLoaded('ben_religion')),
            "marital_status" => LookupResource::make($this->whenLoaded('ben_marital_status')),
            "email" => $this->email,
            "verification_type" => $this->verification_type,
            "verification_number" => $this->verification_number,
            "image" => $this->image,//Storage::disk('public')->url($this->image),
            "signature" => $this->signature,//Storage::disk('public')->url($this->signature),
            "current_division_id" => $this->current_division_id,
            "currentDivision" => LocationResource::make($this->whenLoaded('currentDivision')),
            "current_district_id" => $this->current_district_id,
            "currentDistrict" => LocationResource::make($this->whenLoaded('currentDistrict')),
            "current_upazila_id" => $this->current_upazila_id,
            "currentUpazila" => LocationResource::make($this->whenLoaded('currentUpazila')),
            "current_city_corp_id" => $this->current_city_corp_id,
            "currentCityCorporation" => LocationResource::make($this->whenLoaded('currentCityCorporation')),
            "current_district_pourashava_id" => $this->current_district_pourashava_id,
            "currentDistrictPourashava" => LocationResource::make($this->whenLoaded('currentDistrictPourashava')),
            "current_thana_id" => $this->current_thana_id,
            "currentThana" => LocationResource::make($this->whenLoaded('currentThana')),
            "current_pourashava_id" => $this->current_pourashava_id,
            "currentPourashava" => LocationResource::make($this->whenLoaded('currentPourashava')),
            "current_union_id" => $this->current_union_id,
            "currentUnion" => LocationResource::make($this->whenLoaded('currentUnion')),
            "current_ward_id" => $this->current_ward_id,
            "currentWard" => LocationResource::make($this->whenLoaded('currentWard')),
            "current_post_code" => $this->current_post_code,
            "current_address" => $this->current_address,
            "mobile" => $this->mobile,
            "permanent_division_id" => $this->permanent_division_id,
            "permanentDivision" => LocationResource::make($this->whenLoaded('permanentDivision')),
            "permanent_district_id" => $this->permanent_district_id,
            "permanentDistrict" => LocationResource::make($this->whenLoaded('permanentDistrict')),
            "permanent_upazila_id" => $this->permanent_upazila_id,
            "permanentUpazila" => LocationResource::make($this->whenLoaded('permanentUpazila')),
            "permanent_city_corp_id" => $this->permanent_city_corp_id,
            "permanentCityCorporation" => LocationResource::make($this->whenLoaded('permanentCityCorporation')),
            "permanent_district_pourashava_id" => $this->permanent_district_pourashava_id,
            "permanentDistrictPourashava" => LocationResource::make($this->whenLoaded('permanentDistrictPourashava')),
            "permanent_thana_id" => $this->permanent_thana_id,
            "permanentThana" => LocationResource::make($this->whenLoaded('permanentThana')),
            "permanent_pourashava_id" => $this->permanent_pourashava_id,
            "permanentPourashava" => LocationResource::make($this->whenLoaded('permanentPourashava')),
            "permanent_union_id" => $this->permanent_union_id,
            "permanentUnion" => LocationResource::make($this->whenLoaded('permanentUnion')),
            "permanent_ward_id" => $this->permanent_ward_id,
            "permanentWard" => LocationResource::make($this->whenLoaded('permanentWard')),
            "permanent_post_code" => $this->permanent_post_code,
            "permanent_address" => $this->permanent_address,
            "permanent_mobile" => $this->permanent_mobile,
            "union_or_pourashava" => ($this->permanentUnion?->name_en ?: $this->permanentPourashava?->name_en),
            "nominee_en" => $this->nominee_en,
            "nominee_bn" => $this->nominee_bn,
            "nominee_verification_number" => $this->nominee_verification_number,
            "nominee_address" => $this->nominee_address,
            "nominee_image" => $this->nominee_image,//Storage::disk('public')->url($this->nominee_image),
            "nominee_signature" => $this->nominee_signature,//Storage::disk('public')->url($this->nominee_signature),
            "nominee_relation_with_beneficiary" => $this->nominee_relation_with_beneficiary,
            "nominee_nationality" => LookupResource::make($this->getNomineeNationality),
            "account_type" => $this->account_type,
            "bank_id" => $bank_id,
            "bank_name" => $this->getBankName($bank_id),
            "mfs_id" => $mfs_id,
            "mfs_name" => $this->getMfsName($mfs_id),
            "bank_branch_id" => $bank_branch_id,
            "branch_name" => $this->getBankBranchName($bank_branch_id),
            "account_name" => $account_name,
            "account_number" => $account_number,
            "account_owner" => $accountOwnerLookup != null ? LookupResource::make($accountOwnerLookup) : null,
//            "account_owner_type" => LookupResource::make(intval($account_owner)),
            "financial_year_id" => $this->financial_year_id,
            "financialYear" => FinancialResource::make($this->whenLoaded('financialYear')),
            "monthly_allowance" => $this->allowanceAmount(),
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "score" => $this->score,
            "delete_cause" => $this->delete_cause,
            "deleted_at" => $this->deleted_at,
            "application_date" => $this->application_date,
            "approve_date" => $this->approve_date,
            "is_new_beneficiary" => $this?->is_new_beneficiary ?? false,
            "is_account_changed" => $this?->is_account_changed ?? false,
            "is_replaced" => $this?->is_replaced ?? false,
            "is_regular_beneficiary" => $this?->is_regular_beneficiary ?? false,
            "is_verified" => $this?->verifyLogs()->where('financial_year_id', app('CurrentFinancialYear')?->id)->exists() ?? false,
            "last_payment_date" => $this?->last_payment_date,
            "last_payroll_date" => $this?->last_payroll_date,
            "inactive_cause" => LookupResource::make($this?->inactive_cause),
            "getBeneficiaryAccountChange" => $getBeneficiaryAccountChange,
//            "get_office_id_from_wards" => $get_office_id_from_wards,
            "get_office" => $get_office,
            "get_office_type" => $get_office_type,
            // "has_payment" => $this->PaymentCycleDetails()->where('financial_year_id',getCurrentFinancialYear()->id)->count() > 0,
            "duplicates" => $this->is_duplicate? $this->getDupliicates() : [],
            'disability_id' => $this->DISABILITY_ID,
            "class_level"   => LookupResource::make($this->allowance_class),
            'additional_fields' => $this->whenLoaded('allowAddiFields', function () {
                return $this->formatAdditionalFields();
            }),

            ...$this->getAdditionalData()
        ];
    }

    private function getAdditionalData(){
        if(request()->has('additionalFieldIds')){
            $values = $this->application?->applicationAllowanceValues()->whereIn('allow_addi_fields_id', request()->additionalFieldIds)->with('additionalFieldValue','additionalField')->get()??[];
            $data = [];
            Log::debug(json_encode($values));
            foreach($values as $value){
                $key = str_replace(' ', '_', strtolower($value->additionalField->name_en));
                if($value->additionalFieldValue){
                    $data[$key] = $value->additionalFieldValue;
                }else{
                    $data[$key] = $value->value;
                }
            }
            return $data;
        }

        return [];
    }

    private function getDupliicates(){
        // return Beneficiary::where('id', $this->id)->where([
        return Beneficiary::where('id', '<>', $this->id)->where([
            'name_en' => $this->name_en,
            'mother_name_en' => $this->mother_name_en,
            'father_name_en' => $this->father_name_en,
            'date_of_birth' => $this->date_of_birth
        ])->with('program')->get()
        // ->map(function($item){
        //     $item->program = AllowanceResource::make($item->program);
        // })
        ;
    }

    protected function formatAdditionalFields()
    {
        return $this->allowAddiFields->map(function ($field) {

            $pivotData = $field->pivot ?? null;

            if (!$pivotData) {
                return [
                    'id'       => $field->id,
                    'name_en'  => $field->name_en,
                    'name_bn'  => $field->name_bn,
                    'type'     => $field->type,
                    'verified' => $field->verified,
                    'option'   => $field->option,
                    'pivot'    => null,
                    'allow_addi_field_values' => [],
                ];
            }

            $selectedValueId = $pivotData->allow_addi_field_values_id ?? null;

//            logger()->info('Selected Value ID for field '.$field->id.': '.$selectedValueId);

            $filteredOptions = collect([]);

            if ($field->type === 'dropdown' && $selectedValueId) {
                $filteredOptions = $field->additional_field_value
                    ->where('id', $selectedValueId)
                    ->values();
            }

//            logger()->info('filteredOptions  '.$field->id.': '. $filteredOptions);

            return [
                'id'       => $field->id,
                'name_en'  => $field->name_en,
                'name_bn'  => $field->name_bn,
                'type'     => $field->type,
                'verified' => $field->verified,
                'option'   => $field->option,
                'pivot'    => [
                    'beneficiary_id'             => $pivotData->beneficiary_id,
                    'allow_addi_fields_id'       => $pivotData->allow_addi_fields_id,
                    'value'                      => $pivotData->value,
                    'allow_addi_field_values_id' => $selectedValueId,
                ],
                'allow_addi_field_values' => $filteredOptions->map(function ($option) {
                    return [
                        'id'                  => $option->id,
                        'additional_field_id' => $option->additional_field_id,
                        'value'               => $option->value,
                        'value_en'            => $option->value_en,
                        'value_bn'            => $option->value_bn,
                    ];
                }),
            ];
        });
    }


    private function beneficiary_address()
    {
        $beneficiary_address = $this->permanent_address;
        if ($this->permanentUnion)
            $beneficiary_address .= ', ' . $this->permanentUnion?->name_en;
        elseif ($this->permanentPourashava)
            $beneficiary_address .= ', ' . $this->permanentPourashava?->name_en;
        elseif ($this->permanentThana)
            $beneficiary_address .= ', ' . $this->permanentThana?->name_en;

        if ($this->permanentUpazila)
            $beneficiary_address .= ', ' . $this->permanentUpazila?->name_en;
        elseif ($this->permanentCityCorporation)
            $beneficiary_address .= ', ' . $this->permanentCityCorporation?->name_en;
        elseif ($this->permanentDistrictPourashava)
            $beneficiary_address .= ', ' . $this->permanentDistrictPourashava?->name_en;

        if ($this->permanentDistrict)
            $beneficiary_address .= ', ' . $this->permanentDistrict?->name_en;

        return $beneficiary_address;
    }

    public function getBankName($id)
    {
        if ($id != null) {
            return Bank::select('id', 'name_en', 'name_bn', 'charge')->where('id', $id)->first();
        } else {
            return '';
        }
    }
    public function getBankBranchName($id)
    {
        if ($id != null) {
            return BankBranch::select('id', 'bank_id', 'name_en', 'name_bn', 'district_id')
                                ->where('id', $id)->first();
        } else {
            return '';
        }
    }
    public function getMfsName($id)
    {
        if ($id != null) {
            return Mfs::where('id', $id)->first();
        } else {
            return '';
        }

    }
}
