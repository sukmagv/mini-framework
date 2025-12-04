<?php

namespace Core;

class Model
{
    protected $conn;
    protected $table;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    public function findAll() 
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

    public function findOne($id) 
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = ?";
            $stmt =  $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();

                return $result->fetch_assoc();
            }

            return [];

        } catch (\mysqli_sql_exception $e) {
            return [
                'error' => $e,
                'message' => $e->getMessage()
            ];
        }
    }

    public function create($data) 
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

    public function update($id, $data) 
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

    public function delete($id) 
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