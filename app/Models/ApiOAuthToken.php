<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiOAuthToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_type', 'client_id', 'client_secret', 
        'access_token', 'refresh_token', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at ? Carbon::now()->greaterThan($this->expires_at) : true;
    }
}
