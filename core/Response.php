<?php

namespace Core;

/**
 * Base response class for generating consistent API success and failure responses.
 */

class Response
{
    /**
     * Success response format function
     *
     * @param string|array $message
     * @param array $data
     * @param integer $code
     * @return array
     */
    public static function success(string|array $message, array $data = [], int $code = 200): array
    {
        http_response_code($code);
        
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Failed response format function
     *
     * @param string|array $message
     * @param integer $code
     * @return array
     */
    public static function failed(string|array $message, int $code = 400): array
    {
        http_response_code($code);

        return [
            'status' => 'failed',
            'message' => $message
        ];
    }
}

?>