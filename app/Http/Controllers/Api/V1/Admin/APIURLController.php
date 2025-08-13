<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\APIManager\StoreRequest;
use App\Http\Requests\Admin\APIManager\UpdateRequest;
use App\Models\APIUrl;
use Illuminate\Http\Request;

class APIURLController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = APIUrl::query();

        $query->when(request('search'), function ($q, $v) {
            $q->where('name', 'like', "%$v%")
                ->orWhere('method', 'like', "%$v%")
                ->orWhere('url', 'like', "%$v%")
                ->orWhere('table', 'like', "%$v%")
            ;
        });


        return $this->sendResponse($query->paginate(
            request('perPage')
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $apiUrl = new APIUrl();
        $apiUrl->name = $request->name;
        $apiUrl->method = $request->method;
        $apiUrl->url = $request->url;
        $apiUrl->table = $request->table;
        $apiUrl->save();

        return $this->sendResponse($apiUrl, 'URL stored successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(APIUrl $apiUrl)
    {
        return $this->sendResponse($apiUrl);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, APIUrl $apiUrl)
    {
        $apiUrl->name = $request->name;
        $apiUrl->method = $request->method;
        $apiUrl->url = $request->url;
        $apiUrl->table = $request->table;
        $apiUrl->status = $request->status ?? $apiUrl->status;
        $apiUrl->save();

        return $this->sendResponse($apiUrl, 'URL updated successfully');
    }
     public function updateURL(UpdateRequest $request, APIUrl $apiUrl)

    {
        $apiUrl=APIUrl::find( $request->id);
        $apiUrl->name = $request->name;
        $apiUrl->method = $request->method;
        $apiUrl->url = $request->url;
        $apiUrl->table = $request->table;
        // $apiUrl->status = $request->status ?: $apiUrl->status;
        $apiUrl->save();

        return $this->sendResponse($apiUrl, 'URL updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(APIUrl $apiUrl)
    {
        $apiUrl->delete();

        return $this->sendResponse($apiUrl, 'Url deleted successfully');
    }





}
