<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CommitteePermission\StoreRequest;
use App\Http\Services\Admin\CommitteePermission\CommitteePermissionService;
use App\Http\Traits\MessageTrait;

class CommitteePermissionController extends Controller
{
    use MessageTrait;

    public function __construct(public CommitteePermissionService $permissionService)
    {
    }

    /**
     * @OA\Get(
     *      path="/admin/committee-permissions",
     *      operationId="getCommitteePermissions",
     *      tags={"COMMITTEE-PERMISSION-MANAGEMENT"},
     *      summary="get committees with permissions list",
     *      description="Returns permissions of each committee",
     *      security={{"bearer_token":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
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
     *          response=404,
     *          description="Not Found!"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *     )
     */
    public function index()
    {
        return $this->sendResponse(
            $this->permissionService->getCommitteePermissions(),
            $this->fetchSuccessMessage
        );
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/committee-permissions",
     *      operationId="storeCommitteePermission",
     *      tags={"COMMITTEE-PERMISSION-MANAGEMENT"},
     *      summary="store permission of committee",
     *      description="store permission of committee",
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
     *                      property="committee_type_id",
     *                      description="id from lookup table by committee_type",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                       property="approve",
     *                       description="permission",
     *                       type="integer",
     *
     *                    ),
     *                   @OA\Property(
     *                       property="forward",
     *                       description="permission",
     *                       type="integer",
     *
     *                    ),
     *                        @OA\Property(
     *                        property="reject",
     *                        description="permission",
     *                        type="integer",
     *
     *                     ),
     *                        @OA\Property(
     *                        property="waiting",
     *                        description="permission",
     *                        type="integer",
     *
     *                     ),
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
    public function store(StoreRequest $request)
    {
        return $this->sendResponse(
            $this->permissionService->saveCommitteePermission($request),
            $this->insertSuccessMessage
        );

    }



    public function update(StoreRequest $request, $id)
    {
        return $this->sendResponse(
            $this->permissionService->saveCommitteePermission($request),
            $this->updateSuccessMessage
        );
    }

    /**
     * @OA\Delete(
     *     path="/admin/committee-permissions/{id}",
     *       operationId="deleteCommitteePermission",
     *       tags={"COMMITTEE-PERMISSION-MANAGEMENT"},
     *       summary="delete permissions of committee",
     *       description="delete permissions of committee",
     *       security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the committee type",
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource deleted successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function destroy($id)
    {
        if ($this->permissionService->deleteByCommitteeType($id)) {
            return $this->sendResponse([], $this->deleteSuccessMessage);
        }

        return $this->sendError([], 'No permissions found', 404);
    }
}
