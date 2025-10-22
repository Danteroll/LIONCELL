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
  <title>Micas de Celular</title>
  <link rel="stylesheet" href="estilos.css">
  <style>
    body { font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0; }

    header { background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    header h1 { margin: 0; font-size: 1.5rem; }
    header a { color: white; text-decoration: none; font-size: 1rem; background-color: #13005e00; padding: 8px 15px; border-radius: 5px; }
    
    .section-title { text-align: center; font-size: 1.3rem; margin-top: 25px; color: #2404a1; }
    .filters-container { text-align: center; margin: 20px auto; }
    .filters-container label { font-weight: bold; margin-right: 10px; }
    .filters-container select { padding: 8px; font-size: 1rem; margin: 0 10px 10px 0; }

    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; max-width: 1200px; margin: 0 auto 40px auto; padding: 0 20px; }
    .card { background-color: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; padding: 10px; transition: transform 0.2s; }
    .card:hover { transform: scale(1.03); }
    .card img { width: 100%; height: 165px; border-radius: 10px; object-fit: cover; }
    .marca-modelo { font-size: 0.9rem; color: #555; margin-bottom: 5px; }
    .volver-inicio { display:inline-block; margin:20px; padding:10px 15px; background:#0063f7; color:white; text-decoration:none; border-radius:5px; transition: background 0.3s; }
    .volver-inicio:hover { background:#004aad; }

   footer { background-color: #646464ff; color: white; text-align: center; padding: 15px; margin-top: 30px; }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <div class="logo"><img src="imagenes/LogoLionCell.png" width="60" height="60"> LION CELL</div>
    <h1>Micas de Celular</h1>
    <a href="index.php" class="volver-inicio">← Volver al inicio</a>
  </header>

  <!-- Filtros centrados -->
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

  <!-- Grid de micas -->
  <section class="grid" id="micaGrid">
    <div class="card" data-marca="Motorola" data-modelo="G73">
      <img src="micaMotoG73.jpg" alt="Mica Motorola G73">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Motorola | Modelo: G73</div>
      <p>$149 MXN</p>
    </div>
    <div class="card" data-marca="Samsung" data-modelo="A54">
      <img src="micaSamsungA54.jpg" alt="Mica Samsung A54">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Samsung | Modelo: A54</div>
      <p>$159 MXN</p>
    </div>
    <div class="card" data-marca="Apple" data-modelo="iPhone13">
      <img src="micaIphone13.jpg" alt="Mica iPhone 13">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Apple | Modelo: iPhone 13</div>
      <p>$199 MXN</p>
    </div>
    <div class="card" data-marca="Xiaomi" data-modelo="RedmiNote12">
      <img src="micaXiaomiRedmi.jpg" alt="Mica Xiaomi Redmi">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Xiaomi | Modelo: Redmi Note 12</div>
      <p>$139 MXN</p>
    </div>
    <div class="card" data-marca="Samsung" data-modelo="S22">
      <img src="micaSamsungS22.jpg" alt="Mica Samsung S22">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Samsung | Modelo: S22</div>
      <p>$169 MXN</p>
    </div>
    <div class="card" data-marca="Apple" data-modelo="iPhone14">
      <img src="micaIphone14.jpg" alt="Mica iPhone 14">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Apple | Modelo: iPhone 14</div>
      <p>$219 MXN</p>
    </div>
    <div class="card" data-marca="Motorola" data-modelo="G82">
      <img src="micaMotorolaG82.jpg" alt="Mica Motorola G82">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Motorola | Modelo: G82</div>
      <p>$149 MXN</p>
    </div>
    <div class="card" data-marca="Xiaomi" data-modelo="Redmi11">
      <img src="micaXiaomiRedmi11.jpg" alt="Mica Xiaomi Redmi 11">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Xiaomi | Modelo: Redmi 11</div>
      <p>$129 MXN</p>
    </div>
    <div class="card" data-marca="Oppo" data-modelo="Reno8">
      <img src="micaOppoReno8.jpg" alt="Mica Oppo Reno 8">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Oppo | Modelo: Reno 8</div>
      <p>$149 MXN</p>
    </div>
    <div class="card" data-marca="Oppo" data-modelo="Reno9">
      <img src="micaOppoReno9.jpg" alt="Mica Oppo Reno 9">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Oppo | Modelo: Reno 9</div>
      <p>$159 MXN</p>
    </div>
    <div class="card" data-marca="Samsung" data-modelo="Note20">
      <img src="micaSamsungNote20.jpg" alt="Mica Samsung Note 20">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Samsung | Modelo: Note 20</div>
      <p>$169 MXN</p>
    </div>
    <div class="card" data-marca="Apple" data-modelo="iPhoneSE">
      <img src="micaIphoneSE.jpg" alt="Mica iPhone SE">
      <h3>Mica Protector</h3>
      <div class="marca-modelo">Marca: Apple | Modelo: iPhone SE</div>
      <p>$139 MXN</p>
    </div>
  </section>

  <footer>
    <p>&copy; 2025 LionCell. Todos los derechos reservados.</p>
  </footer>

  <script>
    const marcaSelect = document.getElementById('marcaSelect');
    const modeloSelect = document.getElementById('modeloSelect');
    const cards = document.querySelectorAll('#micaGrid .card');

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
