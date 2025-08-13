<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\Systemconfig\SystemconfigService;
use App\Models\AllowanceProgram;
use App\Models\Application;
use App\Models\Location;
use App\Models\Lookup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SystemconfigDashboardController extends Controller
{
    use MessageTrait;
    private $systemconfigService;

    public function __construct(SystemconfigService $systemconfigService) {
        $this->systemconfigService= $systemconfigService;
    }

    public function getAllLocationApplicationCount(Request $request){

        $a = Location::where('type','division')->groupBy('type')->count();


        $counts = [
            'permanent_division_count' => Location::where('type','division')->groupBy('type')->count(),
            'permanent_district_count' => Location::where('type','district')->groupBy('type')->count(),

            'permanent_district_pourashava_count' => Location::where('type','city')->where('location_type',1)->groupBy('type')->count(),
            'permanent_city_corp_count' => Location::where('type','city')->where('location_type',3)->groupBy('type')->count(),
            'permanent_thana_count' => Location::where('type','thana')->where('location_type',3)->groupBy('type')->count(),
            'permanent_upazila_count' => Location::where('type','thana')->where('location_type',2)->groupBy('type')->count(),
            'permanent_union_count' => Location::where('type','union')->count(),
            'permanent_pourashava_count' => Location::where('type','pouro')->count(),
            'permanent_ward_count' => Location::where('type','ward')->count(),
        ];


        $items = [
            ['title_en' => 'Division', 'title_bn' => 'বিভাগ', 'number' => $counts['permanent_division_count'], 'link' => '/system-configuration/division'],
            ['title_en' => 'District', 'title_bn' => 'জেলা', 'number' => $counts['permanent_district_count'], 'link' => '/system-configuration/district'],
            ['title_en' => 'Upazila', 'title_bn' => 'উপজেলা', 'number' => $counts['permanent_upazila_count'], 'link' => '/system-configuration/city'],
            ['title_en' => 'City Cor.', 'title_bn' => 'সিটি কর্পোরেশন', 'number' => $counts['permanent_city_corp_count'], 'link' => '/system-configuration/city'],
            ['title_en' => 'Dist/Pau', 'title_bn' => 'জেলা/পৌরসভা', 'number' => $counts['permanent_district_pourashava_count'], 'link' => '/system-configuration/city'],
            ['title_en' => 'Union', 'title_bn' => 'ইউনিয়ন', 'number' => $counts['permanent_union_count'], 'link' => '/system-configuration/union'],
            ['title_en' => 'Thana', 'title_bn' => 'থানা', 'number' => $counts['permanent_thana_count'], 'link' => '/system-configuration/union'],
            ['title_en' => 'Pourashava', 'title_bn' => 'পৌরসভা', 'number' => $counts['permanent_pourashava_count'], 'link' => '/system-configuration/union'],
            ['title_en' => 'Ward', 'title_bn' => 'ওয়ার্ড', 'number' => $counts['permanent_ward_count'], 'link' => '/system-configuration/ward'],
        ];
        return response()->json([
            'data' => $items,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    public function programWiseBeneficiaryCount(Request $request)
    {
        $status = $request->status;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        // Initialize the query
        $query = AllowanceProgram::where('system_status', 1);

        // If start date and end date are provided, filter by the specified date range
        if ($startDate && $endDate) {
            $query->withCount(['beneficiaries' => function ($query) use ($startDate, $endDate,$status) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }]);
        } else {
            // If start date and end date are not provided, filter by the current year
            $query->withCount(['beneficiaries' => function ($query) use ($currentYear,$status) {
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

    public function officeWiseTotalUserCount(Request $request){

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;
        $office_type = $request->office_type;

        $query = Lookup::where('type', 3);

        if ($office_type) {
            $query->where('id', $office_type);
        }

        if ($startDate && $endDate) {
            $query->withCount(['users' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }]);
        } else {
            // If start date and end date are not provided, filter by the current year
            $query->withCount(['users' => function ($query) use ($currentYear) {
                $query->whereYear('created_at', $currentYear);
            }]);
        }

        $officeUser = $query->get();

        return response()->json([
            'data' => $officeUser,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);


    }
}
