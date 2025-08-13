<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PushNotificationDevice
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $device_key
 * @property string|null $device_type
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereDeviceKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereUserId($value)
 * @property string $device
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|PushNotificationDevice whereDevice($value)
 * @property int|null $userId
 * @property string|null $deviceKey
 * @property string|null $deviceType
 * @property string|null $ipAddress
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @mixin \Eloquent
 */
class PushNotificationDevice extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','device_key','device_type','ip_address','device'];
    /**
     * Get the user associated with this model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo("App\Models\User", 'user_id');
    }
}
