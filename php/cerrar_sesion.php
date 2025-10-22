<?php
require_once 'conexion_be.php'; 

// Iniciar sesión antes de destruir
session_start();

// Limpieza total
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?><!DOCTYPE html>
<html>
<head>
    <title>Alerta de sesión cerrada</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.5;
            color: #1d2129;
            background-color: #f6f7f8;
            margin: 0;
            padding: 0;
        }

        .alert-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .alert {
            background-color: #fff;
            border: 1px solid #ccd0d5;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,0.05);
            max-width: 600px;
            width: 100%;
            padding: 16px;
        }

        .alert-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #3b5998;
        color: #fff;
        font-size: 24px;
        overflow: hidden;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
        background-image: url('../imagenes/LogoLionCell.ico');
        }


        .alert-text {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .alert-title {
            font-weight: bold;
            font-size: 18px;
            margin: 0 0 8px;
        }

        .alert-message {
            margin: 0;
        }

        .alert-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }

        .alert-action {
            background-color: #3b5998;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            padding: 8px 16px;
            margin-left: 8px;
            transition: background-color 0.2s ease-in-out;
        }

        .alert-action:hover {
            background-color: #2d4373;
        }
    </style>
</head>

<!DOCTYPE html>
<html>
<head>
    <title>Alerta de sesión cerrada</title>
    <style>
        /* (aquí deja todo tu CSS tal como lo tienes) */
    </style>
</head>
<body>
    <div class="alert-container">
        <div class="alert">
            <div class="alert-icon"></div>
            <div class="alert-text">
                <h2 class="alert-title">Sesión cerrada</h2>
                <p class="alert-message">
                    Tu sesión ha sido cerrada correctamente. Serás redirigido a la página principal en unos segundos.
                </p>
                <div class="alert-actions">
                    <button class="alert-action" onclick="window.location.href='../index.php'">
                        Ir a la página principal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Redirección automática en 2.5 segundos
        setTimeout(function() {
            window.location.href = "../formulario.php";
        }, 2500);
    </script>
</body>
</html>

