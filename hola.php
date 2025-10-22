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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap" rel="stylesheet">
  <style>
 html {
    scroll-behavior: smooth;
  }
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial, Helvetica, sans-serif; }
    body { background:#fff; color:#3a3a3a; }

    /* --- HEADER --- */
    header {
      background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%);
      color:white;
      padding:10px 20px;
      display:flex;
      align-items:center;
      justify-content:space-between;
    }
    header .logo { display:flex; align-items:center; font-weight:bold; color:#aaccfa; font-size:20px; }
    .search-bar { position:relative; }
    input { width:900px; padding:10px; padding-right:35px; border-radius:5px; border:0; outline:none; font-size:1rem; }
    button { background-color:rgb(69,112,255); padding:10px 15px; position:absolute; border:0; top:0; bottom:0; right:0; margin:auto; border-radius:0 5px 5px 0; color:#fff; cursor:pointer; }
    .fa-solid{ font-size:20px; }
    .fa-user{ font-size:35px; color: rgba(255,255,255,1);}
    .fa-cart-shopping{font-size:30px; color: rgba(255, 255, 255, 1);}
    header .account { display:flex; align-items:center; gap:15px; }

    /* --- SLOGAN --- */
    .slogan-box {
      background:#eaf3ff;
      border-bottom:2px solid #b0d4ff;
      text-align:center;
      padding:20px;
      font-size:1.4rem;
      font-weight:bold;
      color:#004aad;
      letter-spacing:1px;
      box-shadow:0 3px 8px rgba(0,0,0,0.1);
      font-family:'Montserrat', sans-serif;
    }
    /* --- NAV --- */
nav {
  display: flex;
  justify-content: center;
  align-items: center;
  background: white;
  width: 100%;
  height: 65px; /
}

nav a {
  
  color: rgb(0, 0, 0);
  text-decoration: none;
  margin: 0 30px; 
  font-size: 1.2rem;
  font-weight: 500;
  transition: 0.3s;
}

nav a:hover,
nav .ofertas {
 
  color: #e60838;
  transform: scale(1.1);
}

.nav.container {
  display: center;
  justify-content: center;
  gap: 20px;
}
    /* --- BANNER --- */
.banner {
  display: flex;
  justify-content: flex-start;
  align-items: stretch; 
  gap: 20px;
  padding: 20px;
}


.banner .left {
  flex: 2; 
}

.banner .left img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 25px;
  transition: transform 0.3s;
}


.banner .right {
  flex: 1; 
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 20px;
}

.banner .right img {
  width: 100%;
  height: 50%; 
  object-fit: cover;
  border-radius: 20px;
  transition: transform 0.3s;
}

.banner img:hover {
  transform: scale(1.03);
}

    /* --- CATEGORÍAS --- */
    .categories {
      display:grid; 
      grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));
      gap:25px; 
      padding:30px 60px; 
      text-align:center;
    }
    .categories a.item {
      display:block;
      background: linear-gradient(180deg, #69a5ff, #87cbdb);
      border-radius:15px; 
      padding:20px; 
      box-shadow:0 2px 10px rgba(0,0,0,0.1); 
      transition: transform 0.3s, box-shadow 0.3s; 
      height:350px; 
      text-decoration:none;
      color:#000;
    }
    .categories a.item:hover { transform:translateY(-5px); box-shadow:0 5px 15px rgba(0,0,0,0.2); }
    .categories img { width:100%; max-width:200px; height:250px; max-height:300px; border-radius:10px; }
    .categories span { display:block; margin-top:20px; font-size:1.3rem; font-weight:500; }

    h1 { font-size:1.5em; font-weight:bold; color:#333; margin-bottom:20px; text-align:center; }

    #mobile-form { display:flex; align-items:center; justify-content:center; gap:10px; }
    select { padding:10px 30px 10px 15px; border:1px solid #ccc; border-radius:4px; min-width:180px; font-size:1em; appearance:none; }
    .main-center-container { margin-top:50px; }

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
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="logo"><img src="imagenes/LogoLionCell.png" width="75" height="75"> LION CELL</div>
    <div class="search-bar">
      <input type="text" placeholder="Encuentra lo que busques...">
      <button><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
   <div class="account">
    <a href="formulario.php"><i class="fa-solid fa-user"></i></a>
    <i class="fa-solid fa-cart-shopping"></i>
</div>
  </header>

  <!-- SLOGAN -->
  <div class="slogan-box">“! CONECTA TU ESTILO ¡”</div>

   <!-- NAV -->
  <nav class="nav container">
    <a href="lanzamientos.php">Lanzamientos</a>
    <a href="#categorias">Categorías</a>
    <a href="#">Marcas▼</a>
    <a href="#" class="ofertas">Ofertas</a>
  </nav>

   <!-- BANNERS -->
  <section class="banner">
  <div class="left">
    <img src="imagenes/exclusivos.png" alt="Exclusivos en línea">
  </div>
  <div class="right">
    <img src="imagenes/exclusivos1.png" alt="Tu vida digital">
    <img src="imagenes/exclusivos2.png" alt="No te quedes sin pila">
  </div>
</section>

  <!-- CATEGORÍAS -->
  <section class="categories" id="categorias">
    <a href="fundas.php" class="item">
      <img src="imagenes/fundas sin fondo.png" alt="CARCASAS">
      <span>Carcasas</span>
    </a>
    <a href="cargadores.php" class="item">
      <img src="imagenes/cargador sin fondo.png" alt="Cargadores">
      <span>Cargadores</span>
    </a>
    <a href="micas.php" class="item">
      <img src="imagenes/micas sin fondo.png" alt="Micas">
      <span>Micas</span>
    </a>
    <a href="audifonos.php" class="item">
      <img src="imagenes/audifonos sin fondo.png" alt="Audífonos">
      <span>Audífonos</span>
    </a>
    <a href="soportes.php" class="item">
      <img src="imagenes/soporte sin fondo.png" alt="Soportes">
      <span>Soportes</span>
    </a>
    <a href="memorias.php" class="item">
      <img src="imagenes/memorias sin fondo.png" alt="Memorias">
      <span>Memorias</span>
    </a>
    <a href="tarjeteros.php" class="item">
      <img src="imagenes/tarjeteros sin fondos.png" alt="Tarjeteros">
      <span>Tarjeteros</span>
    </a>
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