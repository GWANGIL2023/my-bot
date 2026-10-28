<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

function jwtSecret(): string
{
    $secret = $_ENV['JWT_SECRET'] ?? '';

    if (strlen($secret) < 32) {
        throw new RuntimeException('JWT_SECRET 未配置或长度不足');
    }

    return $secret;
}

// 生成 Token
function createToken(int $userId): string
{
    $now = time();
    $ttl = max(60, (int) ($_ENV['JWT_TTL'] ?? 3600));

    return JWT::encode([
        'iss' => $_ENV['JWT_ISSUER'] ?? 'http://localhost:8000',
        'iat' => $now,
        'nbf' => $now,
        'exp' => $now + $ttl,
        'sub' => (string) $userId,
    ], jwtSecret(), 'HS256');
}

// 验证 Token
function verifyToken(string $token): object
{
    return JWT::decode($token, new Key(jwtSecret(), 'HS256'));
}

// 从请求头获取当前用户
function currentUserId(): int
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $matches)) {
        http_response_code(401);
        throw new RuntimeException('缺少 Bearer Token');
    }

    try {
        $payload = verifyToken($matches[1]);
        $userId = filter_var($payload->sub ?? null, FILTER_VALIDATE_INT);

        if ($userId === false || $userId < 1) {
            throw new RuntimeException('Token 用户 ID 无效');
        }

        return $userId;
    } catch (Throwable $e) {
        http_response_code(401);
        throw new RuntimeException('Token 无效或已过期');
    }
}

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
}

$router = new AltoRouter();

// 首页
$router->map('GET', '/', function () {
    jsonResponse(['data' => 'Hello PHP!']);
}, 'home');

// 用户列表
$router->map('GET', '/users', function () {
    jsonResponse(['data' => '用户列表']);
}, 'users');

// 用户详情，ID 必须是整数
$router->map('GET', '/users/[i:id]', function ($id) {
    $userId = currentUserId();
    
    jsonResponse(['data' => [
        'id' => $id,
        'username' => 'zhangsan',
    ]]);
}, 'user.detail');

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

