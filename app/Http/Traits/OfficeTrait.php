<?php

namespace App\Http\Traits;

trait OfficeTrait
{
    // Default Office Types
    private $ministryType = 4;
    private $headOfficeType = 5;
    private $divisionType = 6;
    private $districtType = 7;
    private $upazilaType = 8;
    private $ucdDistrictPouroshavaType = 9;
    private $ucdUpazilaType = 10;
    private $circleSocialServiceType = 11;

    public static function getOfficeTypes()
    {
      
        $types = [
            // ['id' => 1, 'name' => 'Ministry'],
            // ['id' => 2, 'name' => 'Head Office'],
            // ['id' => 3, 'name' => 'Division'],
            // ['id' => 4, 'name' => 'District'],
            // ['id' => 5, 'name' => 'Upazila'],
            // ['id' => 6, 'name' => 'UCD'],
            // ['id' => 7, 'name' => 'Upazila UCD'],
            // ['id' => 8, 'name' => 'Circle Social Service'],
            ['id' => 4, 'type' => 3, 'value_en' => 'Ministry', 'value_bn' => 'Ministry','default'=>1],
            ['id' => 5, 'type' => 3, 'value_en' => 'Head Office', 'value_bn' => 'Head Office','default'=>1],
            ['id' => 6, 'type' => 3, 'value_en' => 'Division', 'value_bn' => 'Division','default'=>1],
            ['id' => 7, 'type' => 3, 'value_en' => 'District', 'value_bn' => 'District','default'=>1],
            ['id' => 8, 'type' => 3, 'value_en' => 'Upazila', 'value_bn' => 'Upazila','default'=>1],
            ['id' => 9, 'type' => 3, 'value_en' => 'UCD (City Corp/ Dist Paurashava)', 'value_bn' => 'UCD (City Corp/ Dist Paurashava)','default'=>1],
            ['id' => 10, 'type' => 3, 'value_en' => 'UCD (Upazila)', 'value_bn' => 'UCD (Upazila)','default'=>1],
            ['id' => 11, 'type' => 3, 'value_en' => 'Circle Social Service', 'value_bn' => 'Circle Social Service','default'=>1],
        ];

        return collect($types);
    }
}
