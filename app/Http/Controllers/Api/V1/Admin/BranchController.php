<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\Admin\CommonResource;
use App\Models\BankBranch;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $data = BankBranch::with(['bank', 'district']);

        if ($request->has('bank_id')){
            $data = $data->where('bank_id', '=', $request->bank_id);
        }

        if ($request->has('branch_id')){
            $data = $data->where('id', '=', $request->branch_id);
        }

        if ($request->has('status')){
            $data = $data->where('status', '=', $request->status);
        }

        if ($request->has('is_main_branch')){
            $data = $data->where('is_main_branch', '=', $request->is_main_branch);
        }

        if ($request->has('district_id')){
            $data = $data->where('district_id', '=', $request->district_id);
        }

        if ($request->searchText) {
            $searchText = $request->searchText;
            $data = $data->where(function ($query) use ($searchText) {
                $query->where('name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('routing_number', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('swift_code', 'LIKE', '%' . $searchText . '%')

                    ->orWhere('contact_name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('contact_name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('contact_designation', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('contact_email', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('contact_mobile_no', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('contact_telephone_number', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('contact_address_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('contact_address_bn', 'LIKE', '%' . $searchText . '%')

                      ->orWhereHas('bank', function ($q) use ($searchText) {
                          $q->where(function ($q2) use ($searchText) {
                              $q2->where('name_en', 'LIKE', '%' . $searchText . '%')
                                 ->orWhere('name_bn', 'LIKE', '%' . $searchText . '%');
                          });
                      });
            });
        }
        if ($request->has('sort_by') && $request->has('sort_order')) {
            $data = $data->orderBy($request->sort_by, $request->sort_order);
        } else {
            $data = $data->latest();
        }


        $perPage = $request->get('perPage', 10);
        $branches = $data->paginate($perPage);

        return CommonResource::collection($branches);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'integer',
            'name_en' => 'sometimes|required|string|max:255',
            'name_bn' => 'sometimes|required|string|max:255',
            'routing_number' => 'sometimes|required|string|max:255',
            'swift_code' => 'nullable|string|max:20',
            'is_main_branch' => 'nullable|integer',
            'district_id' => 'required|integer',
            'contact_name_en' => 'nullable|string|max:300',
            'contact_name_bn' => 'nullable|string|max:300',
            'contact_designation' => 'nullable|string|max:300',
            'contact_email' => 'nullable|email|string|max:300',
            'contact_mobile_no' => 'nullable|string|regex:/^01[3-9]\d{8}$/|max:20',
            'contact_telephone_number' => 'nullable|string|max:20',
            'contact_address_en' => 'nullable|string|max:300',
            'contact_address_bn' => 'nullable|string|max:300',
        ]);

        $data = $request->all();
        $data['is_main_branch'] = $request->is_main_branch??0;

        $branch = BankBranch::create($data);

        return response()->json($branch, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BankBranch $bankBranch)
    {
        return response()->json($bankBranch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'bank_id' => 'integer',
            'name_en' => 'sometimes|required|string|max:255',
            'name_bn' => 'sometimes|required|string|max:255',
            'routing_number' => 'sometimes|required|string|max:255',
            'swift_code' => 'nullable|string|max:20',
            'is_main_branch' => 'required|integer|in:0,1',
            'district_id' => 'required|integer',
            'contact_name_en' => 'nullable|string|max:300',
            'contact_name_bn' => 'nullable|string|max:300',
            'contact_designation' => 'nullable|string|max:300',
            'contact_email' => 'nullable|email|string|max:300',
            'contact_mobile_no' => 'nullable|string|regex:/^01[3-9]\d{8}$/|max:20',
            'contact_telephone_number' => 'nullable|string|max:20',
            'contact_address_en' => 'nullable|string|max:300',
            'contact_address_bn' => 'nullable|string|max:300',
        ]);

//        BankBranch::where('id', $request->id)->update($request->all());

        BankBranch::where('id', $request->id)->update($request->only([
            'bank_id',
            'name_en',
            'name_bn',
            'routing_number',
            'swift_code',
            'is_main_branch',
            'district_id',
            'contact_name_en',
            'contact_name_bn',
            'contact_designation',
            'contact_email',
            'contact_mobile_no',
            'contact_telephone_number',
            'contact_address_en',
            'contact_address_bn',
        ]));


        return response()->json(['message' => 'Branch updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankBranch $branch)
    {
        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully']);
    }

    public function getBranchesByBankId($id)
    {
        $branches = BankBranch::query()->where('bank_id', $id)->orderBy('name_en', 'ASC')->get();
        return CommonResource::collection($branches);
    }

    public function updateStatus(BankBranch $id)
    {
        $beforeUpdate = $id->replicate();

        $id->update(['status' => !$id->status]);

        Helper::activityLogUpdate($id, $beforeUpdate,'Bank Branch','Bank Branch Status Updated !');

        return $this->sendResponse($id, 'Bank Branch status updated successfully');

    }
}
