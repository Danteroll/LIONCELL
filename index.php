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
    /* --- PRODUCTOS --- */
    .productos .head {
      font-family:'Montserrat', sans-serif;
      font-size:2rem;
      color:#004aad;
      text-align:left;
      margin-left:60px;
      position:relative;
      margin-bottom:25px;
    }
    .productos .head::after {
      content:"";
      display:block;
      width:300px;
      height:4px;
      background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%);
      margin-top:8px;
      border-radius:2px;
    }
    .productos .box-container { display:flex; flex-wrap:wrap; gap:1.5rem; justify-content:center; padding:30px; }
    .productos .box-container .box {
      flex:1 1 30rem;
      box-shadow:0 .5rem 1.5rem rgba(0,0,0,0.1);
      border-radius:.5rem;
      border:.1rem solid rgba(0,0,0,0.1);
      position:relative;
      text-align:center;
      padding-bottom:15px;
      transition:transform 0.3s ease, box-shadow 0.3s ease;
    }
    .productos .box-container .box:hover { transform:translateY(-5px); box-shadow:0 8px 20px rgba(0,0,0,0.2); }
    .productos .box-container .box .image { position:relative; text-align:center; padding-top:2rem; overflow:hidden; }
    .productos .box-container .box .image img { height:18rem; width:auto; object-fit:contain; transition: transform 0.3s ease; }
    .productos .box-container .box:hover .image img { transform:scale(1.1); }
    .productos .box-container .box h3{ font-size:1.3rem; color:#333; margin-top:10px; }
    .productos .box-container .box .precio{ font-size:1.5rem; font-weight:bolder; color:#2866c2; margin-top:5px; padding-top:1rem; }

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
      
      <?php if (!isset($_SESSION['usuario'])): ?>
          <a href="formulario.php" id="loginBtn">Ingresar / Registrar</a>
      <?php endif; ?>
      <?php if (isset($_SESSION['usuario'])): ?>
          <div class="cart"><i class="fa-solid fa-cart-shopping"></i></div>
          <a href="perfil/home.php"><i class="fa-solid fa-user"></i></a>
      <?php endif; ?>
    </div>
  </header>

  <!-- NAV -->
  <nav>
    <a href="lanzamientos.php">Lanzamientos</a>
    <a href="#categorias">Categorías</a>
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
  <section class="categories" id="categorias">
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

  <!-- PRODUCTOS -->
  <section class="productos" id="productos">
    <h1 class="head">Lo más vendido...</h1>
    <div class="box-container">
      <div class="box">
        <div class="image"><img src="imagenes/fundaIMotoG73.jpg" alt=""></div>
        <h3>Funda Motorola G73</h3>
        <div class="precio">$100.00</div>
      </div>
      <div class="box">
        <div class="image"><img src="imagenes/fundaIMotoG73.jpg" alt=""></div>
        <h3>Funda Samsung A54</h3>
        <div class="precio">$120.00</div>
      </div>
      <div class="box">
        <div class="image"><img src="imagenes/fundaIMotoG73.jpg" alt=""></div>
        <h3>Funda Samsung A54</h3>
        <div class="precio">$120.00</div>
      </div>
      <div class="box">
        <div class="image"><img src="imagenes/fundaIMotoG73.jpg" alt=""></div>
        <h3>Funda Samsung A54</h3>
        <div class="precio">$120.00</div>
      </div>
      <div class="box">
        <div class="image"><img src="imagenes/fundaIMotoG73.jpg" alt=""></div>
        <h3>Funda Samsung A54</h3>
        <div class="precio">$120.00</div>
      </div>
      <div class="box">
        <div class="image"><img src="imagenes/fundaIMotoG73.jpg" alt=""></div>
        <h3>Funda Samsung A54</h3>
        <div class="precio">$120.00</div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer-container">
      <div class="footer-section">
        <h3>Información</h3>
        <ul>
          <li><a href="informacion.php#contacto">Contacto</a></li>
          <li><a href="informacion.php#aviso">Aviso legal</a></li>
          <li><a href="informacion.php#historia">Nuestra historia</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Información de la tienda</h3>
        <ul>
          <li>Lion Cell — Accesorios para tu celular</li>
          <li>📍 México</li>
          <li>📞 +52 55 0000 0000</li>
          <li>✉ contacto@lioncell.com</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">© 2025 Lion Cell. Todos los derechos reservados.</div>
  </footer>

</body>
</html>
