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
  <title>Información - Lion Cell</title>
  <link rel="icon" href="imagenes/LogoLionCell.ico">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }
    body { background: #fff; color: #3a3a3a; line-height: 1.6; }

    header { background: linear-gradient(90deg, #0063f7, #00afdb); color: white; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between; }
    header h1 { font-size: 1.5rem; }
    header a { color: white; text-decoration: none; font-size: 1rem; background-color: #13005e00; padding: 8px 15px; border-radius: 5px; }

    main { max-width: 1000px; margin: 30px auto; padding: 0 20px; }

    h2 { font-family: 'Montserrat', sans-serif; color: #004aad; font-size: 1.8rem; margin-bottom: 15px; border-bottom: 3px solid #00afdb; display: inline-block; padding-bottom: 5px; }

    section { margin-bottom: 40px; }

    p { margin-bottom: 15px; font-size: 1rem; color: #333; }

    footer { background-color:#2404a1; color:white; text-align:center; padding:20px; margin-top:30px; font-family:'Montserrat', sans-serif; }
    footer a { color: #fff; text-decoration: underline; }
  </style>
</head>
<body>
  <header>
    <h1>Información - Lion Cell</h1>
    <a href="index.php">Volver al inicio</a>
  </header>

  <main>
    <section id="contacto">
      <h2>Contacto</h2>
      <p>📍 Dirección: México</p>
      <p>📞 Teléfono: +52 55 0000 0000</p>
      <p>✉ Correo: contacto@lioncell.com</p>
    </section>

    <section id="aviso-legal">
      <h2>Aviso Legal</h2>
      <p>La información publicada en este sitio es propiedad de Lion Cell. Queda prohibida la reproducción total o parcial de cualquier contenido sin autorización expresa. Lion Cell no se hace responsable por mal uso de los productos adquiridos.</p>
    </section>

    <section id="nuestra-historia">
      <h2>Nuestra Historia</h2>
      <p>Lion Cell nació en 2020 con el objetivo de ofrecer accesorios de calidad para celulares en México. Nos especializamos en fundas, cargadores, micas, audífonos y más, buscando siempre la satisfacción de nuestros clientes.</p>
    </section>
  </main>

  <footer class="footer">
    <p>© 2025 Lion Cell. Todos los derechos reservados.</p>
  </footer>
</body>
</html>
