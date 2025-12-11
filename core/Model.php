<?php

namespace Core;

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
        try {
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

        } catch (\mysqli_sql_exception $e) {
            return [
                'error' => $e,
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Retrieve a single record by its ID
     *
     * @param integer $id
     * @return array
     */
    public function findOne(int $id): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            $stmt =  $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();

                return $row ?? [];
            }

            return [];

        } catch (\mysqli_sql_exception $e) {
            return [
                'error' => $e,
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * Insert a new record into the table
     *
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            $fields = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), '?'));

            $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ($placeholders)";
            $stmt =  $this->conn->prepare($sql);

            $types = str_repeat("s", count($data));
            $values = array_values($data);
            $stmt->bind_param($types, ...$values);

            if ($stmt->execute()) {
                return $this->findOne($this->conn->insert_id);
            }

            return [];

        } catch (\mysqli_sql_exception $e) {
            return [
                'error' => $e,
                'message' => $e->getMessage()
            ];
        }
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
        try {
            $fields = implode(" = ?, ", array_keys($data)) . " = ?";
            $types = str_repeat('s', count($data)) . 'i';
            
            $sql = "UPDATE {$this->table} SET $fields WHERE id = ?";
            $stmt = $this->conn->prepare($sql);

            $values = array_values($data);
            $values[] = $id;

            $stmt->bind_param($types, ...$values);

            if ($stmt->execute()) {
                return $this->findOne($id);
            }

            return [];
            
        } catch (\mysqli_sql_exception $e) {
            return [
                'error' => $e,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a record by ID
     *
     * @param integer $id
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            $check = $this->findOne($id);

            if (!$check) {
                return ['not_found' => true];
            }

            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt =  $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            return ['deleted' => $id];

        } catch (\mysqli_sql_exception $e) {
            return [
                'error' => $e,
                'message' => $e->getMessage()
            ];
        }
    }

}