<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ApiPurpose extends Model
{
    use HasFactory;


    protected $appends = ['columns'];

    protected $casts = [
        'parameters' => 'array'
    ];


    public function url(): Attribute
    {
        return new Attribute(
            get: fn($value) => url('api/v1' . $value)
        );
    }

    public function columns(): Attribute
    {
        return  new Attribute(
            get: fn() => $this->getColumns(),
        );
    }


    public function getColumns()
    {
        $keys = [];

        foreach ((array)$this->parameters as $item) {
            foreach ($item as $k => $v) {
                $keys[] = $k;
            }
        }

        return $keys;
    }




    public function module()
    {
        return $this->belongsTo(ApiModule::class, 'api_module_id', 'id');
    }

}
