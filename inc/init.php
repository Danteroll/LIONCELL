<?php

// --- Sesión segura ---
if (session_status() === PHP_SESSION_NONE) {
    // Estas directivas deben configurarse *antes* de iniciar la sesión
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', 1);
    }

    session_start();
}

// --- Funciones de sesión ---
function isLoggedIn(): bool {
    return !empty($_SESSION['usuario']);
}

function isAdmin(): bool {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1;
}

// --- Configuración general ---
date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Conexión PDO ---
$db_host = '127.0.0.1';
$db_port = 3306;
$db_name = 'sitio';
$db_user = 'root';
$db_pass = '';

$charset = 'utf8mb4';
$collation = 'utf8mb4_spanish_ci';

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$charset}";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Fuerza el collation por sesión
    $pdo->exec("SET NAMES {$charset} COLLATE {$collation}");
    $pdo->exec("SET collation_connection = {$collation}");

} catch (PDOException $e) {
    http_response_code(500);
    exit('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
}
