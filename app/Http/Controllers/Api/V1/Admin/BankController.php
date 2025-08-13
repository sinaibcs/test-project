<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Mfs;
use App\Models\Bank;
use App\Models\BankBranch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Admin\CommonResource;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $data = Bank::query();

        if ($request->searchText) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('name_en', 'LIKE', '%' . $request->searchText . '%')
                    ->orWhere('name_bn', 'LIKE', '%' . $request->searchText . '%');
            });
        }

        if ($request->has('sort_by') && $request->has('sort_order')) {
            $data = $data->orderBy($request->sort_by, $request->sort_order);
        } else {
            $data = $data->latest();
        }

        $perPage = $request->get('perPage', 10);
        $banks = $data->paginate($perPage);

        return CommonResource::collection($banks);
    }

    public function get(){
        $data = Cache::remember('all_banks', now()->addMinutes(env('CACHE_TIMEOUT')), function(){
            return Bank::orderBy('name_en')->get();
        });
        return CommonResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // return $request->all();
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_bn' => 'required|string|max:255',
        ]);

        $bank = Bank::create($request->all());

        return response()->json($bank, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bank $bank)
    {
        return response()->json($bank);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bank $bank)
    {
        // return $request->all();
        $request->validate([
            'name_en' => 'sometimes|required|string|max:255',
            'name_bn' => 'sometimes|required|string|max:255',
        ]);

        $bank->update([
            'name_en' => $request->input('name_en', $bank->name_en),
            'name_bn' => $request->input('name_bn', $bank->name_bn),
            'charge' => $request->input('charge', $bank->charge),
        ]);

        return response()->json($bank);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        $bank->delete();
        return response()->json(['message' => 'Bank deleted successfully']);
    }

    public function getAllBanks()
    {
        $banks = Bank::all();
        return CommonResource::collection($banks);
    }

    public function getAllMfs()
    {
        $mfsList = Mfs::all();
        return CommonResource::collection($mfsList);
    }
}
