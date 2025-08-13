<?php

namespace Database\Seeders;

use App\Models\BeneficiaryChangeType;
use Illuminate\Database\Seeder;

class BeneficiaryChangeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $changeTypes = [
            [
                'name_en' => 'Personal Information Change',
                'name_bn' => 'ব্যাক্তিগত তথ্য পরিবর্তন',
                'keyword' => 'PERSONAL_INFO_CHANGE',
            ],
            [
                'name_en' => 'Contact Information Change',
                'name_bn' => 'যোগাযোগের ঠিকানা পরিবর্তন',
                'keyword' => 'CONTACT_INFO_CHANGE',
            ],
            [
                'name_en' => 'Account Change',
                'name_bn' => 'অ্যাকাউন্ট পরিবর্তন',
                'keyword' => 'ACCOUNT_CHANGE',
            ],
            [
                'name_en' => 'Nominee Change',
                'name_bn' => 'নমিনি পরিবর্তন',
                'keyword' => 'NOMINEE_CHANGE',
            ]
        ];
        BeneficiaryChangeType::upsert($changeTypes, uniqueBy: ['keyword']);
//        foreach ($changeTypes as $data)
//            BeneficiaryChangeType::create($data);
    }
}
