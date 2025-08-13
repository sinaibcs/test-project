<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmergencyBeneficiariesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('emergency_beneficiaries')->insert([
           'allotment_id' => 1,
                'program_id' => 1,
                'application_id' => Str::uuid(),
                'beneficiary_id' => Str::uuid(),
                'name_en' => 'John Doe',
                'name_bn' => 'জন ডো',
                'mother_name_en' => 'Jane Doe',
                'mother_name_bn' => 'জেন ডো',
                'father_name_en' => 'Richard Roe',
                'father_name_bn' => 'রিচার্ড রো',
                'spouse_name_en' => null,
                'spouse_name_bn' => null,
                'identification_mark' => 'Scar on left cheek',
                'age' => '30',
                'date_of_birth' => '1994-01-01',
                'nationality' => 'Bangladeshi',
                'gender_id' => 1,
                'education_status' => 'Bachelor\'s Degree',
                'profession' => 'Teacher',
                'religion' => 'Islam',
                'marital_status' => 'Single',
                'email' => 'john@example.com',
                'verification_type' => 1,
                'verification_number' => '1234567890',
                'image' => null,
                'signature' => null,
                'current_division_id' => 1,
                'current_district_id' => 1,
                'current_location_type' => 1,
                'current_city_corp_id' => 1,
                'current_district_pourashava_id' => null,
                'current_upazila_id' => 1,
                'current_pourashava_id' => null,
                'current_thana_id' => 1,
                'current_union_id' => 1,
                'current_ward_id' => 1,
                'current_location_id' => 1,
                'current_post_code' => '1234',
                'current_address' => '123 Main Street',
                'current_mobile' => '01700000000',
                'permanent_division_id' => 1,
                'permanent_district_id' => 1,
                'permanent_location_type' => 1,
                'permanent_city_corp_id' => 1,
                'permanent_district_pourashava_id' => null,
                'permanent_upazila_id' => 1,
                'permanent_pourashava_id' => null,
                'permanent_thana_id' => 1,
                'permanent_union_id' => 1,
                'permanent_ward_id' => 1,
                'permanent_location_id' => 1,
                'permanent_post_code' => '5678',
                'permanent_address' => '456 Secondary Street',
                'permanent_mobile' => '01711111111',
                'nominee_en' => 'Jane Roe',
                'nominee_bn' => 'জেন রো',
                'nominee_verification_number' => '0987654321',
                'nominee_address' => '456 Secondary Street',
                'nominee_image' => null,
                'nominee_signature' => null,
                'nominee_relation_with_beneficiary' => 'Sister',
                'nominee_nationality' => 'Bangladeshi',
                'nominee_date_of_birth' => '1990-01-01',
                'account_name' => 'John Doe',
                'account_number' => '12345678',
                'account_owner' => 'John Doe',
                'account_type' => 1,
                'bank_name' => 'Example Bank',
                'branch_name' => 'Example Branch',
                'monthly_allowance' => 1000.00,
                'status' => 1,
                'isExisting' => 1,
                'isSelected' => 1,
                'created_at' => now(),
                'updated_at' => now(),
        ]);
    }
}