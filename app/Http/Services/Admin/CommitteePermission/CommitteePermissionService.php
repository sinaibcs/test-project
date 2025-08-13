<?php

namespace App\Http\Services\Admin\CommitteePermission;

use App\Models\CommitteePermission;
use App\Models\Lookup;

class CommitteePermissionService
{
    public function getCommitteePermissions()
    {
        return Lookup::whereType(17)
            ->when(request('searchText'), function ($q, $v) {
                $q->where('value_en', 'like', "%$v%");
            })
            ->with('committeePermission')
            ->select('id', 'type', 'value_en', 'value_bn')
            ->paginate(request('perPage', 15));
    }


    public function saveCommitteePermission($request)
    {
        $permission = new CommitteePermission();
        $permission->committee_type_id = $request->committee_type_id;
        $permission->approve = (bool)$request->approve;
        $permission->recommendation = (bool)$request->recommendation;
        $permission->forward = (bool)$request->forward;
        $permission->reject = (bool)$request->reject;
        $permission->waiting = (bool)$request->waiting;
        $permission->created_by = auth()->id();
        $permission->save();

        return $permission;
    }


    public function deleteByCommitteeType($committeeTypeId)
    {
        return CommitteePermission::where('committee_type_id', $committeeTypeId)->delete();
    }










}