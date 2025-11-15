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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Lion Cell – Lanzamientos</title>
  <link rel="icon" href="imagenes/LogoLionCell.ico">
  <link rel="stylesheet" href="estilos.css"/>
</head>
<body>

<style>

   body { font-family: Arial, sans-serif; background-color: #f7f7f7; margin:0; padding:0;}
    header { background:  linear-gradient(90deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%); color:white; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:1.5rem; }
    header a { color:white; text-decoration:none; padding:8px 15px; border-radius:5px; }

 .lc-breadcrumbs{
  max-width:1200px;
  margin:12px auto 0;
  padding:0 16px;
  color:#888;
  font-size:13px;
}
.lc-breadcrumbs a{ color:#666; text-decoration:none; }
.lc-breadcrumbs a:hover{ text-decoration:underline; }
.lc-breadcrumbs span{ margin:0 6px; }
.lc-breadcrumbs .current{ color:#222; }

.lc-grid{
  display:grid;
  grid-template-columns: repeat(4, 1fr);
  gap:24px;
}
.lc-card{
  background:#fff;
  border:1px solid rgba(10,10,10,.08);
  border-radius:12px;
  overflow:hidden;
  display:flex;
  flex-direction:column;
}
.lc-card img{
  width:100%; height:220px; object-fit:contain; background:#fff;
}
.card-body{ padding:12px 14px 16px; display:flex; flex-direction:column; gap:8px; }
.price{ font-weight:700; color:#222; }
.name{
  font-size:14px; font-weight:500; color:#333; line-height:1.35;
  height:3.6em; overflow:hidden;
}

/* Botones  (azul y dorado) */
.btn{
  margin-top:8px;
  padding:10px 14px;
  border:none;
  border-radius:8px;
  font-weight:700;
  color:#fff;
  cursor:pointer;
}
.btn-primary{ background:#2E77FF; }
.btn-primary:hover{ background:#2467e8; }
.btn-alt{ background:#D4AE59; color:#fff; }
.btn-alt:hover{ background:#c89d44; }

.lc-filters{
  background:#fff;
  border:1px solid rgba(10,10,10,.08);
  border-radius:12px;
  padding:16px;
  height:max-content;
}
.lc-filters h3{
  font-size:16px; font-weight:700; margin-bottom:10px;
}
.f-group{ border-top:1px solid #eee; padding:10px 0; }
.f-group:first-of-type{ border-top:none; padding-top:0; }
.f-group summary{
  cursor:pointer; list-style:none; font-weight:600; color:#333;
}
.f-group[open] summary{ color:#0B3B8E; }
.f-list{ display:flex; flex-direction:column; gap:8px; margin-top:10px; }
.f-list label{ font-size:14px; color:#444; }

.f-range{ margin-top:10px; }
.f-range input[type="range"]{ width:100%; }
.f-nums{
  display:flex; align-items:center; gap:8px; margin-top:10px;
}
.f-nums .dash{ color:#888; }
.f-nums input[type="number"]{
  width:110px; padding:8px 10px; border:1px solid #e6e9f0; border-radius:10px;
}

/* ====== Contenido ====== */
.lc-content{ min-width:0; }
.lc-title{
  font-size:28px;
  font-weight:700;
  margin-bottom:18px;
}

footer { background-color:  #646464ff; color:white; text-align:center; padding:15px; margin-top:30px; }
  .volver-inicio { display:inline-block; margin:20px; padding:10px 15px; background:#0063f7; color:white; text-decoration:none; border-radius:5px; transition: background 0.3s; }
    .volver-inicio:hover { background:#004aad; }

  </style>

  <!-- Breadcrumb -->
  

   <header>
    <div class="logo"><img src="imagenes/LogoLionCell.png" width="60" height="60"> LION CELL</div>
    <h1>Lanzamientos</h1>
    <a href="index.php" class="volver-inicio">← Volver al inicio</a>
  </header>

  <main class="lc-page">
    <!-- Sidebar -->
    <aside class="lc-filters">
      <h3>Filtros</h3>

      <details open class="f-group">
        <summary>Precio</summary>
        <div class="f-range">
          <input type="range" min="0" max="27999" value="0" id="rmin">
          <input type="range" min="0" max="27999" value="27999" id="rmax">
          <div class="f-nums">
            <div>
              <label>$</label>
              <input type="number" id="nmin" min="0" max="27999" value="0">
            </div>
            <span class="dash">—</span>
            <div>
              <label>$</label>
              <input type="number" id="nmax" min="0" max="27999" value="27999">
            </div>
          </div>
        </div>
      </details>

      <details class="f-group">
        <summary>Marca</summary>
        <div class="f-list">
          <label><input type="checkbox"> Iphone</label>
          <label><input type="checkbox"> Samsung</label>
          <label><input type="checkbox"> Huawei</label>
          <label><input type="checkbox"> Honor</label>
          <label><input type="checkbox"> Redmi</label>
          <label><input type="checkbox"> Zte</label>
          <label><input type="checkbox"> Moto G</label>
          <label><input type="checkbox"> Oppo</label>
        </div>
      </details>

      <details class="f-group">
        <summary>Tipo de producto</summary>
        <div class="f-list">
          <label><input type="checkbox"> Fundas</label>
          <label><input type="checkbox"> Micas</label>
          <label><input type="checkbox"> Accesorios</label>
        </div>
      </details>
    </aside>

    <!-- Contenido -->
    <section class="lc-content">
      <h1 class="lc-title">Lanzamientos</h1>

      <div class="lc-grid">
        <!-- Card 1 -->
        <article class="lc-card">
          <img src="imagenes/fundaSamS22.jpg" alt="Funda Samsung">
          <div class="card-body">
            <div class="price">$ 150.00</div>
            <h2 class="name">Funda para Samsung S22</h2>
            <button class="btn btn-primary">Agregar al carrito</button>
          </div>
        </article>

        <!-- Card 2 -->
        <article class="lc-card">
          <img src="imagenes/fundaIphone15PM.jpg" alt="Funda Iphone">
          <div class="card-body">
            <div class="price">$ 150.00</div>
            <h2 class="name">Funda para Iphone 15 ProMax</h2>
            <button class="btn btn-alt">Agregar al carrito</button>
          </div>
        </article>

        <!-- Card 3 -->
        <article class="lc-card">
          <img src="imagenes/fundaOppoA38.jpg" alt="Funda Oppo">
          <div class="card-body">
            <div class="price">$ 150.00</div>
            <h2 class="name">Funda para Oppo A38</h2>
            <button class="btn btn-primary">Agregar al carrito</button>
          </div>
        </article>

        <!-- Card 4 -->
        <article class="lc-card">
          <img src="imagenes/fundaIMotoG73.jpg" alt="Funda Moto G">
          <div class="card-body">
            <div class="price">$ 150.00</div>
            <h2 class="name">Funda para Moto G73</h2>
            <button class="btn btn-alt">Agregar al carrito</button>
          </div>
        </article>

      </div>
    </section>
  </main>

  <footer>
  <p>&copy; 2025 LionCell. Todos los derechos reservados.</p>
</footer>

  

  <!-- sincroniza sliders y números -->
  <script>
    const rmin = document.getElementById('rmin');
    const rmax = document.getElementById('rmax');
    const nmin = document.getElementById('nmin');
    const nmax = document.getElementById('nmax');

    function clamp() {
      if (+rmin.value > +rmax.value) rmin.value = rmax.value;
      if (+nmin.value > +nmax.value) nmin.value = nmax.value;
    }
    function syncFromRange(){
      nmin.value = rmin.value;
      nmax.value = rmax.value;
      clamp();
    }
    function syncFromNumber(){
      rmin.value = nmin.value;
      rmax.value = nmax.value;
      clamp();
    }
    [rmin, rmax].forEach(i => i.addEventListener('input', syncFromRange));
    [nmin, nmax].forEach(i => i.addEventListener('input', syncFromNumber));
  </script>
</body>
</html>