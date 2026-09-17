<?php

$host = 'fi10.bot-hosting.cloud:25517';
$dbname = 'test';
$username = 'root';
$password = 'lfjIqyCACwMygUMaqSQqycIZ';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

try {
    $pdo = new PDO($dsn, $username, $password);

    // 设置错误模式
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $version = $pdo->query('SELECT VERSION()')->fetchColumn();

    echo "MySQL 连接成功！ " 。$version . '<br>';

    $stmt = $pdo->query("SELECT * FROM users");

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        echo $user['id'] . ' - ';
        echo $user['name'] . ' - ';
        echo $user['pass'] . ' - ';
        echo $user['email'] . '<br>';
    }

    $name = $_GET['name'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE name = :name"
    );

    $stmt->execute([
        ':name' => $name
    ]);

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        echo $user['id'] . ' - ';
        echo $user['name'] . ' - ';
        echo $user['pass'] . ' - ';
        echo $user['email'] . '<br>';
    }
    
    

} catch (PDOException $e) {
    echo "连接失败：" . $e->getMessage();
}
