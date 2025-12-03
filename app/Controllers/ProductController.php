<?php

namespace App\Controllers;

use App\Models\Product;

class ProductController
{
    public function index()
    {
        $products = (new Product)->findAll();

        return json_encode([
            'message' => 'all products retrieved',
            'data' => $products
        ]);
    }

    public function store()
    {
        $data = $_POST;

        $products = new Product();
        $response = $products->create($data);

        return json_encode([
            'message' => 'all products stored',
            'data' => $response
        ]);
    }

    public function show($id)
    {
        $products = (new Product)->findOne($id);

        return json_encode([
            'message' => 'selected products retrieved',
            'data' => $products
        ]);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $products = new Product();
        $response = $products->update($id, $data);

        return json_encode([
            'message' => 'selected products updated',
            'data' => $response
        ]);
    }

    public function delete($id)
    {
        $product = new Product();
        $product->delete($id);

        return json_encode([
            'message' => 'success'
        ]); 
    }
}