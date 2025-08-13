<?php

namespace App\Http\Controllers\Api\V1\Admin\Training;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Training\TrainerRequest;
use App\Http\Requests\Admin\Training\TrainerUpdateRequest;
use App\Http\Services\Admin\Training\TrainerService;
use App\Models\Trainer;
use App\Models\TrainingProgramModule;
use App\Models\TrainingProgramTrainer;
use Illuminate\Http\Request;

class TrainerController extends Controller
{

    public function __construct(public TrainerService $trainerService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Trainer::query();

        $query->when(request('search'), function ($q, $v) {
            $q->where('name', 'like', "%$v%")
                ->orWhere('id', $v)
                ->orWhere('mobile_no', 'like', "%$v%")
                ->orWhere('email', 'like', "%$v%")
                ->orWhereHas('designation', function ($q) use ($v) {
                    $q->where('value_en', 'like', "%$v%");
                });

        });

        $query->with('designation', 'user');
        $query->latest();

        return $this->sendResponse($query->paginate(
            request('perPage')
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TrainerRequest $request)
    {
        if ($request->is_external == 1) {
            $user = $this->trainerService->storeUser($request);
            $trainer = $this->trainerService->storeTrainer($request, $user->id);
            $this->trainerService->approveUser($user);
        } else {
            $trainer = $this->trainerService->storeTrainer($request, $request->user_id);
        }

        Helper::activityLogInsert($trainer, '','Trainer','Trainer Created !');

        return $this->sendResponse($trainer, 'Trainer created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainer $trainer)
    {
        $trainer->load('designation', 'programs.modules');

        $trainer->programs_count = $trainer->programs()->count();

        $trainer->modules_count = TrainingProgramModule::query()
            ->whereIn('training_program_id', $trainer->programs()->pluck('id'))
            ->get()
            ->unique('module_id')
            ->count()
        ;

        $trainer->rating = round($trainer->ratings()->avg('rating'), 2);

        return $this->sendResponse($trainer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TrainerUpdateRequest $request, Trainer $trainer)
    {
        $beforeUpdate = $trainer->replicate();

        $trainer = $this->trainerService->updateTrainer($request, $trainer);

        Helper::activityLogUpdate($trainer, $beforeUpdate,'Trainer','Trainer Updated !');

        return $this->sendResponse($trainer, 'Trainer updated successfully');

    }



    public function updateStatus(Trainer $trainer)
    {
        $beforeUpdate = $trainer->replicate();

        $trainer->update(['status' => !$trainer->status]);

        Helper::activityLogUpdate($trainer, $beforeUpdate,'Trainer','Trainer Status Updated !');

        return $this->sendResponse($trainer, 'Trainer status updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainer $trainer)
    {
        $trainer->delete();

        TrainingProgramTrainer::where('trainer_id', $trainer->id)->delete();

        Helper::activityLogDelete($trainer, '','Trainer','Trainer Deleted !');

        return $this->sendResponse($trainer, 'Trainer deleted successfully');

    }
}
