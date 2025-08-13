<?php

namespace App\Http\Controllers\Api\V1\Admin\Training;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Training\TrainingCircularRequest;
use App\Models\TrainingCircular;

class TrainingCircularController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = TrainingCircular::query();
        $query->with('modules', 'circularType', 'trainingType', 'status');
        $query->when(request('search'), function ($q, $v) {
            $q->where('circular_name', 'like', "%$v%")
            ;
        });

        $query->when(request('training_type_id'), function ($q, $v) {
            $q->where('training_type_id', $v);
        });

        $query->when(request('circular_type_id'), function ($q, $v) {
            $q->where('circular_type_id', $v);
        });

        $query->when(request('module_id'), function ($q, $v) {
            $q->whereHas('modules', function ($q) use ($v) {
                $q->whereId($v);
            });
        });

        $query->when(request('start_date'), function ($q, $v) {
            $q->whereDate('start_date', '>=', $v);
        });


        $query->when(request('end_date'), function ($q, $v) {
            $q->whereDate('end_date', '<=', $v);
        });

        $query->latest();

        return $this->sendResponse($query
            ->paginate(request('perPage'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TrainingCircularRequest $request)
    {
        $trainingCircular = TrainingCircular::create($request->except('module_id'));

        $trainingCircular->modules()->attach($request->module_id);

        Helper::activityLogInsert($trainingCircular, '','Training Circular','Training Circular Created !');

        return $this->sendResponse($trainingCircular, 'Training Circular created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(TrainingCircular $circular)
    {
        $circular->load('modules', 'circularType', 'trainingType', 'status');

        return $this->sendResponse($circular);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TrainingCircularRequest $request, TrainingCircular $circular)
    {
        $beforeUpdate = $circular->replicate();

        $circular->update($request->except('module_id'));

        $circular->modules()->sync($request->module_id);

        Helper::activityLogUpdate($circular, $beforeUpdate,'Training Circular','Training Circular Updated !');

        return $this->sendResponse($circular, 'Training Circular updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TrainingCircular $circular)
    {
        $circular->programs()->delete();

        $circular->delete();

        Helper::activityLogDelete($circular, '','Training Circular','Training Circular Deleted !');

        return $this->sendResponse($circular, 'Training Circular deleted successfully');

    }
}
