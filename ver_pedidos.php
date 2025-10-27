<?php
session_start();
require_once __DIR__ . '/inc/init.php';

if (empty($_SESSION['usuario'])) {
    header("Location: formulario.php");
    exit;
}

$nombreCliente = $_SESSION['nombre'] ?? '';
$correo        = $_SESSION['correo'] ?? '';
$telefono      = $_SESSION['telefono'] ?? '';

// Traer todos los pedidos del cliente (según correo o nombre)
$sql = "SELECT id_pedido, nombre_cliente, telefono, correo, total, estado, fecha_pedido
        FROM pedidos
        WHERE correo = ?
        ORDER BY fecha_pedido DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$correo]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📦 Mis pedidos - Lion Cell</title>
<link rel="icon" href="imagenes/LogoLionCell.ico">
<style>
:root{
  --brand-1:#1e3a8a;--brand-2:#2563eb;--brand-3:#e6c065;
  --shadow:0 6px 24px rgba(0,0,0,.08);
}
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;margin:0;padding:0;}
header{background:linear-gradient(90deg,var(--brand-1),var(--brand-2),var(--brand-3));
  color:#fff;padding:15px 20px;display:flex;align-items:center;gap:20px;}
header img{width:60px;height:60px;border-radius:8px;}
main{max-width:1000px;margin:20px auto;padding:0 20px;}
h1{color:#1e3a8a;text-align:center;}
table{width:100%;border-collapse:collapse;background:#fff;box-shadow:var(--shadow);}
th,td{padding:12px;text-align:left;border-bottom:1px solid #ddd;}
th{background:#f0f0f0;}
.estado{padding:4px 10px;border-radius:8px;font-weight:bold;color:#fff;}
.estado.reservado{background:#2563eb;}
.estado.vendido{background:#16a34a;}
.estado.cancelado{background:#dc2626;}
.estado.expirado{background:#6b7280;}
a.volver{display:inline-block;margin-top:20px;text-decoration:none;color:#2563eb;}
a.volver:hover{text-decoration:underline;}
.btn-detalles{padding:6px 12px;border:none;border-radius:6px;background:#1e3a8a;color:white;cursor:pointer;}
</style>
</head>
<body>
<header>
  <img src="imagenes/LogoLionCell.png" alt="Lion Cell">
  <h1>Mis pedidos</h1>
</header>
<main>
<?php if(empty($pedidos)): ?>
  <p>No tienes pedidos registrados aún.</p>
  <a href="index.php" class="volver">← Volver a productos</a>
<?php else: ?>
  <table>
    <thead>
      <tr>
        <th>ID Pedido</th>
        <th>Fecha</th>
        <th>Estado</th>
        <th>Total</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($pedidos as $p): ?>
        <tr>
          <td>#<?= (int)$p['id_pedido'] ?></td>
          <td><?= date('d/m/Y H:i', strtotime($p['fecha_pedido'])) ?></td>
          <td><span class="estado <?= htmlspecialchars($p['estado']) ?>"><?= htmlspecialchars(ucfirst($p['estado'])) ?></span></td>
          <td>$<?= number_format((float)$p['total'], 2) ?></td>
          <td>
            <form action="ver_pedido_detalle.php" method="get">
              <input type="hidden" name="id" value="<?= (int)$p['id_pedido'] ?>">
              <button class="btn-detalles">Ver detalle</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
<a href="index.php" class="volver">← Volver a productos</a>
</main>
</body>
</html>
