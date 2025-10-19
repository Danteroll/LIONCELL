<?php
//session_star para usuarios
require_once __DIR__ . '/inc/init.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: formulario.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lion Cell</title>
  <link rel="icon" href="imagenes/LogoLionCell.ico">
  <link rel="stylesheet" href="estilos.css">
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="logo"><img src="imagenes/LogoLionCell.png" witdh="75px" height="75px"> LION CELL</div>
    <div class="search-bar">
      <input type="text" placeholder="Encuentra lo que busques...">
    </div>
    <div class="account">
      <div class="cart">🛒</div>
      <?php if (!isset($_SESSION['usuario'])): ?>
          <a href="formulario.php" id="loginBtn">Ingresar / Registrar</a>
      <?php endif; ?>
      <?php if (isset($_SESSION['usuario'])): ?>
          <a href="perfil/home.php"><i class="fa-solid fa-user">👤</i></a>
      <?php else: ?>
          <a href="formulario.php"><i class="fa-solid fa-right-to-bracket"></i></a>
      <?php endif; ?>
    </div>
  </header>

  <!-- NAV -->
  <nav>
    <a href="index.php">🏠</a>
    <a href="lanzamientos.php">Lanzamientos</a>
    <a href="categorias.php">Categorías</a>
    <a href="marcas.php">Marcas</a>
    <a href="ofertas.php" class="ofertas">Ofertas</a>
  </nav>

  <!-- BANNERS -->
  <section class="banner">
    <div class="left">
      <img src="imagenes/exclusivos.PNG" alt="Exclusivos en línea">
    </div>
    <div class="right">
      <img src="imagenes/exclusivos1.PNG" alt="Promoción 1">
      <img src="imagenes/exclusivos2.PNG" alt="Promoción 2">
    </div>
  </section>

  <!-- CATEGORÍAS -->
  <section class="categories">
    <div class="item">
      <img src="imagenes/fundas.jpg" alt="Fundas">
      <span>Fundas</span>
    </div>
    <div class="item">
      <img src="imagenes/cargadores.jpg" alt="Cargadores">
      <span>Cargadores</span>
    </div>
    <div class="item">
      <img src="imagenes/micas.jpg" alt="Micas">
      <span>Micas</span>
    </div>
    <div class="item">
      <img src="imagenes/audifonos.jpg" alt="Audífonos">
      <span>Audífonos</span>
    </div>
    <div class="item">
      <img src="imagenes/soportes.jpg" alt="Soportes">
      <span>Soportes</span>
    </div>
    <div class="item">
      <img src="imagenes/memorias.jpg" alt="Memorias">
      <span>Memorias</span>
    </div>
    <div class="item">
      <img src="imagenes/tarjeteros.jpg" alt="Tarjeteros">
      <span>Tarjeteros</span>
    </div>
  </section>

</body>
</html>
