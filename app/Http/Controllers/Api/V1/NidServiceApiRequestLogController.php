<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NidServiceApiRequestLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NidServiceApiRequestLogController extends Controller
{
    /**
     * Get day-wise successful and failed API request counts for a given month.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDayWiseStatus(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/', // Expects YYYY-MM format
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $yearMonth = $request->input('month');
            list($year, $month) = explode('-', $yearMonth);

            // Ensure year and month are valid integers
            $year = (int)$year;
            $month = (int)$month;

            if (!checkdate($month, 1, $year)) { // Check if the month and year form a valid date
                 return response()->json(['success' => false, 'message' => 'Invalid month or year provided.'], 400);
            }

            $data = NidServiceApiRequestLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN error IS NULL OR error = "" THEN 1 ELSE 0 END) as successful_count'),
                DB::raw('SUM(CASE WHEN error IS NOT NULL AND error != "" THEN 1 ELSE 0 END) as failed_count')
            )
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('type', 'FETCH')
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'), 'asc')
                ->get();
            
            // If you need to ensure all days of the month are present, even with 0 counts,
            // you would need to generate a date range and merge results.
            // For simplicity, this returns only days with actual logs.
            // The frontend Vue component already handles cases with no data for some days.

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\Exception $e) {
            // Log the exception for debugging
            // Log::error('Error fetching day-wise status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching day-wise status data.'], 500);
        }
    }

    /**
     * Get month-wise successful and failed API request counts for a given year.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMonthWiseStatus(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 5), // Expects YYYY format
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $year = (int)$request->input('year');

            $data = NidServiceApiRequestLog::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'), // Include year for clarity, though filtered by it
                DB::raw('SUM(CASE WHEN error IS NULL OR error = "" THEN 1 ELSE 0 END) as successful_count'),
                DB::raw('SUM(CASE WHEN error IS NOT NULL AND error != "" THEN 1 ELSE 0 END) as failed_count')
            )
                ->whereYear('created_at', $year)
                ->where('type', 'FETCH')
                ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
                ->orderBy(DB::raw('MONTH(created_at)'), 'asc')
                ->get();

            return response()->json(['success' => true, 'data' => $data]);

        } catch (\Exception $e) {
            // Log::error('Error fetching month-wise status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching month-wise status data.'], 500);
        }
    }

    /**
     * Get a list of distinct years for which log data exists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistinctYears()
    {
        try {
            $years = NidServiceApiRequestLog::select(DB::raw('DISTINCT YEAR(created_at) as year'))
                ->orderBy('year', 'desc')
                ->pluck('year'); // Pluck directly into a simple array

            return response()->json(['success' => true, 'data' => $years]);

        } catch (\Exception $e) {
            // Log::error('Error fetching distinct years: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching distinct years.'], 500);
        }
    }

    /**
     * Get a list of distinct months (numeric 1-12) for a given year for which log data exists.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistinctMonthsInYear(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 5),
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        try {
            $year = (int)$request->input('year');

            $months = NidServiceApiRequestLog::select(DB::raw('DISTINCT MONTH(created_at) as month'))
                ->whereYear('created_at', $year)
                ->orderBy('month', 'asc')
                ->pluck('month'); // Pluck directly into a simple array of month numbers

            return response()->json(['success' => true, 'data' => $months]);

        } catch (\Exception $e) {
            // Log::error('Error fetching distinct months for year ' . $year . ': ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching distinct months.'], 500);
        }
    }
}
