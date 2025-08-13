<?php

namespace App\Models;

use App\Models\Allotment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\AllowanceProgram
 *
 * @property int $id
 * @property string $code
 * @property string $name_en
 * @property string $name_bn
 * @property string|null $guideline
 * @property int $service_type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereGuideline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereUpdatedAt($value)
 * @property string $nameEn
 * @property string $nameBn
 * @property int $serviceType
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property int $version
 * @property-read \App\Models\Lookup|null $lookup
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereVersion($value)
 * @property string $paymentCycle
 * @property int|null $isMarital
 * @property string|null $maritalStatus
 * @property int $isActive
 * @property int $is_nominee_optional
 * @property int|null $isAgeLimit
 * @property int $isDisableClass
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdditionalFields> $addtionalfield
 * @property-read int|null $addtionalfieldCount
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereis_nominee_optional($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereIsAgeLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereIsDisableClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereIsMarital($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgram wherePaymentCycle($value)
 * @mixin \Eloquent
 */
class AllowanceProgram extends Model
{
    public function genderLookup()
{
   // Split the "23,24" string into an array
$genderIds = explode(',', $this->gender);

// Fetch the names of genders from the Gender model
return Lookup::whereIn('id', $genderIds)->pluck('value_en')->toArray();

}
    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)
            ->orderBy('name_en', 'asc');
    }
    public function lookup()
    {
        return $this->belongsTo(Lookup::class,'service_type');
    }

    public function addtionalfield()
    {
        return $this->belongsToMany(AdditionalFields::class, 'additional_fields_allowance_program', 'allowance_program_id','field_id')->withPivot('display_order');
    }

    public function ages(){
        return $this->hasMany(AllowanceProgramAge::class,'allowance_program_id');
    }


    public function classAmounts()
    {
        return $this->hasMany(AllowanceProgramAmount::class, 'allowance_program_id', 'id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'program_id');
    }
     public function grievances()
    {
        return $this->hasMany(Grievance::class, 'program_id');
    }

    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class, 'program_id');
    }

    /**
     * Get all of the payroll for the AllowanceProgram
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payroll(): HasMany
    {
        return $this->hasMany(Payroll::class, 'program_id', 'id');
    }

    /**
     * Get all of the emergencyPayroll for the AllowanceProgram
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emergencyPayroll(): HasMany
    {
        return $this->hasMany(EmergencyPayroll::class, 'program_id', 'id');
    }

    public function programAmount(): HasOne
    {
        return $this->hasOne(AllowanceProgramAmount::class, 'allowance_program_id', 'id');
    }

    public function budgets(){
        return $this->hasMany(Budget::class, 'program_id', 'id');
    }
    
    public function allotments(){
        return $this->hasMany(Allotment::class, 'program_id', 'id');
    }

    public function disableAreas(){
        return $this->hasMany(OnlineApplicationDisabledLocation::class, 'allowance_program_id', 'id');
    }
    
    public function subPrograms(){
        return $this->hasMany(AllowanceProgram::class, 'parent_id', 'id');
    }

    public function parent(){
        return $this->belongsTo(AllowanceProgram::class, 'parent_id', 'id');
    }

    public function preAssignedFileds(){
        return $this->belongsToMany(AdditionalFields::class, 'program_additional_field_pre_assignments',  'allowance_program_id','additional_field_id', 'id','id');
    }
}