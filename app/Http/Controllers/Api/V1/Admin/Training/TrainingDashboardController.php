<?php

namespace App\Http\Controllers\Api\V1\Admin\Training;

use App\Http\Controllers\Controller;
use App\Models\Lookup;
use App\Models\Trainer;
use App\Models\TrainingCircular;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramParticipant;
use App\Models\TrainingRating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\DB;

class TrainingDashboardController extends Controller
{
    public function trainingProgramlist()
    {
        $data = TrainingProgram::select('id', 'program_name')->get();
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => "Data successfully fetched",
        ], ResponseAlias::HTTP_OK);
    }
    public function trainingModulelist()
    {
        $data = Lookup::where('type', 29)->get();
        return response()->json([
            'success' => true,
            'total' => $data->count(),
            'data' => $data,
            'message' => "Data successfully fetched",
        ], ResponseAlias::HTTP_OK);
    }
    public function cardCalculation()
    {
        // Total number of courses
        $totalCourse = Lookup::where('type', 29)->count() ?? 0;

        // Total number of participants with status 1
        $totalParticipants = TrainingProgramParticipant::where('status', 1)->count() ?? 0;

        // Training completion percentage
        $trainingCompletion = TrainingProgramParticipant::where('status', 1)
            ->where('invitation_status', 1)
            ->count() ?? 0;

        $totalInvitations = TrainingProgramParticipant::where('invitation_status', 1)->count() ?? 0;

        $completionPercentage = $totalInvitations > 0
            ? ($trainingCompletion / $totalInvitations) * 100
            : 0;

        // Total number of active batches
        $activeBatches = TrainingProgram::where('status', 82)->count() ?? 0;

        // Total number of trainers and active trainers
        $totalNumberofTrainers = Trainer::count() ?? 0;
        $totalNumberofActiveTrainers = Trainer::where('status', 1)->count() ?? 0;

        // Average enrollment per training program
        $averageEnrollment = TrainingProgram::withCount('participants')
            ->get()
            ->avg('participants_count') ?? 0;

        // Prepare data for response
        $data = [
            [
                'id' => 1,
                'name_en' => 'Total Course',
                'name_bn' => 'সর্বমোট কোর্স',
                'value' => $totalCourse,
                'icon' => 'mdi mdi-book-open-variant',
            ],
            [
                'id' => 2,
                'name_en' => 'Total Participants',
                'name_bn' => 'সর্বমোট অংশগ্রহণকারী',
                'value' => $totalParticipants,
                'icon' => 'mdi mdi-crowd',
            ],
            [
                'id' => 3,
                'name_en' => 'Training Completion',
                'name_bn' => 'প্রশিক্ষণ সম্পন্ন হয়েছে',
                'value' => $completionPercentage,
                'icon' => 'mdi mdi-card-multiple',
            ],
            [
                'id' => 4,
                'name_en' => 'Active Batches',
                'name_bn' => 'সক্রিয় ব্যাচ',
                'value' => $activeBatches,
                'icon' => 'mdi mdi-database',
            ],
            [
                'id' => 5,
                'name_en' => 'Total No of Trainers',
                'name_bn' => 'সর্বমোট প্রশিক্ষকের সংখ্যা',
                'value' => $totalNumberofTrainers,
                'icon' => 'mdi mdi-account-group',
            ],
            [
                'id' => 6,
                'name_en' => 'Active Trainers',
                'name_bn' => 'সক্রিয় প্রশিক্ষক',
                'value' => $totalNumberofActiveTrainers,
                'icon' => 'mdi mdi-human-queue',
            ],
            [
                'id' => 7,
                'name_en' => 'Enrolment Per Training (Avg.)',
                'name_bn' => 'প্রতি প্রশিক্ষণে নিবন্ধন (গড়)',
                'value' => round($averageEnrollment, 2),
                'icon' => 'mdi mdi-percent-circle-outline',
            ],
        ];

        // Return JSON response
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => "Data successfully fetched",
        ], ResponseAlias::HTTP_OK);
    }
    public function topTrainers(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        $data = Trainer::query()
            ->select('trainers.id', 'trainers.name',
                DB::raw("COUNT(t2.module_id) as total")
            )
            ->leftJoin('training_program_trainers as t1', 'trainers.id', '=', 't1.trainer_id')
            ->leftJoin('training_program_modules as t2', 't1.training_program_id', '=', 't2.training_program_id')
            ->leftJoin('training_programs as t3', 't2.training_program_id', '=', 't3.id')
            ->when($request->program_id, function ($q, $v) {
                $q->where('t3.id', $v);
            })
            ->when($request->start_date, function ($q, $startDate) {
                $q->whereDate('t3.created_at', '>=', $startDate);
            })
            ->when($request->end_date, function ($q, $endDate) {
                $q->whereDate('t3.created_at', '<=', $endDate);
            })
            ->groupBy('trainers.id', 'trainers.name')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => "Data successfully fetched",
        ], ResponseAlias::HTTP_OK);

    }
    public function topParticipants(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        $data = User::query()
            ->select('users.id', 'users.full_name',
                DB::raw("COUNT(t2.module_id) as total")
            )
            ->leftJoin('training_program_participants as t1', 'users.id', '=', 't1.user_id')
            ->leftJoin('training_program_modules as t2', 't1.training_program_id', '=', 't2.training_program_id')
            ->leftJoin('training_programs as t3', 't2.training_program_id', '=', 't3.id')
            ->when($request->program_id, function ($q, $v) {
                $q->where('t3.id', $v);
            })
            ->when($request->start_date, function ($q, $startDate) {
                $q->whereDate('t3.created_at', '>=', $startDate);
            })
            ->when($request->end_date, function ($q, $endDate) {
                $q->whereDate('t3.created_at', '<=', $endDate);
            })
            ->groupBy('users.id', 'users.full_name')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => "Data successfully fetched",
        ], ResponseAlias::HTTP_OK);

    }
    public function trainingStatus(Request $request){

        $module = $request->module_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        $query = Lookup::where('type', 31)
            ->withCount('trainingPrograms');
        if($module){
            $query->when($request->module_id, function ($q, $module) {
                $q->whereHas('trainingPrograms.modules', function ($q) use($module) {
                    $q->where('module_id', $module);
                });
            });
        }
        if ($startDate && $endDate) {
            $query->whereHas('trainingPrograms', function ($q) use ($startDate, $endDate) {
                $q->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            });
        } else {
            $query->whereYear('created_at', $currentYear);
        }

        $data = $query->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => "Data successfully fetched",
        ], ResponseAlias::HTTP_OK);
    }
    public function trainingMode(Request $request)
    {

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $trainingCircularQuery = TrainingCircular::select('id', 'training_type_id');

        if ($request->has('program_id')) {
            $trainingCircularQuery->whereHas('programs', function ($query) use ($request) {
                $query->where('training_circular_id', $request->program_id);
            });
        }

        // Retrieve filtered TrainingCircular records
        $trainingCounts = $trainingCircularQuery
            ->when($request->start_date, function ($q, $startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($request->end_date, function ($q, $endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            })
            ->get()
            ->groupBy('training_type_id')
            ->map(function ($items) {
                return count($items);
            });

        // Retrieve counts for online (training_type_id = 61) and offline (training_type_id = 62)
        $onlineCount = $trainingCounts->get(61);
        $offlineCount = $trainingCounts->get(62);

        // Return counts in the desired format as JSON response
        return response()->json([
            'success' => true,
            'data' => ['online' => $onlineCount, 'offline' => $offlineCount],
            'message' => "Data successfully fetched"
        ], ResponseAlias::HTTP_OK);
    }

    public function monthWiseParticipants(Request $request)
    {
        $participantsData = [
            'day' => [],
            'month' => [],
            'year' => []
        ];

        $selectedMonth = $request->input('month', Carbon::now()->month); // Default to current month if not provided
        $selectedYear = $request->input('year', Carbon::now()->year); // Default to current year if not provided

        $daysInMonth = Carbon::create($selectedYear, $selectedMonth)->daysInMonth;


        // Calculate day-wise data for the selected month and year
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $startDate = Carbon::create($selectedYear, $selectedMonth, $day)->startOfDay();
            $endDate = Carbon::create($selectedYear, $selectedMonth, $day)->endOfDay();

            $count = TrainingProgramParticipant::where('invitation_status', 1)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $participantsData['day'][] = [
                'label' => str_pad($day, 2, '0', STR_PAD_LEFT), // Format day as '01', '02', ..., '31'
                'value' => $count
            ];
        }

        // Calculate month-wise data for the selected year
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($selectedYear, $month, 1)->startOfDay();
            $endDate = Carbon::create($selectedYear, $month, 1)->endOfMonth()->endOfDay();

            $count = TrainingProgramParticipant::where('invitation_status', 1)
                ->whereMonth('created_at', $selectedMonth)
                ->whereYear('created_at', $selectedYear)
                ->count();

            $participantsData['month'][] = [
                'label' => Carbon::create()->month($month)->format('M'), // Format month as 'Jan', 'Feb', ..., 'Dec'
                'value' => $count
            ];
        }

        // Calculate year-wise data
        $years = TrainingProgramParticipant::where('invitation_status', 1)
            ->whereYear('created_at', $selectedYear)
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('COUNT(*) as total'))
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        foreach ($years as $yearData) {
            $participantsData['year'][] = [
                'label' => $yearData->year,
                'value' => $yearData->total
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $participantsData,
            'message' => "Participants data successfully fetched",
        ], ResponseAlias::HTTP_OK);
    }


//    public function monthWiseParticipants(Request $request){
//
//        $participantsData = [
//            'day' => [],
//            'month' => [],
//            'year' => []
//        ];
//
//        $currentMonthDays = Carbon::now()->daysInMonth;
//
//        for ($day = 1; $day <= $currentMonthDays; $day++) {
//            $startDate = Carbon::now()->startOfMonth()->addDays($day - 1)->startOfDay();
//            $endDate = Carbon::now()->startOfMonth()->addDays($day - 1)->endOfDay();
//            $count = TrainingProgramParticipant::where('invitation_status', 1)
//                ->whereBetween('created_at', [$startDate, $endDate])
//                ->count();
//
//            $participantsData['day'][] = [
//                'label' => str_pad($day, 2, '0', STR_PAD_LEFT), // Format day as '01', '02', ..., '31'
//                'value' => $count
//            ];
//        }
//
//        // Calculate month-wise data for the current year
//        for ($month = 1; $month <= 12; $month++) {
//            $startDate = Carbon::create(Carbon::now()->year, $month, 1)->startOfDay();
//            $endDate = Carbon::create(Carbon::now()->year, $month, 1)->endOfMonth()->endOfDay();
//
//            $count = TrainingProgramParticipant::where('invitation_status', 1)
//                ->whereMonth('created_at', $month)
//                ->whereYear('created_at', Carbon::now()->year)
//                ->count();
//
//            $participantsData['month'][] = [
//                'label' => Carbon::create()->month($month)->format('M'), // Format month as 'Jan', 'Feb', ..., 'Dec'
//                'value' => $count
//            ];
//        }
//
//        // Calculate year-wise data
//        $years = TrainingProgramParticipant::where('invitation_status', 1)
//            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('COUNT(*) as total'))
//            ->groupBy('year')
//            ->orderBy('year')
//            ->get();
//
//        foreach ($years as $yearData) {
//            $participantsData['year'][] = [
//                'label' => $yearData->year,
//                'value' => $yearData->total
//            ];
//        }
//
//        return response()->json([
//            'success' => true,
//            'data' => $participantsData,
//            'message' => "Participants data successfully fetched",
//        ], ResponseAlias::HTTP_OK);
//    }
}
