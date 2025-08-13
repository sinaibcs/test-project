<?php

namespace App\Http\Services\Global;

use App\Models\AllowanceProgram;
use Illuminate\Support\Facades\DB;

class GlobalService
{

    public function getPrograms()
    {
        return AllowanceProgram::with('lookup')->get();
    }

    public function getdropdownList($request)
    {
        $tableName = $request->input('table_name');
        $fields = $request->input('field_name', ['*']);
        $condition = $request->input('condition', []);

        $query = DB::table($tableName)->select($fields);

        if (!empty($condition)) {
            foreach ($condition as $field => $value) {
                if ($value === null) {
                    $query->whereNull($field);
                } else {
                    $query->where($field, $value);
                }
            }
//            $query->whereNull('deleted_at');
        }
        return $query->get();
    }
}
