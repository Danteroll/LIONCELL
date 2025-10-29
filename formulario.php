<?php

    session_start();
    if(isset($_SESSION['usuario'])){
        header("location: index.php");
    }
    if(isset($_GET['logout'])){
        session_destroy();
        header("Location: formulario.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>formulario</title>
    <link rel="icon" href="imagenes/LogoLionCell.ico">
    <link rel="stylesheet" href="formulario.css">
<body>
    <main>
        <div class="contenedor__todo">
            <div class="caja__trasera">
                <div class="caja__trasera-login">
                    <h3>¿Ya tienes una cuenta?</h3>
                    <p>Inicia sesión para entrar en la página</p>
                    <button id="btn__iniciar-sesion">Iniciar Sesión</button>
                </div>
                <div class="caja__trasera-register">
                    <h3>¿Aún no tienes una cuenta?</h3>
                    <p>Regístrate para que puedas iniciar sesión</p>
                    <button id="btn__registrarse">Regístrarse</button>
                </div>
            </div>

            <!--Formulario de Login y registro-->
            <div class="contenedor__login-register">
                <!--Login-->
                <form action="php/login_usuario_be.php" method="POST" class="formulario__login">
                    <h2>Iniciar Sesión</h2>
                    <div class="campo-select">
                        <label for="tipo">Tipo: *</label>
                        <select id="tipo" name="tipo" required>
                        <option value="usuario">Usuario</option>
                        <option value="administrador">Administrador</option>
                        </select>
                    </div>
                    <input type="email" placeholder="Correo Electrónico *" name="correo" required>
                    <input type="password" placeholder="Contraseña *" name="contrasena" minlength="8" maxlength="25" pattern="^(?=\S{8,25}$)(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$" required title="8–25 caracteres, sin espacios, con al menos 1 letra y 1 número.">
                    <button>Entrar</button>
                </form>

                <!--Register-->
                <form action="php/registro_usuario_be.php" method="POST" class="formulario__register" id="form-register">
                    <h2>Regístrarse</h2>

                    <!-- Usuario: sin espacios, sin caracteres especiales, máx 30 -->
                    <input
                    type="text"
                    placeholder="Nombre de usuario *"
                    name="usuario"
                    id="usuario"
                    minlength="3"
                    maxlength="30"
                    pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9]{3,30}$"
                    required
                    title="Solo letras (incluye acentos) y números, sin espacios ni símbolos. Máx 30."
                    >

                    <!-- Nombre: SIN números ni símbolos; permite espacios simples entre palabras -->
                    <input
                    type="text"
                    placeholder="Nombres *"
                    name="nombre"
                    id="nombre"
                    minlength="1"
                    maxlength="50"
                    pattern="^(?!.*\s{2,})[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$"
                    required
                    title="Solo letras (incluye acentos). Puedes usar espacios simples entre nombres. Sin números ni símbolos."
                    >

                    <!-- Apellido Paterno: solo letras, SIN espacios -->
                    <input
                    type="text"
                    placeholder="Apellido Paterno *"
                    name="app"
                    id="app"
                    minlength="1"
                    maxlength="30"
                    pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ]+$"
                    required
                    title="Solo letras (incluye acentos), sin espacios."
                    >

                    <!-- Apellido Materno: solo letras, SIN espacios -->
                    <input
                    type="text"
                    placeholder="Apellido Materno"
                    name="apm"
                    id="apm"
                    minlength="1"
                    maxlength="30"
                    pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ]+$"
                    title="Solo letras (incluye acentos), sin espacios."
                    >

                    <!-- Correo: type=email + patrón ASCII con TLD (2–24 letras) -->
                    <input
                    type="email"
                    placeholder="Correo electrónico *"
                    name="correo"
                    id="correo"
                    maxlength="80"
                    pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,24}$"
                    required
                    title="Escribe un correo válido (ej. nombre@dominio.com). Solo ASCII, TLD de 2 a 24 letras."
                    >
                    <div style="display:flex;gap:8px;align-items:center;">
                    <select name="region" id="region" required class="region-select">
                        <option value="+52" selected>🇲🇽 +52 (México)</option>
                        <option value="+1">🇺🇸 +1 (EE.UU.)</option>
                        <option value="+54">🇦🇷 +54 (Argentina)</option>
                        <option value="+57">🇨🇴 +57 (Colombia)</option>
                        <option value="+58">🇻🇪 +58 (Venezuela)</option>
                        <option value="+34">🇪🇸 +34 (España)</option>
                    </select>

                    <input 
                        type="text" 
                        id="telefono" 
                        name="telefono" 
                        maxlength="10" 
                        required
                        placeholder="Teléfono *" 
                        pattern="\d{10}" 
                        title="Debe tener exactamente 10 números sin espacios ni símbolos"
                        oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                    </div>

                    <!-- Contraseña (como ya la tienes, sin espacios, 8–25, 1 letra + 1 número) -->
                    <input
                    type="password"
                    placeholder="Contraseña *"
                    name="contrasena"
                    id="contrasena"
                    minlength="8"
                    maxlength="25"
                    pattern="^(?=\S{8,25}$)(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+$"
                    required
                    title="8–25 caracteres, sin espacios, con al menos 1 letra y 1 número."
                    >


                    <button type="submit">Regístrarse</button>
                </form>

            </div>
        </div>
    </main>
    <script src="formulario.js"></script>
</body>
</html>