<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'simple_crm');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Timezone
date_default_timezone_set('Asia/Tehran');

// Error Reporting (برای محیط توسعه)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection Function
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("خطا در اتصال به دیتابیس: " . $e->getMessage());
    }
}

// Get single database connection instance
$db = getDBConnection();
