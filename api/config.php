<?php
// Uses Railway variables when deployed, XAMPP defaults when local
$host = getenv('MYSQLHOST')     ?: 'localhost';
$port = getenv('MYSQLPORT')     ?: '3306';
$db   = getenv('MYSQLDATABASE') ?: 'carcare_db';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error'  => $e->getMessage(),
        'host'   => $host,
        'port'   => $port,
        'db'     => $db,
        'user'   => $user,
        'driver_installed' => in_array('mysql', PDO::getAvailableDrivers()),
    ]);
    exit;
}
