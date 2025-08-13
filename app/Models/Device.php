<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Device
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $device_type
 * @property string $device_name
 * @property string $mac_address
 * @property string $username_mapping
 * @property string|null $purpose_use
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Device newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Device query()
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereMacAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device wherePurposeUse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereUsernameMapping($value)
 * @property int $userId
 * @property int $deviceType
 * @property string $deviceName
 * @property string $macAddress
 * @property string $usernameMapping
 * @property string|null $purposeUse
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property string|null $deviceId
 * @property string|null $ipAddress
 * @property string|null $deviceDetails
 * @property int $createdBy
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Device whereStatus($value)
 * @mixin \Eloquent
 */
class Device extends Model
{
    use HasFactory;
    //    public function newQuery($excludeDeleted = true)
    //    {
    //        return parent::newQuery($excludeDeleted)
    //            ->orderBy('name', 'asc');
    //    }

    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
