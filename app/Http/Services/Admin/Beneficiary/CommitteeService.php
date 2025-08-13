<?php

namespace App\Http\Services\Admin\Beneficiary;


use App\Http\Requests\Admin\Beneficiary\StoreCommitteeRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateCommitteeRequest;
use App\Http\Traits\MessageTrait;
use App\Models\AllowanceProgram;
use App\Models\Committee;
use App\Models\Location;
use App\Models\Lookup;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Committee Service
 */
class CommitteeService
{
    use MessageTrait;

    /**
     * @param Request $request
     * @return Committee
     * @throws \Throwable
     */
    public function save(StoreCommitteeRequest $request): Committee
    {
        DB::beginTransaction();
        try {
            do {
                $code = mt_rand(100000, 999999);
            } while (Committee::where('code', $code)->exists());

            $name = $this->committeeName($request->committee_type, $request->program_id, $request->location_id);
            $location_id = $this->getLocation($request);
            $validatedCommitteeData = $request->safe()->merge(['code' => $code, 'name' => $name, 'location_id' => $location_id])->toArray();
//            dump($validatedCommitteeData);
            $committee = Committee::create($validatedCommitteeData);

            $validatedMemberData = $request->validated('members');
            if ($validatedMemberData)
                $committee->members()->createMany($validatedMemberData);

            DB::commit();
            return $committee;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request, $forPdf = false): \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
    {
        $location_id = $request->query('location_id');
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'created_at');
        $orderByDirection = $request->query('orderBy', 'desc');

        $query = Committee::query();
        if ($location_id)
            $query = $query->where('location_id', $location_id);
        if ($searchText) {
            $query = $query->where(function ($q) use ($searchText) {
                $q->whereRaw('LOWER(code) LIKE "%' . strtolower($searchText) . '%"')
                    ->orWhereRaw('LOWER(name) LIKE "%' . strtolower($searchText) . '%"')
                    ->orWhereRaw('LOWER(details) LIKE "%' . strtolower($searchText) . '%"');
            });
        }
        if ($forPdf)
            return $query->with('program', 'members', 'committeeType', 'officeType', 'location.parent.parent.parent', 'office')
                ->orderBy("$sortByColumn", "$orderByDirection")
                ->get();
        else
            return $query->with('program', 'members', 'committeeType', 'officeType', 'location.parent.parent.parent', 'office')
                ->orderBy("$sortByColumn", "$orderByDirection")
                ->paginate($perPage);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function detail($id)
    {
        return Committee::with(
            'program',
            'members',
            'committeeType',
            'officeType',
//            'location.parent.parent.parent',
            'division',
            'district',
            'locationType',
            'cityCorp',
            'districtPourashava',
            'upazila',
//            'subLocationType',
            'pourashava',
            'thana',
            'union',
            'ward',
            'office')->findOrFail($id);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Throwable
     */
    public function update(UpdateCommitteeRequest $request, $id): mixed
    {
        DB::beginTransaction();
        try {
            $committee = Committee::findOrFail($id);
            $name = $this->committeeName($request->committee_type, $request->program_id, $request->location_id);
            $location_id = $this->getLocation($request);
            $validatedCommitteeData = $request->safe()->merge(['name' => $name, 'location_id' => $location_id])->toArray();
//                ->only([
//                    'details',
//                    'program_id',
//                    'committee_type',
//                    'office_type',
//                    'division_id',
//                    'district_id',
//                    'location_type_id',
//                    'city_corp_id',
//                    'district_pourashava_id',
//                    'upazila_id',
//                    'sub_location_type_id',
//                    'pourashava_id',
//                    'thana_id',
//                    'union_id',
//                    'ward_id',
//                    'location_id',
//                    'office_id']);
//            dump($validatedCommitteeData);
            $committee->fill($validatedCommitteeData);
            $committee->save();

            $committee->members()->delete();

            $validatedMemberData = $request->validated('members');
            if ($validatedMemberData)
                $committee->members()->createMany($validatedMemberData);

            DB::commit();
            return $committee;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @param $id
     * @return bool
     * @throws \Throwable
     */
    public function delete($id): bool
    {
        DB::beginTransaction();
        try {
            $committee = Committee::findOrFail($id);
            $committee->members()->delete();
            $committee->delete();
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param $committee_type
     * @param $program_id
     * @param $location_id
     * @return string
     */
    private function committeeName($committee_type, $program_id, $location_id): string
    {
        $program = AllowanceProgram::find($program_id);
        $committee_type = Lookup::find($committee_type);
        if ($location_id) {
            $location = Location::find($location_id);
            $name = Str::slug($committee_type->value_en, '_') . '_' . Str::slug($location->name_en, '_') . '_' . Str::slug($program->name_en, '_');
        } else {
            $location = 'Bangladesh';
            $name = Str::slug($committee_type->value_en, '_') . '_' . Str::slug($location, '_') . '_' . Str::slug($program->name_en, '_');
        }
        return $name;

    }

    /**
     * @param Request $request
     * @return mixed|null
     */
    private function getLocation(Request $request): mixed
    {
        $location_id = null;
        if ($request->has('committee_type')) {
            if ($request->committee_type == 12 && $request->has('union_id')) {
                $location_id = $request->union_id;
            } else if ($request->committee_type == 13 && $request->has('ward_id')) {
                $location_id = $request->ward_id;
            } else if ($request->committee_type == 14 && $request->has('upazila_id')) {
                $location_id = $request->upazila_id;
            } else if ($request->committee_type == 15 && $request->has('city_corp_id')) {
                $location_id = $request->city_corp_id;
            } else if ($request->committee_type == 16 && $request->has('district_pourashava_id')) {
                $location_id = $request->district_pourashava_id;
            } else if ($request->committee_type == 17 && $request->has('district_id')) {
                $location_id = $request->district_id;
            }
        }
        return $location_id;
    }
}
