<?php

namespace Core;

use Enums\HttpStatus;

/**
 * Base response class for generating consistent API success and failure responses.
 */
class Response
{
    /**
     * Undocumented function
     *
     * @param string|array $message
     * @param array $data
     * @param enum $code
     * @return array
     */
    public static function success(string|array $message, array $data = [], HttpStatus $code = HttpStatus::OK): array
    {
        http_response_code($code->value);
        
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];

        return $response;
    }

    /**
     * Failed response format function
     *
     * @param string|array $message
     * @param enum $code
     * @return array
     */
    public static function failed(string|array $message, HttpStatus $code = HttpStatus::BAD_REQUEST): array
    {
        http_response_code($code->value);

        $response = [
            'status' => 'failed',
            'message' => $message
        ];

        return $response;
    }
}

?>