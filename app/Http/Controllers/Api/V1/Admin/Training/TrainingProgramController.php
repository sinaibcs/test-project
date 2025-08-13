<?php

namespace App\Http\Controllers\Api\V1\Admin\Training;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Training\TrainingProgramRequest;
use App\Http\Services\Admin\Training\KoboService;
use App\Http\Services\Admin\Training\ProgramService;
use App\Http\Traits\RoleTrait;
use App\Models\KoboUpdateToken;
use App\Models\TimeSlot;
use App\Models\TrainingProgram;
use App\Models\Trainer;
use App\Models\TrainingCircular;
use App\Models\TrainingProgramParticipant;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TrainingProgramController extends Controller
{
    use RoleTrait;

    public function __construct(public ProgramService $programService, public KoboService $koboService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TrainingProgram::query();

        $query->when(request('search'), function ($q, $v) {
            $q->where('program_name', 'like', "%$v%")
            ;
        });

        $query->when(request('training_type_id'), function ($q, $v) {
            $q->whereHas('trainingCircular', function ($q) use ($v) {
                $q->where('training_type_id', $v);
            });
        });

        $query->when(request('training_circular_id'), function ($q, $v) {
            $q->where('training_circular_id', $v);
        });

        $query->when(request('module_id'), function ($q, $v) {
            $q->whereHas('modules', function ($q) use ($v) {
                $q->whereId($v);
            });
        });

        $query->when(request()->has('status'), function ($q, $v) {
            $q->where('status', request('status'));
        });

        $query->when(request('trainer_id'), function ($q, $v) {
            $q->whereHas('trainers', function ($q) use ($v) {
                $q->whereId($v);
            });
        });


        $query->when(request('start_date'), function ($q, $v) {
            $q->whereDate('start_date', '>=', $v);
        });


        $query->when(request('end_date'), function ($q, $v) {
            $q->whereDate('end_date', '<=', $v);
        });

        $user = auth()->user();

        if ($user->user_type !=1 && $user->hasRole($this->participant)) {
            $query->whereHas('participants', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        if ($user->user_type !=1 && $user->hasRole($this->trainer)) {
            $query->whereHas('trainers', function ($q) {
                $q->where('trainer_id', auth()->id());
            });
        }


        $query->with('modules', 'trainingCircular.trainingType', 'trainers', 'statusName');

        $query->latest();

        return $this->sendResponse($query
            ->paginate(request('perPage'))
        );




    }


    public function testKobo(TrainingProgram $program)
    {
        $token = env('KOBO_API_TOKEN');
        $formId = "a8dzS9mTqe7onKLtg7GJoC";
        $formId = "aoh7beyhUxZ2yAMCJjgCwW";

        $endpoint = 'assets/' . $formId . '/data?format=json';
        $endpoint = 'assets/' . $formId . '?format=json';
//        $endpoint = 'assets/' . $formId . '?.json';


        $examResults = $this->koboService->getData($endpoint, $token);

        return $examResults;
        dd($examResults['content']['survey']);
//        dd($examResults['summary']['labels']);

    }

    public function syncData(TrainingProgram $program)
    {
        if (!$program->form_id && !$program->training_form_id) {
            throw new HttpException(400, "No data to sync");
        }

        DB::beginTransaction();
        try {
            $token = KoboUpdateToken::value('token') ?: env('KOBO_API_TOKEN');
            if ($program->form_id) {
                $this->koboService->insertQuestion($program, $token);
                $this->koboService->insertAnswers($program, $token);
            }

            if ($program->training_form_id) {
                $this->koboService->insertTrainingPaper($program, $token);
                $this->koboService->insertTrainerRatingAnswers($program, $token);
            }
            DB::commit();
            return $this->sendResponse($program, 'Data synced successfully');
        } catch (\Exception $exception) {
            DB::rollBack();

            if ($exception instanceof GuzzleException) {
                return $this->sendError('Invalid Token');
            }

            throw $exception;
        }




    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TrainingProgramRequest $request)
    {
        DB::beginTransaction();
        try {
            $program = $this->programService->storeProgram($request);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        Helper::activityLogInsert($program, '','Training Program','Training Program Created !');
        return $this->sendResponse($program, 'Training Program created successfully');
    }



    public function circulars()
    {
        $circulars = TrainingCircular::with('modules:id,value_en,value_bn')
            ->get(['id', 'circular_name']);

        return $this->sendResponse($circulars);
    }


    public function trainers()
    {
        return $this->sendResponse(Trainer::whereStatus(1)->get());
    }


    public function timeSlots()
    {
        return $this->sendResponse(TimeSlot::get(['id', 'time']));
    }

    /**
     * Display the specified resource.
     */
    public function show(TrainingProgram $program)
    {
        $program->load('trainingCircular.trainingType', 'modules', 'trainers', 'statusName', 'users');

        return $this->sendResponse($program);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TrainingProgramRequest $request, TrainingProgram $program)
    {
        $beforeUpdate = $program->replicate();

        DB::beginTransaction();
        try {
            $program = $this->programService->updateProgram($request, $program);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        Helper::activityLogUpdate($program, $beforeUpdate,'Training Program','Training Program Updated !');
        return $this->sendResponse($program, 'Training Program updated successfully');
    }


    public function updateStatus(TrainingProgram $program)
    {
        $beforeUpdate = $program->replicate();
        $program->update(['status' => !$program->status]);
        Helper::activityLogUpdate($program, $beforeUpdate,'Training Program','Training Program Status Updated !');

        return $this->sendResponse($program, 'Training Program status updated successfully');

    }


    public function acceptInvitation(Request $request, TrainingProgramParticipant $participant)
    {
        $beforeUpdate = $participant->replicate();
        $participant->update(['invitation_status' => $request->invitation_status]);
        Helper::activityLogUpdate($participant, $beforeUpdate,'Training Program','Training Program Invitation Status Updated !');

        return $this->sendResponse($participant, 'Invitation status updated successfully');

    }


    public function updateExamStatus(TrainingProgram $program)
    {
        $beforeUpdate = $program->replicate();
        $program->update(['exam_status' => !$program->exam_status]);
        Helper::activityLogUpdate($program, $beforeUpdate,'Training Program','Training Program Exam Status Updated !');

        return $this->sendResponse($program, 'Exam status updated successfully');

    }

    public function updateRatingStatus(TrainingProgram $program)
    {
        $beforeUpdate = $program->replicate();
        $program->update(['rating_status' => !$program->rating_status]);
        Helper::activityLogUpdate($program, $beforeUpdate,'Training Program','Training Program Rating Status Updated !');

        return $this->sendResponse($program, 'Rating status updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainingProgram $program)
    {
        $program->delete();

        TrainingProgramParticipant::where('training_program_id', $program->id)->delete();

        Helper::activityLogDelete($program, '','Training Program','Training Program Deleted !');

        return $this->sendResponse($program, 'Training Program deleted successfully');

    }



}
