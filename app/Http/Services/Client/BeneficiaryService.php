<?php

namespace App\Http\Services\Client;

use App\Helpers\Helper;
use App\Models\ApiDataReceive;
use App\Models\ApiLog;
use App\Models\Beneficiary;
use Illuminate\Http\Request;

class BeneficiaryService
{

    public function updateNominee($request, $beneficiary, $columns)
    {
        if ($request->nominee_en && in_array('nominee_en', $columns))
            $beneficiary->nominee_en = $request->nominee_en;

        if ($request->nominee_bn && in_array('nominee_bn', $columns))
            $beneficiary->nominee_bn = $request->nominee_bn;

        if ($request->nominee_verification_number && in_array('nominee_verification_number', $columns))
            $beneficiary->nominee_verification_number = $request->nominee_verification_number;

        if ($request->nominee_address && in_array('nominee_address', $columns))
            $beneficiary->nominee_address = $request->nominee_address;

        if ($request->nominee_image && in_array('nominee_image', $columns))
            $beneficiary->nominee_image = $request->file('nominee_image')->store('public');

        if ($request->nominee_signature && in_array('nominee_signature', $columns))
            $beneficiary->nominee_signature = $request->file('nominee_signature')->store('public');

        if ($request->nominee_relation_with_beneficiary && in_array('nominee_relation_with_beneficiary', $columns))
            $beneficiary->nominee_relation_with_beneficiary = $request->nominee_relation_with_beneficiary;

        if ($request->nominee_nationality && in_array('nominee_nationality', $columns))
            $beneficiary->nominee_nationality = $request->nominee_nationality;

        $beneficiary->save();

        return $beneficiary;
    }



    public function updateAccount($request, $beneficiary, $columns)
    {
        if ($request->account_name && in_array('account_name', $columns))
            $beneficiary->account_name = $request->account_name;

        if ($request->account_owner && in_array('account_owner', $columns))
            $beneficiary->account_owner = $request->account_owner;

        if ($request->account_number && in_array('account_number', $columns))
            $beneficiary->account_number = $request->account_number;

        if ($request->account_type && in_array('account_type', $columns))
            $beneficiary->account_type = $request->account_type;

        if ($request->bank_name && in_array('bank_name', $columns))
            $beneficiary->bank_name = $request->bank_name;

        if ($request->branch_name && in_array('branch_name', $columns))
            $beneficiary->branch_name = $request->branch_name;

        $beneficiary->save();

        return $beneficiary;
    }



    public function applyLocationFilter($query, $request, $columns): mixed
    {
        $division_id = $request->query('division_id');
        $district_id = $request->query('district_id');
        $city_corp_id = $request->query('city_corp_id');
        $district_pourashava_id = $request->query('district_pourashava_id');
        $upazila_id = $request->query('upazila_id');
        $pourashava_id = $request->query('pourashava_id');
        $thana_id = $request->query('thana_id');
        $union_id = $request->query('union_id');
        $ward_id = $request->query('ward_id');


        if ($division_id && in_array('division_id', $columns))
            $query = $query->where('permanent_division_id', $division_id);
        if ($district_id && in_array('district_id', $columns))
            $query = $query->where('permanent_district_id', $district_id);
        if ($city_corp_id && in_array('city_corp_id', $columns))
            $query = $query->where('permanent_city_corp_id', $city_corp_id);
        if ($district_pourashava_id && in_array('district_pourashava_id', $columns))
            $query = $query->where('permanent_district_pourashava_id', $district_pourashava_id);
        if ($upazila_id && in_array('upazila_id', $columns))
            $query = $query->where('permanent_upazila_id', $upazila_id);
        if ($pourashava_id && in_array('pourashava_id', $columns))
            $query = $query->where('permanent_pourashava_id', $pourashava_id);
        if ($thana_id && in_array('thana_id', $columns))
            $query = $query->where('union_id', $thana_id);
        if ($union_id && in_array('application_id', $columns))
            $query = $query->where('permanent_union_id', $union_id);
        if ($ward_id && in_array('ward_id', $columns))
            $query = $query->where('permanent_ward_id', $ward_id);

        return $query;
    }






    public function getList($request, $columns)
    {
        $program_id = $request->query('program_id');

        $beneficiary_id = $request->query('beneficiary_id');
        $nominee_name = $request->query('nominee_name');
        $account_number = $request->query('account_number');
        $nid = $request->query('nid');
        $status = $request->query('status');

        $perPage = in_array('perPage', $columns) ? $request->query('perPage') : 15;
        $page = in_array('page', $columns) ? $request->query('page') : 1;

        $sortByColumn = $request->query('sortBy', 'created_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $query = Beneficiary::query();
        if ($program_id && in_array('program_id', $columns))
            $query = $query->where('program_id', $program_id);

        $query = $this->applyLocationFilter($query, $request, $columns);

        // advance search
        if ($beneficiary_id && in_array('application_id', $columns))
            $query = $query->where('application_id', $beneficiary_id);
        if ($nominee_name && in_array('nominee_name', $columns))
            $query = $query->whereRaw('UPPER(nominee_en) LIKE "%' . strtoupper($nominee_name) . '%"');
        if ($account_number && in_array('account_number', $columns))
            $query = $query->where('account_number', $account_number);
        if ($nid && in_array('nid', $columns))
            $query = $query->where('verification_number', $nid);
        if ($status && in_array('status', $columns))
            $query = $query->where('status', $status);


        return $query->with('program',
            'permanentDivision',
            'permanentDistrict',
            'permanentCityCorporation',
            'permanentDistrictPourashava',
            'permanentUpazila',
            'permanentPourashava',
            'permanentThana',
            'permanentUnion',
            'permanentWard')->orderBy("$sortByColumn", "$orderByDirection")
            ->paginate($perPage, ['*'], 'page', $page);

    }




}
