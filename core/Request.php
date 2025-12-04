<?php

namespace Core;

class Request
{
    public static function validated(array $data, array $rules)
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
