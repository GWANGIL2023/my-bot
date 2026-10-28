<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// 加载项目根目录的 .env 文件
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// 读取环境变量
$appEnv = $_ENV['APP_ENV'] ?? 'production';
$dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
$dbPort = $_ENV['DB_PORT'] ?? '3306';
$dbName = $_ENV['DB_NAME'] ?? '';
$dbUser = $_ENV['DB_USER'] ?? '';
$dbPassword = $_ENV['DB_PASS'] ?? '';

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

} catch (PDOException $e) {
    error_log($e->getMessage());
    exit('数据库连接失败');
}

$stmt = $pdo->query('SELECT 1');
$result = $stmt->fetch();

var_dump($result);
