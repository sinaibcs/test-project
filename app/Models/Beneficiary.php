<?php

namespace App\Models;

use App\Http\Services\Admin\Beneficiary\BeneficiaryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Beneficiary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'beneficiaries';
    protected $guarded = ['id'];

    public function mainProgram()
    {
        return $this->belongsTo(AllowanceProgram::class, 'main_program_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gender()
    {
        return $this->belongsTo(Lookup::class, 'gender_id');
    }
    public function allowance_class()
    {
        return $this->belongsTo(Lookup::class, 'type_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ben_religion()
    {
        return $this->belongsTo(Lookup::class, 'religion');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ben_education_status()
    {
        return $this->belongsTo(Lookup::class, 'education_status');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ben_profession()
    {
        return $this->belongsTo(Lookup::class, 'profession');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ben_marital_status()
    {
        return $this->belongsTo(Lookup::class, 'marital_status');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ben_nationality()
    {
        return $this->belongsTo(Lookup::class, 'nationality');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentDivision()
    {
        return $this->belongsTo(Location::class, 'current_division_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentDistrict()
    {
        return $this->belongsTo(Location::class, 'current_district_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentCityCorporation()
    {
        return $this->belongsTo(Location::class, 'current_city_corp_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentDistrictPourashava()
    {
        return $this->belongsTo(Location::class, 'current_district_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentUpazila()
    {
        return $this->belongsTo(Location::class, 'current_upazila_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentPourashava()
    {
        return $this->belongsTo(Location::class, 'current_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentThana()
    {
        return $this->belongsTo(Location::class, 'current_thana_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentUnion()
    {
        return $this->belongsTo(Location::class, 'current_union_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentWard()
    {
        return $this->belongsTo(Location::class, 'current_ward_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentDivision()
    {
        return $this->belongsTo(Location::class, 'permanent_division_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentDistrict()
    {
        return $this->belongsTo(Location::class, 'permanent_district_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentCityCorporation()
    {
        return $this->belongsTo(Location::class, 'permanent_city_corp_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentDistrictPourashava()
    {
        return $this->belongsTo(Location::class, 'permanent_district_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentUpazila()
    {
        return $this->belongsTo(Location::class, 'permanent_upazila_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentPourashava()
    {
        return $this->belongsTo(Location::class, 'permanent_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentThana()
    {
        return $this->belongsTo(Location::class, 'permanent_thana_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentUnion()
    {
        return $this->belongsTo(Location::class, 'permanent_union_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentWard()
    {
        return $this->belongsTo(Location::class, 'permanent_ward_id', 'id');
    }

    /**
     * Get the PayrollDetails associated with the Beneficiary
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function PayrollDetails(): HasMany
    {
        return $this->hasMany(PayrollDetail::class, 'beneficiary_id', 'beneficiary_id');
    }

    /**
     * Get the PaymentCycle associated with the Beneficiary
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function PaymentCycleDetails(): HasMany
    {
        return $this->hasMany(PayrollPaymentCycleDetail::class, 'beneficiary_id', 'beneficiary_id');
    }

    /**
     * Get all of the beneficiaryPayrollPaymentStatusLog for the Beneficiary
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function beneficiaryPayrollPaymentStatusLog(): HasMany
    {
        return $this->hasMany(BeneficiaryPayrollPaymentStatusLog::class, 'beneficiary_id', 'beneficiary_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(BankBranch::class, 'bank_branch_id', 'id');
    }

    public function mfs()
    {
        return $this->belongsTo(Mfs::class, 'mfs_id', 'id');
    }

    public function getReligion()
    {
        return $this->belongsTo(Lookup::class, 'religion');
    }

    public function getEducation()
    {
        return $this->belongsTo(Lookup::class, 'education_status');
    }

    public function getProfession()
    {
        return $this->belongsTo(Lookup::class, 'profession');
    }

    public function getMaritialStatus()
    {
        return $this->belongsTo(Lookup::class, 'marital_status');
    }

    public function getNationality()
    {
        return $this->belongsTo(Lookup::class, 'nationality');
    }
    public function getNomineeNationality()
    {
        return $this->belongsTo(Lookup::class, 'nominee_nationality');
    }

    public function getImageAttribute()
    {
        $file = $this->attributes['image'] ?? null;
        if ($file)
            $url = asset("cloud/".$file);
        else
            $url = null;
        return $url;
    }

    public function getSignatureAttribute()
    {
        $file = $this->attributes['signature'] ?? null;
        if ($file)
            $url = asset("cloud/".$file);
        else
            $url = null;
        return $url;
    }

    public function getNomineeImageAttribute()
    {
        $file = $this->attributes['nominee_image'] ?? null;
        if ($file)
            $url = asset("cloud/".$file);
        else
            $url = asset(env('AVATER_PHOTO_PLACEHOLDER_PATH'));
        return $url;
    }

    public function getNomineeSignatureAttribute()
    {
        $file = $this->attributes['nominee_signature'] ?? null;
        if ($file)
            $url = asset("cloud/".$file);
        else
            $url = asset(env('SIGNATURE_PLACEHOLDER_PATH'));
        return $url;
    }

    /**
     * Get all of the verifyLogs for the Beneficiary
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifyLogs(): HasMany
    {
        return $this->hasMany(BeneficiaryVerifyLog::class, 'beneficiary_id', 'id');
    }

    public function changeTrackings()
    {
        return $this->hasMany(BeneficiaryChangeTracking::class, 'beneficiary_id')
            ->orderBy('id', 'desc');
    }

    public function getBeneficiaryChangeTrackingAccountChange(): HasMany
    {
        return $this->hasMany(BeneficiaryChangeTracking::class, 'beneficiary_id', 'id')
                        ->where('change_type_id', 3)
                        ->orderBy('id', 'desc');
    }

    public function get_office_id_from_wards()
    {
        return $this->hasMany(OfficeHasWard::class, 'ward_id', 'permanent_ward_id')
            ->orderBy('id', 'desc');
    }


    /**
     * Get the mfsAccountValidation associated with the Beneficiary
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function mfsAccountVarification(): MorphOne
    {
        return $this->morphOne(MfsAccountVerification::class, 'verifiable');
    }

    /**
     * Get the application that owns the Beneficiary
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id', 'application_id');
    }

    public function allowanceAmount(){
        return BeneficiaryService::getMonthlyAllowanceAmount($this);
    }

    public function getStatus($lang = 'en'){
        if($lang == 'en'){
            if($this->status == 1){
                return 'Active';
            }elseif($this->status == 2){
                return 'Inactive';
            }elseif($this->status == 3){
                return 'Waiting';
            }
        }elseif($lang == 'bn'){
            if($this->status == 1){
                return 'সক্রিয়';
            }elseif($this->status == 2){
                return 'নিষ্ক্রিয়';
            }elseif($this->status == 3){
                return 'অপেক্ষেয়মান';
            }
        }
        return '';
    }

    public function allowAddiFields()
    {
        return $this->belongsToMany(
            AdditionalFields::class,
            'beneficiaries_allowance_values',
            'beneficiary_id',
            'allow_addi_fields_id'
        )
            ->withPivot(['value', 'allow_addi_field_values_id'])
            ->using(BeneficiaryAllowanceValuePivot::class);
    }

    public function allowAddiFieldValues()
    {
        return $this->belongsToMany(
            AdditionalFieldValues::class,
            'beneficiaries_allowance_values',
            'beneficiary_id',
            'allow_addi_field_values_id'
        )
            ->withPivot('value')
            ->using(BeneficiaryAllowanceValuePivot::class);
    }

}
