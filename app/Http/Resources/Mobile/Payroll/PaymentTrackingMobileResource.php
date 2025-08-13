<?php

namespace App\Http\Resources\Mobile\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTrackingMobileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            "id" => $this->id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "beneficiary_id" => $this->beneficiary_id,
            "beneficiary_address" => $this->beneficiary_address(),
            "age" => $this->age,
            "date_of_birth" => $this->date_of_birth,
            "nationality" => $this->nationality,
            "email" => $this->email,
            "verification_number" => $this->verification_number,
            "payroll_details" => $this->payrollDetailsArray($this->whenLoaded('PayrollDetails')),
        ];
    }

    private function beneficiary_address()
    {
        $beneficiary_address_en = $this->permanent_address;
        $beneficiary_address_bn = $this->permanent_address;

        if ($this->permanentUnion) {
            $beneficiary_address_en .= ', ' . $this->permanentUnion->name_en;
            $beneficiary_address_bn .= ', ' . $this->permanentUnion->name_bn;
        } elseif ($this->permanentPourashava) {
            $beneficiary_address_en .= ', ' . $this->permanentPourashava->name_en;
            $beneficiary_address_bn .= ', ' . $this->permanentPourashava->name_bn;
        } elseif ($this->permanentThana) {
            $beneficiary_address_en .= ', ' . $this->permanentThana->name_en;
            $beneficiary_address_bn .= ', ' . $this->permanentThana->name_bn;
        }

        if ($this->permanentUpazila) {
            $beneficiary_address_en .= ', ' . $this->permanentUpazila->name_en;
            $beneficiary_address_bn .= ', ' . $this->permanentUpazila->name_bn;
        } elseif ($this->permanentCityCorporation) {
            $beneficiary_address_en .= ', ' . $this->permanentCityCorporation->name_en;
            $beneficiary_address_bn .= ', ' . $this->permanentCityCorporation->name_bn;
        } elseif ($this->permanentDistrictPourashava) {
            $beneficiary_address_en .= ', ' . $this->permanentDistrictPourashava->name_en;
            $beneficiary_address_bn .= ', ' . $this->permanentDistrictPourashava->name_bn;
        }

        if ($this->permanentDistrict) {
            $beneficiary_address_en .= ', ' . $this->permanentDistrict->name_en;
            $beneficiary_address_bn .= ', ' . $this->permanentDistrict->name_bn;
        }

        return [
            'en' => $beneficiary_address_en,
            'bn' => $beneficiary_address_bn,
        ];
    }


    private function payrollDetailsArray($payrollDetails)
    {
        if (!$payrollDetails) {
            return null;
        }
        return $payrollDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                // 'payroll_id' => $detail->payroll_id,
                // 'beneficiary_id' => $detail->beneficiary_id,
                'amount' => $detail->amount,
                'charge' => $detail->charge,
                'total_amount' => $detail->total_amount,
                // 'status' => $detail->status,
                // 'deleted_at' => $detail->deleted_at,
                // 'created_at' => $detail->created_at,
                // 'updated_at' => $detail->updated_at,
                // 'updated_by' => $detail->updatedBy->username ?? null,
                'payroll' => $this->payrollArray($detail->payroll),
                'beneficiary_log' => $this->beneficiaryLogArray($detail->beneficiaryPayrollPaymentStatusLog),
                // 'payment_cycle_details' => $this->paymentCycleDetailsArray($detail->paymentCycleDetails),
            ];
        });
    }

    private function payrollArray($payroll)
    {
        if (!$payroll) {
            return null;
        }

        return [
            'id' => $payroll->id,
            // 'program_id' => $payroll->program_id,
            // 'financial_year_id' => $payroll->financial_year_id,
            // 'office_id' => $payroll->office_id,
            'financial_year' => $this->financialYearArray($payroll->financialYear),
            'installment_schedule' => $this->installmentScheduleArray($payroll->installmentSchedule),
            'office' => $payroll->office,
            // 'allotment_id' => $payroll->allotment_id,
            // 'installment_schedule_id' => $payroll->installment_schedule_id,
            // 'total_beneficiaries' => $payroll->total_beneficiaries,
            // 'sub_total_amount' => $payroll->sub_total_amount,
            // 'total_charge' => $payroll->total_charge,
            // 'total_amount' => $payroll->total_amount,
            // 'is_approved' => $payroll->is_approved,
            // 'approved_by_id' => $payroll->approved_by_id,
            // 'approved_by' => $payroll->approvedBy->username ?? null,
            // 'approved_at' => $payroll->approved_at,
            // 'approved_doc' => $payroll->approved_doc,
            // 'approved_note' => $payroll->approved_note,
            // 'is_rejected' => $payroll->is_rejected,
            // 'rejected_by_id' => $payroll->rejected_by_id,
            // 'rejected_by' => $payroll->rejectedBy->username ?? null,
            // 'rejected_at' => $payroll->rejected_at,
            // 'rejected_doc' => $payroll->rejected_doc,
            // 'rejected_note' => $payroll->rejected_note,
            // 'is_payment_cycle_generated' => $payroll->is_payment_cycle_generated,
            // 'payment_cycle_generated_at' => $payroll->payment_cycle_generated_at,
            // 'deleted_at' => $payroll->deleted_at,
            // 'created_by_id' => $payroll->created_by_id,
            // 'updated_by_id' => $payroll->updated_by_id,
            // 'created_at' => $payroll->created_at,
            // 'updated_at' => $payroll->updated_at,
            // 'is_submitted' => $payroll->is_submitted,
            // 'submitted_by_id' => $payroll->submitted_by_id,
            // 'submitted_by' => $payroll->submittedBy->username ?? null,
            // 'submitted_at' => $payroll->submitted_at,
        ];
    }

    private function financialYearArray($financialYear)
    {
        if (!$financialYear) {
            return null;
        }

        return [
            'id' => $financialYear->id,
            'financial_year' => $financialYear->financial_year,
            'start_date' => $financialYear->start_date,
            'end_date' => $financialYear->end_date,
            'status' => $financialYear->status,
            // 'version' => $financialYear->version,
            // 'deleted_at' => $financialYear->deleted_at,
            // 'created_at' => $financialYear->created_at,
            // 'updated_at' => $financialYear->updated_at,
        ];
    }

    private function installmentScheduleArray($installmentSchedule)
    {
        if (!$installmentSchedule) {
            return null;
        }

        return [
            'id' => $installmentSchedule->id,
            // 'installment_number' => $installmentSchedule->installment_number,
            'installment_name_en' => $installmentSchedule->installment_name,
            'installment_name_bn' => $installmentSchedule->installment_name_bn,
            'payment_cycle' => $installmentSchedule->payment_cycle,
            // 'deleted_at' => $installmentSchedule->deleted_at,
            // 'created_at' => $installmentSchedule->created_at,
            // 'updated_at' => $installmentSchedule->updated_at,
        ];
    }

    private function beneficiaryLogArray($items)
    {
        if (!$items) {
            return null;
        }

        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'status_id' => $item->status_id,
                'st_id' => $item->status->id ?? null,
                'name_en' => $item->status->name_en ?? null,
                'name_bn' => $item->status->name_bn ?? null,
                'created_by' => $item->user->username ?? null,
                'created_at' => $item->created_at,
            ];
        })->toArray();
    }

    // private function paymentCycleDetailsArray($paymentCycleDetails)
    // {
    //     // return $paymentCycleDetails->map(function ($detail) {
    //         return [
    //             'id' => $paymentCycleDetails->id,
    //             // 'payroll_payment_cycle_id' => $paymentCycleDetails->payroll_payment_cycle_id,
    //             // 'payroll_id' => $paymentCycleDetails->payroll_id,
    //             'total_amount' => $paymentCycleDetails->total_amount,
    //             // 'payroll_detail_id' => $paymentCycleDetails->payroll_detail_id,
    //             // 'beneficiary_id' => $paymentCycleDetails->beneficiary_id,
    //             'amount' => $paymentCycleDetails->amount,
    //             'charge' => $paymentCycleDetails->charge,
    //             'status' => $paymentCycleDetails->status,
    //             // 'deleted_at' => $paymentCycleDetails->deleted_at,
    //             'created_at' => $paymentCycleDetails->created_at,
    //             'updated_at' => $paymentCycleDetails->updated_at,
    //         ];
    //     // });
    // }
}
