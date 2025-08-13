<?php

namespace App\Http\Controllers\Api\V1\Admin\Training;

use App\Helpers\Helper;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use App\Models\KoboUpdateToken;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Training\TimeSlotRequest;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = TimeSlot::query();

        $query->when(request('search'), function ($q, $v) {
            $q->where('time', 'like', "%$v%")
            ;
        });

        $query->latest();

        return $this->sendResponse($query
            ->paginate(request('perPage'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimeSlotRequest $request)
    {
        $timeSlot = TimeSlot::create($request->validated());

        Helper::activityLogInsert($timeSlot, '','Time Slot','Time Slot Created !');

        return $this->sendResponse($timeSlot, 'Time Slot created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeSlot $timeSlot)
    {
        return $this->sendResponse($timeSlot);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TimeSlotRequest $request, TimeSlot $timeSlot)
    {
        $beforeUpdate = $timeSlot->replicate();

        $timeSlot->update($request->validated());

        Helper::activityLogUpdate($timeSlot, $beforeUpdate,'Time Slot','Time Slot Updated !');

        return $this->sendResponse($timeSlot, 'Time Slot updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeSlot $timeSlot)
    {
        $timeSlot->forceDelete();

        Helper::activityLogDelete($timeSlot, '','Time Slot','Time Slot Deleted !');

        return $this->sendResponse($timeSlot, 'Time Slot deleted successfully');

    }
    //update kobo token
    public function updateToken(Request $request)
{
    // Validate the request
    $request->validate([
        // 'id' => 'required|exists:kobo_update_tokens,id', // Ensure 'id' exists in 'kobo_update_tokens' table
        'token' => 'required|string' // Ensure 'token' is not null and is a string
    ]);

    // Find the existing token record
     $before = KoboUpdateToken::where('id',1)->first();
    $beforeUpdate = KoboUpdateToken::firstOrNew(['id' => 1]);

    // Get the new token from the request
    $updateToken = $request->token;
    $beforeUpdate->token = $updateToken;
    $beforeUpdate->save();

    // Log the update activity
    Helper::activityLogUpdate($beforeUpdate, $before, 'Kobo Token', 'Kobo Token Updated !');

    // Update the token in the database


    // Return a success response
    return $this->sendResponse($updateToken, 'Token updated successfully');
}
    public function getToken(){

        $token=KoboUpdateToken::firstOrCreate(
            ['id' => 1],
            ['token' => env('KOBO_API_TOKEN')]
        );
        return  $token;
    }

}
