<?php

namespace App\Http\Controllers\Api\V1\Admin\Training;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Training\ExternalParticipantRequest;
use App\Http\Requests\Admin\Training\ParticipantRequest;
use App\Http\Requests\Admin\Training\ParticipantUpdateRequest;
use App\Http\Services\Admin\Training\ParticipantService;
use App\Models\TrainingCircular;
use App\Models\TrainingParticipant;
use App\Models\User;
use Illuminate\Http\Request;

class TrainingParticipantController extends Controller
{
    public function __construct(public ParticipantService $participantService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TrainingParticipant::query();

        $query->with('user', 'trainingCircular', 'trainingProgram', 'organization');

        $query->when($request->name, function ($q, $v) {
            $q->where(function ($q) use ($v) {
                $q->where('full_name', 'like', "%$v%")
                    ->orWhereHas('user', function ($q) use ($v) {
                        $q->where('full_name', 'like', "%$v%");
                    });
            });
        });

        $query->when($request->organization_id, function ($q, $v) {
            $q->where('organization_id', $v);
        });

        $query->when($request->circular_id, function ($q, $v) {
            $q->where('training_circular_id', $v);
        });

        $query->when($request->program_id, function ($q, $v) {
            $q->where('training_program_id', $v);
        });

        $query->when($request->office_type, function ($q, $v) {
            $q->whereHas('user', function ($q) use ($v){
                $q->where('office_type', $v);
            });
        });

        $query->when($request->office_id, function ($q, $v) {
            $q->whereHas('user', function ($q) use ($v){
                $q->where('office_id', $v);
            });
        });

        $query->latest();

        return $this->sendResponse($query
            ->paginate(request('perPage'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ParticipantRequest $request)
    {
        $participant = TrainingParticipant::create($request->validated());

        $participant->user->assignRole('participant');

        Helper::activityLogInsert($participant, '','Training Participant','Training Participant Created !');

        return $this->sendResponse($participant, 'Training Participant created successfully');
    }


    public function storeExternalParticipant(ExternalParticipantRequest $request)
    {
        $user = $this->participantService->saveExternalUser($request);

        $participant = $this->participantService->saveExternalParticipant($request, $user);

        Helper::activityLogInsert($participant, '','Training External Participant','Training Participant Created !');

        return $this->sendResponse($participant, 'Participant registration successfully');
    }



    public function getUsers(Request $request, $userType)
    {
        $query = User::query();

        $query->select('id', 'username', 'full_name', 'user_id', 'user_type', 'photo', 'mobile', 'email');

        $query->when($request->location_id, function ($q, $v) {
            $q->where('assign_location_id', $v);
        });


        $query->when($userType == 1, function ($q) {
            $q->whereNotNull('office_type')
                ->whereNull('committee_type_id');
        }) ;

        $query->when($userType == 2, function ($q) {
            $q->whereNotNull('committee_type_id')
                ->whereNull('office_type');
        }) ;

        return $this->sendResponse($query->get());
    }


    public function trainingCirculars()
    {
        $circulars = TrainingCircular::with('programs:id,program_name,training_circular_id')
            ->select('id', 'circular_name')
            ->get();

        return $this->sendResponse($circulars);
    }

    /**
     * Display the specified resource.
     */
    public function show(TrainingParticipant $participant)
    {
        $participant->load('user', 'trainingCircular', 'trainingProgram', 'organization');

        return $this->sendResponse($participant);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ParticipantUpdateRequest $request, TrainingParticipant $participant)
    {
        $beforeUpdate = $participant->replicate();

        $participant->update($request->validated());

        Helper::activityLogUpdate($participant, $beforeUpdate,'Training Participant','Training Participant Updated !');

        return $this->sendResponse($participant, 'Training Participant updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainingParticipant $participant)
    {
        $participant->delete();

        Helper::activityLogDelete($participant, '','Training Participant','Training Participant Deleted !');

        return $this->sendResponse($participant, 'Training Participant deleted successfully');

    }
}
