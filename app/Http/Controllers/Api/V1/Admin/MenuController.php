<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Menu;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\UserTrait;
use App\Http\Traits\MessageTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Admin\Menu\MenuRequest;
use App\Http\Services\Admin\Menu\MenuService;
use App\Http\Resources\Admin\Menu\MenuResource;
use App\Http\Resources\Admin\PermissionResource;
use App\Http\Requests\Admin\Menu\MenuUpdateRequest;

/**
 *
 */
class MenuController extends Controller
{
    use MessageTrait, UserTrait;

    private $MenuService;

    public function __construct(MenuService $MenuService)
    {
        $this->MenuService = $MenuService;
    }

    /**
     * @OA\Get(
     *     path="/admin/menu/get",
     *      operationId="getAllMenu",
     *      tags={"MENU-MANAGEMENT"},
     *      summary="get all menus",
     *      description="get all menus",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         description="search by name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="number of division per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page number",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */

    public function getAllMenu(Request $request)
    {

        $menu = Menu::select(
            'menus.*',
            'permissions.page_url as link'
            )
            ->leftJoin('permissions', function ($join) {
                $join->on('menus.page_link_id', '=', 'permissions.id');
            });

        if ($request->has('sortBy') && $request->has('sortDesc')) {
            $sortBy = $request->query('sortBy');

            $sortDesc = $request->query('sortDesc') == true ? 'desc' : 'asc';

            if ($sortBy === 'link') {
                $menu = $menu->orderBy('permissions.page_url', $sortDesc);
            } else {
                $menu = $menu->orderBy($sortBy, $sortDesc);
            }
        } else {
            $menu = $menu->orderBy('order', 'asc');
        }

        $searchValue = $request->input('search');

        if ($searchValue) {
            $menu->where(function ($query) use ($searchValue) {
                $query->where('label_name_en', 'like', '%' . $searchValue . '%')
                    ->orWhere('label_name_bn', 'like', '%' . $searchValue . '%')
                    ->orWhere('permissions.page_url', 'like', '%' . $searchValue . '%');
            });

            $itemsPerPage = 10;

            if($request->has('itemsPerPage')) {
                $itemsPerPage = $request->get('itemsPerPage');

                return $menu->paginate($itemsPerPage, ['*'], $request->get('page'));
            }
        }else{
            $itemsPerPage = 10;

            if($request->has('itemsPerPage')) {
                $itemsPerPage = $request->get('itemsPerPage');

                return $menu->paginate($itemsPerPage);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/menu/get-all",
     *      operationId="getMenus",
     *     tags={"MENU-MANAGEMENT"},
     *      summary="get all menus",
     *      description="get all menus",
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */

     private function getAccessibleMenuTree()
     {
         $user = Auth::user();
     
         // Check if the user is a super admin
         if ($user->hasRole('super-admin')) {
             // Fetch all menus for super admin
             $menus = Menu::with('pageLink')->get();
         } else {
             // Get user's permissions
             $permissions = $user->getAllPermissions()->pluck('id')->toArray();
     
             // Fetch menus accessible by permissions
             $accessibleMenus = Menu::whereIn('page_link_id', $permissions)->orWhere('link_type',2)->get();
     
             // Include all parents of the accessible menus
             $allMenus = collect();
     
             foreach ($accessibleMenus as $menu) {
                 $current = $menu;
     
                 // Traverse up to add all parent menus
                 while ($current) {
                     $allMenus->push($current);
                     $current = $current->parent;
                 }
             }
     
             // Remove duplicates
             $menus = $allMenus->unique('id');
         }
     
         // Ensure menus are sorted by hierarchy and IDs
         $menus = $menus->sortBy('order');
     
         // Generate menu tree
         return $this->buildMenuTree($menus);
     }
     
     private function buildMenuTree($menus, $parentId = null)
     {
         return $menus
             ->where('parent_id', $parentId)
             ->map(function ($menu) use ($menus) {
                 return [
                    "id" => $menu->id,
                    "label_name_en" => $menu->label_name_en,
                    "label_name_bn" => $menu->label_name_bn,
                    "order" => $menu->order,
                    "icon" => $menu->icon,
                    "page_link" => $menu->pageLink? PermissionResource::make($menu->pageLink) : null,
                    "link_type" => $menu->link_type,
                    "link" => $menu->link,
                    "created_at" => $menu->created_at,
                    "updated_at" => $menu->updated_at,
                    'permission' => $this->permissionName($menu),
                    'children' => $this->buildMenuTree($menus, $menu->id), // Recursive call
                 ];
             })
             ->values(); // Reset array keys for consistency
     }

     private function permissionName($menu)
    {
        // Ensure children are loaded
        if ($menu->relationLoaded('children')) {
            if ($menu->children->count() > 0) {
                foreach ($menu->children as $child) {
                    if ($child->relationLoaded('children') && $child->children->count() > 0) {
                        foreach ($child->children as $child2) {
                            if ($child2->pageLink) {
                                return $child2->pageLink->module_name;
                            }
                            return $child->pageLink->sub_module_name;
                        }
                    } else if ($child->pageLink) {
                        return $child->pageLink->module_name;
                    }
                }
            }
        }

        return null;
    }

    public function getMenus(Request $request){

        $menues = $this->getAccessibleMenuTree();
        return [
            'data' => $menues,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ];

        // $menus = Menu::with("children.children.pageLink","children.pageLink","pageLink")->whereParentId(null)->orderBy('order', 'asc')->get();

        // return MenuResource::collection($menus)->additional([
        //     'success' => true,
        //     'message' => $this->fetchSuccessMessage,
        // ]);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/menu/insert",
     *      operationId="insertMenu",
     *      tags={"MENU-MANAGEMENT"},
     *      summary="insert a menu",
     *      description="insert a menu",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="label_name_en",
     *                      description="english name of the menu",
     *                      type="text",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="label_name_bn",
     *                      description="bangla name of the menu",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="order",
     *                      description="sl of the menu",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="page_link_id",
     *                      description="page link id",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="parent_id",
     *                      description="parent menu id",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="link_type",
     *                      description="page link type. ex:1->external, 2->internal",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="link",
     *                      description="page link if link type is external",
     *                      type="text",
     *                   ),
     *
     *                 ),
     *             ),
     *
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */
    public function insertMenu(MenuRequest $request)
    {

        try {
            $menu = $this->MenuService->createMenu($request);
            Helper::activityLogInsert($menu,'','Menu','Menu Created !');
//            activity("Menu")
//                ->causedBy(auth()->user())
//                ->performedOn($menu)
//                ->log('Menu Created !');
            return MenuResource::make($menu)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/menu/get_page_url",
     *      operationId="getPageUrl",
     *      tags={"MENU-MANAGEMENT"},
     *      summary="get paginated role",
     *      description="get paginated role",
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */

    public function getPageUrl()
    {
        $page_urls = Permission::select('id', 'page_url','name')->get();

        return response()->json([
            'page_urls' => $page_urls
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/admin/menu/get_parent",
     *      operationId="getParent",
     *      tags={"MENU-MANAGEMENT"},
     *      summary="get paginated role",
     *      description="get paginated role",
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */
    public function getParent()
    {
        $parents = Menu::select('id', 'parent_id', 'label_name_en', 'label_name_bn','page_link_id')->orderBy('order','asc')->get();
        $parents = $this->getMenuList($parents);

        return \response()->json([
            'parents' => $parents
        ], Response::HTTP_OK);
    }

    public function getMenuList($parents, $parent_id = null)
    {
        $menuList = [];
        foreach ($parents as $parent) {
            if ($parent->parent_id == $parent_id && $parent->page_link_id == null) {
                $menuList[] = $parent;
                $menuList = array_merge($menuList, $this->getMenuList($parents, $parent->id));
            }
        }
        return $menuList;
    }

    public function menuEdit($id)
    {
        $menu = Menu::find($id);

        return \response()->json([
            'menu' => $menu
        ],Response::HTTP_OK);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/menu/update/{id}",
     *      operationId="updateMenu",
     *      tags={"MENU-MANAGEMENT"},
     *      summary="update a menu",
     *      description="update a menu",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="label_name_en",
     *                      description="english name of the menu",
     *                      type="text",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="label_name_bn",
     *                      description="bangla name of the menu",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="order",
     *                      description="sl of the menu",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="page_link_id",
     *                      description="page link id",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="parent_id",
     *                      description="parent menu id",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="link_type",
     *                      description="page link type. ex:1->external, 2->internal",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="link",
     *                      description="page link if link type is external",
     *                      type="text",
     *                   ),
     *
     *                 ),
     *             ),
     *
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */

    public function updateMenu(MenuUpdateRequest $request, $id)
    {
        if ($request->_method == 'PUT')
        {
            \DB::beginTransaction();

            try {

                $BeforeUpdate = Menu::find($id);

                $menu = Menu::findOrFail($id);

                $menu->label_name_en = $request->label_name_en;
                $menu->label_name_bn = $request->label_name_bn;
                $menu->icon = $request->icon;
                $menu->order = $request->order;

                $menu->link_type              = $request->link_type;

                if ($request->link_type == 2) {
                    $menu->page_link_id = null;
                    $menu->link = $request->link;
                } else {
                    $menu->link = null;
                    $menu->page_link_id = $request->page_link_id;
                }

                if ($request->parent_id == null)
                {
                    if ($menu->save()) {
                        $menu->parent_id = null;
                        $menu->save();
                    }
                }else{
                    if ($menu->save()) {
                        $menu->parent_id = $request->parent_id;
                        $menu->save();
                    }
                }
                Helper::activityLogUpdate($menu,$BeforeUpdate,"Menu","Menu Updated !");
//                 activity("Menu")
//                 ->causedBy(auth()->user())
//                 ->performedOn($menu)
//                 ->log('Menu Updated !');

                DB::commit();

                return \response()->json([
                    'message' => 'Menu updated successful'
                ],Response::HTTP_OK);

            }catch (\Exception $e){
                \DB::rollBack();

                $error = $e->getMessage();

                return \response()->json([
                    'error' => $error
                ],Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/menu/destroy/{id}",
     *      operationId="destroyMenu",
     *      tags={"MENU-MANAGEMENT"},
     *      summary="destroy menu",
     *      description="destroy menu from menu lists",
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */

    public function destroyMenu($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();
        Helper::activityLogDelete($menu,'','Menu','Menu Deleted!!');
        return \response()->json([
            'message' => 'Menu destroy successful'
        ],Response::HTTP_OK);
    }
}
