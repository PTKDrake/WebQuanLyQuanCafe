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

    public static function createTables(): void
    {
        self::query("CREATE TABLE IF NOT EXISTS user(
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(30) NOT NULL,
            password VARCHAR(255) NOT NULL,
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
            category_id INT NOT NULL REFERENCES categories(id),
            name NVARCHAR(50) NOT NULL,
            description TEXT NULL,
            in_stock INT NOT NULL,
            price DECIMAL(16,2) NOT NULL,
            image LONGBLOB NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS order_details(
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL REFERENCES orders(id),
            item_id INT NOT NULL REFERENCES items(id),
            quantity INT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
        self::query("CREATE TABLE IF NOT EXISTS orders(
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT REFERENCES users(id),
            total_paid DECIMAL(16,2) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT NOW(),
            updated_at TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW()
        )");
    }
}