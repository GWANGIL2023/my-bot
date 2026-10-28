<?php
declare(strict_types=1);

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

$router = new AltoRouter();

// 首页
$router->map('GET', '/', function () {
    echo 'Hello PHP!';
}, 'home');

// 用户列表
$router->map('GET', '/users', function () {
    echo '用户列表';
}, 'users');

// 用户详情，ID 必须是整数
$router->map('GET', '/users/[i:id]', function ($id) {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'id' => $id,
        'username' => 'zhangsan',
    ], JSON_UNESCAPED_UNICODE);
}, 'user.detail');

// API 示例
$router->map('GET', '/api/ping', function () {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'code' => 200,
        'message' => 'pong',
    ], JSON_UNESCAPED_UNICODE);
}, 'api.ping');

// POST 请求
$router->map('POST', '/api/users', function () {
    header('Content-Type: application/json; charset=utf-8');

    $username = $_POST['username'] ?? '';

    echo json_encode([
        'code' => 200,
        'username' => $username,
    ], JSON_UNESCAPED_UNICODE);
}, 'api.users.create');

// 匹配并执行路由
$match = $router->match();

if ($match && is_callable($match['target'])) {
    call_user_func_array(
        $match['target'],
        $match['params']
    );
} else {
    http_response_code(404);
    echo '404 Not Found';
}

