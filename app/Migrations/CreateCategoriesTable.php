<?php

namespace App\Migrations;

use mysqli;

class CreateCategoriesTable
{
    /**
     * Query to create table
     *
     * @param \mysqli $db
     * @return void
     */
    public function up(\mysqli $db): void
    {
        $db->query("CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    /**
     * Query to delete table form database
     *
     * @param \mysqli $db
     * @return void
     */
    public function down(\mysqli $db): void
    {
        $db->query("DROP TABLE IF EXISTS categories");
    }
}
