<?php

namespace Core;

class Response
{
    public static function success($message, $data, $code)
    {
        http_response_code($code);
        return json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => (! empty($data) ? $data : [])
        ]);
    }

    public static function failed($message, $code)
    {
        http_response_code($code);
        return json_encode([
            'status' => 'failed',
            'message' => $message
        ]);
    }
}

?>