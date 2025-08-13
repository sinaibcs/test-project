<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\Systemconfig\SystemconfigService;
use App\Models\AllowanceProgram;
use App\Models\ApiDataReceive;
use App\Models\ApiList;
use App\Models\ApiLog;
use App\Models\Application;
use App\Models\Location;
use App\Models\Lookup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ApiDashboardController extends Controller
{
    use MessageTrait;

    public function getAllApiCount(Request $request){

        $count['organization'] = ApiDataReceive::count();
        $count['apiList'] = ApiList::count();

        $count['apiRequest'] = ApiLog::where('request_time', '>', now()->subDay())->count();
        $count['apiReceive'] = ApiLog::whereStatus(1)
            ->where('request_time', '>', now()->subDay())
            ->count();


        $data = [
            ['title_en' => 'Total Organization', 'title_bn' => 'মোট সংগঠন', 'number' => $count['organization']],
            ['title_en' => 'Total API', 'title_bn' => 'মোট এপিআই', 'number' => $count['apiList']],
            ['title_en' => 'Total API Request', 'title_bn' => 'মোট এপিআই অনুরোধ', 'number' => $count['apiRequest']],
            ['title_en' => 'Total API Data Receive', 'title_bn' => 'মোট এপিআই ডেটা প্রাপ্তি', 'number' => $count['apiReceive']],
        ];


        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }





    public function organizationWiseCount(Request $request)
    {
        $query = ApiDataReceive::query();

        if ($request->start_date && $request->end_date) {
            $query->withCount(['apiLogs' => function ($query) use($request) {
                $query->whereDate('request_time', '>=', $request->start_date)
                    ->whereDate('request_time', '<=', $request->end_date);
            }]);
        } else {
            $query->withCount(['apiLogs' => function ($query) {
                $query->whereYear('created_at', now()->year);
            }]);
        }


        return response()->json([
            'data' => $query->get(),
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);

    }


    public function dateWiseCount(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)
            : now()->subDays(4);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();

        $countDates = ApiLog::query()->select(
            DB::raw("DATE(request_time) request_date"),
            DB::raw("COUNT(*) total")
        )->groupBy('request_date')
            ->having('request_date', '>=', $startDate)
            ->having('request_date', '<=', $endDate)
            ->orderBy('request_date')
//            ->limit(5)
            ->get();

        $countDates = $countDates->map(function ($i) {
            $i->request_date = Carbon::parse($i->request_date)->format('M j');
            return $i;
        });

        return $this->sendResponse($countDates);

    }





}
