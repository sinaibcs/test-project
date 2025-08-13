<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Mfs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Admin\CommonResource;

class MfsController extends Controller
{
    public function index(Request $request)
    {
        $data = Mfs::query();

        if ($request->searchText) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('name_en', 'LIKE', '%' . $request->searchText . '%')
                    ->orWhere('routing_number', 'LIKE', '%' . $request->searchText . '%')
                    ->orWhere('name_bn', 'LIKE', '%' . $request->searchText . '%');
            });
        }

        if ($request->has('sort_by') && $request->has('sort_order')) {
            $data = $data->orderBy($request->sort_by, $request->sort_order);
        } else {
            $data = $data->latest();
        }

        $perPage = $request->get('perPage', 10);
        $mfsList = $data->paginate($perPage);

        return CommonResource::collection($mfsList);
    }

    public function get(){
        $data = Cache::remember('all_mfs', now()->addMinutes(env('CACHE_TIMEOUT')), function(){
            return Mfs::orderBy('name_en')->get();
        });
        return CommonResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_bn' => 'required|string|max:255',
        ]);

        $mfs = Mfs::create($request->all());

        return response()->json($mfs, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Mfs $mfs)
    {
        return response()->json($mfs);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name_en' => 'sometimes|required|string|max:255',
            'name_bn' => 'sometimes|required|string|max:255',
        ]);

        $mfs =Mfs::where('id',$request->id)->update([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'routing_number' => $request->routing_number,
            'charge' => $request->charge,
        ]);

        return response()->json($mfs);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Mfs::where('id',$id)->delete();
        return response()->json(['message' => 'MFS deleted successfully']);
    }

    /**
     * Get all MFS records.
     */
    public function getAllMfs()
    {
        $mfsList = Mfs::all();
        return CommonResource::collection($mfsList);
    }
}
