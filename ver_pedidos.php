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
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
:root {
  --brand-1:#1e3a8a;
  --brand-2:#2563eb;
  --brand-3:#e6c065;
  --container:1200px;
  --shadow:0 6px 24px rgba(0,0,0,.08);
}
body {
  font-family:Arial, Helvetica, sans-serif;
  background:#f7f7f7;
  margin:0;
  padding:0;
}

/* 🔹 Encabezado */
header {
  background:linear-gradient(90deg, var(--brand-1), var(--brand-2), var(--brand-3));
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:15px 20px;
}
.header-left {
  display:flex;
  align-items:center;
  gap:15px;
}
header img {
  width:60px;
  height:60px;
  border-radius:8px;
}
header h1 {
  color:#fff;
  margin:0;
  font-size:1.6rem;
  font-weight:bold;
}

/* 🔵 Botón azul arriba a la derecha */
.btn-top-right {
  background:#2563eb;
  color:#fff;
  padding:8px 18px;
  text-decoration:none;
  border-radius:8px;
  font-weight:500;
  font-size:16px;
  box-shadow:0 2px 6px rgba(0,0,0,0.2);
  transition:background 0.2s ease, transform 0.1s ease;
}
.btn-top-right:hover {
  background:#1e3a8a;
  transform:scale(1.03);
}

/* 🔹 Contenido principal */
main {
  max-width:var(--container);
  margin:20px auto;
  padding:0 16px;
  background:#fff;
  box-shadow:var(--shadow);
  border-radius:10px;
}
table {
  width:100%;
  border-collapse:collapse;
  margin-top:10px;
}
th, td {
  padding:12px;
  text-align:left;
  border-bottom:1px solid #ddd;
}
th {
  background:#f0f0f0;
}
.estado {
  padding:5px 10px;
  border-radius:5px;
  font-weight:bold;
  color:#fff;
}
.estado.en_proceso { background:#2563eb; }
.estado.vendido { background:#16a34a; }
.estado.cancelado { background:#dc2626; }
.estado.expirado { background:#6b7280; }
.btn-detalles {
  background:#2563eb;
  color:#fff;
  border:none;
  border-radius:6px;
  padding:6px 12px;
  cursor:pointer;
}
.btn-detalles:hover { background:#1e3a8a; }
p {
  font-size:1.1rem;
}
</style>
</head>

<body>
<header>
  <div class="header-left">
    <img src="imagenes/LogoLionCell.png" alt="Lion Cell">
    <h1>Mis pedidos</h1>
  </div>
  <a href="index.php" class="btn-top-right">← Volver a inicio</a>
</header>

<main>
<?php if(empty($pedidos)): ?>
  <p>No tienes pedidos registrados aún.</p>
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
</main>

</body>
</html>
