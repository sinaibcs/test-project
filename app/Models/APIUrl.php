<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class APIUrl extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "api_urls";

    protected $guarded = ['id'];

    protected $appends = ['columns'];


    protected function columns(): Attribute
    {
        return new Attribute(
            get: fn() => Schema::getColumnListing($this->table)
        );
    }


    public function url()
    {
        return $this->belongsTo(APIUrl::class, 'api_url_id', 'id');
    }



}
