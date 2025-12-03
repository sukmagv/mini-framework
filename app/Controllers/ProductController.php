<?php

namespace App\Controllers;

use App\Models\Product;
use App\Requests\ProductRequest;

class ProductController
{
    public function index()
    {
        $categories = (new Product)->findAll();

        return json_encode([
            'message' => 'all products retrieved',
            'data' => $categories
        ]);
    }

    public function store()
    {
        $data = $_POST;

        $category = new Product();
        $response = $category->create($data);

        return json_encode([
            'message' => 'all products stored',
            'data' => $response
        ]);
    }

    public function show($id)
    {
        $category = (new Product)->findOne($id);

        return json_encode([
            'message' => 'selected products retrieved',
            'data' => $category
        ]);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $category = new Product();
        $response = $category->update($id, $data);

        return json_encode([
            'message' => 'selected products updated',
            'data' => $response
        ]);
    }

    public function delete($id)
    {
        $category = new Product();
        $category->delete($id);

        return json_encode([
            'message' => 'success'
        ]); 
    }
}