<?php

namespace App\Http\Traits;

trait LookupTrait
{
    // Default Lookup Types
    private $locationType = 1;
    private $allowanceServiceType = 2;
    private $officeType = 3;
    private $healthStatusType = 4;
    private $financialStatusType = 5;
    private $socialStatusType = 6;
    private $pmtScoringType = 7;
    private $EducationStatusType = 8;
    private $religionType = 9;
    private $householdAssetOwnType = 10;
    private $disabilityType = 11;
    private $disabilityLevelType = 12;
    private $bankNameType = 13;
    private $branchNameType = 14;
    private $complaintCategoryType = 15;
    private $moduleNameType = 16;

    public static function getLookUpTypes()
    {
        $types = [
            ['id' => 1, 'name' => 'Location Type'],
            ['id' => 2, 'name' => 'Gender'],
            ['id' => 3, 'name' => 'Office category'],
            ['id' => 4, 'name' => 'Health Status'],
            ['id' => 5, 'name' => 'Financial Status'],
            ['id' => 6, 'name' => 'Social Status'],
            ['id' => 7, 'name' => 'PMT Scoring'],
            ['id' => 8, 'name' => 'Education Status'],
            ['id' => 9, 'name' => 'Religion'],
            ['id' => 10, 'name' => 'Household Asset Own'],
            ['id' => 10, 'name' => 'Household Asset Own'],
            ['id' => 11, 'name' => 'Disability Type'],
            ['id' => 12, 'name' => 'Disability Level'],
            ['id' => 13, 'name' => 'Bank Name'],
            ['id' => 14, 'name' => 'Branch Name'],
            ['id' => 15, 'name' => 'Complaint Category'],
            ['id' => 16, 'name' => 'Module Name'],
            ['id' => 17, 'name' => 'Committee Type'],
            ['id' => 18, 'name' => 'Organization'],
            ['id' => 19, 'name' => 'Designation'],
            ['id' => 20, 'name' => 'Class'],
            ['id' => 24, 'name' => 'Trainer Designation'],
            ['id' => 25, 'name' => 'Grievance Solution type'],
            ['id' => 26, 'name' => 'Training Circular Type'],
            ['id' => 27, 'name' => 'Training Type'],
            ['id' => 28, 'name' => 'Training Circular Status'],
            ['id' => 29, 'name' => 'Training Module'],
            ['id' => 30, 'name' => 'Training Participant Designation'],
            ['id' => 31, 'name' => 'Training Program Status'],
            ['id' => 32, 'name' => 'Maritial Status'],
            ['id' => 33, 'name' => 'Nationality'],
            ['id' => 34, 'name' => 'Profession'],
            ['id' => 35, 'name' => 'Account Owner'],
            ['id' => 36, 'name' => 'Land Ownership'],
            ['id' => 37, 'name' => 'Mobile Ownership'],
            ['id' => 38, 'name' => 'Relations With Bef'],
            ['id' => 39, 'name' => 'Months']
        ];

        return collect($types);
    }
}
