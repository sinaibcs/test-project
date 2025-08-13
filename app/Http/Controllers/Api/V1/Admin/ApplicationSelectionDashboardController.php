<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\Admin\Application\ApplicationService;
use App\Http\Traits\MessageTrait;
use App\Models\AllowanceProgram;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApplicationSelectionDashboardController extends Controller
{
    use MessageTrait;

    /**
     * @var ApplicationService
     */
    private ApplicationService $applicationService;

    /**
     * @param ApplicationService $applicationService
     */
    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    public function programStatusWisetotalNumberOfdApplication(Request $request)
    {
        $status = $request->status;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        // Initialize the query
        $query = AllowanceProgram::where('system_status', 1);

        // If start date and end date are provided, filter by the specified date range
        if ($startDate && $endDate) {
            $query->withCount(['applications' => function ($query) use ($startDate, $endDate,$status) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
                $query->where('status', $status);
            }]);
        } else {
            // If start date and end date are not provided, filter by the current year
            $query->withCount(['applications' => function ($query) use ($currentYear,$status) {
                $query->whereYear('created_at', $currentYear);
                $query->where('status', $status);
            }]);
        }

        // Execute the query
        $programs = $query->get();

        return response()->json([
            'data' => $programs,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);

    }


    public function programWisetotalNumberOfdApplication(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        // Initialize the query
        $query = AllowanceProgram::where('system_status', 1);

        // If start date and end date are provided, filter by the specified date range
        if ($startDate && $endDate) {
            $query->withCount(['applications' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }]);
        } else {
            // If start date and end date are not provided, filter by the current year
            $query->withCount(['applications' => function ($query) use ($currentYear) {
                $query->whereYear('created_at', $currentYear);
            }]);
        }

        // Execute the query
        $programs = $query->get();

        return response()->json([
            'data' => $programs,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }
}

