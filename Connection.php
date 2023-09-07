<?php

const HOST = "localhost";
const USERNAME = "coffee";
const PASSWORD = "iYt-duNkwEbZ]y7!";
const DATABASE = "coffee";
$connection = null;

function getConnection(): mysqli
{
    global $connection;
    if ($connection === null) {
        return initDatabaseAndConnection();
    }
    return $connection;
}

function initDatabaseAndConnection(string $host = HOST, string $username = USERNAME, string $password = PASSWORD, string $database = DATABASE, int $tries = 0): mysqli
{
    global $connection;
    $connection = new mysqli($host, $username, $password);
    if ($connection->connect_error) {
        if ($tries < 5) {
            return initDatabaseAndConnection($host, $username, $password, $database, $tries + 1);
        }
        die("Không thể tạo connection, lỗi: " . $connection->connect_error);
    }
    $result = $connection->query("CREATE DATABASE IF NOT EXISTS $database");
    if (!$result) {
        die("Đã xảy ra lỗi khi tạo database: " . $connection->error);
    }
    $connection->select_db($database);
    createTables();
    return $connection;
}

function query(string $query, array $params = []): bool
{
    $connection = getConnection();
    if (!$connection->execute_query($query, $params)) {
        die("Query error: " . $connection->error);
    }
    return true;
}

function register(string $username, string $password): bool
{
    $connection = getConnection();
    $result = $connection->execute_query("SELECT * FROM users WHERE username=?", [$username]);
    if ($result->num_rows > 0) return false;
    $stmt = $connection->prepare("INSERT INTO users(username, password) VALUES (?, ?)");
    return $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
}

/**
 * @throws Exception
 */
function login(string $username, string $password): ?string
{
    $connection = getConnection();
    $result = $connection->execute_query("SELECT * FROM users WHERE username=?", [$username]);
    if ($result->num_rows == 0) return null;
    $user = $result->fetch_assoc();
    if (!password_verify($password, $user['password'])) return null;
    $token = generateToken(20);
    $result = $connection->execute_query("INSERT INTO tokens (user_id, value) VALUES (?, ?)", [$user['id'], $token]);
    if (!$result) return null;
    return $token;
}

function loadToken(string $token): ?array
{
    $connection = getConnection();
    $result = $connection->execute_query("SELECT * FROM tokens WHERE value=?", [$token]);
    if ($result->num_rows == 0) return null;
    return $result->fetch_assoc();
}

function getCategories(): array
{
    $connection = getConnection();
    return $connection->execute_query("select * from categories")->fetch_all();
}

function createCategory(string $name, ?string $description = ''): ?array
{
    $connection = getConnection();
    if (getCategory($name) != null) return null;
    $result = $connection->execute_query("insert into categories (name, description) values (?, ?)", [$name, $description]);
    return getCategory($name);
}

function removeCategory(string $id): ?bool
{
    $connection = getConnection();
    $category = getCategory($id);
    if ($category == null) return null;
    $connection->execute_query("delete from categories where id=?", [$category['id']]);
    return true;
}

function editCategory(string $id, ?string $name, ?string $description): ?array
{
    $connection = getConnection();
    $category = getCategory($id);
    if ($category == null) return null;
    $connection->execute_query("update categories set name=?, description=? where id=?", [$name ?? $category['name'], $description ?? $category['description'], $category['id']]);
    return getCategory($category['id']);
}

function getCategory(string $id): ?array
{
    $connection = getConnection();
    $result = $connection->execute_query("select * from categories where name=?", [$id]);
    if ($result->num_rows == 0) {
        $result = $connection->execute_query("select * from categories where id=?", [$id]);
        if ($result->num_rows == 0) return null;
    }
    return $result->fetch_assoc();
}

function createItem(string $category_id, string $name, int $in_stock, float $price, string $image, ?string $description = ''): ?array
{
    $connection = getConnection();
    if (getItem($name) != null) return null;
    $category = getCategory($category_id);
    if ($category == null) return null;
    $result = $connection->execute_query("insert into items (category_id, name, in_stock, price, image, description) values (?, ?, ?, ?, ?, ?)", [$category['id'], $name, $in_stock, $price, $image, $description]);
    return getItem($connection->insert_id);
}

function removeItem(string $id): ?bool
{
    $connection = getConnection();
    $item = getItem($id);
    if ($item == null) return null;
    $connection->execute_query("delete from items where id=?", [$id]);
    return true;
}

function editItem(string $id, ?string $category_id = null, ?string $name = null, ?int $in_stock = null, ?float $price = null, ?string $image = null, ?string $description = null): ?array
{
    $connection = getConnection();
    $item = getItem($id);
    if ($item == null) return null;
    $connection->execute_query("update items set category_id=?, name=?, in_stock=?, price=?, image=?, description=? where id=?", [$category_id ?? $item['category_id'], $name ?? $item['name'], $in_stock ?? $item['in_stock'], $price ?? $item['price'], $image ?? $item['image'], $description ?? $item['description'], $item['id']]);
    return getItem($item['id']);
}

function getItem(string $id): ?array
{
    $connection = getConnection();
    $result = $connection->execute_query("select * from items where id=?", [$id]);
    if ($result->num_rows == 0) return null;
    return $result->fetch_assoc();
}

function getItems(?int $category_id = null): array
{
    $connection = getConnection();
    if ($category_id != null) {
        $result = $connection->execute_query("select * from items where category_id=?", [$category_id]);
    } else {
        $result = $connection->execute_query("select * from items");
    }
    $items = [];
    while ($item = $result->fetch_assoc()) $items[] = $item;
    return $items;
}

function getTablePositions(): array
{
    $connection = getConnection();
    $result = $connection->execute_query("select * from table_positions");
    $table_positions = [];
    while ($table_position = $result->fetch_assoc()) $table_positions[] = $table_position;
    return $table_positions;
}

function createTable(int $position): ?array
{
    $connection = getConnection();
    if (getTableByPosition($position) != null) return null;
    $table_position = getTablePosition($position);
    if ($table_position == null) return null;
    $connection->execute_query("insert into tables (table_position_id) values (?)", [$table_position['id']]);
    return getTableById($connection->insert_id);
}

function addTableItem(int $table_position, int $item_id, int $quantity): ?bool
{
    $connection = getConnection();
    $table = getTableByPosition($table_position);
    if ($table == null) $table = createTable($table_position);
    $item = getItem($item_id);
    if ($item == null) return null;
    if ($item['in_stock'] < $quantity) return false;
    $connection->execute_query("insert into table_details (table_id, item_id, quantity) values (?, ?, ?)", [$table['id'], $item_id, $quantity]);
    editItem($item_id, null, null, $item['in_stock'] - $quantity);
    return true;
}

function getTableItems(int $table_position): array
{
    $connection = getConnection();
    $table = getTableByPosition($table_position);
    if ($table == null) return [];
    $result = $connection->execute_query("select * from table_details where table_id=?", [$table['id']]);
    $items = [];
    while ($i = $result->fetch_assoc()) {
        $item = getItem($i['item_id']);
        $i['name'] = $item['name'];
        $i['price'] = $item['price'];
        $items[] = $i;
    }
    return $items;
}

function removeTable(int $id): ?bool
{
    $connection = getConnection();
    $table = getTableById($id);
    if ($table == null) return null;
    return $connection->execute_query("delete from tables where id=?", [$id]);
}

function getTableById(int $id): ?array
{
    $connection = getConnection();
    $result = $connection->execute_query("select * from tables where id=?", [$id]);
    if ($result->num_rows == 0) return null;
    return $result->fetch_assoc();
}

function getTablePosition(int $position): ?array
{
    $connection = getConnection();
    $result = $connection->execute_query("select * from table_positions where position=?", [$position]);
    if ($result->num_rows == 0) return null;
    return $result->fetch_assoc();
}

function getTableByPosition(int $position): ?array
{
    $connection = getConnection();
    $table_position = getTablePosition($position);
    $result = $connection->execute_query("select * from tables where table_position_id=?", [$table_position['id']]);
    if ($result->num_rows == 0) return null;
    return $result->fetch_assoc();
}

function calculateTablePrice(int $table_position): ?float
{
    $connection = getConnection();
    $table = getTableByPosition($table_position);
    if ($table == null) return null;
    $result = $connection->execute_query("select * from table_details where table_id=?", [$table['id']]);
    $sum = 0;
    while ($table_detail = $result->fetch_assoc()) {
        $item = getItem($table_detail['item_id']);
        $sum += $item['price'] * $table_detail['quantity'];
    }
    return $sum;
}

function makeBill(int $table_position, float $total_paid): ?int
{
    $connection = getConnection();
    $table = getTableByPosition($table_position);
    if ($table == null) return null;
    $connection->execute_query("insert into bills(total_paid, total_price) values (?, ?)", [$total_paid, calculateTablePrice($table_position)]);
    $bill_id = $connection->insert_id;
    $result = $connection->execute_query("select * from table_details where table_id=?", [$table['id']]);
    while ($table_detail = $result->fetch_assoc()) {
        $connection->execute_query("insert into bill_details(bill_id, item_id, quantity) values (?, ?, ?)", [$bill_id, $table_detail['item_id'], $table_detail['quantity']]);
    }
    if (removeTable($table['id']) == null) return null;
    return $bill_id;
}

function getBills(): array
{
    $connection = getConnection();
    $result = $connection->execute_query("select * from bills");
    $bills = [];
    while ($bill = $result->fetch_assoc()) {
        $info = '';
        foreach (getBillDetails($bill['id']) as $i => $bill_detail) {
            $item = getItem($bill_detail['item_id']);
            $info .= ($i == 0 ? '' : ', ') . "{$item['name']} x{$bill_detail['quantity']}";
        }
        $bill['info'] = $info;
        $bills[] = $bill;
    }
    return $bills;
}

function getBillDetails(int $bill_id): array
{
    $connection = getConnection();
    $result = $connection->execute_query("select * from bill_details where bill_id=?", [$bill_id]);
    $bill_details = [];
    while ($bill_detail = $result->fetch_assoc()) $bill_details[] = $bill_detail;
    return $bill_details;
}

/**
 * @throws Exception
 */
function generateToken($length = 16): string
{
    $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $pieces = [];
    $max = mb_strlen($stringSpace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces[] = $stringSpace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

function seeding(): void
{
    query("insert into categories(name, description) values (?, ?)", ['coffe', '']);
    query("insert into categories(name, description) values (?, ?)", ['tea', '']);
    query("insert into categories(name, description) values (?, ?)", ['milk', '']);
    query("insert into categories(name, description) values (?, ?)", ['cake', '']);
    for ($i = 1; $i <= 10; $i++)
        query("insert into table_positions(position) values (?)", [$i]);
}

function createTables(): void
{
    query("CREATE TABLE IF NOT EXISTS users(
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(30) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    query("CREATE TABLE IF NOT EXISTS tokens(
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id),
            value VARCHAR(255) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    query("CREATE TABLE IF NOT EXISTS categories(
            id INT AUTO_INCREMENT PRIMARY KEY,
            name NVARCHAR(50) UNIQUE NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    query("CREATE TABLE IF NOT EXISTS items(
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
    query("CREATE TABLE IF NOT EXISTS bills(
            id INT AUTO_INCREMENT PRIMARY KEY,
            total_paid DECIMAL(16,2) NOT NULL,
            total_price DECIMAL(16,2) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    query("CREATE TABLE IF NOT EXISTS bill_details(
            id INT AUTO_INCREMENT PRIMARY KEY,
            bill_id INT NOT NULL,
            FOREIGN KEY (bill_id) REFERENCES bills(id),
            item_id INT NOT NULL,
            FOREIGN KEY (item_id) REFERENCES items(id),
            quantity INT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    query("CREATE TABLE IF NOT EXISTS table_positions(
            id INT AUTO_INCREMENT PRIMARY KEY,
            position INT NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    query("CREATE TABLE IF NOT EXISTS tables(
            id INT AUTO_INCREMENT PRIMARY KEY,
            table_position_id INT NOT NULL,
            FOREIGN KEY (table_position_id) REFERENCES table_positions(id),
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    query("CREATE TABLE IF NOT EXISTS table_details(
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