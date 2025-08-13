<?php

namespace App\Http\Services\Mobile\Menu;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuService
{
    public function createMenu(Request $request){

        DB::beginTransaction();
        try {
            $menu                       = New Menu;
            $menu->label_name_en          = $request->label_name_en;
            $menu->label_name_bn          = $request->label_name_bn;
            $menu->page_link_id           = $request->page_link_id;
            $menu->link_type              = $request->link_type;
            $menu->link                   = $request->link;


            if (!key_exists('parent_id', $request->all()))
            {

                    $menu->parent_id = null;
                    $menu->save();
            }else{
                    $menu->parent_id = $request->parent_id;
                    $menu->save();
            }



            DB::commit();
            return $menu;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
