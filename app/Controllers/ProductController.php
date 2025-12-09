<?php

namespace App\Controllers;

use Core\Database;
use Core\Request;
use Core\Response;
use App\Models\Product;
use App\Controllers\Controller;

class ProductController extends Controller
{
    protected \mysqli $db;
    protected Product $products;

    /**
     * Initialize the controller by setting up database connection
     * 
     * Instantiating the Product model
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->connection();

        $this->products = new Product($this->db);
    }

    /**
     * Retrieve all products from the database.
     *
     * @return array
     */
    public function index(): array
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

    /**
     * Store a new product in the database
     *
     * @return array
     */
    public function store(): array
    {
        $data = Request::all();

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

    /**
     * Get specific product data by ID
     *
     * @param integer $id
     * @return array
     */
    public function show(int $id): array
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

    /**
     * Update a product data by ID
     *
     * @param integer $id
     * @return array
     */
    public function update(int $id): array
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

    /**
     * Delete product data by ID
     *
     * @param integer $id
     * @return array
     */
    public function delete(int $id): array
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