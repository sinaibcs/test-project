<?php

namespace App\Http\Resources\Admin\Beneficiary;

use App\Models\BankBranch;
use App\Models\Mfs;
use Carbon\Carbon;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryAccountChangeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "ben_id" => $this->ben_id,
            'program_name_en' => $this->program_name_en,
            'program_name_bn' => $this->program_name_bn,
            "beneficiary_id" => $this->beneficiary_id,
            "application_id" => $this->application_id,
            "verification_number" => $this->verification_number,
            "verification_type" => $this->verification_type,
            "mobile" => $this->mobile,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "district_name_en" => $this->district_name_en,
            "district_name_bn" => $this->district_name_bn,
            "previous_info" => $this->accountInfo($this->previous_value),
            "current_info" => $this->accountInfo($this->change_value),
            "created_by" => json_decode($this->created_by),
            "approved_by" => $this->approved_by? json_decode($this->approved_by) : null,
            "updated_at" => $this->updated_at
        ];
    }

    private function accountInfo($info){
        $data = [
            'account_name' => null,
            'account_type' => null,
            'account_number' => null,
            'bank_name_en' => null,
            'bank_name_bn' => null,
            'branch_name_en' => null,
            'branch_name_bn' => null,
            'mfs_name_en' => null,
            'mfs_name_bn' => null,
        ];
        if($info == null)
            return $data;
        $info = json_decode($info);


        $data['account_type'] = $info->account_type??null;
        $data['account_number'] = $info->account_number??null;

        if(($info->account_type??null) == 1){
            $bank = Bank::find($info->bank_id??null);
            $branch = BankBranch::find($info->branch_id??null);
            $data['bank_name_en'] = $bank?->name_en;
            $data['bank_name_bn'] = $bank?->name_bn;
            $data['branch_name_en'] = $branch?->name_en;
            $data['branch_name_bn'] = $branch?->name_bn;
        }elseif(($info->account_type??null) == 2){
            $mfs = Mfs::find($info->mfs_id??null);
            $data['mfs_name_en'] = $mfs?->name_en;
            $data['mfs_name_bn'] = $mfs?->name_bn;
        }
        return $data;
    }
}
