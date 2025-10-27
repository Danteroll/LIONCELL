<?php
session_start();
require_once __DIR__ . '/inc/init.php';

if (empty($_SESSION['usuario'])) {
  header("Location: formulario.php");
  exit;
}

$carrito = $_SESSION['carrito'] ?? [];

if (empty($carrito)) {
  $msg = "Tu carrito está vacío. No se puede generar un pedido.";
  $ok = false;
} else {
  try {
    $pdo->beginTransaction();

    // Datos del cliente (desde sesión)
   $id_cliente     = $_SESSION['usuario'] ?? null;
$nombre_cliente = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['app'] ?? ''));
$correo         = $_SESSION['correo'] ?? '';
$telefono       = $_SESSION['telefono'] ?? '';


    // Crear pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (nombre_cliente, correo, telefono, estado, total)
                           VALUES (?, ?, ?, 'reservado', 0)");
    $stmt->execute([$nombre_cliente, $correo, $telefono]);
    $id_pedido = $pdo->lastInsertId();

    $total_pedido = 0;

    // Preparar sentencias
    $stmtItem = $pdo->prepare("
      INSERT INTO pedido_items (id_pedido, id_producto, cantidad, precio_unit, subtotal)
      VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInv = $pdo->prepare("
      UPDATE inventario
      SET stock_actual = stock_actual - ?
      WHERE id_producto = ? AND stock_actual >= ?
    ");

    foreach ($carrito as $id_producto => $item) {
      $cantidad = (int)$item['cantidad'];
      $precio_unit = (float)$item['precio'];
      $subtotal = $cantidad * $precio_unit;
      $total_pedido += $subtotal;

      $stmtItem->execute([$id_pedido, $id_producto, $cantidad, $precio_unit, $subtotal]);
      $stmtInv->execute([$cantidad, $id_producto, $cantidad]);

      if ($stmtInv->rowCount() === 0) {
        throw new Exception("Stock insuficiente para el producto ID $id_producto.");
      }
    }

    // Actualizar total
    $pdo->prepare("UPDATE pedidos SET total = ? WHERE id_pedido = ?")
        ->execute([$total_pedido, $id_pedido]);

    $pdo->commit();

    unset($_SESSION['carrito']);
    $msg = "✅ Tu pedido fue registrado correctamente.<br><br>
            Se apartaron tus productos del inventario y podrás confirmar el pago en tienda o con un administrador.";
    $ok = true;

  } catch (Exception $e) {
    $pdo->rollBack();
    $msg = "❌ Error al crear el pedido:<br><br>" . htmlspecialchars($e->getMessage());
    $ok = false;
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmación de Pedido – Lion Cell</title>
<link rel="icon" href="imagenes/LogoLionCell.ico">
<style>
:root{
  --brand-1:#1e3a8a;--brand-2:#2563eb;--brand-3:#e6c065;
  --shadow:0 6px 24px rgba(0,0,0,.08);
}
body{
  font-family:Arial,Helvetica,sans-serif;
  background:#f7f7f7;
  margin:0;padding:0;
  display:flex;justify-content:center;align-items:center;
  height:100vh;
}
.card{
  background:#fff;
  border:1px solid rgba(10,10,10,.08);
  border-radius:12px;
  padding:24px;
  text-align:center;
  box-shadow:var(--shadow);
  max-width:500px;
}
h2{
  color:#1e3a8a;margin-bottom:16px;
}
p{
  color:#444;margin-bottom:24px;font-size:1rem;line-height:1.5em;
}
.btn{
  display:inline-block;
  padding:10px 20px;
  margin:6px;
  border:none;
  border-radius:12px;
  font-weight:600;
  text-decoration:none;
  cursor:pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}
.btn:hover{
  transform: translateY(-2px);
  box-shadow:0 4px 16px rgba(0,0,0,.2);
}
.btn-continue{background:#f3f3f3;color:#333;}
.btn-home{background:linear-gradient(90deg,var(--brand-1),var(--brand-2));color:#fff;}
.btn-error{background:#ef4444;color:#fff;}
</style>
</head>
<body>
<div class="card">
  <h2><?= $ok ? '✅ Pedido realizado' : '⚠️ Ocurrió un problema' ?></h2>
  <p><?= $msg ?></p>
  <?php if ($ok): ?>
    <a href="index.php" class="btn btn-home">Volver al inicio</a>
    <a href="ver_pedidos.php" class="btn btn-continue">Ver mis pedidos</a>
  <?php else: ?>
    <a href="compras.php" class="btn btn-error">Volver al carrito</a>
  <?php endif; ?>
</div>
</body>
</html>
