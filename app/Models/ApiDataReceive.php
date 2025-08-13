<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiDataReceive extends Model
{
    use HasFactory, SoftDeletes;



    public function apiList()
    {
        return $this->belongsToMany(ApiList::class, 'api_selects', 'api_data_receive_id', 'api_list_id')
            ->orderByDesc('created_at');
    }


    public function apiLogs()
    {
        return $this->hasMany(ApiLog::class, 'api_data_receive_id', 'id');
    }


}
