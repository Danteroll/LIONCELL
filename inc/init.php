<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
function isLoggedIn(): bool { return !empty($_SESSION['usuario']); }
function isAdmin(): bool { return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1; }

// -------- Ajustes útiles --------
date_default_timezone_set('America/Mexico_City'); // o el que uses

// En desarrollo (quítalo en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// -------- Conexión PDO --------
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

    // Fuerza collation por sesión 
    $pdo->exec("SET NAMES {$charset} COLLATE {$collation}");
    $pdo->exec("SET collation_connection = {$collation}");

} catch (PDOException $e) {
    // Error en desarrollo; en producción, registra y muestra mensaje genérico
    http_response_code(500);
    exit('Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()));
}

