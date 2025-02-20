<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class HandleException extends Exception
{
    protected $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage()
        ], $this->code);
    }
}
