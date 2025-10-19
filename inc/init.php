<?php
// inc/init.php

// Configuración segura de cookies de sesión (opcional pero recomendable)
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
// Si usas HTTPS, habilita secure:
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Opcional: función helper para saber si hay usuario
function isLoggedIn(): bool {
    return !empty($_SESSION['usuario_id']); // o $_SESSION['usuario']
}

function isAdmin(): bool {
    return isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1;
}
