<?php
session_start();
require_once __DIR__ . '/inc/init.php';

if (empty($_SESSION['usuario'])) {
    header("Location: formulario.php");
    exit;
}

$id_cliente = (int)$_SESSION['usuario'];
$id_pedido = (int)($_GET['id'] ?? 0);

// Verificar que el pedido pertenece al usuario
$sql = "SELECT * FROM pedidos WHERE id_pedido = ? AND correo = ? LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pedido, $_SESSION['correo']]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$pedido) {
    die("Pedido no encontrado o no autorizado.");
}

// Traer los productos del pedido
$sqlItems = "SELECT pi.*, p.nombre, p.imagen 
             FROM pedido_items pi
             JOIN productos p ON p.id_producto = pi.id_producto
             WHERE pi.id_pedido = ?";
$stmt = $pdo->prepare($sqlItems);
$stmt->execute([$id_pedido]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle del pedido #<?= $id_pedido ?> - Lion Cell</title>
<link rel="icon" href="imagenes/LogoLionCell.ico">
<style>
:root{
  --brand-1:#1e3a8a;--brand-2:#2563eb;--brand-3:#e6c065;
  --shadow:0 6px 24px rgba(0,0,0,.08);
}
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;margin:0;padding:0;}
main{max-width:800px;margin:30px auto;padding:20px;background:#fff;box-shadow:var(--shadow);border-radius:10px;}
h2{color:#1e3a8a;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:10px;border-bottom:1px solid #ddd;}
th{background:#f0f0f0;}
img{width:60px;height:60px;object-fit:cover;border-radius:8px;}
a.volver{display:inline-block;margin-top:20px;text-decoration:none;color:#2563eb;}
a.volver:hover{text-decoration:underline;}
</style>
</head>
<body>
<main>
<h2>Detalle del pedido #<?= (int)$pedido['id_pedido'] ?></h2>
<p><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']) ?><br>
<strong>Fecha:</strong> <?= htmlspecialchars($pedido['fecha_pedido']) ?><br>
<strong>Total:</strong> $<?= number_format((float)$pedido['total'],2) ?></p>

<table>
<thead>
<tr><th>Producto</th><th>Imagen</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr>
</thead>
<tbody>
<?php foreach($items as $it): ?>
<tr>
  <td><?= htmlspecialchars($it['nombre']) ?></td>
  <td><img src="<?= htmlspecialchars($it['imagen']) ?>" alt=""></td>
  <td><?= (int)$it['cantidad'] ?></td>
  <td>$<?= number_format((float)$it['precio_unit'],2) ?></td>
  <td>$<?= number_format((float)$it['subtotal'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<a href="ver_pedidos.php" class="volver">← Volver a mis pedidos</a>
</main>
</body>
</html>
