<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\API\ApiListRequest;
use App\Models\ApiList;
use App\Models\ApiModule;
use App\Models\ApiSelect;
use Illuminate\Support\Facades\Schema;

class APIListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ApiList::query();

        $query->when(request('search'), function ($q, $v) {
            $q->where('name', 'like', "%$v%")
            ;
        });

        return $this->sendResponse($query->paginate(
            request('perPage')
        ));
    }


    public function getModules()
    {
        $data = ApiModule::with('purposes')->get();

        return $this->sendResponse($data);

    }


    public function getApiList()
    {
        $data = ApiList::get();

        return $this->sendResponse($data);
    }


    public function getTableList()
    {
        $data = [];

        $tables = Schema::getTableListing();

        foreach ($tables as $table) {
            $data[] = [
                'table' => $table,
                'columns' => Schema::getColumnListing($table)
            ];
        }

        return  $this->sendResponse($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApiListRequest $request, ApiList $apiList)
    {
        $apiList->api_purpose_id = $request->api_purpose_id;
        $apiList->api_unique_id = $request->api_unique_id;
        $apiList->name = $request->name;
        $apiList->selected_columns = $request->selected_columns;
        $apiList->save();

        Helper::activityLogInsert($apiList, '','Api List','Api List Created');


        return $this->sendResponse($apiList, 'API created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(ApiList $apiList)
    {
        $apiList->load('purpose.module');
        return $this->sendResponse($apiList);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApiListRequest $request, ApiList $apiList)
    {
        $beforeUpdate = $apiList->replicate();
        $apiList->api_purpose_id = $request->api_purpose_id;
        $apiList->api_unique_id = $request->api_unique_id;
        $apiList->name = $request->name;
        $apiList->selected_columns = $request->selected_columns;
        $apiList->save();

        Helper::activityLogUpdate($apiList, $beforeUpdate,'Api List','Api List Updated !');


        return $this->sendResponse($apiList, 'API updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiList $apiList)
    {
        ApiSelect::where('api_list_id', $apiList->id)->delete();

        $apiList->delete();

        Helper::activityLogDelete($apiList, '','Api List','Api List Deleted !');

        return $this->sendResponse($apiList, 'API deleted successfully');
    }
}
