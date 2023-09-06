<?php

class Connection
{
    private const HOST = "localhost";
    private const USERNAME = "coffee";
    private const PASSWORD = "iYt-duNkwEbZ]y7!";
    private const DATABASE = "coffee";
    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {
            return self::initDatabaseAndConnection();
        }
        return self::$connection;
    }

    public static function initDatabaseAndConnection(string $host = self::HOST, string $username = self::USERNAME, string $password = self::PASSWORD, string $database = self::DATABASE, int $tries = 0): mysqli
    {
        $connection = new mysqli($host, $username, $password);
        if ($connection->connect_error) {
            if ($tries < 5) {
                return self::initDatabaseAndConnection($host, $username, $password, $database, $tries + 1);
            }
            die("Không thể tạo connection, lỗi: " . $connection->connect_error);
        }
        $result = $connection->query("CREATE DATABASE IF NOT EXISTS $database");
        if (!$result) {
            die("Đã xảy ra lỗi khi tạo database: " . $connection->error);
        }
        $connection->select_db($database);
        self::createTables();
        self::$connection = $connection;
        return $connection;
    }

    public static function query(string $query): bool
    {
        $connection = self::getConnection();
        if (!$connection->query($connection->escape_string($query))) {
            die("Query error: " . $connection->error);
        }
        return true;
    }

    public static function register(string $username, string $password): bool
    {
        $connection = self::getConnection();
        $result = $connection->execute_query("SELECT * FROM users WHERE username=?", [$username]);
        if ($result->num_rows > 0) return false;
        $stmt = $connection->prepare("INSERT INTO users(username, password) VALUES (?, ?)");
        return $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    }

    /**
     * @throws Exception
     */
    public static function login(string $username, string $password): ?string
    {
        $connection = self::getConnection();
        $result = $connection->execute_query("SELECT * FROM users WHERE username=?", [$username]);
        if ($result->num_rows == 0) return null;
        $user = $result->fetch_assoc();
        if ($user['password'] != password_hash($password, PASSWORD_DEFAULT)) return null;
        $token = self::generateToken(20);
        $result = $connection->execute_query("INSERT INTO tokens (user_id, value) VALUES (?, ?)", [$user['id'], $token]);
        if (!$result) return null;
        return $token;
    }

    public static function loadToken(string $token): ?array
    {
        $connection = self::getConnection();
        $result = $connection->execute_query("SELECT * FROM tokens WHERE value=?", [$token]);
        if ($result->num_rows == 0) return null;
        return $result->fetch_assoc();
    }

    public static function createCategory(string $name, ?string $description = ''): ?array
    {
        $connection = self::getConnection();
        if (self::getCategory($name) != null) return null;
        $result = $connection->execute_query("insert into categories (name, description) values (?, ?)", [$name, $description]);
        return self::getCategory($name);
    }

    public static function removeCategory(string $id): ?bool
    {
        $connection = self::getConnection();
        $category = self::getCategory($id);
        if ($category == null) return null;
        $connection->execute_query("remove from categories where name=?", [$id]);
        return true;
    }

    public static function editCategory(string $id, ?string $name, ?string $description): ?array
    {
        $connection = self::getConnection();
        $category = self::getCategory($id);
        if ($category == null) return null;
        $connection->execute_query("update set categories name=?, description=? where id=?", [$name ?? $category['name'], $description ?? $category['description'], $category['id']]);
        return self::getCategory($category['id']);
    }

    public static function getCategory(string $id): ?array
    {
        $connection = self::getConnection();
        $result = $connection->execute_query("select * from categories where name=?", [$id]);
        if ($result->num_rows == 0) {
            $result = $connection->execute_query("select * from categories where id=?", [$id]);
            if ($result->num_rows == 0) return null;
        }
        return $result->fetch_assoc();
    }

    public static function createItem(string $category_id, string $name, int $in_stock, float $price, string $image, ?string $description = ''): ?array
    {
        $connection = self::getConnection();
        if (self::getItem($name) != null) return null;
        $category = self::getCategory($category_id);
        if ($category == null) return null;
        $result = $connection->execute_query("insert into items (category_id, name, in_stock, price, image, description) values (?, ?, ?, ?, ?, ?)", [$category['id'], $name, $in_stock, $price, $image, $description]);
        return self::getItem($connection->insert_id);
    }

    public static function removeItem(string $id): ?bool
    {
        $connection = self::getConnection();
        $item = self::getItem($id);
        if ($item == null) return null;
        $connection->execute_query("remove from items where name=?", [$id]);
        return true;
    }

    public static function editItem(string $id, ?string $category_id, ?string $name, ?int $in_stock, ?float $price, ?string $image, ?string $description): ?array
    {
        $connection = self::getConnection();
        $item = self::getItem($id);
        if ($item == null) return null;
        $category = self::getCategory($category_id);
        if ($category == null) return null;
        $connection->execute_query("update set items category_id=?, name=?, in_stock=?, price=?, image=?, description=? where id=?", [$category_id ?? $item['category_id'], $name ?? $item['name'], $in_stock ?? $item['in_stock'], $price ?? $item['price'], $image ?? $item['image'], $description ?? $item['description'], $item['id']]);
        return self::getItem($item['id']);
    }

    public static function getItem(string $id): ?array
    {
        $connection = self::getConnection();
        $result = $connection->execute_query("select * from items where id=?", [$id]);
        if ($result->num_rows == 0) return null;
        return $result->fetch_assoc();
    }

    public static function createTable(int $table_position): ?array
    {
        $connection = self::getConnection();
        if (self::getTableByPosition($table_position) != null) return null;
        $connection->execute_query("insert into tables (table_position) values (?)", [$table_position]);
        return self::getTableById($connection->insert_id);
    }

    public static function addTableItem(int $table_position, int $item_id, int $quantity): void
    {
        $connection = self::getConnection();
        $table = self::getTableByPosition($table_position);
        if ($table == null) $table = self::createTable($table_position);
        $connection->execute_query("insert into table_details (table_id, item_id, quantity) values (?, ?, ?)", [$table['id'], $item_id, $quantity]);
    }

    public static function removeTable(int $table_position): ?bool
    {
        $connection = self::getConnection();
        if (self::getTableByPosition($table_position) != null) return null;
        $connection->execute_query("delete from tables where table_position=?", [$table_position]);
        return true;
    }

    public static function getTableById(int $id): ?array
    {
        $connection = self::getConnection();
        $result = $connection->execute_query("select * from tables where id=?", [$id]);
        if ($result->num_rows == 0) return null;
        return $result->fetch_assoc();
    }

    public static function getTableByPosition(int $position): ?array
    {
        $connection = self::getConnection();
        $result = $connection->execute_query("select * from tables where table_position=?", [$position]);
        if ($result->num_rows == 0) return null;
        return $result->fetch_assoc();
    }

    public static function calculateTablePrice(int $table_position): ?float
    {
        $connection = self::getConnection();
        $table = self::getTableByPosition($table_position);
        if($table == null) return null;
        $result = $connection->execute_query("select * from table_details where table_id=?", [$table['id']]);
        $table_details = $result->fetch_all();
        $sum = 0;
        foreach ($table_details as $table_detail){
            $item = self::getItem($table_detail['item_id']);
            $sum += $item['price'] * $table_detail['quantity'];
        }
        return $sum;
    }

    public static function makeBill(int $table_position, float $total_paid): ?int
    {
        $connection = self::getConnection();
        $table = self::getTableByPosition($table_position);
        if($table == null) return null;
        $connection->execute_query("insert into bills(total_paid, total_price) values (?, ?)", [$total_paid, self::calculateTablePrice($table_position)]);
        $bill_id = $connection->insert_id;
        $result = $connection->execute_query("select * from table_details where table_id=?", [$table['id']]);
        foreach ($result->fetch_all() as $table_detail){
            $connection->execute_query("insert into bill_details(bill_id, item_id, quantity) values (?, ?, ?)", [$bill_id, $table_detail['item_id'], $table_detail['quantity']]);
        }
        self::removeTable($table_position);
        return $bill_id;
    }

    /**
     * @throws Exception
     */
    public static function generateToken($length = 16): string
    {
        $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pieces = [];
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $stringSpace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public static function createTables(): void
    {
        self::query("CREATE TABLE IF NOT EXISTS users(
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(30) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS tokens(
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id),
            value VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS categories(
            id INT AUTO_INCREMENT PRIMARY KEY,
            name NVARCHAR(50) UNIQUE NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS items(
            id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NOT NULL,
            FOREIGN KEY (category_id) REFERENCES categories(id),
            name NVARCHAR(50) NOT NULL,
            in_stock INT NOT NULL,
            price DECIMAL(16,2) NOT NULL,
            image LONGBLOB NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS bills(
            id INT AUTO_INCREMENT PRIMARY KEY,
            total_paid DECIMAL(16,2) NOT NULL,
            total_price DECIMAL(16,2) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS bill_details(
            id INT AUTO_INCREMENT PRIMARY KEY,
            bill_id INT NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id),
            item_id INT NOT NULL,
            FOREIGN KEY (item_id) REFERENCES items(id),
            quantity INT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS tables(
            id INT AUTO_INCREMENT PRIMARY KEY,
            table_position INT NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS table_details(
            id INT AUTO_INCREMENT PRIMARY KEY,
            table_id INT NOT NULL,
            FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE,
            item_id INT NOT NULL,
            FOREIGN KEY (item_id) REFERENCES items(id),
            quantity INT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    }
}