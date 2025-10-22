<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FUNDAS</title>
  <link rel="stylesheet" href="estilos.css">
  <style>
    body { font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0; }
    header { background: linear-gradient(90deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%); }
    header h1 { margin: 0; font-size: 1.5rem; }
    header a { color: white; text-decoration: none; font-size: 1rem; background-color: #13005e00; padding: 8px 15px; border-radius: 5px; }
    .section-title { text-align: center; font-size: 1.3rem; margin-top: 25px; color: #2404a1; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; padding: 20px; max-width: 1200px; margin: 10px auto 30px auto; }
    .card { background-color: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); text-align: center; padding: 10px; transition: transform 0.2s; }
    .card:hover { transform: scale(1.03); }
    .card img { width: 100%; height: 165px; border-radius: 10px; object-fit: cover;  align-items: center; }
    footer { background-color: #646464ff; color: white; text-align: center; padding: 15px; margin-top: 30px; }
     .volver-inicio { display:inline-block; margin:20px; padding:10px 15px; background:#0063f7; color:white; text-decoration:none; border-radius:5px; transition: background 0.3s; }
    .volver-inicio:hover { background:#004aad; }
  </style>
</head>
<body>
  <header>
     <!-- HEADER -->
    <div class="logo"><img src="imagenes/LogoLionCell.png" width="60" height="60"> LION CELL</div>
    <h1>FUNDAS</h1>
    <a href="index.php" class="volver-inicio">← Volver al inicio</a>
  </header>

  <h2 class="section-title">Fundas Disponibles</h2>
  <section class="grid">
    <div class="card">
      <img src="imagenes/fundaIMotoG73.jpg" alt="Funda Motorola G73">
      <h3>Funda Motorola G73</h3>
      <p>$100 MXN</p>
    </div>

    <div class="card">
      <img src="imagenes/fundaSamsungA54.jpg" alt="Funda Samsung A54">
      <h3>Funda Samsung A54</h3>
      <p>$120 MXN</p>
    </div>

    <div class="card">
      <img src="imagenes/fundaXiaomiRedmi.jpg" alt="Funda Xiaomi Redmi">
      <h3>Funda Xiaomi Redmi</h3>
      <p>$90 MXN</p>
    </div>

    <div class="card">
      <img src="imagenes/fundaIPhone14.jpg" alt="Funda iPhone 14">
      <h3>Funda iPhone 14</h3>
      <p>$150 MXN</p>
    </div>
  </section>


  <footer>
    <p>&copy; 2025 LionCell. Todos los derechos reservados.</p>
  </footer>
</body>
</html>
