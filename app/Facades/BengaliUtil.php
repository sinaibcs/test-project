<?php


namespace App\Facades;


use Illuminate\Support\Facades\Facade;

class BengaliUtil extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
//        return app()->make('BengaliUtilService');
        return 'bengali-util-service';
    }

}
