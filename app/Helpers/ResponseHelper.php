<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class ResponseHelper
{
    public static function successResponse($message, $data)
    {
        return self::generateResponse(200, $message, $data);
    }

    public static function errorResponse($code, $message, $data = null, $error = '')
    {
        return self::generateResponse($code, $message, $data, $error);
    }

    public static function serverErrorResponse($error)
    {
        return self::generateResponse(500, 'Terjadi kesalahan pada server', null, $error->getMessage());
    }

    private static function generateResponse($code, $message, $data = null, $error = '')
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'error' => $error
        ];
    }
}
