<?php

namespace App\Controllers;

use Core\Database;
use Core\Request;
use Core\Response;
use App\Models\Product;
use Core\HttpStatus;

class ProductController
{
    protected \mysqli $db;
    protected Product $product;

    /**
     * Initialize the controller by setting up database connection
     * 
     * Instantiating the Product model
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->connection();

        $this->product = new Product($this->db);
    }

    /**
     * Retrieve all products from the database.
     *
     * @return array
     */
    public function index(): array
    {
        $products = $this->product->findAll();

        return Response::success('Data retrieved', $products, HttpStatus::OK);
    }

    /**
     * Store a new product in the database
     *
     * @return array
     */
    public function store(Request $request): array
    {
        $product = $request->validated([
            'name' => 'required',
            'category' => 'required'
        ]);

        $response = $this->product->create($product);

        return Response::success('Data created successfully', $response, HttpStatus::CREATED); 
    }

    /**
     * Get specific product data by ID
     *
     * @param integer $id
     * @return array
     */
    public function show(int $id): array
    {
        $product = $this->product->findOneOrFail($id);

        return Response::success('Selected data retrieved', $product, HttpStatus::OK);
    }

    /**
     * Update a product data by ID
     *
     * @param integer $id
     * @return array
     */
    public function update(int $id, Request $request): array
    {
        $product = $this->product->findOneOrFail($id);

        $product = $request->validated([
            'name' => 'required',
            'category' => 'required'
        ]);

        $response = $this->product->update($id, $product);

        return Response::success('Data updated successfully', $response, HttpStatus::OK); 
    }

    /**
     * Delete product data by ID
     *
     * @param integer $id
     * @return array
     */
    public function delete(int $id): array
    {
        $product = $this->product->findOneOrFail($id);

        $response = $this->product->delete($product['id']);

        return Response::success('Selected data deleted', $response, HttpStatus::OK);

    }
}