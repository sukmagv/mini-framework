<?php

namespace Core;

/**
 * Base request class for handling and retrieving HTTP request data
 */

class Request
{
    /**
     * Get all input from JSON request
     *
     * @return array
     */
    public static function all(): array
    {
        $data = $_POST;

        if (empty($data)) {
            $json = file_get_contents('php://input');
            
            if ($json) {
                $data = json_decode($json, true) ?? [];
            }
        }

        return $data;
    }
    
    /**
     * Validate input data function
     *
     * @param array $data
     * @param array $rules
     * @return array
     */
    public static function validated(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            if ($rule === 'required' && empty($data[$field])) {
                $errors[] = "$field is required";
            }
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return ['data' => $data];
    }
}
