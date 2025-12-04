<?php

namespace App\Controllers;

use Core\Database;
use App\Models\Product;
use App\Requests\ProductRequest;

class ProductController
{
    protected $db;
    protected $products;

    public function __construct()
    {
        $this->db = Database::getInstance()->connection();

        $this->products = new Product($this->db);
    }

    public function index()
    {
        $allProducts = $this->products->findAll();

        return json_encode([
            'message' => 'all products retrieved',
            'data' => $allProducts
        ]);
    }

    public function store()
    {
        $data = $_POST;

        $result = ProductRequest::validated($data);

        if (isset($result['errors'])) {
            return json_encode([
                'message' => 'Validation error',
                'errors' => $result['errors']
            ]);
        }
        
        $response = $this->products->create($data);

        return json_encode([
            'message' => 'all products stored',
            'data' => $response
        ]);
    }

    public function show($id)
    {
        $oneProduct = $this->products->findOne($id);

        return json_encode([
            'message' => 'selected products retrieved',
            'data' => $oneProduct
        ]);
    }

    public function update($id)
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $result = ProductRequest::validated($data);

        if (isset($result['errors'])) {
            return json_encode([
                'message' => 'Validation error',
                'errors' => $result['errors']
            ]);
        }

        $response = $this->products->update($id, $data);

        return json_encode([
            'message' => 'selected products updated',
            'data' => $response
        ]);
    }

    public function delete($id)
    {
        $response = $this->products->delete($id);

        return json_encode([
            'message' => 'success',
        ]); 
    }
}