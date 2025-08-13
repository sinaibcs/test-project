<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\Payroll\PaymentTrackingMobileResource;
use App\Models\Beneficiary;
use Illuminate\Http\Request;

class PaymentTrackingController extends Controller
{
    public function getPaymentTrackingInfoMobile(Request $request)
    {
        // return 1;
        $Beneficiary = Beneficiary::with(
            'PayrollDetails.payroll.financialYear',
            'PayrollDetails.payroll.installmentSchedule',
            'PayrollDetails.payroll.office',
            'PayrollDetails.beneficiaryPayrollPaymentStatusLog.status',
            'PayrollDetails.beneficiaryPayrollPaymentStatusLog.user',
        )
            ->where(function ($query) use ($request) {
                $query->where('verification_number', $request->beneficiary_id)
                    ->orWhere('beneficiary_id', $request->beneficiary_id);
            })
            ->where('mobile', $request->phone_no)
            ->first();

        if ($Beneficiary) {
            return (new PaymentTrackingMobileResource($Beneficiary))->additional([
                'success' => true,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Beneficiary not found'
            ], 404);
        }
    }
}
