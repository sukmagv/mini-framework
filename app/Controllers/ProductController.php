<?php

namespace App\Controllers;

use Core\Database;
use Core\Request;
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

        return [
            'message' => 'All data has been retrieved',
            'data' => $products
        ];
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

        return [
            'message' => 'New data has been created',
            'data' => $response
        ];
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

        return [
            'message' => 'Selected data has been retrieved',
            'data' => $product
        ];
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
            'name' => 'required',
            'category' => 'required'
        ]);

        $response = $this->product->update($id, $product);

        return [
            'message' => 'Data has been updated',
            'data' => array_merge(['id' => $id], $response)
        ];
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

        return [
            'message' => 'Data has been deleted',
            'data' => $response
        ];
    }
}