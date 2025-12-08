<?php

namespace App\Controllers;

use Core\Database;
use Core\Response;
use App\Models\Product;
use Core\Request;

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

        if (isset($allProducts['error'])) {
            return Response::failed($allProducts['message'], 500);
        }

        if (empty($allProducts)) {
            return Response::failed('Data not found', 404);
        }

        return Response::success('All data retrieved', $allProducts, 200);
    }

    public function store()
    {
        $data = $_POST;

        $result = Request::validated($data, [
            'name' => 'required',
            'category' => 'required'
        ]);

        if (isset($result['errors'])) {
            return Response::failed($result['errors'], 422);
        }
        
        $response = $this->products->create([
            "name" => $data["name"],
            "category" => $data["category"]
        ]);

        if (isset($response['error'])) {
            return Response::failed($response['message'], 500);
        }

        return Response::success('Data created successfully', $response, 201);
    }

    public function show($id)
    {
        $oneProduct = $this->products->findOne($id);

        if (isset($oneProduct['error'])) {
            return Response::failed($oneProduct['message'], 500);
        }

        if (empty($oneProduct)) {
            return Response::failed('Data ID not found', 404);
        }

        return Response::success('Selected data retrieved', $oneProduct, 200);
    }

    public function update($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return Response::failed('Invalid ID', 400);
        }

        $existing = $this->products->findOne($id);
        
        if (!$existing) {
            return Response::failed('Data ID not found', 404);
        }
        
        $data = Request::all();
        
        $result = Request::validated($data, [
            'name' => 'required',
            'category' => 'required'
        ]);

        if (isset($result['errors'])) {
            return Response::failed($result['errors'], 422);
        }

        $response = $this->products->update(
            $id, 
            [
                "name" => $data["name"],
                "category" => $data["category"]
            ]
        );

        if (isset($response['error'])) {
            return Response::failed($response['message'], 500);
        }

        return Response::success('Data updated successfully', $response, 200);
    }

    public function delete($id)
    {
        $response = $this->products->delete($id);

        if (isset($response['error'])) {
            return Response::failed($response['message'], 500);
        }

        if (isset($response['not_found'])) {
            return Response::failed('Data ID not found', 404);
        }

        return Response::success('Selected data deleted', $response, 200);

    }
}