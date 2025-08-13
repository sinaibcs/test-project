<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Notification
 *
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @property int $id
 * @property int $userId
 * @property string $title
 * @property string|null $body
 * @property string|null $link
 * @property string|null $type
 * @property string $platform
 * @property int $status
 * @property int $seen
 * @property string|null $seenAt
 * @property int|null $createTime
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property string|null $deletedAt
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereSeenAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUserId($value)
 * @mixin \Eloquent
 */
class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['message', 'user_id', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
