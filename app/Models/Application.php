<?php

namespace App\Models;

use App\Models\Variable;
use App\Models\OfficeHasWard;
use App\Models\AdditionalFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Application
 *
 * @property int $id
 * @property string $applicationId
 * @property int|null $forwardCommitteeId
 * @property string|null $remark
 * @property int $programId
 * @property string $verificationType
 * @property string $verificationNumber
 * @property int $age
 * @property string $dateOfBirth
 * @property string $nameEn
 * @property string $nameBn
 * @property string $motherNameEn
 * @property string $motherNameBn
 * @property string $fatherNameEn
 * @property string $fatherNameBn
 * @property string $spouseNameEn
 * @property string $spouseNameBn
 * @property string $identificationMark
 * @property string $image
 * @property string $signature
 * @property string $nationality
 * @property int $genderId
 * @property string $educationStatus
 * @property string $profession
 * @property string $religion
 * @property int $currentLocationId
 * @property string $currentPostCode
 * @property string $currentAddress
 * @property string $mobile
 * @property int $permanentLocationId
 * @property string $permanentPostCode
 * @property string $permanentAddress
 * @property string $permanentMobile
 * @property string $nomineeEn
 * @property string $nomineeBn
 * @property string $nomineeVerificationNumber
 * @property string $nomineeAddress
 * @property string $nomineeImage
 * @property string $nomineeSignature
 * @property string $nomineeRelationWithBeneficiary
 * @property string $nomineeNationality
 * @property string $accountName
 * @property string $accountNumber
 * @property string $accountOwner
 * @property string $maritalStatus
 * @property string $email
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAccountOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCurrentAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCurrentLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCurrentPostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereEducationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereFatherNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereFatherNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereForwardCommitteeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereGenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereIdentificationMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereMotherNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereMotherNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeRelationWithBeneficiary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNomineeVerificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePermanentAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePermanentLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePermanentMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePermanentPostCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereProfession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereReligion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSpouseNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSpouseNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereVerificationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereVerificationType($value)
 * @mixin \Eloquent
 */
class Application extends Model
{
    use HasFactory, SoftDeletes;

    // hide these fields from json response
    // protected $hidden = ['score'];
    protected $fillable = ['status'];

    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted);
        // ->orderBy('score', 'asc');
    }

    public static function permanentDistrict($location_id)
    {
        // dd($location_id);
        // permanent_location_id get this parent_id parent_id rations location id by maintaining chain
        $permanentLocation = Location::find($location_id);
        // check location type and then again and again check parent id and type while not get type = district
        while ($permanentLocation->type != 'district') {
            $permanentLocation = Location::find($permanentLocation->parent_id);
        }
        return $permanentLocation;
    }
    public static function permanentDivision($location_id)
    {
        // permanent_location_id get this parent_id parent_id rations location id by maintaining chain
        $permanentLocation_Division = Location::find($location_id);
        // check location type and then again and again check parent id and type while not get type = district
        while ($permanentLocation_Division->type != 'division') {
            $permanentLocation_Division = Location::find($permanentLocation_Division->parent_id);
        }
        return $permanentLocation_Division;
    }

    public function current_location()
    {
        return $this->belongsTo(Location::class, 'current_location_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }
    public function permanent_location()
    {
        return $this->belongsTo(Location::class, 'permanent_location_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function mainProgram()
    {
        return $this->belongsTo(AllowanceProgram::class, 'main_program_id', 'id');
    }
    public function program()
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id', 'id');
    }
    public function gender()
    {
        return $this->belongsTo(Lookup::class, 'gender_id', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }
    public function religion()
    {
        return $this->belongsTo(Lookup::class, 'religion', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }
    public function classes()
    {
        return $this->belongsTo(Lookup::class, 'type_id', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }
    public function accountOwner()
    {
        return $this->belongsTo(Lookup::class, 'account_owner', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }
    public function maritalStatus()
    {
        return $this->belongsTo(Lookup::class, 'marital_status', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }
    public function educationStatus()
    {
        return $this->belongsTo(Lookup::class, 'education_status', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }
    public function profession()
    {
        return $this->belongsTo(Lookup::class, 'profession', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }
     public function nationality()
    {
        return $this->belongsTo(Lookup::class, 'nationality', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }

    public function get_nominee_nationality()
    {
        return $this->belongsTo(Lookup::class, 'nominee_nationality', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }

    public function get_nominee_relationship()
    {
        return $this->belongsTo(Lookup::class, 'nominee_relation_with_beneficiary', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }

    public function allowance()
    {
        return $this->belongsTo(Lookup::class, 'gender_id', 'id')->select(['id', 'type', 'value_en', 'value_bn', 'display_order']);
    }

    // public function additionalFields()
    // {
    //     return $this->hasMany(ApplicationAllowanceValues::class, 'application_id', 'id')
    //                 ->join('additional_fields', 'application_allowance_values.allow_addi_fields_id', '=', 'additional_fields.id');
    // }

    // public function additionalFieldValues()
    // {
    //     return $this->hasMany(ApplicationAllowanceValues::class, 'application_id', 'id')
    //                 ->join('additional_field_values', 'application_allowance_values.allow_addi_field_values_id', '=', 'additional_field_values.id');
    // }
    public function allowAddiFields()
    {
        return $this->belongsToMany(AdditionalFields::class, 'application_allowance_values', 'application_id', 'allow_addi_fields_id')
            ->withPivot('value')->using(ApplicationAllowanceValuePivot::class);
        // ->with('allowAddiFieldValues');
    }
    public function allowAddiFieldValue()
    {
        return $this->belongsToMany(AdditionalFieldValues::class, 'application_allowance_values', 'application_id', 'allow_addi_field_values_id')
            ->withPivot('value')->using(ApplicationAllowanceValuePivot::class);
        // ->with('allowAddiField');
    }

    public function applicationAllowanceValues(){
        return $this->hasMany(ApplicationAllowanceValues::class, 'application_id');
    }

    public function subvariable()
    {
        return $this->belongsToMany(Variable::class, 'application_poverty_values', 'application_id', 'sub_variable_id')
            ->with('parent');
    }
    public function variable()
    {
        return $this->belongsToMany(Variable::class, 'application_poverty_values', 'application_id', 'variable_id');

    }

    // public function variable()
    // {
    //     return $this->belongsToMany(Variable::class,
    //     'parent_id');
    // }

    public function poverty_score() //emu
    {
        return $this->belongsToMany(Variable::class, 'application_poverty_values', 'application_id', 'variable_id');
    }
    public function poverty_score_value() //emu
    {
        return $this->belongsToMany(Variable::class, 'application_poverty_values', 'application_id', 'sub_variable_id');
    }

//    public function povertyValues()
//     {
//         return $this->hasMany(ApplicationPovertyValues::class, 'application_id');
//     }

    public function committeeApplication()
    {
        // return $this->hasOne(CommitteeApplication::class, 'application_id', 'id');
        return $this->hasMany(CommitteeApplication::class, 'application_id', 'id');
    }

    public function pmtScore()
    {
        return $this->belongsTo(PMTScore::class, 'cut_off_id', 'id');
    }

    public function beneficiary()
    {
        return $this->hasOne(Beneficiary::class, 'application_table_id', 'id');
    }

    public function district()
    {
        return $this->belongsTo(Location::class, 'permanent_district_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function districtPouroshova()
    {
        return $this->belongsTo(Location::class, 'permanent_district_pourashava_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function cityCorporation()
    {
        return $this->belongsTo(Location::class, 'permanent_city_corp_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function upazila()
    {
        return $this->belongsTo(Location::class, 'permanent_upazila_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function thana()
    {
        return $this->belongsTo(Location::class, 'permanent_thana_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function union()
    {
        return $this->belongsTo(Location::class, 'permanent_union_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }


    public function pourashava()
    {
        return $this->belongsTo(Location::class, 'permanent_pourashava_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function ward()
    {
        return $this->belongsTo(Location::class, 'permanent_ward_id', 'id')->select(['id', 'parent_id', 'code', 'name_en', 'name_bn', 'type', 'location_type']);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_name', 'id');
    }
    public function branch()
    {
        return $this->belongsTo(BankBranch::class, 'branch_name', 'id');
    }

    public function mfs()
    {
        return $this->belongsTo(Mfs::class, 'mfs_name', 'id');
    }

    public function getStatus()
    {

//        return match ($this->status) {
//            0 => 'Not Selected',
//            1 => 'Forward',
//            2 => 'Approved',
//            3 => 'Rejected',
//            4 => 'Waiting'
//        };

        return match ($this->status) {
            0 => 'অনির্বাচিত',
            1 => 'ফরওয়ার্ড',
            2 => 'অনুমোদিত',
            3 => 'বাতিল',
            4 => 'অপেক্ষমান'
        };


    }

    /**
     * Get the officeHasWord that owns the Application
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function officeHasWard(): BelongsTo
    {
        return $this->belongsTo(OfficeHasWard::class, 'permanent_ward_id', 'id');
    }

}
