<?php

namespace App\Models;

use App\Http\Traits\PermissionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Office
 *
 * @property int $id
 * @property int|null $division_id
 * @property int|null $district_id
 * @property int|null $thana_id
 * @property string $name_en
 * @property string $name_bn
 * @property int $office_type
 * @property string $office_address
 * @property string|null $comment
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Office newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Office newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Office query()
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereNameBn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereOfficeAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereOfficeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereThanaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereUpdatedAt($value)
 * @property int|null $divisionId
 * @property int|null $districtId
 * @property int|null $thanaId
 * @property string $nameEn
 * @property string $nameBn
 * @property int $officeType
 * @property string $officeAddress
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property-read \App\Models\Location|null $district
 * @property-read \App\Models\Location|null $division
 * @property-read \App\Models\Location|null $thana
 * @property int|null $parentId
 * @property int $version
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereVersion($value)
 * @property int|null $assignLocationId
 * @property-read \App\Models\Location|null $assignLocation
 * @method static \Illuminate\Database\Eloquent\Builder|Office whereAssignLocationId($value)
 * @mixin \Eloquent
 */
class Office extends Model
{
    use PermissionTrait;


    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)->orderBy('name_en', 'asc');
    }

    public function officeType()
    {
        return $this->belongsTo(Lookup::class, 'office_type')->select('id', 'type', 'value_en', 'value_bn', 'display_order');
    }

    public function assignLocation()
    {
        return $this->belongsTo(Location::class, 'assign_location_id');
    }

    public function wards()
    {
        return $this->hasMany(OfficeHasWard::class, 'office_id');
    }

    public function ward_location()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }


    public function officeWards()
    {
        return $this->belongsToMany(Location::class, 'office_has_wards', 'office_id', 'ward_id');
    }



}
