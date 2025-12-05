<?php

namespace Core;

/**
 * Base request class for handling and retrieving HTTP request data
 */

class Request
{
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
