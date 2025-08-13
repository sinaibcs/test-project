<?php

namespace App\Http\Traits;

trait AdditionalFieldTrait
{

    private $yearlyIncome = 1;
    private $yearlyIncomeType='dropdown';
    private $govtPrivateBeneficiaryDetails = 2;
    private $govtPrivateBeneficiaryDetailsType='dropdown';
    private $totalNoOfFamilyMember = 3;
    private $totalNoOfFamilyMemberType='number';
    private $noOfMale = 4;
    private $noOfMaleType='number';
    private $noOfFemale = 5;
    private $noOfFemaleType='number';
    private $noOfChildren = 6;
    private $noOfChildrenType='number';
    private $healthStatus = 7;
    private $healthStatusType='checkbox';
    private $financialStatus = 8;
    private $financialStatusType='dropdown';
    private $socialStatus = 9;
    private $socialStatusType='dropdown';
    private $landOwnership = 10;
    private $landOwnershipType='dropdown';
    private $disNo = 11;
    private $disNoType='number';
    private $disabilityType = 12;
    private $disabilityTypeType='disabled';
    private $disabilityTypeAccordingToDis = 13;
    private $disabilityTypeAccordingToDisType='dropdown';
    private $disabilityLevelAccordingToDis = 14;
    private $disabilityLevelAccordingToDisType='dropdown';
    private $uploadAnyKindOfRecommendation = 15;
    private $uploadAnyKindOfRecommendationType='file';
    private $gardenWorkerId = 16;
    private $gardenWorkerIdType='number';
    private $teaGardenName = 17;
    private $teaGardenNameType='text';
    private $dateOfEnrollmentInGarden = 18;
    private $dateOfEnrollmentInGardenType='date';
    private $guardianName = 19;
    private $guardianNameType='text';
    private $nameOfTheInstitute = 20;
    private $nameOfTheInstituteType='text';
    private $class = 21;
    private $classType='dropdown';

}
