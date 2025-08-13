<?php

use App\Models\FinancialYear;

if (!function_exists('handleResponse')) {
    function handleResponse($result, $msg)
    {
        $res = [
            'success' => true,
            'data' => $result,
            'message' => $msg,
        ];
        return response()->json($res, 200);
    }
}


if (!function_exists('handleError')) {
    function handleError($error, $errorMsg = [], $code = 404)
    {
        $res = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMsg)) {
            $res['data'] = $errorMsg;
        }
        return response()->json($res, $code);
    }
}

if (!function_exists('getCurrentFinancialYear')) {
    function getCurrentFinancialYear()
    {

        return FinancialYear::whereDate('start_date','<=', now())->whereDate('end_date','>=', now())->first();
    }
}
