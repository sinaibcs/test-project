<?php

namespace App\Services\MfsValidationServices;

use App\Models\BkashValidation;

class MfsValidationService{

    private array $types = [
        'Sync', 'Beneficiary'
    ];

    function __construct(private BkashValidationService $bkashValidationService, private NagadValidationService $nagadValidationService){

    }

    public function validate($mfsId, $phone, $type = 'Sync'): ?bool {
        if(!in_array($type, $this->types)){
            throw new \Exception('Invalid argument passed as type');
        }
        if($mfsId == 1){
            $this->bkashValidationService->validateBeneficiary($phone, $type);
            $data = new BkashValidation;
            $data->phone_number = $phone;
            $data->save();
            
            if($type == 'Sync'){
                $bkashValidation = null;
                $try = 0;
                while($bkashValidation == null && $try < 10){
                    usleep( 5 * 1000);
                    $bkashValidation = BkashValidation::where('phone_number', $phone)->whereNotNUll('response_at')->latest()->first();
                    $try++;
                }
                if($bkashValidation){
                    return $bkashValidation->status == 1;
                }else{
                    throw new \Exception('No response from Bkash server.');
                }
            }
        }elseif($mfsId == 2){
            return $this->nagadValidationService->validate($phone);
        }else{
            throw new \Exception('Invalid MFS');
        }
    }
}