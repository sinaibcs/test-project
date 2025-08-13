<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Traits\PermissionTrait;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;

/**
 * App\Models\User
 *
 * @property int $id
 * @property int|null $division_id
 * @property int|null $district_id
 * @property int|null $thana_id
 * @property string $username
 * @property string|null $full_name
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $mobile
 * @property int|null $office_id
 * @property int|null $assign_location_id
 * @property string|null $password
 * @property string|null $remember_token
 * @property int|null $user_type
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAssignLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOfficeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereThanaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property int|null $divisionId
 * @property int|null $districtId
 * @property int|null $thanaId
 * @property string|null $fullName
 * @property \Illuminate\Support\Carbon|null $emailVerifiedAt
 * @property int|null $officeId
 * @property int|null $assignLocationId
 * @property string|null $rememberToken
 * @property int|null $userType
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property \Illuminate\Support\Carbon|null $deletedAt
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notificationsCount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissionsCount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $rolesCount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokensCount
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property int|null $userId
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserId($value)
 * @property int|null $officeType
 * @property-read \App\Models\Location|null $assignLocation
 * @property-read \App\Models\Lookup|null $office
 * @property-read \App\Models\Lookup|null $officeType
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOfficeType($value)
 * @property int $isDefaultPassword
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsDefaultPassword($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, PermissionTrait;

    public $appends = ["photo_url", "photo_signature_url"];

    protected $guard_name = 'sanctum';
    protected $guarded = ['id'];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'salt',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'programs_id' => 'array'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (auth()->check() && auth()->user()->assign_location_id) {
            static::addGlobalScope('assign_location_type', function (EloquentBuilder $builder) {
//                $data = $this->getUserPermissionsForUser();
            });
        }
    }

    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)//            ->orderBy('full_name', 'asc')
            ;
    }

    public function getPhotoUrlAttribute()
    {
        $file = $this->attributes['photo'] ?? null;
        if ($file)
            $url = asset("cloud/".$file);
        else
            $url = 'https://i2.wp.com/ui-avatars.com/api/' . Str::slug("Avatar") . '/400';

        return $url;
    }

    public function getPhotoSignatureUrlAttribute()
    {
        $file = $this->attributes['photo_signature'] ?? null; // Check for 'photo_signature' attribute

        if ($file) {
            // If there's a 'photo_signature', return a URL for it
            return asset("cloud/" . $file);
        } else {
            // Return a default signature if not present
            return 'https://i2.wp.com/ui-avatars.com/api/signature/400';
        }
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id', 'id');
    }

    public function officeTypeInfo()
    {
        return $this->belongsTo(Lookup::class, 'office_type', 'id');
    }

    public function assign_location()
    {
        return $this->belongsTo(Location::class, 'assign_location_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(Location::class, 'assign_location.parent_id', 'id');
    }

    //  'district'  => DistrictResource::make($this->whenLoaded('parent')),


    public function committee()
    {
        return $this->belongsTo(Committee::class, 'committee_id', 'id');
    }


    public function committeePermission()
    {
        return $this->hasOne(CommitteePermission::class, 'committee_type_id', 'committee_type_id')
            ->orderByDesc('id');
    }

    public function lookup()
    {
        return $this->belongsTo(Lookup::class, 'office_type', 'id');
    }

    public function userWards()
    {
        return $this->belongsToMany(Location::class, 'user_has_wards', 'user_id', 'ward_id');
    }

    public function unions()
    {
        return $this->belongsToMany(Location::class, 'user_has_unions', 'user_id', 'union_id');
    }

    public function getPrograms()
    {
        return $this->programs_id;
    }
   public function userType(){
    return $this->belongsTo(Role::class,'user_type','id');

   }

}
