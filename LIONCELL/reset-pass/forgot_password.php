<?php
include '../php/conexion_be.php';
// Verificar si se ha enviado el formulario de restablecimiento de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener la dirección de correo electrónico del formulario
    $email = $_POST['correo'];

    // Verificar si la dirección de correo electrónico existe en la base de datos
    $query = "SELECT * FROM usuarios WHERE correo = '$email'";
    $resultado = mysqli_query($conexion, $query);
    if (mysqli_num_rows($resultado) > 0) {
        // Generar un token de restablecimiento de contraseña único y seguro
        $token = bin2hex(random_bytes(16));

        // Guardar el token en la base de datos asociado al correo electrónico del usuario
        $query = "UPDATE usuarios SET token = '$token' WHERE correo = '$email'";
        mysqli_query($conexion, $query);

        // Construir el enlace de restablecimiento de contraseña
        $reset_password_link = 'https://tusitio.com/reset-password.php?token=' . $token;

        // Construir el correo electrónico de restablecimiento de contraseña
        $subject = 'Restablecer contraseña';
        $message = "Hola,\n\nHaz clic en el siguiente enlace para restablecer tu contraseña:\n\n$reset_password_link\n\nSaludos,\nTusitio.com";
        $headers = 'From: tu@tusitio.com' . "\r\n" .
            'Reply-To: tu@tusitio.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        // Enviar el correo electrónico de restablecimiento de contraseña
        if (mail($email, $subject, $message, $headers)) {
            // Redirigir al usuario a una página de confirmación
            header('Location: reset_password_confirmation.php');
            exit;
        } else {
            $error_message = 'No se pudo enviar el correo electrónico';
        }
    } else {
        $error_message = 'La dirección de correo electrónico no existe';
    }
}

// Cerrar la conexión a la base de datos
mysqli_close($conexion);
?>
