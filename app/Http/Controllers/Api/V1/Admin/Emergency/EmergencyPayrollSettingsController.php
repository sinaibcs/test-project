<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Http\Controllers\Controller;
use App\Models\AllowanceProgram;
use App\Models\EmergencyAllotment;
use App\Models\EmergencyPayrollSetting;
use App\Models\FinancialYear;
use App\Models\Installment;
use App\Models\PayrollInstallmentSchedule;
use App\Models\PayrollInstallmentSetting;
use App\Models\PayrollVerificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmergencyPayrollSettingsController extends Controller
{
    public function getFinancialYear()
    {
        return FinancialYear::where("status", 1)->first();
    }
    public function getAllAllowance()
    {
        return EmergencyAllotment::where("status", 1)->get();
    }

    public function getAllInstallments()
    {
        return PayrollInstallmentSchedule::get();
    }

    public function payrollSettingSubmit(Request $request)
    {
        $rules = [
            'allowances' => 'array',
            'allowances.*.allowance_id' => 'integer',
            'allowances.*.selectedInstallments' => 'array',
            'allowances.*.selectedInstallments.*.installment_id' => 'integer',
            'financial_year' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        \DB::beginTransaction();

        try {
            $allSettings = EmergencyPayrollSetting::withTrashed()->get();
            foreach ($allSettings as $key => $setting) {
                $setting->forceDelete();
            }
            foreach ($request->allowances as $item) {
                $allowanceId = $item['allotment_id'];
                $installments = $item['selectedInstallments'];
                foreach ($installments as $key => $value) {
                    EmergencyPayrollSetting::create([
                        'allotment_id' => $allowanceId,
                        'financial_year_id' => $request->financial_year,
                        'installment_schedule_id' => $value['installment_id'],
                    ]);
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll Setting Updated Successfully',
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating payroll setting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSettingData(Request $request)
    {
        // $groupedData = EmergencyPayrollSetting::with('allowance', 'installment')
        //     ->get()
        //     ->groupBy('allotment_id');

        $groupedData = EmergencyPayrollSetting::with(['allowance', 'installment'])
            ->get()
            ->filter(function ($item) {
                return $item->allowance && $item->installment &&
                    $item->allowance->payment_cycle === $item->installment->payment_cycle;
            })
            ->groupBy('allotment_id');

        if ($groupedData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No data found',
            ]);
        }

        $formattedData = [];
        foreach ($groupedData as $programId => $items) {
            $installmentIds = $items->pluck('installment_schedule_id')->toArray();
            $formattedData[] = [
                'allotment_id' => $programId,
                'installment_ids' => $installmentIds,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedData,
        ]);
    }

    public function payrollVerification(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'verificationType' => 'required|in:direct_approval,verification_process',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        PayrollVerificationSetting::truncate();
        PayrollVerificationSetting::create([
            'verification_type' => $request->verificationType,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll Verification Setting Updated Successfully',
        ]);
    }

    public function getVerificationSetting()
    {
        return PayrollVerificationSetting::latest()->first();
    }
}
