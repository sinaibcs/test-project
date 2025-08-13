<?php

namespace App\Http\Controllers\Api\V1\Admin\Training;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use App\Models\TrainingProgramParticipant;
use App\Models\TrainingRating;
use Illuminate\Http\Request;

class TrainingRatingController extends Controller
{
    public function store(Request $request)
    {
        foreach ($request->ratings as $rating) {
            TrainingRating::firstOrCreate([
                'training_program_id' => $request->program_id,
                'user_id' => auth()->id(),
                'trainer_id' => $rating['trainer_id']
            ], [
                'rating' => $rating['rating']
            ]);
        }

        return $this->sendResponse([], 'Rating given successfully');

    }
}
