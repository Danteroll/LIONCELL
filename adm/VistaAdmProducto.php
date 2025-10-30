<?php
// ======= Autorización básica de admin =======
session_start();
if (empty($_SESSION['usuario']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    header("Location: ../formulario.php");
    exit;
}

// ======= Conexión PDO y utilidades =======
require_once __DIR__ . '/../inc/init.php';
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ======= Catálogos =======
$marcas       = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre")->fetchAll();
$categorias   = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();
$dispositivos = $pdo->query("SELECT id_dispositivo, modelo, id_marca FROM dispositivos ORDER BY modelo")->fetchAll();

// ======= Utilidades =======
function guardarImagenProducto(array $file, string $skuParaNombre): ?string {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Error al subir archivo.');

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
        throw new RuntimeException('Extensión no permitida (jpg, jpeg, png, webp).');
    }

    $dir = __DIR__ . '/../imagenes/productos/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $dest = $dir . $skuParaNombre . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('No se pudo mover la imagen.');
    }
    return 'imagenes/productos/' . $skuParaNombre . '.' . $ext;
}

function nombrePorID(PDO $pdo, string $tabla, string $pk, int $id, string $campo = 'nombre'): string {
    $stmt = $pdo->prepare("SELECT $campo FROM $tabla WHERE $pk = ? LIMIT 1");
    $stmt->execute([$id]);
    return (string)($stmt->fetchColumn() ?: '');
}

$action = $_POST['action'] ?? '';
$msg = ''; $err = '';

try {
   if ($action === 'create_product') {
    $nombre = trim($_POST['nombre'] ?? '');
    $id_m_raw = $_POST['id_marca'] ?? '';
    $id_m = (int)$id_m_raw;
    $id_c = (int)($_POST['id_categoria'] ?? 0);
    $modelo = trim($_POST['modelo'] ?? '');

    $precio = (float)($_POST['precio'] ?? 0);
    $costo  = (float)($_POST['costo'] ?? 0);
    $gasto  = (float)($_POST['gasto'] ?? 0);

    if ($nombre === '') throw new RuntimeException('El nombre del producto es obligatorio.');
    if ($modelo === '') throw new RuntimeException('El modelo es obligatorio.');
    if ($precio <= 0 || $costo <= 0 || $gasto < 0) {
        throw new RuntimeException('Precio, costo y gasto deben ser valores positivos.');
    }

    // Si el usuario seleccionó "Agregar nueva marca"
    if ($id_m_raw === 'nueva') {
        $nombreNuevaMarca = trim($_POST['nueva_marca'] ?? '');
        if ($nombreNuevaMarca === '') {
            throw new RuntimeException('Debes escribir el nombre de la nueva marca.');
        }
        $stmt = $pdo->prepare("INSERT INTO marcas (nombre) VALUES (?)");
        $stmt->execute([$nombreNuevaMarca]);
        $id_m = (int)$pdo->lastInsertId();
    }

    if ($id_m <= 0) throw new RuntimeException('Debes seleccionar una marca.');
    if ($id_c <= 0) throw new RuntimeException('Debes seleccionar una categoría.');

    // Busca o crea modelo en tabla dispositivos
    $stmt = $pdo->prepare("SELECT id_dispositivo FROM dispositivos WHERE modelo = ? AND id_marca = ? LIMIT 1");
    $stmt->execute([$modelo, $id_m]);
    $id_d = (int)$stmt->fetchColumn();

    if ($id_d === 0) {
        $stmt = $pdo->prepare("INSERT INTO dispositivos (modelo, id_marca) VALUES (?, ?)");
        $stmt->execute([$modelo, $id_m]);
        $id_d = (int)$pdo->lastInsertId();
    }

    // Insertar temporalmente sin SKU para obtener el ID
    $sql = "INSERT INTO productos (sku, nombre, id_marca, id_categoria, id_dispositivo, precio, costo, gasto)
            VALUES ('TEMP',?,?,?,?,?,?,?)";
    $pdo->prepare($sql)->execute([$nombre, $id_m, $id_c, $id_d, $precio, $costo, $gasto]);
    $nuevo_id = (int)$pdo->lastInsertId();

    //Generar SKU automáticamente según la marca y categoría
    $marca = strtoupper(substr(nombrePorID($pdo, 'marcas', 'id_marca', $id_m), 0, 3)) ?: 'GEN';
    $cat   = strtoupper(substr(nombrePorID($pdo, 'categorias', 'id_categoria', $id_c), 0, 3)) ?: 'GEN';
    $sku   = "{$cat}-{$marca}-" . str_pad($nuevo_id, 3, '0', STR_PAD_LEFT);

    // Actualizar el SKU real
    $pdo->prepare("UPDATE productos SET sku=? WHERE id_producto=?")->execute([$sku, $nuevo_id]);

    //Guardar imagen (opcional)
    if (!empty($_FILES['imagen']['name'])) {
        $ruta = guardarImagenProducto($_FILES['imagen'], $sku);
        $pdo->prepare("UPDATE productos SET imagen=? WHERE id_producto=?")->execute([$ruta, $nuevo_id]);
    }

    $msg = "✅ Producto creado correctamente (SKU: $sku).";
}

    // ===================   EDITAR PRODUCTO   =====================
   if ($action === 'update_product') {
    $id            = (int)($_POST['id_producto'] ?? 0);
    $sku           = trim($_POST['sku'] ?? '');
    $nombre        = trim($_POST['nombre'] ?? '');
    $id_m_raw      = $_POST['id_marca'] ?? '';
    $id_marca      = (int)$id_m_raw;
    $id_categoria  = (int)($_POST['id_categoria'] ?? 0);
    $modelo        = trim($_POST['modelo'] ?? '');
    $precio        = (float)($_POST['precio'] ?? 0);
    $costo         = (float)($_POST['costo'] ?? 0);
    $gasto         = (float)($_POST['gasto'] ?? 0);

    if ($id <= 0)                      throw new RuntimeException('ID inválido.');
    if ($sku === '' || $nombre === '') throw new RuntimeException('SKU y Nombre son obligatorios.');
    if ($modelo === '')                throw new RuntimeException('El modelo es obligatorio.');
    if ($precio <= 0 || $costo <= 0 || $gasto < 0) {
        throw new RuntimeException('Precio, costo y gasto deben ser valores positivos.');
    }

    //Si el usuario seleccionó "Agregar nueva marca"
    if ($id_m_raw === 'nueva') {
        $nombreNuevaMarca = trim($_POST['nueva_marca'] ?? '');
        if ($nombreNuevaMarca === '') {
            throw new RuntimeException('Debes escribir el nombre de la nueva marca.');
        }
        $stmt = $pdo->prepare("INSERT INTO marcas (nombre) VALUES (?)");
        $stmt->execute([$nombreNuevaMarca]);
        $id_marca = (int)$pdo->lastInsertId();
    }

    if ($id_marca <= 0) throw new RuntimeException('Debes seleccionar una marca.');

    // Busca o crea modelo en dispositivos
    $stmt = $pdo->prepare("SELECT id_dispositivo FROM dispositivos WHERE modelo = ? AND id_marca = ? LIMIT 1");
    $stmt->execute([$modelo, $id_marca]);
    $id_d = (int)$stmt->fetchColumn();

    if ($id_d === 0) {
        $stmt = $pdo->prepare("INSERT INTO dispositivos (modelo, id_marca) VALUES (?, ?)");
        $stmt->execute([$modelo, $id_marca]);
        $id_d = (int)$pdo->lastInsertId();
    }

    // Imagen opcional
    $ruta = (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK)
              ? guardarImagenProducto($_FILES['imagen'], $sku)
              : null;

    if ($ruta) {
        $sql = "UPDATE productos
                  SET sku=?, nombre=?, id_marca=?, id_categoria=?, id_dispositivo=?, precio=?, costo=?, gasto=?, imagen=?
                WHERE id_producto=?";
        $params = [$sku,$nombre,$id_marca,$id_categoria,$id_d,$precio,$costo,$gasto,$ruta,$id];
    } else {
        $sql = "UPDATE productos
                  SET sku=?, nombre=?, id_marca=?, id_categoria=?, id_dispositivo=?, precio=?, costo=?, gasto=?
                WHERE id_producto=?";
        $params = [$sku,$nombre,$id_marca,$id_categoria,$id_d,$precio,$costo,$gasto,$id];
    }
    $pdo->prepare($sql)->execute($params);

    $msg = "✏️ Producto actualizado correctamente.";
}

    // ==================   ELIMINAR PRODUCTO   ====================
    if ($action === 'delete_product') {
        $id = (int)($_POST['id_producto'] ?? 0);
        if ($id <= 0) throw new RuntimeException('ID inválido.');
        $pdo->prepare("DELETE FROM productos WHERE id_producto=?")->execute([$id]);
        $msg = '🗑️ Producto eliminado.';
    }

} catch (Throwable $e) {
    $err = $e->getMessage();
}

// ======= Listado con filtros =======
$q = trim($_GET['q'] ?? '');
$m = (int)($_GET['marca'] ?? 0);
$c = (int)($_GET['cat'] ?? 0);

$where = []; $par = [];
if ($q !== '') { $where[]="(p.nombre LIKE ? OR p.sku LIKE ?)"; $par[]="%$q%"; $par[]="%$q%"; }
if ($m > 0)    { $where[]="p.id_marca=?";     $par[]=$m; }
if ($c > 0)    { $where[]="p.id_categoria=?"; $par[]=$c; }
$w = $where ? 'WHERE '.implode(' AND ', $where) : '';

$sqlListado = "SELECT p.*,
                      COALESCE(NULLIF(p.imagen,''),'imagenes/default.png') AS ruta_img,
                      m.nombre AS marca, c.nombre AS categoria, d.modelo
               FROM productos p
               LEFT JOIN marcas m ON m.id_marca = p.id_marca
               LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
               LEFT JOIN dispositivos d ON d.id_dispositivo = p.id_dispositivo
               $w
               ORDER BY p.id_producto DESC
               LIMIT 200";
$st = $pdo->prepare($sqlListado);
$st->execute($par);
$productosListado = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Productos - Panel de Administración</title>
<link rel="stylesheet" href="estilos.css">
<link rel="icon" href="/../imagenes/LogoLionCell.ico">
<style>
/*estilos para forms */
.form-agregar, .form-editar {background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:16px;}
.form-agregar h4, .form-editar h4 {margin:6px 0 12px 0}
.form-agregar label, .form-editar label {display:block;margin-top:8px;color:#374151}
.form-agregar input, .form-agregar select, .form-editar input, .form-editar select {
  width:100%;max-width:480px;padding:8px;border:1px solid #d1d5db;border-radius:8px;
}
.badge{display:inline-block;padding:2px 8px;background:#eef2ff;color:#3730a3;border-radius:8px;font-size:.85rem;}
.btn{padding:8px 12px;border:none;border-radius:8px;background:#2563eb;color:#fff;cursor:pointer}
.btn:hover{background:#1e3a8a}
</style>
</head>
<body>

  <!-- Contenedor general -->
  <div class="layout">

    <!-- Sidebar -->
    <div class="sidebar">
      <h2>Administración</h2>
      <div class="menu">
        <a href="VistaAdmUsuario.php">👤 Usuarios</a>
        <a href="VistaAdmProducto.php" class="active">🛍 Productos</a>
        <a href="VistaAdmPedidos.php">📦 Pedidos</a>
        <a href="VistaAdmVentas.php">📊 Reporte de Ventas</a>
        <a href="VistaAdmInventario.php">📋 Inventario</a>
        <a href="../index.php">Vista de usuario</a>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="main-content">
      <div class="topbar">
        <h3>Gestión de productos</h3>
        <div class="user"><span>Administrador</span></div>
      </div>
      <?php if($msg): ?>
        <div style="background:#ecfffa;border:1px solid #a7f3d0;color:#065f46;padding:10px;margin-bottom:12px;"><?=h($msg)?></div>
      <?php endif; ?>
      <?php if($err): ?>
        <div style="background:#fff5f5;border:1px solid #fecaca;color:#991b1b;padding:10px;margin-bottom:12px;">Error: <?=h($err)?></div>
      <?php endif; ?>

 <!-- Filtros -->
    <div class="prod-sidebar">
      <form method="get" action="VistaAdmProducto.php#productos" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <input type="text" name="q" placeholder="Buscar por nombre o SKU..." value="<?=h($q)?>" style="flex:1;padding:8px;border-radius:5px;border:1px solid #ccc" />
        <select name="marca">
          <option value="0">Todas las marcas</option>
          <?php foreach($marcas as $mm): ?>
            <option value="<?=$mm['id_marca']?>" <?=$m===$mm['id_marca']?'selected':''?>><?=h($mm['nombre'])?></option>
          <?php endforeach; ?>
        </select>
        <select name="cat">
          <option value="0">Todas las categorías</option>
          <?php foreach($categorias as $cc): ?>
            <option value="<?=$cc['id_categoria']?>" <?=$c===$cc['id_categoria']?'selected':''?>><?=h($cc['nombre'])?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Filtrar</button>
        <a class="btn" style="background:#ccc;color:#000" href="VistaAdmProducto.php#productos">Limpiar</a>
        <button type="button" class="btn" style="background:#00ccff" onclick="abrirFormCrear()">+ Agregar producto</button>
      </form>
    </div>

    <!-- ===== Form CREAR (separado) ===== -->
    <div id="formCrear" class="form-agregar" style="display:none">
      <form method="post" enctype="multipart/form-data" action="VistaAdmProducto.php#productos">
        <input type="hidden" name="action" value="create_product">
        <h4>Agregar nuevo producto</h4>

        <!-- SKU se genera solo -->
        <label>SKU (se generará automáticamente)</label>
        <input id="sku_preview" readonly placeholder="Se generará al guardar" style="background:#f5f5f5;">

        <label>Nombre*</label>
        <input name="nombre" id="c_nombre" required>

        <label>Marca*</label>
        <select name="id_marca" id="c_id_marca" required onchange="toggleNuevaMarca()">
          <option value="">— Selecciona una marca —</option>
          <?php foreach($marcas as $mm): ?>
            <option value="<?=$mm['id_marca']?>" data-pref="<?=strtoupper(substr($mm['nombre'],0,3))?>"><?=h($mm['nombre'])?></option>
          <?php endforeach; ?>
          <option value="nueva">Agregar nueva marca</option>
        </select>

        <div id="wrapNuevaMarca" style="display:none;margin-top:6px">
          <input type="text" name="nueva_marca" id="c_nueva_marca" placeholder="Escribe nueva marca">
        </div>

        <label>Modelo*</label>
        <input type="text" name="modelo" id="c_modelo" placeholder="Ej. Galaxy A55" required>

        <label>Categoría*</label>
        <select name="id_categoria" id="c_id_categoria" required>
          <option value="">— Selecciona una categoría —</option>
          <?php foreach($categorias as $cc): ?>
            <option value="<?=$cc['id_categoria']?>" data-pref="<?=strtoupper(substr($cc['nombre'],0,3))?>"><?=h($cc['nombre'])?></option>
          <?php endforeach; ?>
        </select>

        <label>Precio*</label>
        <input type="number" step="0.01" name="precio" id="c_precio" required min="0.01">

        <label>Costo*</label>
        <input type="number" step="0.01" name="costo" id="c_costo" required min="0.01">

        <label>Gasto*</label>
        <input type="number" step="0.01" name="gasto" id="c_gasto" required min="0.01">

        <label>Imagen (opcional)</label>
        <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp">

        <div style="margin-top:12px;display:flex;gap:8px">
          <button class="btn" type="submit">Guardar</button>
          <button class="btn" type="button" style="background:#ccc;color:#000" onclick="cerrarFormCrear()">Cancelar</button>
        </div>
      </form>
    </div>

    <!-- ===== Form EDITAR (separado) ===== -->
    <div id="formEditar" class="form-editar" style="display:none">
      <form method="post" enctype="multipart/form-data" action="VistaAdmProducto.php#productos">
        <input type="hidden" name="action" value="update_product">
        <input type="hidden" name="id_producto" id="e_id_producto">
        <h4>Editar producto</h4>

        <label>SKU*</label>
        <input name="sku" id="e_sku" required>

        <label>Nombre*</label>
        <input name="nombre" id="e_nombre" required>

        <label>Marca*</label>
        <select name="id_marca" id="e_id_marca" required onchange="toggleNuevaMarca()">
          <option value="">— Selecciona una marca —</option>
          <?php foreach($marcas as $mm): ?>
            <option value="<?=$mm['id_marca']?>"><?=h($mm['nombre'])?></option>
          <?php endforeach; ?>
          <option value="nueva">Agregar nueva marca</option>
        </select>

        <div id="campoNuevaMarca" style="display:none;margin-top:5px;">
          <input type="text" name="nueva_marca" id="nueva_marca" placeholder="Escribe el nombre de la nueva marca">
        </div>

        <script>
        function toggleNuevaMarcaEdit() {
        const sel = document.getElementById('e_id_marca');
        const campo = document.getElementById('campoNuevaMarca');
        campo.style.display = (sel.value === 'nueva') ? 'block' : 'none';
        }
        </script>


        <label>Categoría</label>
        <select name="id_categoria" id="e_id_categoria">
          <option value="0">—</option>
          <?php foreach($categorias as $cc): ?>
            <option value="<?=$cc['id_categoria']?>"><?=h($cc['nombre'])?></option>
          <?php endforeach; ?>
        </select>

        <label>Modelo*</label>
        <input type="text" name="modelo" id="e_modelo" required>


        <label>Precio</label>
        <input type="number" step="0.01" name="precio" id="e_precio">

        <label>Costo</label>
        <input type="number" step="0.01" name="costo" id="e_costo">

        <label>Gasto</label>
        <input type="number" step="0.01" name="gasto" id="e_gasto">

        <label>Imagen (opcional)</label>
        <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp">

        <div style="margin-top:12px;display:flex;gap:8px">
          <button class="btn" type="submit">Guardar cambios</button>
          <button class="btn" type="button" style="background:#ccc;color:#000" onclick="cerrarFormEditar()">Cancelar</button>
        </div>
      </form>
    </div>

    <!-- Lista de productos -->
    <div id="contenido-productos">
      <?php if (!$productosListado): ?>
        <p>No hay productos para mostrar.</p>
      <?php else: ?>
        <?php foreach($productosListado as $p): ?>
          <div class="producto-item">
            <div style="display:flex;gap:12px;align-items:center">
              <img src="<?=h('../' . ltrim($p['ruta_img'], '/'))?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:10px;border:1px solid #ddd">
              <div style="flex:1">
                <b><?=h($p['nombre'])?></b><br>
                <span class="badge"><?=h($p['sku'])?></span>
                <div style="color:#555;margin-top:4px">
                  <?=h($p['marca'] ?: '—')?> • <?=h($p['categoria'] ?: '—')?> • <?=h($p['modelo'] ?: '—')?>
                </div>
              </div>
              <div style="font-weight:700;color:#111">$<?=number_format((float)$p['precio'],2)?></div>
            </div>

            <div style="display:flex;gap:8px;margin-top:10px">
              <button class="btn" style="background:#f59e0b"
              onclick='abrirFormEditar(
                <?= (int)$p["id_producto"] ?>,
                <?= json_encode($p["sku"]) ?>,
                <?= json_encode($p["nombre"]) ?>,
                <?= (int)$p["id_marca"] ?>,
                <?= (int)$p["id_categoria"] ?>,
                <?= json_encode($p["modelo"]) ?>,
                <?= (float)$p["precio"] ?>,
                <?= (float)$p["costo"] ?>,
                <?= (float)$p["gasto"] ?>
              )'>Editar</button>


              <form method="post" action="VistaAdmProducto.php#productos" onsubmit="return confirm('¿Eliminar este producto?')">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                <button class="btn" style="background:#ef4444">Borrar</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  
      ...
    </div>
<script>
function validarCamposNumericos(e) {
  const form = e.target.closest('form');
  const precio = parseFloat(form.querySelector('[name="precio"]').value) || 0;
  const costo  = parseFloat(form.querySelector('[name="costo"]').value)  || 0;
  const gasto  = parseFloat(form.querySelector('[name="gasto"]').value)  || 0;

  if (precio <= 0 || costo <= 0 || gasto < 0) {
    alert('Precio, costo y gasto deben ser números positivos mayores a 0.');
    e.preventDefault();
    return false;
  }
  return true;
}

// Asigna validación a ambos formularios
document.addEventListener('DOMContentLoaded', ()=>{
  const forms = document.querySelectorAll('form[action*="VistaAdmProducto.php"]');
  forms.forEach(f => f.addEventListener('submit', validarCamposNumericos));
});

// ======= Toggle formularios =======
function abrirFormCrear(){
  document.getElementById('formEditar').style.display='none';
  document.getElementById('formCrear').style.display='block';
  // limpia la creación
  document.getElementById('sku_preview').value='';
  ['c_nombre','c_nuevo_modelo','c_precio','c_costo','c_gasto','c_nueva_marca'].forEach(id=>{const el=document.getElementById(id); if(el) el.value='';});
  document.getElementById('c_id_marca').value='';
  document.getElementById('c_id_categoria').value='';
  document.getElementById('wrapNuevaMarca').style.display='none';
  window.scrollTo({top:0,behavior:'smooth'});
}
function cerrarFormCrear(){ document.getElementById('formCrear').style.display='none'; }

function abrirFormEditar(id,sku,nombre,id_marca,id_categoria,modelo,precio,costo,gasto){
  document.getElementById('formCrear').style.display='none';
  const f = document.getElementById('formEditar');
  document.getElementById('e_id_producto').value = id;
  document.getElementById('e_sku').value       = sku || '';
  document.getElementById('e_nombre').value    = nombre || '';
  document.getElementById('e_id_marca').value  = id_marca || 0;
  document.getElementById('e_id_categoria').value = id_categoria || 0;
  document.getElementById('e_modelo').value    = modelo || '';
  document.getElementById('e_precio').value    = (precio ?? '') === 0 ? '' : precio;
  document.getElementById('e_costo').value     = (costo  ?? '') === 0 ? '' : costo;
  document.getElementById('e_gasto').value     = (gasto  ?? '') === 0 ? '' : gasto;
  f.style.display = 'block';
  f.scrollIntoView({behavior:'smooth', block:'start'});
}

function cerrarFormEditar(){ document.getElementById('formEditar').style.display='none'; }

// ======= Lógica UI creación =======
function toggleNuevaMarca(){
  const sel = document.getElementById('c_id_marca');
  document.getElementById('wrapNuevaMarca').style.display = (sel.value === 'nueva') ? 'block' : 'none';
}

// El SKU real se genera al guardar con el ID.
(function(){
  const skuPrev = document.getElementById('sku_preview');
  const selCat  = document.getElementById('c_id_categoria');
  const selMar  = document.getElementById('c_id_marca');
  function updatePreview(){
    const prefCat = selCat.selectedOptions[0]?.dataset?.pref || '';
    const prefMar = selMar.selectedOptions[0]?.dataset?.pref || (selMar.value==='nueva' ? (document.getElementById('c_nueva_marca').value||'NEW').substr(0,3).toUpperCase() : '');
    if(prefCat && prefMar){
      skuPrev.value = `${prefCat}-${prefMar}-XXX`;
    } else {
      skuPrev.value = '';
    }
  }
  selCat.addEventListener('change', updatePreview);
  selMar.addEventListener('change', updatePreview);
  const nm = document.getElementById('c_nueva_marca');
  nm.addEventListener('input', updatePreview);
})();
</script>
  </div>

</body>

</html>
