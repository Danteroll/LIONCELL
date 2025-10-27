<?php
require_once 'conexion_be.php';
require_once 'validacion.php';

// Captura segura
$nombre    = trim($_POST['nombre'] ?? '');
$app       = trim($_POST['app'] ?? '');
$apm       = trim($_POST['apm'] ?? '');
$correo    = trim($_POST['correo'] ?? '');
$usuario   = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$region = $_POST['region'] ?? '+52';
$telefono = trim($_POST['telefono'] ?? '');

// Validación del teléfono
if (!preg_match('/^\d{10}$/', $telefono)) {
    die('❌ El número telefónico debe tener exactamente 10 dígitos sin espacios ni símbolos.');
}

// Combinar prefijo con número
$telefonoCompleto = $region . $telefono;
// === VALIDACIONES BACKEND ===
if (!esNombreValido($nombre))       salir("Nombre inválido (solo letras, espacios simples, sin números ni símbolos)");
if (!esApellidoValido($app))        salir("Apellido paterno inválido (solo letras, sin espacios ni números)");

if (!esUsuarioValido($usuario))     salir("Usuario inválido (3–30 caracteres, solo letras/números, sin espacios)");
if (!esCorreoASCIIValido($correo))  salir("Correo inválido (usa formato nombre@dominio.com sin ñ)");
if (!esContrasenaValida($contrasena)) salir("Contraseña inválida (8–25 caracteres, al menos 1 letra y 1 número, sin espacios)");

// === VERIFICAR DUPLICADOS ===
$check = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ? OR usuario = ? LIMIT 1");
$check->bind_param("ss", $correo, $usuario);
$check->execute();
$resultado = $check->get_result();

if ($resultado->num_rows > 0) {
    echo '
        <script>
            alert("El correo o usuario ya está registrado. Intenta con otros datos.");
            window.location = "../formulario.php";
        </script>
    ';
    exit();
}

// === HASH DE CONTRASEÑA ===
$hash = password_hash($contrasena, PASSWORD_BCRYPT);

// === INSERCIÓN SEGURA ===
$stmt = $conexion->prepare(
    "INSERT INTO usuarios (nombre, app, apm, correo, telefono, usuario, contrasena, role_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, 2)"
);
$stmt->bind_param("sssssss", $nombre, $app, $apm, $correo, $telefonoCompleto, $usuario, $hash);

if ($stmt->execute()) {
    echo '
        <script>
            alert("Usuario registrado exitosamente");
            window.location = "../formulario.php";
        </script>
    ';
} else {
    echo '
        <script>
            alert("Error al registrar usuario. Intenta nuevamente.");
            window.location = "../formulario.php";
        </script>
    ';
}

$stmt->close();
$conexion->close();
?>
