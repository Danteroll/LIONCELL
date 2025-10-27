<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$carrito = $_SESSION['carrito'] ?? [];
$total = 0;

// Manejo de POST: actualización y eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar cantidades
    if (isset($_POST['cantidades'])) {
        foreach ($_POST['cantidades'] as $id => $cant) {
            $cant = max(1, (int)$cant);
            if (isset($carrito[$id])) $carrito[$id]['cantidad'] = $cant;
        }
    }

    // Eliminar producto
    if (isset($_POST['eliminar_producto'])) {
        $idEliminar = $_POST['eliminar_producto'];
        if (isset($carrito[$idEliminar])) unset($carrito[$idEliminar]);
    }

    $_SESSION['carrito'] = $carrito;
    header('Location: compras.php');
    exit;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🛒 Compras - Lion Cell</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="imagenes/LogoLionCell.ico">
<style>
:root{--brand-1:#1e3a8a;--brand-2:#2563eb;--brand-3:#e6c065;--container:1200px;--shadow:0 6px 24px rgba(0,0,0,.08);}
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;margin:0;padding:0;}
header{background:linear-gradient(90deg,var(--brand-1),var(--brand-2),var(--brand-3));color:#fff;padding:15px 20px;display:flex;align-items:center;gap:20px;}
header img{width:60px;height:60px;border-radius:8px;}
main{max-width:var(--container);margin:20px auto;padding:0 16px;}
h2{margin-top:0;}
table{width:100%;border-collapse:collapse;background:#fff;box-shadow:var(--shadow);}
th,td{padding:12px;text-align:left;border-bottom:1px solid #ddd;}
th{background:#f0f0f0;}
input[type=number]{width:60px;padding:6px;border-radius:6px;border:1px solid #ccc;}
button{padding:6px 12px;border:none;border-radius:6px;background:#2563eb;color:#fff;cursor:pointer;}
button:hover{background:#1e3a8a;}
.total-row td{font-weight:800;text-align:right;}
a.back{display:inline-block;margin-top:12px;color:#2563eb;text-decoration:none;}
a.back:hover{text-decoration:underline;}
.btn-pedir{display:inline-block;margin-top:20px;padding:10px 20px;border:none;border-radius:8px;background:#22c55e;color:#fff;cursor:pointer;}
.btn-pedir:hover{background:#16a34a;}
</style>
</head>
<body>
<header>
<img src="imagenes/LogoLionCell.png" alt="Lion Cell"> <h1>Tu carrito de compras</h1>
</header>
<main>
<?php if(empty($carrito)): ?>
  <p>No tienes productos en el carrito.</p>
  <a href="index.php" class="back">← Volver a productos</a>
<?php else: ?>
<form method="post">
<table>
<tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th>Acción</th></tr>
<?php foreach($carrito as $id => $item):
  $subtotal = $item['precio'] * $item['cantidad'];
  $total += $subtotal;
?>
<tr>
<td><?= h($item['nombre']) ?></td>
<td>$<?= number_format($item['precio'],2) ?></td>
<td><input type="number" name="cantidades[<?= $id ?>]" value="<?= $item['cantidad'] ?>" min="1" onchange="this.form.submit()"></td>
<td>$<?= number_format($subtotal,2) ?></td>
<td><button type="submit" name="eliminar_producto" value="<?= $id ?>">Eliminar</button></td>
</tr>
<?php endforeach; ?>
<tr class="total-row">
<td colspan="3">Total:</td>
<td colspan="2">$<?= number_format($total,2) ?></td>
</tr>
</table>
</form>

<form action="procesar_pedido.php" method="post" onsubmit="return confirm('¿Confirmar pedido?');">
  <button type="submit" class="btn-pedir">Pedir ahora</button>
</form>

<a href="index.php" class="back">🛍 Seguir comprando</a>
<?php endif; ?>
</main>
</body>
</html>
