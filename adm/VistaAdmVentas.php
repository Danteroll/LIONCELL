<?php 
session_start();
if (empty($_SESSION['usuario']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
  header("Location: ../formulario.php");
  exit;
}

require_once __DIR__ . '/../inc/init.php';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$msg = '';
$err = '';
$action = $_POST['action'] ?? '';

try {
  // === REGISTRAR VENTA LOCAL (PUNTO DE VENTA) ===
  if ($action === 'registrar_venta') {
    $id_producto = (int)($_POST['id_producto'] ?? 0);
    $cantidad    = (int)($_POST['cantidad'] ?? 0);

    if ($id_producto <= 0 || $cantidad <= 0) {
      throw new Exception("Datos de venta inválidos.");
    }

    // Obtener precio, costo y stock
    $stmt = $pdo->prepare("
      SELECT p.nombre, p.precio, p.costo, COALESCE(i.stock_actual,0) AS stock_actual
      FROM productos p
      LEFT JOIN inventario i ON i.id_producto = p.id_producto
      WHERE p.id_producto = ?
    ");
    $stmt->execute([$id_producto]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$prod) throw new Exception("Producto no encontrado.");

    $precio = (float)$prod['precio'];
    $costo  = (float)($prod['costo'] ?? 0);
    $stock_actual = (int)$prod['stock_actual'];

    if ($cantidad > $stock_actual) {
      throw new Exception("Stock insuficiente. Solo hay $stock_actual unidades disponibles.");
    }

    $subtotal = $precio * $cantidad;
    $ganancia = ($precio - $costo) * $cantidad;

    // === Registrar venta general (id_pedido NULL para local) ===
    $pdo->prepare("INSERT INTO ventas (id_pedido, total, fecha_venta) VALUES (NULL, ?, NOW())")->execute([$subtotal]);
    $id_venta = $pdo->lastInsertId();

    // === Insertar detalle ===
    $pdo->prepare("
      INSERT INTO venta_items (id_venta, id_producto, cantidad, precio_unit, subtotal)
      VALUES (?, ?, ?, ?, ?)
    ")->execute([$id_venta, $id_producto, $cantidad, $precio, $subtotal]);

    // === Actualizar inventario ===
    $pdo->prepare("
      UPDATE inventario
      SET stock_actual = stock_actual - ?
      WHERE id_producto = ?
    ")->execute([$cantidad, $id_producto]);

    $msg = "✅ Venta local registrada correctamente (ID Venta #$id_venta).";
  }

  // === CONSULTAR VENTAS POR PEDIDOS (clientes) ===
  $ventasPedidos = $pdo->query("
    SELECT 
      p.id_pedido,
      p.nombre_cliente,
      p.total,
      p.fecha_pedido,
      SUM(pi.cantidad * (pr.precio - COALESCE(pr.costo, 0))) AS ganancia
    FROM pedidos p
    LEFT JOIN pedido_items pi ON pi.id_pedido = p.id_pedido
    LEFT JOIN productos pr ON pr.id_producto = pi.id_producto
    WHERE p.estado = 'vendido'
    GROUP BY p.id_pedido
    ORDER BY p.fecha_pedido DESC
  ")->fetchAll();

  // === Ver items de pedido específico ===
  $pedidoItems = [];
  if (isset($_GET['ver_items'])) {
    $id_pedido = (int)$_GET['ver_items'];
    $stmt = $pdo->prepare("
      SELECT pi.id_producto, p.nombre, pi.cantidad, pi.precio_unit, pi.subtotal
      FROM pedido_items pi
      JOIN productos p ON p.id_producto = pi.id_producto
      WHERE pi.id_pedido = ?
    ");
    $stmt->execute([$id_pedido]);
    $pedidoItems = $stmt->fetchAll();
  }

  // === Ventas de punto de venta (registradas manualmente) ===
  $ventasPunto = $pdo->query("
    SELECT 
      v.id_venta,
      v.total,
      v.fecha_venta,
      GROUP_CONCAT(CONCAT(p.nombre,' × ',vi.cantidad) SEPARATOR ', ') AS productos,
      COALESCE(SUM(vi.cantidad * (p.precio - COALESCE(p.costo,0))), 0) AS ganancia
    FROM ventas v
    LEFT JOIN venta_items vi ON vi.id_venta = v.id_venta
    LEFT JOIN productos p ON p.id_producto = vi.id_producto
    WHERE v.id_pedido IS NULL 
    GROUP BY v.id_venta
    ORDER BY v.fecha_venta DESC
  ")->fetchAll();

  // === GANANCIAS TOTALES POR FECHA ===
  $resumenFechas = $pdo->query("
    SELECT DATE(v.fecha_venta) AS fecha,
           SUM(v.total) AS total_vendido,
           SUM(vi.cantidad*(p.precio - COALESCE(p.costo,0))) AS ganancia
    FROM ventas v
    LEFT JOIN venta_items vi ON vi.id_venta = v.id_venta
    LEFT JOIN productos p ON p.id_producto = vi.id_producto
    GROUP BY DATE(v.fecha_venta)
    ORDER BY fecha DESC
  ")->fetchAll();

  // === PRODUCTOS CON MARCA ===
  $productos = $pdo->query("
    SELECT p.id_producto, p.nombre, p.precio, COALESCE(i.stock_actual,0) AS stock, m.nombre AS marca
    FROM productos p
    LEFT JOIN inventario i ON i.id_producto = p.id_producto
    LEFT JOIN marcas m ON m.id_marca = p.id_marca
    ORDER BY p.nombre ASC
  ")->fetchAll();

} catch (Throwable $e) {
  $err = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>📊 Reporte de Ventas - Lion Cell</title>
<link rel="stylesheet" href="estilos.css">
<link rel="icon" href="/../imagenes/LogoLionCell.ico">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
h2{margin-top:0;}
.tabs{display:flex;gap:10px;margin-bottom:20px;}
.tab-btn{padding:10px 20px;border:none;border-radius:8px;cursor:pointer;background:#ddd;color:#333;font-weight:600;}
.tab-btn.active{background:#2563eb;color:#fff;}
.section-content{display:none;}
.section-content.active{display:block;}
.table-admin{width:100%;border-collapse:collapse;background:#fff;margin-top:10px;}
.table-admin th,.table-admin td{padding:10px;border-bottom:1px solid #ddd;text-align:left;}
.table-admin th{background:#f2f2f2;}
.success{background:#ecfffa;border:1px solid #a7f3d0;color:#065f46;padding:10px;margin-bottom:10px;}
.error{background:#fff5f5;border:1px solid #fecaca;color:#991b1b;padding:10px;margin-bottom:10px;}
input,select{padding:8px;border:1px solid #ccc;border-radius:6px;}
button{padding:8px 14px;border:none;border-radius:6px;background:#2563eb;color:#fff;cursor:pointer;}
button:hover{background:#1e3a8a;}
.select2-container .select2-selection--single{height:38px;}
</style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <div class="menu">
    <a href="VistaAdmUsuario.php">👤 Usuarios</a>
    <a href="VistaAdmProducto.php">🛍 Productos</a>
    <a href="VistaAdmPedidos.php">📦 Pedidos</a>
    <a href="VistaAdmVentas.php" class="active">📊 Reporte de Ventas</a>
    <a href="VistaAdmInventario.php">📋 Inventario</a>
    <a href="../index.php">Vista de Usuario</a>
  </div>
</div>

<div class="main-content">
  <div class="topbar">
    <h3>Reporte de Ventas</h3>
    <div class="user"><span>Administrador</span></div>
  </div>

  <?php if($msg): ?><div class="success"><?=h($msg)?></div><?php endif; ?>
  <?php if($err): ?><div class="error"><?=h($err)?></div><?php endif; ?>

  <div class="tabs">
    <button class="tab-btn active" onclick="showTab('pedidos')">🧾 Ventas por pedidos</button>
    <button class="tab-btn" onclick="showTab('local')">🏪 Ventas punto de venta</button>
    <button class="tab-btn" onclick="showTab('resumen')">📊 Ganancia total</button>
  </div>

  <!-- === SECCIÓN 1: VENTAS DE PEDIDOS === -->
<div id="pedidos" class="section-content active">
  <h2>Ventas por pedidos</h2>
  <?php if(empty($ventasPedidos)): ?>
    <p>No hay pedidos vendidos registrados.</p>
  <?php else: ?>
    <table class="table-admin">
      <thead>
        <tr>
          <th>ID Pedido</th>
          <th>Cliente</th>
          <th>Total</th>
          <th>Ganancia</th>
          <th>Fecha</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($ventasPedidos as $v): ?>
          <tr>
            <td><?=$v['id_pedido']?></td>
            <td><?=h($v['nombre_cliente'])?></td>
            <td>$<?=number_format($v['total'],2)?></td>
            <td style="color:#16a34a;">+$<?=number_format($v['ganancia'] ?? 0,2)?></td>
            <td><?=date('d/m/Y H:i',strtotime($v['fecha_pedido']))?></td>
            <td>
              <button class="btn toggle-items" data-id="<?=$v['id_pedido']?>">Ver productos</button>
            </td>
          </tr>
          <!-- Fila oculta con los productos -->
          <tr class="items-row" id="items-<?=$v['id_pedido']?>" style="display:none;background:#fafafa;">
            <td colspan="6">
              <div class="loading">Cargando productos...</div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<script>
// === Mostrar/Ocultar productos del pedido ===
document.querySelectorAll('.toggle-items').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const row = document.getElementById('items-' + id);

    if (row.style.display === 'none') {
      // Mostrar (expandir)
      row.style.display = 'table-row';
      const div = row.querySelector('.loading');
      div.textContent = 'Cargando productos...';
      try {
        const res = await fetch('obtener_items_pedido.php?id_pedido=' + id);
        const html = await res.text();
        div.innerHTML = html;
        btn.textContent = 'Ocultar productos';
      } catch (e) {
        div.textContent = '❌ Error al cargar productos.';
      }
    } else {
      // Ocultar
      row.style.display = 'none';
      btn.textContent = 'Ver productos';
    }
  });
});
</script>


  <!-- === SECCIÓN 2: PUNTO DE VENTA === -->
  <div id="local" class="section-content">
    <h2>Ventas locales</h2>
    <form method="post" id="formVentaLocal" style="margin-bottom:15px;">
      <input type="hidden" name="action" value="registrar_venta">
      <select name="id_producto" id="id_producto" required style="width:300px;">
        <option value="">Seleccionar producto</option>
        <?php foreach($productos as $p): ?>
          <option value="<?=$p['id_producto']?>" data-stock="<?=$p['stock']?>" data-nombre="<?=h($p['nombre'])?>" data-marca="<?=h($p['marca'])?>" data-precio="<?=number_format($p['precio'],2,'.','')?>">
            <?=h($p['nombre'])?> — <?=h($p['marca'])?> — $<?=number_format($p['precio'],2)?>
          </option>
        <?php endforeach; ?>
      </select>
      <label style="margin-left:8px;">Cantidad:</label>
      <input type="number" name="cantidad" id="cantidad" min="1" value="1" required>
      <button type="submit">Registrar venta</button>
      <div id="info-stock" style="margin-top:8px;font-size:0.95em;color:#444;"></div>
    </form>

    <?php if(empty($ventasPunto)): ?>
      <p>No hay ventas locales registradas.</p>
    <?php else: ?>
      <table class="table-admin">
        <thead><tr><th>ID Venta</th><th>Productos</th><th>Total</th><th>Ganancia</th><th>Fecha</th></tr></thead>
        <tbody>
          <?php foreach($ventasPunto as $v): ?>
            <tr>
              <td><?=$v['id_venta']?></td>
              <td><?=h($v['productos'])?></td>
              <td>$<?=number_format($v['total'],2)?></td>
              <td style="color:#16a34a;">+$<?=number_format($v['ganancia'],2)?></td>
              <td><?=date('d/m/Y H:i',strtotime($v['fecha_venta']))?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- === SECCIÓN 3: RESUMEN GANANCIAS === -->
  <div id="resumen" class="section-content">
    <h2>Ganancia total por fecha</h2>
    <?php if(empty($resumenFechas)): ?>
      <p>No hay registros de ventas aún.</p>
    <?php else: ?>
      <table class="table-admin">
        <thead><tr><th>Fecha</th><th>Total Vendido</th><th>Ganancia</th></tr></thead>
        <tbody>
          <?php foreach($resumenFechas as $r): ?>
            <tr>
              <td><?=date('d/m/Y',strtotime($r['fecha']))?></td>
              <td>$<?=number_format($r['total_vendido'],2)?></td>
              <td style="color:#16a34a;">+$<?=number_format($r['ganancia'],2)?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<script>
function showTab(id){
  document.querySelectorAll('.section-content').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  event.target.classList.add('active');
}

$(document).ready(function() {
  $('#id_producto').select2({
    placeholder: "Escribe para buscar producto...",
    allowClear: true
  });
});

// Mostrar stock disponible al seleccionar producto
const selectProd = document.getElementById('id_producto');
const inputCant = document.getElementById('cantidad');
const infoStock = document.getElementById('info-stock');

selectProd.addEventListener('change', () => {
  const opt = selectProd.options[selectProd.selectedIndex];
  const stock = parseInt(opt.dataset.stock || 0);
  const marca = opt.dataset.marca || '';
  if (opt.value) {
    infoStock.innerHTML = `📦 Stock disponible: <b>${stock}</b> unidades<br>🏷️ Marca: <b>${marca}</b>`;
  } else {
    infoStock.textContent = '';
  }
});

// Validar cantidad antes de enviar
document.getElementById('formVentaLocal').addEventListener('submit', (e) => {
  const opt = selectProd.options[selectProd.selectedIndex];
  const stock = parseInt(opt.dataset.stock || 0);
  const cant = parseInt(inputCant.value || 0);
  const nombre = opt.dataset.nombre || 'Producto';

  if (!opt.value) {
    alert('Por favor selecciona un producto válido.');
    e.preventDefault();
    return;
  }

  if (cant <= 0) {
    alert('La cantidad debe ser mayor a 0.');
    e.preventDefault();
    return;
  }

  if (cant > stock) {
    alert(`❌ No puedes vender ${cant} unidades de "${nombre}".\n📦 Solo hay ${stock} disponibles.`);
    e.preventDefault();
  }
});
</script>
</body>
</html>
