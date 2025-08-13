<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
     /**
     * @OA\Info(
     *      version="1.0.0",
     *      title="CTM Application api Documentation",
     *      description="L5 Swagger OpenApi description of CTM api",
     *      @OA\Contact(
     *          email="tarikulislamnahid15@gmail.com"
     *      ),
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )
     *
     * @OA\Server(
     *      url=L5_SWAGGER_CONST_HOST,
     *      description="CTM API Host"
     * )
     *
     *
     * @OAS\SecurityScheme(
     *      securityScheme="bearer_token",
     *      type="http",
     *      scheme="bearer"
     * )
     */

     public function sendResponse($result = [], $message ='', $code = Response::HTTP_OK)
     {
         $response = [
             'data'      => $result,
             'success'   => true,
             'message'   => $message,
         ];
         if (empty($result)) {
             unset($response['data']);
         }
         // return response()->json($response, 200);
         return new JsonResponse($response, $code);
     }

     public function sendCollectionResponse($collection, $message, $code = Response::HTTP_OK)
     {
         $response = [
             'data'      => $collection,
             'success'   => true,
             'message'   => $message,
         ];

         return new JsonResponse($response, $code);
     }

     public function sendError($error, $errorMessages = [], $code = Response::HTTP_INTERNAL_SERVER_ERROR)
     {
         $response = [
             'success' => false,
             'message' => $error,
         ];
         if (!empty($errorMessages)) {
             $response['errors'] = $errorMessages;
         }
         if (empty($error)) {
             unset($response['message']);
         }

         return new JsonResponse($response, $code);
         //return response()->json($response, $code);
     }
}
