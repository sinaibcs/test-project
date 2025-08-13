<?php

namespace App\Http\Traits;

trait BeneficiaryTrait
{
    // application fake data list
    public static function getApplications()
    {
        $applications = [
            [
                'id'                    =>1,
                'application_id'        =>'123456789',
                'program_id'            =>1,
                'gender_id'             =>23,
                'verification_type'     =>1,
                'verification_number'   =>23324123123,
                'date_of_birth'         => '1990-01-01',
                'current_location_id'   =>5142,
                'current_post_code'     =>1234,
                'current_address'       => 'Dhaka',
                'permanent_location_id' =>5142,
                'permanent_post_code'   =>1234,
                'permanent_address'     => 'Dhaka',
                'status'                =>1,
            ],
            [
                'id'                    =>2,
                'application_id'        =>'123456789',
                'program_id'            =>1,
                'gender_id'             =>24,
                'verification_type'     =>1,
                'verification_number'   =>23324123123,
                'date_of_birth'         => '1990-01-01',
                'current_location_id'   =>88,
                'current_post_code'     =>1234,
                'current_address'       => 'Dhaka',
                'permanent_location_id' =>88,
                'permanent_post_code'   =>1234,
                'permanent_address'     => 'Dhaka',
                'status'                =>1,
            ],
            [
                'id'                    =>3,
                'application_id'        =>'123456789',
                'gender_id'             =>25,
                'program_id'            =>1,
                'verification_type'     =>1,
                'verification_number'   =>23324123123,
                'date_of_birth'         => '1990-01-01',
                'current_location_id'   =>3905,
                'current_post_code'     =>1234,
                'current_address'       => 'Dhaka',
                'permanent_location_id' =>3905,
                'permanent_post_code'   =>1234,
                'permanent_address'     => 'Dhaka',
                'status'                =>1,
            ],
            [
                'id'                    =>4,
                'application_id'        =>'123456789',
                'program_id'            =>1,
                'gender_id'             =>23,
                'verification_type'     =>1,
                'verification_number'   =>23324123123,
                'date_of_birth'         => '1990-01-01',
                'current_location_id'   =>5142,
                'current_post_code'     =>1234,
                'current_address'       => 'Dhaka',
                'permanent_location_id' =>5142,
                'permanent_post_code'   =>1234,
                'permanent_address'     => 'Dhaka',
                'status'                =>1,
            ],
            [
                'id'                    =>5,
                'application_id'        =>'123456789',
                'program_id'            =>1,
                'gender_id'             =>24,
                'verification_type'     =>1,
                'verification_number'   =>23324123123,
                'date_of_birth'         => '1990-01-01',
                'current_location_id'=> 3905,
                'current_post_code'=> 1234,
                'current_address'=> 'Dhaka',
                'permanent_location_id'=> 3905,
                'permanent_post_code'=> 1234,
                'permanent_address'=> 'Dhaka',
                'status'=> 1,
            ],
        ];
        return collect($applications);
    }

    public static function getBeneficiary(){
        $beneficiaries = [
            [
                'id'                        =>1,
                'beneficiary_id'            =>'123456789',
                'application_id'            =>1,
                'status'                    =>1,
                'monthly_honorarium'        =>1000,
            ],
            [
                'id'                        =>2,
                'beneficiary_id'            =>'123456789',
                'application_id'            =>2,
                'status'                    =>1,
                'monthly_honorarium'        =>500,
            ],
            [
                'id'                        =>3,
                'beneficiary_id'            =>'123456789',
                'application_id'            =>3,
                'status'                    =>1,
                'monthly_honorarium'        =>700,
            ],
            [
                'id'                        =>4,
                'beneficiary_id'            =>'123456789',
                'application_id'            =>4,
                'status'                    =>1,
                'monthly_honorarium'        =>800,
            ],
            [
                'id'                        =>5,
                'beneficiary_id'            =>'123456789',
                'application_id'            =>5,
                'status'                    =>1,
                'monthly_honorarium'        =>700,
            ],
        ];
        return collect($beneficiaries);

    }

}
