<?php
// agregar_carrito.php
session_start();

// Recoger datos del POST
$id_producto = $_POST['id_producto'] ?? null;
$nombre      = $_POST['nombre'] ?? '';
$precio      = $_POST['precio'] ?? 0;

if (!$id_producto) {
    die('Producto no válido');
}

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar producto al carrito o aumentar cantidad
if (isset($_SESSION['carrito'][$id_producto])) {
    $_SESSION['carrito'][$id_producto]['cantidad'] += 1;
} else {
    $_SESSION['carrito'][$id_producto] = [
        'nombre' => $nombre,
        'precio' => $precio,
        'cantidad' => 1,
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Producto agregado – Lion Cell</title>
<link rel="icon" href="imagenes/LogoLionCell.ico">
<style>
:root{
    --brand-1:#1e3a8a;--brand-2:#2563eb;--brand-3:#e6c065;
    --container:800px;--shadow:0 6px 24px rgba(0,0,0,.08);
}
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;margin:0;padding:0;display:flex;justify-content:center;align-items:center;height:100vh;}
.card{background:#fff;border:1px solid rgba(10,10,10,.08);border-radius:12px;padding:24px;text-align:center;box-shadow:var(--shadow);max-width:400px;}
h2{color:#1e3a8a;margin-bottom:16px;}
p{color:#555;margin-bottom:24px;font-size:1rem;}
.btn {display:inline-block;padding:10px 20px;margin:6px;border:none;border-radius:12px;font-weight:600;text-decoration:none;cursor:pointer;transition: transform 0.2s, box-shadow 0.2s;}
.btn-continue{background:#f3f3f3;color:#333;}
.btn-continue:hover{transform: translateY(-2px);box-shadow:0 4px 16px rgba(0,0,0,.2);}
.btn-cart{background:linear-gradient(90deg,var(--brand-1),var(--brand-2));color:#fff;}
.btn-cart:hover{transform: translateY(-2px);box-shadow:0 4px 16px rgba(0,0,0,.2);}
</style>
</head>
<body>
<div class="card">
    <h2>✅ Producto agregado al carrito</h2>
    <p><strong><?= htmlspecialchars($nombre) ?></strong> se ha agregado correctamente.</p>
    <a href="buscar.php" class="btn btn-continue">Seguir comprando</a>
    <a href="compras.php" class="btn btn-cart">Ir al carrito 🛒</a>
</div>
</body>
</html>
