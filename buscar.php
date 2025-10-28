<?php
// buscar.php — Lion Cell (combina diseño de Lanzamientos + lógica de búsqueda)
require_once __DIR__ . '/inc/init.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   1) ENTRADAS (GET) LIMPIAS
   ========================= */
$q       = trim($_GET['q'] ?? '');
$min     = isset($_GET['min']) && $_GET['min'] !== '' ? max(0, (float)$_GET['min']) : null;
$max     = isset($_GET['max']) && $_GET['max'] !== '' ? max(0, (float)$_GET['max']) : null;
$catIn   = $_GET['cat'] ?? [];               // array: ['fundas','micas', ...]
$brands  = $_GET['brand'] ?? [];             // array de marcas (strings)
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

// Si llegó vacío, puedes redirigir o mostrar todo:
// if ($q === '' && !$catIn && is_null($min) && is_null($max) && !$brands) {
//   header('Location: index.php'); exit;
// }

/* =====================================
   2) MAPEO CATEGORÍAS (ajusta a tu DB)
   ===================================== */
$catMap = [
  'fundas'      => 1,
  'micas'       => 2,
  'accesorios'  => 3,

];

// Normaliza seleccionadas a IDs válidos
$catIds = [];
foreach ((array)$catIn as $c) {
  $k = strtolower(trim($c));
  if (isset($catMap[$k])) $catIds[] = (int)$catMap[$k];
}
$catIds = array_values(array_unique($catIds));

/* ==========================================
   3) CONSTRUCCIÓN DE WHERE + PARÁMETROS PDO
   ========================================== */
$where = [];
$paramsWhere = [];

// Texto libre q => divide en términos
if ($q !== '') {
  $terms = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
  $likes = array_map(fn($t) => '%' . $t . '%', $terms);
  foreach ($likes as $like) {
    $where[] = '(p.nombre LIKE ? OR p.sku LIKE ? OR m.nombre LIKE ? OR d.modelo LIKE ?)';
    array_push($paramsWhere, $like, $like, $like, $like);
  }
}

// Categorías
if ($catIds) {
  $place = implode(',', array_fill(0, count($catIds), '?'));
  $where[] = "p.id_categoria IN ($place)";
  foreach ($catIds as $idc) $paramsWhere[] = $idc;
}

// Rango de precios
if (!is_null($min)) { $where[] = 'p.precio >= ?'; $paramsWhere[] = $min; }
if (!is_null($max)) { $where[] = 'p.precio <= ?'; $paramsWhere[] = $max; }

// Marcas
if ($brands) {
  $brandLikes = [];
  foreach ($brands as $b) {
    $brandLikes[] = 'm.nombre LIKE ?';
    $paramsWhere[] = '%' . trim($b) . '%';
  }
  $where[] = '(' . implode(' OR ', $brandLikes) . ')';
}

$whereSql = $where ? implode(' AND ', $where) : '1=1';

/* =================
   4) CONSULTA COUNT
   ================= */
$sqlCount = "
  SELECT COUNT(*) 
  FROM productos p
  LEFT JOIN marcas m       ON m.id_marca = p.id_marca
  LEFT JOIN dispositivos d ON d.id_dispositivo = p.id_dispositivo
  WHERE $whereSql
";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($paramsWhere);
$totalRows  = (int)($stmtCount->fetchColumn() ?: 0);
$totalPages = max(1, (int)ceil($totalRows / $perPage));

/* ========================
   5) CONSULTA DE RESULTADOS
   ======================== */
$sql = "
  SELECT 
    p.id_producto, p.sku, p.nombre, p.precio,
    COALESCE(NULLIF(p.imagen,''), 'imagenes/fallback-producto.jpg') AS imagen,
    m.nombre AS marca, d.modelo
  FROM productos p
  LEFT JOIN marcas m       ON m.id_marca = p.id_marca
  LEFT JOIN dispositivos d ON d.id_dispositivo = p.id_dispositivo
  WHERE $whereSql
  ORDER BY p.nombre ASC
  LIMIT ? OFFSET ?
";
$params = array_merge($paramsWhere, [$perPage, $offset]);
$stmt   = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/* ==================
   6) HELPERS FRONTEND
   ================== */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function urlWith($overrides) {
  $base = $_GET;
  foreach ($overrides as $k=>$v) {
    if ($v === null) unset($base[$k]);
    else $base[$k] = $v;
  }
  return 'buscar.php?' . http_build_query($base);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8" />
<title>Lion Cell – Resultados de búsqueda</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" href="imagenes/LogoLionCell.ico">
<style>
:root{
  --brand-1:#1e3a8a;--brand-2:#2563eb;--brand-3:#e6c065;
  --container:1200px;--shadow:0 6px 24px rgba(0,0,0,.08);
}
body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f7;margin:0}
header{background:linear-gradient(90deg,var(--brand-1),var(--brand-2),var(--brand-3));color:#fff;padding:15px 20px;display:flex;justify-content:space-between;align-items:center}
.logo{display:flex;align-items:center;gap:10px;font-weight:800}
.logo img{width:56px;height:56px;object-fit:contain;border-radius:8px}
.volver-inicio{color:#fff;text-decoration:none;padding:8px 12px;background:#0063f7;border-radius:8px}
.volver-inicio:hover{background:#004aad}
.lc-page{max-width:var(--container);margin:18px auto;padding:0 16px;display:grid;grid-template-columns:280px 1fr;gap:24px}
.lc-filters{background:#fff;border:1px solid rgba(10,10,10,.08);border-radius:12px;padding:16px;height:max-content}
.lc-filters h3{margin:0 0 10px;font-size:16px}
.f-group{border-top:1px solid #eee;padding:10px 0}
.f-group:first-of-type{border-top:none;padding-top:0}
.f-group summary{cursor:pointer;font-weight:700;color:#333}
.f-group[open] summary{color:#0B3B8E}
.f-list{display:flex;flex-direction:column;gap:8px;margin-top:10px}
.f-nums{display:flex;align-items:center;gap:8px;margin-top:10px}
.f-nums input{width:110px;padding:8px 10px;border:1px solid #e6e9f0;border-radius:10px}
.dash{color:#888}
.search-wrap{margin:8px 0 14px}
.search-bar{position:relative;display:flex;align-items:center;max-width:700px}
.search-bar input{width:100%;padding:12px 48px 12px 16px;border-radius:50px;border:1px solid #e6e9f0;outline:none;box-shadow:var(--shadow)}
.search-bar button{position:absolute;right:6px;height:36px;padding:0 12px;border:0;border-radius:12px;background:#2E77FF;color:#fff;cursor:pointer}
.search-bar button:hover{background:#2467e8}
.lc-title{font-size:26px;margin:0 0 8px}
.subtitle{color:#555;margin:0 0 14px}
.grid{display:grid;gap:18px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))}
.card{background:#fff;border:1px solid rgba(10,10,10,.08);border-radius:12px;overflow:hidden;box-shadow:var(--shadow)}
.card .img{height:220px;display:flex;align-items:center;justify-content:center;background:#fff}
.card .img img{max-width:100%;max-height:100%;object-fit:contain}
.card .body{padding:10px 12px 14px}
.meta{color:#6b7280;font-size:.9rem}
.name{font-size:14px;font-weight:600;margin:6px 0 0}
.price{font-weight:800;color:#222;margin-top:6px}
.pager{display:flex;gap:8px;justify-content:center;margin:22px 0}
.pager a,.pager span{padding:8px 12px;border:1px solid #ddd;border-radius:10px;text-decoration:none;color:#111;background:#fff}
.pager .current{background:#2563eb;color:#fff;border-color:#2563eb}
@media (max-width:980px){.lc-page{grid-template-columns:1fr}}

/* Botón agregar al carrito */
.add-cart-form { margin-top:10px; text-align:center; }
.btn-add-cart {
  padding:8px 16px;
  background:linear-gradient(90deg,var(--brand-1),var(--brand-2));
  color:#fff;
  font-weight:600;
  border:none;
  border-radius:12px;
  cursor:pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}
.btn-add-cart:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,.2);
}
</style>
</head>
<body>
<header>
  <div class="logo"><img src="imagenes/LogoLionCell.png" width="60" height="60" alt=""> LION CELL</div>
  <h1 style="margin:0">Búsqueda</h1>
  <a href="index.php" class="volver-inicio">← Volver al inicio</a>
</header>
<main class="lc-page">
<aside class="lc-filters">
<h3>Filtros</h3>
<form id="filtersForm" action="buscar.php" method="get">
<div class="search-wrap">
  <div class="search-bar">
    <input type="search" name="q" placeholder="Encuentra lo que busques..." value="<?= h($q) ?>" autocomplete="off">
    <button type="submit">Buscar</button>
  </div>
</div>
<details open class="f-group">
  <summary>Precio</summary>
  <div class="price-row">
    <div class="input-pre"><span class="prefix">$</span><input type="number" name="min" min="0" step="1" value="<?= h($min ?? '') ?>" placeholder="Mínimo"></div>
    <span class="dash">—</span>
    <div class="input-pre"><span class="prefix">$</span><input type="number" name="max" min="0" step="1" value="<?= h($max ?? '') ?>" placeholder="Máximo"></div>
  </div>
</details>
<details class="f-group">
  <summary>Marca</summary>
  <div class="f-list">
    <?php $marcasUi = ['Iphone','Samsung','Huawei','Honor','Redmi','Zte','Moto','Oppo'];
    foreach ($marcasUi as $mUi):
      $checked = in_array($mUi, (array)$brands, true) ? 'checked' : '';
    ?>
      <label><input type="checkbox" name="brand[]" value="<?= h($mUi) ?>" <?= $checked ?>> <?= h($mUi) ?></label>
    <?php endforeach; ?>
  </div>
</details>
<details class="f-group">
  <summary>Tipo de producto</summary>
  <div class="f-list">
    <?php foreach ($catMap as $slug=>$idc): ?>
      <label>
        <input type="checkbox" name="cat[]" value="<?= h($slug) ?>" <?= in_array($idc, $catIds, true) ? 'checked':'' ?>>
        <?= ucfirst($slug) ?>
      </label>
    <?php endforeach; ?>
  </div>
</details>
<div class="filter-actions">
  <button type="submit" class="btn-apply">Aplicar filtros</button>
  <a href="buscar.php" class="btn-clear">Limpiar</a>
</div>
</form>
</aside>
<section class="lc-content">
<h2 class="lc-title">Resultados</h2>
<p class="subtitle"><?= $totalRows ?> resultado<?= $totalRows===1?'':'s' ?> <?= $q!=='' ? 'para “'.h($q).'”' : '' ?></p>
<?php if (!$rows): ?>
  <p>No encontramos resultados. Prueba con otra palabra o ajusta filtros.</p>
<?php else: ?>
<div class="grid">
  <?php foreach ($rows as $r): ?>
  <article class="card">
    <div class="img">
      <img src="<?= h($r['imagen']) ?>" alt="<?= h($r['nombre']) ?>" loading="lazy">
    </div>
    <div class="body">
      <div class="meta">
        <?= h($r['marca'] ?: '—') ?><?= $r['modelo'] ? ' · '.h($r['modelo']) : '' ?><?= ' · '.h($r['sku']) ?>
      </div>
      <div class="name"><?= h($r['nombre']) ?></div>
      <div class="price">$<?= number_format((float)$r['precio'], 2) ?></div>
      <!-- Botón Agregar al carrito -->
      <form action="agregar_carrito.php" method="post" class="add-cart-form">
        <input type="hidden" name="id_producto" value="<?= h($r['id_producto']) ?>">
        <input type="hidden" name="nombre" value="<?= h($r['nombre']) ?>">
        <input type="hidden" name="precio" value="<?= h($r['precio']) ?>">
        <button type="submit" class="btn-add-cart">🛒 Agregar al carrito</button>
      </form>
    </div>
  </article>
  <?php endforeach; ?>
</div>
<?php if ($totalPages > 1): ?>
<nav class="pager" aria-label="Paginación">
  <?php if ($page > 1): ?><a href="<?= h(urlWith(['page'=>$page-1])) ?>">&laquo; Anterior</a><?php endif; ?>
  <?php $start = max(1, $page - 2); $end = min($totalPages, $page + 2); for ($i=$start; $i<=$end; $i++): ?>
    <?php if ($i === $page): ?><span class="current"><?= $i ?></span>
    <?php else: ?><a href="<?= h(urlWith(['page'=>$i])) ?>"><?= $i ?></a><?php endif; ?>
  <?php endfor; ?>
  <?php if ($page < $totalPages): ?><a href="<?= h(urlWith(['page'=>$page+1])) ?>">Siguiente &raquo;</a><?php endif; ?>
</nav>
<?php endif; ?>
<?php endif; ?>
</section>
</main>
</body>
</html>
