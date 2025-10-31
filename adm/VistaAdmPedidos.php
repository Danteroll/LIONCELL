<?php
// ======= Autorización básica de admin =======
session_start();
if (empty($_SESSION['usuario']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    header("Location: ../formulario.php");
    exit;
}

// ======= Conexión PDO =======
require_once __DIR__ . '/../inc/init.php';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$action = $_POST['action'] ?? '';
$msg = '';
$err = '';

try {
    // --- PEDIDOS: marcar vendido o cancelar ---
    if ($action === 'mark_sold') {
        $id_pedido = (int)($_POST['id_pedido'] ?? 0);
        if ($id_pedido > 0) {
            try {
                $pdo->beginTransaction();

                // Obtiene los productos del pedido
                $stmtItems = $pdo->prepare("
                    SELECT id_producto, cantidad, precio_unit, subtotal
                    FROM pedido_items
                    WHERE id_pedido = ?
                ");
                $stmtItems->execute([$id_pedido]);
                $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                if (empty($items)) {
                    throw new Exception("El pedido no tiene productos asociados.");
                }

                // Verifica disponibilidad de stock
                foreach ($items as $it) {
                    $stmtStock = $pdo->prepare("
                        SELECT COALESCE(stock_actual, 0)
                        FROM inventario
                        WHERE id_producto = ?
                        FOR UPDATE
                    ");
                    $stmtStock->execute([$it['id_producto']]);
                    $stock_disponible = (int)$stmtStock->fetchColumn();

                    if ($stock_disponible < $it['cantidad']) {
                        throw new Exception("❌ Stock insuficiente para el producto ID {$it['id_producto']} (solo hay $stock_disponible unidades).");
                    }
                }

                // Cambia estado del pedido
                $pdo->prepare("UPDATE pedidos SET estado = 'vendido' WHERE id_pedido = ?")
                    ->execute([$id_pedido]);

                // Obtiene total del pedido
                $stmtPedido = $pdo->prepare("SELECT total FROM pedidos WHERE id_pedido = ?");
                $stmtPedido->execute([$id_pedido]);
                $total_pedido = (float)$stmtPedido->fetchColumn();

                // Inserta registro de venta
                $pdo->prepare("
                    INSERT INTO ventas (id_pedido, total, costo_total, ganancia, fecha_venta)
                    VALUES (?, ?, 0, 0, NOW())
                ")->execute([$id_pedido, $total_pedido]);
                $id_venta = $pdo->lastInsertId();

                // Inserta detalle de venta y actualiza el inventario
                $stmtVentaItem = $pdo->prepare("
                    INSERT INTO venta_items (id_venta, id_producto, cantidad, precio_unit, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $costo_total = 0;

                foreach ($items as $it) {
                    $stmtVentaItem->execute([
                        $id_venta,
                        $it['id_producto'],
                        $it['cantidad'],
                        $it['precio_unit'],
                        $it['subtotal']
                    ]);

                    // Obtiene costo del producto
                    $stmtCosto = $pdo->prepare("SELECT costo FROM productos WHERE id_producto = ?");
                    $stmtCosto->execute([$it['id_producto']]);
                    $costo_unit = (float)$stmtCosto->fetchColumn();
                    $costo_total += $costo_unit * $it['cantidad'];

                    // Resta del inventario
                    $pdo->prepare("
                        UPDATE inventario
                        SET stock_actual = stock_actual - ?
                        WHERE id_producto = ?
                    ")->execute([$it['cantidad'], $it['id_producto']]);
                }

                // Calcula la ganancia
                $ganancia = $total_pedido - $costo_total;

                // Actualiza venta con costo_total y ganancia
                $pdo->prepare("
                    UPDATE ventas
                    SET costo_total = ?, ganancia = ?
                    WHERE id_venta = ?
                ")->execute([$costo_total, $ganancia, $id_venta]);

                $pdo->commit();
                $msg = '✅ Pedido marcado como vendido, venta registrada y stock actualizado.';

            } catch (Exception $e) {
                $pdo->rollBack();
                $err = htmlspecialchars($e->getMessage());
            }
        }
    }

    if ($action === 'cancel_pedido') {
        $id_pedido = (int)($_POST['id_pedido'] ?? 0);
        if ($id_pedido > 0) {
            $pdo->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id_pedido = ?")->execute([$id_pedido]);
            $msg = 'Pedido cancelado correctamente.';
        }
    }

    // --- Obtiene todos los pedidos ---
    $sql = "SELECT id_pedido, nombre_cliente, telefono, correo, total, estado, fecha_pedido
            FROM pedidos
            ORDER BY fecha_pedido DESC";
    $pedidos = $pdo->query($sql)->fetchAll();

} catch (Throwable $e) {
    $err = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>📦 Pedidos - Panel de Administración</title>
<link rel="stylesheet" href="estilos.css">
<link rel="icon" href="/../imagenes/LogoLionCell.ico">
<style>
.estado{
  padding:4px 10px;border-radius:8px;font-weight:bold;color:#fff;
}
.estado.en_proceso{background:#2563eb;}
.estado.vendido{background:#16a34a;}
.estado.cancelado{background:#dc2626;}
.table-admin{width:100%;border-collapse:collapse;margin-top:20px;background:#fff;}
.table-admin th,.table-admin td{padding:10px;border-bottom:1px solid #ddd;text-align:left;}
.table-admin th{background:#f2f2f2;}
.detalle-row{display:none;background:#f9fafb;}
.detalle-content{padding:10px 20px;}
.btn-detalle{background:#2563eb;color:#fff;border:none;padding:6px 10px;border-radius:6px;cursor:pointer;}
.btn-detalle:hover{background:#1e3a8a;}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Administración</h2>
  <div class="menu">
    <a href="VistaAdmUsuario.php">👤 Usuarios</a>
    <a href="VistaAdmProducto.php">🛍 Productos</a>
    <a href="VistaAdmPedidos.php">📦 Pedidos</a>
    <a href="VistaAdmVentas.php">📊 Reporte de Ventas</a>
    <a href="VistaAdmInventario.php">📋 Inventario</a>
    <a href="../index.php">Vista de usuario</a>
  </div>
</div>

<div class="main-content">
  <div class="topbar">
    <h3>Gestión de pedidos</h3>
    <div class="user"><span>Administrador</span></div>
  </div>

  <?php if($msg): ?>
    <div style="background:#ecfffa;border:1px solid #a7f3d0;color:#065f46;padding:10px;margin-bottom:10px;"><?=h($msg)?></div>
  <?php endif; ?>
  <?php if($err): ?>
    <div style="background:#fff5f5;border:1px solid #fecaca;color:#991b1b;padding:10px;margin-bottom:10px;">Error: <?=h($err)?></div>
  <?php endif; ?>

  <?php if (empty($pedidos)): ?>
    <p>No hay pedidos registrados.</p>
  <?php else: ?>
    <table class="table-admin">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Teléfono</th>
          <th>Correo</th>
          <th>Total</th>
          <th>Estado</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pedidos as $p): ?>
        <tr>
          <td><?= (int)$p['id_pedido'] ?></td>
          <td><?= h($p['nombre_cliente']) ?></td>
          <td><?= h($p['telefono']) ?></td>
          <td><?= h($p['correo']) ?></td>
          <td>$<?= number_format((float)$p['total'], 2) ?></td>
          <td><span class="estado <?= h($p['estado']) ?>"><?= ucfirst(h($p['estado'])) ?></span></td>
          <td><?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></td>
          <td>
            <button class="btn-detalle" data-id="<?= (int)$p['id_pedido'] ?>">Ver detalle</button>

            <?php if ($p['estado'] !== 'vendido'): ?>
              <form method="post" style="display:inline" onsubmit="return confirm('¿Marcar como vendido este pedido?')">
                <input type="hidden" name="action" value="mark_sold">
                <input type="hidden" name="id_pedido" value="<?= (int)$p['id_pedido'] ?>">
                <button class="btn" style="background:#16a34a;color:white;">Vendido</button>
              </form>
            <?php endif; ?>

            <?php if ($p['estado'] !== 'cancelado'): ?>
              <form method="post" style="display:inline" onsubmit="return confirm('¿Cancelar este pedido?')">
                <input type="hidden" name="action" value="cancel_pedido">
                <input type="hidden" name="id_pedido" value="<?= (int)$p['id_pedido'] ?>">
                <button class="btn" style="background:#ef4444;color:white;">Cancelar</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <tr id="detalle-<?= (int)$p['id_pedido'] ?>" class="detalle-row">
          <td colspan="8" class="detalle-content">Cargando detalles...</td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
// Muestra/oculta detalles dinámicamente
document.querySelectorAll('.btn-detalle').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const row = document.getElementById('detalle-' + id);

    if (row.style.display === 'table-row') {
      row.style.display = 'none';
      return;
    }

    document.querySelectorAll('.detalle-row').forEach(r => r.style.display = 'none');

    row.style.display = 'table-row';
    row.querySelector('.detalle-content').innerHTML = '🔄 Cargando...';

    const response = await fetch('ver_detalle_ajax.php?id=' + id);
    const html = await response.text();
    row.querySelector('.detalle-content').innerHTML = html;
  });
});
</script>

</body>
</html>
