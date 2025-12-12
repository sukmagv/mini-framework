<?php

namespace Core;

use Exception;

/**
 * Base model class for database operations.
 */
class Model
{
    protected \mysqli $conn;
    protected string $table;

    public function __construct(\mysqli $connection)
    {
        $this->conn = $connection;
    }

    /**
     * Retrieve all records from the table
     *
     * @return array
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->conn->query($sql);

        if ($result->num_rows > 0) {
            $data = [];

            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }

            return $data;
        }
        return [];
    }
    
    /**
     * Retrieve a single record by its ID
     *
     * @param integer $id
     * @return mixed
     */
    public function findOneOrFail(int $id): mixed
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt =  $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if (!$row) {
            throw new \Exception("ID {$id} not found");
        }
        
        return $row;
    }

    /**
     * Insert a new record into the table
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        $fields = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ($placeholders)";
        $stmt =  $this->conn->prepare($sql);

        $types = str_repeat("s", count($data));
        $values = array_values($data);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $data;
    }

    /**
     * Update an existing record by ID
     *
     * @param integer $id
     * @param array $data
     * @return array
     */
    public function update(int $id, array $data): array
    {
        $fields = implode(" = ?, ", array_keys($data)) . " = ?";
        $types = str_repeat('s', count($data)) . 'i';
        
        $sql = "UPDATE {$this->table} SET $fields WHERE id = ?";
        $stmt = $this->conn->prepare($sql);

        $values = array_values($data);
        $values[] = $id;

        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        return $this->findOneOrFail($id);
    }

    /**
     * Delete a record by ID
     *
     * @param integer $id
     * @return array
     */
    public function delete(int $id): array
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt =  $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return ['deleted' => $id];
    }

}