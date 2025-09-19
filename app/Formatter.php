<?php

namespace App;

class Formatter
{
    /**
     * Format standar respons API
     *
     * @param int $code
     * @param string $message
     * @param mixed $data
     * @param mixed $error
     * @return \Illuminate\Http\JsonResponse
     */
    public static function apiResponse(int $code = 200, string $message = "No message", $data = null, $error = null)
    {
        $success = is_numeric($code) && $code >= 200 && $code < 300;

        return response()->json([
            'success' => $success,
            'message' => $message,
            'content' => $data,
            'error' => $error,
        ], $code);
    }
}
