<?php
namespace App\Http\Controllers\Api\V1\PushNotification;
use App\Http\Controllers\Controller;
use App\Http\Services\Notification\PushNotificationService;
use App\Http\Traits\MessageTrait;
use Illuminate\Http\Request;

class PushNotificationDeviceController extends Controller
{

    use MessageTrait;
    private $PushNotificationService;

    // public function __construct(PushNotificationService $PushNotificationService) {
    //     $this->PushNotificationService = $PushNotificationService;

    // }

    /**
     * @OA\Post(
     * path="/notifications-system/save-token",
     *   tags={"NOTIFICATIONS"},
     *   summary="save fvm key ",
     *   operationId="SaveFcmToken",
     *   security={{"bearer_token":{}}},
     *  @OA\RequestBody(
     *    required=true,
     *    description="save token",
     *    @OA\JsonContent(
     *       required={"token"},
     *       @OA\Property(property="token", type="string", example="FCM token"),
     *    ),
     * ),
     *   @OA\Response(
     *      response=201,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    public function SaveFcmToken(Request $request){
         $user = auth()->user();
        try {

            $this->PushNotificationService->SaveFcmToken($request,$user);

            return $this->sendResponse([], 'Token saved successfully.');

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
}