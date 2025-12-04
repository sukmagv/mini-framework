<?php

namespace App\Requests;

class ProductRequest
{
    public static function validated(array $data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($data['category'])) {
            $errors[] = 'Category is required';
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        return ['data' => $data];
    }
}

?>