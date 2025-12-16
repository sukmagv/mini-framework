<?php

namespace App\Controllers;

use Core\Request;
use Core\Database;
use Core\Response;
use Enums\HttpStatus;
use App\Models\Product;

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

        return Response::success('All data retrieved', $products);
    }

    /**
     * Store a new product in the database
     *
     * @return array
     */
    public function store(Request $request): array
    {
        $product = $request->validated([
            'name' => 'required|string',
            'category' => 'required|string'
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

        return Response::success('Selected data retrieved', $product);
    }

    /**
     * Update a product data by ID
     *
     * @param integer $id
     * @return array
     */
    public function update(int $id, Request $request): array
    {
        $this->product->findOneOrFail($id);

        $product = $request->validated([
            'name' => 'required|string',
            'category' => 'required|string'
        ]);

        $response = $this->product->update($id, $product);

        $result = array_merge(['id' => $id], $response);
        
        return Response::success('Data has been updated', $result);
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

        return Response::success('All data retrieved', $response);
    }
}