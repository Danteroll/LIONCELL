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
            $pdo->prepare("UPDATE pedidos SET estado = 'vendido' WHERE id_pedido = ?")->execute([$id_pedido]);
            $msg = 'Pedido marcado como vendido.';
        }
    }

    if ($action === 'cancel_pedido') {
        $id_pedido = (int)($_POST['id_pedido'] ?? 0);
        if ($id_pedido > 0) {
            $pdo->prepare("UPDATE pedidos SET estado = 'cancelado' WHERE id_pedido = ?")->execute([$id_pedido]);
            $msg = 'Pedido cancelado correctamente.';
        }
    }

    // --- Obtener todos los pedidos ---
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
<style>
.estado{
  padding:4px 10px;border-radius:8px;font-weight:bold;color:#fff;
}
.estado.reservado{background:#2563eb;}
.estado.vendido{background:#16a34a;}
.estado.cancelado{background:#dc2626;}
.table-admin{width:100%;border-collapse:collapse;margin-top:20px;background:#fff;}
.table-admin th,.table-admin td{padding:10px;border-bottom:1px solid #ddd;text-align:left;}
.table-admin th{background:#f2f2f2;}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <div class="menu">
    <a href="VistaAdmUsuario.php">👤 Usuarios</a>
    <a href="VistaAdmProducto.php">🛍 Productos</a>
    <a href="VistaAdmVentas.php">📊 Reporte de Ventas</a>
    <a href="VistaAdmInventario.php">📋 Inventario</a>
    <a href="../index.php">Vista de Usuario</a>
  </div>
</div>

<div class="main-content">
  <div class="topbar">
    <h3>Gestión de Pedidos</h3>
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
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
</body>
</html>
