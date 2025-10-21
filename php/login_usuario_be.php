<?php
session_start();
require_once 'conexion_be.php';
require_once 'validacion.php';

// Captura y saneamiento
$correo     = trim($_POST['correo'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$tipoPet    = strtolower(trim($_POST['tipo'] ?? 'usuario')); 

// Normaliza el tipo para evitar valores inesperados
$tipoPet = ($tipoPet === 'administrador') ? 'administrador' : 'usuario';

// Validaciones mínimas en backend (opcional si ya usas validacion.php)
if (empty($correo) || empty($contrasena)) {
    echo '<script>alert("Completa correo y contraseña"); window.location="../formulario.php";</script>';
    exit();
}

// Consulta segura al usuario por correo
$stmt = $conexion->prepare("SELECT id, contrasena, role_id FROM usuarios WHERE correo = ? LIMIT 1");
if (!$stmt) {
    echo '<script>alert("Error en el servidor. Intenta más tarde."); window.location="../formulario.php";</script>';
    exit();
}
$stmt->bind_param("s", $correo);
$stmt->execute();
$res = $stmt->get_result();

if (!($user = $res->fetch_assoc())) {
    echo '<script>alert("Usuario no existe o correo incorrecto"); window.location="../formulario.php";</script>';
    exit();
}

// Verificar contraseña
if (!password_verify($contrasena, $user['contrasena'])) {
    echo '<script>alert("Contraseña incorrecta"); window.location="../formulario.php";</script>';
    exit();
}

// Si el usuario REAL no es admin (role_id != 1) pero seleccionó "administrador", bloquear inicio
if ((int)$user['role_id'] !== 1 && $tipoPet === 'administrador') {
    echo '
      <script>
        alert("No tienes permisos de administrador. Verifica que hayas seleccionado el tipo correcto.");
        window.location = "../formulario.php";
      </script>
    ';
    exit();
}

// Si todo ok, iniciar sesión
$_SESSION['usuario'] = (int)$user['id'];
$_SESSION['role_id']    = (int)$user['role_id'];

// UX: si admin real y eligió 'usuario', podemos redigira al index (modo 'usuario').
if ($_SESSION['role_id'] === 1) {
    if ($tipoPet === 'usuario') {
        // administrador que decidió entrar como usuario
        header("Location: ../index.php");
    } else {
        // administrador que quiere su panel
        header("Location: ../VistaAdm.php"); 
    }
} else {
    // usuario normal
    header("Location: ../index.php");
}
exit();
