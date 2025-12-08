<?php

namespace App\Models;

use Core\Model;

class Product extends Model 
{
    protected $table = 'products';

    public function __construct($connection)
    {
        return parent::__construct($connection);
    }
}