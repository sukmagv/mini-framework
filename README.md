# PHP Mini Framework
A simple PHP native framework focusing on core structures such as Controllers, Models, Routes, Requests, and Responses.

### 🗃️Features
1. Database Connection
2. CRUD Operations – Manage data with Create, Read, Update, Delete
3. Request Validation – Automatically check input for correctness
4. Formatted Response – Consistent API responses every time

### ⚙️Tech Stack
- Language: PHP 8.2
- Database: MySQL
- Environtment: Localhost

### 📍Installation Guide
1. Clone github:  
https://github.com/sukmagv/mini-framework.git

2. Setting .env
    - copy env.example
    - setup .env  
    ```
    DB_HOST=localhost
    DB_USER=root
    DB_PASS=
    DB_NAME=mini_framework
    ```

3. Install composer  
`composer install`

4. Run PHP built-in server di terminal  
`php -S localhost:8000 -t app`

### ➕Additional Command
Migrate new table  
`php database/migrate.php`

### 📚Folder Structure
```
app/  
|___Controller/  
|___Models/  
|___index.php  
|  
core/  
|___Database.php  
|___Logger.php  
|___Migrator.php  
|___Model.php   
|___Request.php  
|___Response.php  
|___Router.php  
|  
database/  
|___migrations/  
|___migrate.php    
|  
logs/  
|  
postman/  
|  
routes/  
|___web.php  
|  
.env.example  
gitignore  
composer.json  
composer.lock  
README.md
```

### 💻Basic Usage
This section explains the basic workflow when using the mini PHP framework.
1. Define a route  
    All routes are registered inside routes/web.php
    
    ```php
    $router->add('GET', '/product', [ProductController::class, 'index']);
    $router->add('GET', '/product/:id', [ProductController::class, 'show']);
    ```
2. Create a controller  
    Controllers handle the incoming request and return the response.
    ```php
    public function index(): array
    {
        $products = $this->product->findAll();

        return [
            'message' => 'All data has been retrieved',
            'data' => $products
        ];
    }
    ```

3. Create a model  
    Models interact with the database.
    ```php
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
    ```

4. Validated request data  
    The framework provides a simple built-in validation system.  
    ```php
    $product = $request->validated([
        'name' => 'required',
        'category' => 'required'
    ]);
    ```
    If the validation fails, the framework returns a JSON error response with HTTP 400 status.

5. Return a JSON response  
    Use the Response helper for consistent API responses
    ```php
    return Response::success($response['message'], $response['data'], HttpStatus::OK);
    ```

6. API test  
    After setting up the project and defining your routes, try calling them using Postman or any REST client. Use your local endpoint (e.g., http://localhost:8000/product) to send GET, POST, PUT, or DELETE requests.  
    Response example:
    ```json
    {
        "status": "success",
        "message": "Selected data retrieved",
        "data": {
            "id": 1,
            "name": "Luxury Disease",
            "category": "Full Album"
        }
    }
    ```