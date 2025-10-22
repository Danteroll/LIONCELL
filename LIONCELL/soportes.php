<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Soportes de Celular</title>
  <link rel="stylesheet" href="estilos.css">
  <style>
    body { font-family: Arial, sans-serif; background-color: #f7f7f7; margin:0; padding:0;}
    header { background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%); color:white; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; }
    header h1 { margin:0; font-size:1.5rem; }
    header a { color:white; text-decoration:none; padding:8px 15px; border-radius:5px; }
    .filters-container { text-align:center; margin:20px auto; }
    .filters-container label { font-weight:bold; margin-right:10px; }
    .filters-container select { padding:8px; font-size:1rem; margin:0 10px 10px 0; }
    .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:15px; max-width:1200px; margin:0 auto 40px auto; padding:0 20px; }
    .card { background:white; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); text-align:center; padding:10px; transition: transform 0.2s; }
    .card:hover { transform:scale(1.03); }
    .card img { width:100%; height:165px; border-radius:10px; object-fit:cover; }
    .marca-modelo { font-size:0.9rem; color:#555; margin-bottom:5px; }
    footer { background-color:  #646464ff; color:white; text-align:center; padding:15px; margin-top:30px; }
     .volver-inicio { display:inline-block; margin:20px; padding:10px 15px; background:#0063f7; color:white; text-decoration:none; border-radius:5px; transition: background 0.3s; }
    .volver-inicio:hover { background:#004aad; }
  </style>
</head>
<body>
<header>
    <div class="logo"><img src="imagenes/LogoLionCell.png" width="60" height="60"> LION CELL</div>
    <h1>Soportes</h1>
    <a href="index.php" class="volver-inicio">← Volver al inicio</a>
  </header>

<div class="filters-container">
  <label for="marcaSelect">Marca:</label>
  <select id="marcaSelect">
    <option value="">Todas las marcas</option>
    <option value="Motorola">Motorola</option>
    <option value="Samsung">Samsung</option>
    <option value="Apple">Apple</option>
    <option value="Xiaomi">Xiaomi</option>
    <option value="Oppo">Oppo</option>
  </select>

  <label for="modeloSelect">Modelo:</label>
  <select id="modeloSelect">
    <option value="">Todos los modelos</option>
    <option value="G73">G73</option>
    <option value="G82">G82</option>
    <option value="A54">A54</option>
    <option value="S22">S22</option>
    <option value="Note20">Note20</option>
    <option value="iPhone13">iPhone 13</option>
    <option value="iPhone14">iPhone 14</option>
    <option value="iPhoneSE">iPhone SE</option>
    <option value="RedmiNote12">Redmi Note 12</option>
    <option value="Redmi11">Redmi 11</option>
    <option value="Reno8">Reno 8</option>
    <option value="Reno9">Reno 9</option>
  </select>
</div>

<section class="grid" id="soporteGrid">
  <div class="card" data-marca="Samsung" data-modelo="S22">
    <img src="soporteSamsungS22.jpg" alt="Soporte Samsung S22">
    <h3>Soporte Ajustable</h3>
    <div class="marca-modelo">Marca: Samsung | Modelo: S22</div>
    <p>$249 MXN</p>
  </div>

  <div class="card" data-marca="Apple" data-modelo="iPhone14">
    <img src="soporteIphone14.jpg" alt="Soporte iPhone 14">
    <h3>Soporte Universal</h3>
    <div class="marca-modelo">Marca: Apple | Modelo: iPhone 14</div>
    <p>$269 MXN</p>
  </div>

  <!-- Agrega más soportes aquí -->
</section>

<footer>
  <p>&copy; 2025 LionCell. Todos los derechos reservados.</p>
</footer>

<script>
const marcaSelect = document.getElementById('marcaSelect');
const modeloSelect = document.getElementById('modeloSelect');
const cards = document.querySelectorAll('#soporteGrid .card');

function filterCards() {
  const selectedMarca = marcaSelect.value;
  const selectedModelo = modeloSelect.value;

  cards.forEach(card => {
    const cardMarca = card.getAttribute('data-marca');
    const cardModelo = card.getAttribute('data-modelo');

    if ((selectedMarca === "" || cardMarca === selectedMarca) &&
        (selectedModelo === "" || cardModelo === selectedModelo)) {
      card.style.display = "block";
    } else {
      card.style.display = "none";
    }
  });
}

marcaSelect.addEventListener('change', filterCards);
modeloSelect.addEventListener('change', filterCards);
</script>
</body>
</html>
