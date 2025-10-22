
<?php
//session_star para usuarios
require_once __DIR__ . '/inc/init.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: formulario.php");
    exit;
}
?>
<style>
    .fa-solid{ font-size:20px; }
    .fa-user{ font-size:35px; color: rgba(255,255,255,1);}
    .fa-cart-shopping{font-size:30px; color: rgba(255, 255, 255, 1);}
    header .account { display:flex; align-items:center; gap:15px; }
    /* --- FOOTER --- */
    .footer { background-color:#7c7c7c; color:#fff; padding:40px 60px 20px; font-family:'Montserrat', sans-serif; }
    .footer-container { display:flex; flex-wrap:wrap; justify-content:space-between; gap:30px; }
    .footer-section { flex:1 1 300px; }
    .footer-section h3 { font-size:1.2rem; margin-bottom:15px; color:#fff; border-bottom:2px solid #00bfff; display:inline-block; padding-bottom:5px; }
    .footer-section ul { list-style:none; padding:0; }
    .footer-section ul li { margin:8px 0; font-size:0.95rem; }
    .footer-section ul li a { color:#e0e0e0; text-decoration:none; transition:color 0.3s; }
    .footer-section ul li a:hover { color:#000; }
    .footer-bottom { text-align:center; font-size:0.85rem; color:#fff; border-top:1px solid rgba(255,255,255,0.2); margin-top:30px; padding-top:10px; }

  </style>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lion Cell</title>
  <link rel="icon" href="imagenes/LogoLionCell.ico">
  <link rel="stylesheet" href="estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap" rel="stylesheet">
 
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="logo"><img src="imagenes/LogoLionCell.png" witdh="75px" height="75px"> LION CELL</div>
    <div class="search-bar">
      <input type="text" placeholder="Encuentra lo que busques...">
    </div>
    <div class="account">
    <a href="formulario.php"><i class="fa-solid fa-user"></i></a>
    <i class="fa-solid fa-cart-shopping"></i>
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
