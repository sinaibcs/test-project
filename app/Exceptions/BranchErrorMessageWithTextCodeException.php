<?php

namespace App\Exceptions;

use App\Http\Traits\MessageTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BranchErrorMessageWithTextCodeException extends Exception
{
    use MessageTrait;

    private $responseCode, $errorCode, $errorMessage;

    public function __construct(
        $responseCode,
        $errorCode,
        $errorMessage
    ) {
        $this->responseCode = $responseCode;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        Log::error('code :' . $this->errorCode);
    }


    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {

        $data = [
            'success'                       => false,
            'error_code'                    => $this->errorCode,
            'message'                       => $this->errorMessage,

        ];
        return new JsonResponse($data, $this->responseCode);
    }
}
