<?php 
// ======= Autorización básica de admin =======
session_start();
if (empty($_SESSION['usuario']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    header("Location: ../formulario.php");
    exit;
}

// ======= Conexión PDO y utilidades =======
require_once __DIR__ . '/../inc/init.php'; // crea $pdo
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Catálogos para selects
$marcas       = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre")->fetchAll();
$categorias   = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();
$dispositivos = $pdo->query("SELECT id_dispositivo, modelo FROM dispositivos ORDER BY modelo")->fetchAll();

// ======= Acciones =======
$action = $_POST['action'] ?? '';
$msg = ''; $err = '';

try {
    if ($action === 'update_stock') {
        $idProd   = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;
        // Aceptamos enteros >= 0
        $cantidad = isset($_POST['cantidad']) && $_POST['cantidad'] !== '' ? (int)$_POST['cantidad'] : 0;

        $cantidadRaw = $_POST['cantidad'] ?? '';
    // Aceptamos enteros >= 0
    $cantidad = ($cantidadRaw !== '') ? (int)$cantidadRaw : 0;

    // Verifica si la cantidad es decimal
    if (is_numeric($cantidadRaw) && floor($cantidadRaw) != $cantidadRaw) {
        throw new RuntimeException('Datos de stock inválidos. No puede contener decimales.');
    }
        

        if ($idProd <= 0 || $cantidad < 0) {
            throw new RuntimeException('Datos de stock inválidos. No puede ser negativo');
        }

        
$sql = "INSERT INTO inventario (id_producto, stock_actual)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE
            stock_actual = VALUES(stock_actual)";
$pdo->prepare($sql)->execute([$idProd, $cantidad]);


        // Mensaje y redirect para evitar re-envío; permanece en Inventario
        $_SESSION['flash_ok'] = 'Stock actualizado correctamente.';
        header("Location: VistaAdmInventario.php?sec=inventario#inventario");
        exit;
    }
} catch (Throwable $e) {
    $err = $e->getMessage();
}

// ======= Filtros de Inventario (GET) =======
$inv_cat   = (int)($_GET['inv_cat'] ?? 0);
$inv_marca = (int)($_GET['inv_marca'] ?? 0);
$inv_buscar = trim($_GET['inv_buscar'] ?? ''); // ← Nueva variable para la barra de búsqueda

$whereInv = [];
$parInv   = [];

// Filtro por categoría
if ($inv_cat > 0) {
    $whereInv[] = "p.id_categoria = ?";
    $parInv[] = $inv_cat;
}

// Filtro por marca
if ($inv_marca > 0) {
    $whereInv[] = "p.id_marca = ?";
    $parInv[] = $inv_marca;
}

// Filtro por texto (nombre o SKU)
if ($inv_buscar !== '') {
    $whereInv[] = "(p.nombre LIKE ? OR p.sku LIKE ?)";
    $parInv[] = "%$inv_buscar%";
    $parInv[] = "%$inv_buscar%";
}

// Unir condiciones
$wInv = $whereInv ? ('WHERE '.implode(' AND ', $whereInv)) : '';

// ======= Listado de Inventario =======
$sqlInv = "SELECT
             p.id_producto,
             p.sku,
             p.nombre,
             p.precio,
             m.nombre AS marca,
             c.nombre AS categoria,
             d.modelo,
             COALESCE(i.stock_actual, 0) AS cantidad
           FROM productos p
           LEFT JOIN inventario  i ON i.id_producto      = p.id_producto
           LEFT JOIN marcas      m ON m.id_marca         = p.id_marca
           LEFT JOIN categorias  c ON c.id_categoria     = p.id_categoria
           LEFT JOIN dispositivos d ON d.id_dispositivo  = p.id_dispositivo
           $wInv
           ORDER BY c.nombre, m.nombre, d.modelo, p.nombre";
$sti = $pdo->prepare($sqlInv);
$sti->execute($parInv);
$inventarioListado = $sti->fetchAll();

// Mensaje flash tras redirect
if (!empty($_SESSION['flash_ok'])) {
    $msg = $_SESSION['flash_ok'];
    unset($_SESSION['flash_ok']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Panel de Administrador - Negocio de Fundas</title>
<link rel="stylesheet" href="estilos.css">
<link rel="icon" href="/../imagenes/LogoLionCell.ico">
<style>
  .qty-wrap{display:flex;gap:6px;align-items:center}
  .qty-wrap input[type="number"]{width:100px;padding:6px}
  .btn-sm{padding:6px 10px}
</style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Administración</h2>
    <div class="menu">
       <!-- <a href="VistaAdm.php">Catálogo</a>-->
      <a href="VistaAdmUsuario.php">👤 Usuarios</a>
      <a href="VistaAdmProducto.php">🛍 Productos</a>
      <a href="VistaAdmPedidos.php">📦 Pedidos</a>
      <a href="VistaAdmVentas.php">📊 Reporte de Ventas</a>
      <a href="VistaAdmInventario.php" class="active">📋 Inventario</a>
      <a href="../index.php">Vista de usuario</a>
    </div>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="topbar">
      <h3>Inventario de productos</h3>
      <div class="user"><span>Administrador</span></div>
    </div>

    <!-- ===== Inventario (desde BD) ===== -->
    <section id="inventario" class="active">
      <h1> </h1>

      <div class="prod-sidebar">
        <form method="get" action="VistaAdmInventario.php#inventario" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <input type="hidden" name="sec" value="inventario">
          <input type="text" name="inv_buscar" placeholder="Buscar por nombre o SKU..." 
         value="<?= htmlspecialchars($inv_buscar ?? '') ?>" style="flex:1;padding:8px;border-radius:5px;border:1px solid #ccc">
   <select name="inv_cat" title="Categoría">
            <option value="0">Todas las categorías</option>
            <?php foreach($categorias as $cc): ?>
              <option value="<?=$cc['id_categoria']?>" <?= ($inv_cat === (int)$cc['id_categoria']) ? 'selected' : ''; ?>>
                <?=h($cc['nombre'])?>
              </option>
            <?php endforeach; ?>
          </select>

          <select name="inv_marca" title="Marca">
            <option value="0">Todas las marcas</option>
            <?php foreach($marcas as $mm): ?>
              <option value="<?=$mm['id_marca']?>" <?= ($inv_marca === (int)$mm['id_marca']) ? 'selected' : ''; ?>>
                <?=h($mm['nombre'])?>
              </option>
            <?php endforeach; ?>
          </select>

          <button class="btn" type="submit">Filtrar</button>
          <a class="btn" style="background:#ccc;color:#000" href="VistaAdmInventario.php#inventario">Limpiar</a>
        </form>
      </div>

      <div id="contenido-inventario">
        <?php if ($msg): ?>
          <div class="producto-item" style="background:#ecfffa;border:1px solid #a7f3d0;color:#065f46"><?=h($msg)?></div>
        <?php endif; ?>
        <?php if ($err): ?>
          <div class="producto-item" style="background:#fff5f5;border:1px solid #fecaca;color:#991b1b">Error: <?=h($err)?></div>
        <?php endif; ?>

        <?php if (empty($inventarioListado)): ?>
          <p>No hay productos en inventario.</p>
        <?php else: ?>
          <table class="inventario-table">
            <thead>
              <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Modelo</th>
                <th>Precio</th>
                <th style="min-width:160px">Cantidad</th>
                <th>Editar</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($inventarioListado as $row): 
                    $pid = (int)$row['id_producto'];
                    $qty = (int)$row['cantidad'];
              ?>
                <tr>
                  <td><?= $pid ?></td>
                  <td><?= h($row['sku']) ?></td>
                  <td><?= h($row['nombre']) ?></td>
                  <td><?= h($row['marca'] ?: '—') ?></td>
                  <td><?= h($row['categoria'] ?: '—') ?></td>
                  <td><?= h($row['modelo'] ?: '—') ?></td>
                  <td>$<?= number_format((float)$row['precio'], 2) ?></td>

                  <!-- Cantidad actual + edición -->
                  <td>
                    <form method="post" class="qty-wrap" id="f-<?= $pid ?>">
                      <input type="hidden" name="action" value="update_stock">
                      <input type="hidden" name="id_producto" value="<?= $pid ?>">

                      <input type="number" name="cantidad" id="q-<?= $pid ?>" min="0" value="<?= $qty ?>">
                 
                    </form>
                  </td>

                  <td>
                    <button class="btn" type="button" onclick="document.getElementById('f-<?= $pid ?>').submit()">Guardar</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </section>
  </div><!-- /main-content -->

<script>
  function stepQty(id, delta){
    const inp = document.getElementById('q-'+id);
    const v = parseInt(inp.value || '0', 10);
    const n = Math.max(0, v + delta);
    inp.value = n;
  }

  // (Opcional) mantener sección desde ?sec= o #hash
  (function initSection(){
    const params = new URLSearchParams(location.search);
    const secByParam = params.get('sec');
    const secByHash  = location.hash ? location.hash.substring(1) : '';
    const section    = secByParam || secByHash || 'inventario';
    // en esta vista solo hay inventario como activo
  })();
</script>
</body>
</html>
