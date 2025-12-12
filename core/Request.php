<?php

namespace Core;

/**
 * Base request class for handling and retrieving HTTP request data
 */

class Request
{
    private array $data;

    public function __construct()
    {
        $this->data = $this->parse();
    }
    
    /**
     * Get all input from JSON request
     *
     * @return array
     */
    private function parse(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $data = [];

        if ($method === 'POST') {
            if (!empty($_POST)) {
                $data = $_POST;
            }

            $json = file_get_contents('php://input');
            if ($json) {
                $decoded = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = $decoded;
                }
            }

            return $data;
        }

        if (in_array($method, ['PUT'])) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            $raw = file_get_contents('php://input');

            if (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
                parse_str($raw, $data);
                return $data ?? [];
            }

            if (stripos($contentType, 'multipart/form-data') !== false) {
                return self::parseMultipart($raw);
            }

            if (stripos($contentType, 'application/json') !== false) {
                $json = json_decode($raw, true);
                return $json ?? [];
            }
        }

        return $data;
    }

    /**
     * Parse raw multipart/form-data input into an associative array.
     * 
     * Extracts field names and values from PUT or POST requests with multipart/form-data
     *
     * @param string $raw
     * @return array
     */
    private function parseMultipart(string $raw): array
    {
        $data = [];

        if (preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches)) {
            $boundary = $matches[1];
            $blocks = explode("--$boundary", $raw);

            foreach ($blocks as $block) {
                if (preg_match('/name="([^"]+)"/', $block, $m)) {
                    $name = $m[1];
                    $value = trim(substr($block, strpos($block, "\r\n\r\n") + 4));
                    $value = rtrim($value, "\r\n");
                    $data[$name] = $value;
                }
            }
        }

        return $data;
    }
    
    /**
     * Validate input data function
     *
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function validated(array $rules): array
    {
        if ($rules !== null) {
            foreach ($rules as $field => $rule) {
                if ($rule === 'required' && empty($this->data[$field])) {
                    throw new \Exception("{$field} is required");
                }
            }
        }
        
        return $this->data;
    }
}
