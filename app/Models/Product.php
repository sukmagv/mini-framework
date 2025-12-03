<?php

namespace App\Models;

use Core\Database;

class Product extends Database 
{
    protected $table = 'products';

    public function findAll() 
    {
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->connection()->query($sql);

        if ($result->num_rows > 0) {
            $data = [];

            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            return $data;
        }

        return [];
    }

    public function findOne($id) 
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt =  $this->connection()->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();

            return $result->fetch_assoc();
        }

        return [];
    }

    public function create($data) 
    {
        $sql = "INSERT INTO products (name, category) VALUES (?, ?)";
        $stmt =  $this->connection()->prepare($sql);
        $stmt->bind_param("ss", $data['name'], $data['category']);

        if ($stmt->execute()) {
            return $this->findOne($this->connection()->insert_id);
        }

        return [];
    }

    public function update($id, $data) 
    {
        $sql = "UPDATE {$this->table} SET name = ?, category = ? WHERE id = ?";
        $stmt =  $this->connection()->prepare($sql);
        $stmt->bind_param("ssi", $data['name'], $data['category'], $id);

        if ($stmt->execute()) {
            return $this->findOne($id);
        }

        return [];
    }

    public function delete($id) 
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt =  $this->connection()->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

}