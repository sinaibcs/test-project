<?php

namespace App\Http\Resources;

use App\Models\Location;
use App\Http\Traits\UserTrait;
use App\Http\Resources\Admin\PermissionResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Geographic\DistrictResource;
use App\Http\Resources\Admin\Sytemconfig\LocationResource;

class AdminAuthResource extends JsonResource
{

    use UserTrait;
    protected $token = null;
    protected $permissions = [];
    protected $success;
    protected $message;

    public function token($value)
    {
        $this->token = $value;
        return $this;
    }
    public function success($value)
    {
        $this->success = $value;
        return $this;
    }
    public function permissions($value)
    {
        $this->permissions = PermissionResource::collection($value);
        return $this;
    }

    public function message($value)
    {
        $this->message = $value;
        return $this;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                            => $this->id,
            'full_name'                     => $this->full_name,
            'email'                         => $this->email,
            'email_verified_at'             => $this->email_verified_at,
            'phone'                         => $this->phone,
            'photo'                         => $this->photo,
            'photo_url'                         => $this->photo_url,
            'profile'                       => $this->profile,
            'location'         => new LocationResource($this->assign_location),
            'office'                        => $this->office,
            'committee_type_id'                        => $this->committee_type_id,
            'committee'                        => $this->committee,
            'roles'                         => RoleResource::collection($this->roles),
            'roleNames'                     => $this->getRoleNames(),
            'status'            => $this->status,
            'address'            => $this->address,
            'user_type'            => $this->user_type,
            'created_at'                    => $this->created_at,
        ];




    }

    public function with($request)
    {
        return [
            'permissions'   =>  $this->permissions,
            'token'         =>  $this->token,
            'token_type'    => 'Bearer',
            'success'       =>  $this->success,
            'message'       =>  $this->message
        ];
    }
}