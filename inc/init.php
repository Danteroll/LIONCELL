<?php
// --- Sesión segura ---
if (session_status() === PHP_SESSION_NONE) {
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
$db_host = '127.0.0.1';          // o tu host del servidor remoto
$db_port = 3306;
$db_name = 'sitio';              // nombre de tu BD
$db_user = 'root';
$db_pass = '';                   // cambia si estás en hosting

$charset   = 'utf8mb4';
$collation = 'utf8mb4_unicode_ci'; // usa _unicode_ci para compatibilidad máxima

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset={$charset}";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // Fuerza el charset y collation de la sesión actual
    $pdo->exec("SET NAMES '{$charset}' COLLATE '{$collation}'");
    $pdo->exec("SET CHARACTER SET '{$charset}'");
    $pdo->exec("SET SESSION collation_connection = '{$collation}'");

    // Encabezado global para UTF-8 (opcional pero recomendable)
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }

} catch (PDOException $e) {
    http_response_code(500);
    exit('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
}
