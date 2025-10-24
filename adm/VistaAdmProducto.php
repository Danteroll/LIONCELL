<?php
// ======= Autorización básica de admin =======
session_start();
if (empty($_SESSION['usuario']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    header("Location: ../formulario.php");
    exit;
}

// ======= Conexión PDO y utilidades =======
require_once __DIR__ . '/../inc/init.php'; // ← ruta correcta
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Catálogos para selects
$marcas       = $pdo->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre")->fetchAll();
$categorias   = $pdo->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll();
$dispositivos = $pdo->query("SELECT id_dispositivo, modelo FROM dispositivos ORDER BY modelo")->fetchAll();

// ======= Subida de imagen =======
function guardarImagenProducto(array $file, string $sku): ?string {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Error al subir archivo.');

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
        throw new RuntimeException('Extensión no permitida (jpg, jpeg, png, webp).');
    }

    // Guardar fuera de /adm, en /imagenes/productos
    $dir = __DIR__ . '/../imagenes/productos/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $dest = $dir . $sku . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('No se pudo mover la imagen.');
    }
    // Ruta relativa que se guarda en BD
    return 'imagenes/productos/' . $sku . '.' . $ext;
}

// ======= Acciones CRUD =======
$action = $_POST['action'] ?? '';
$msg = ''; $err = '';

try {
    if ($action === 'create_product') {
        $sku    = trim($_POST['sku'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $id_m   = (int)($_POST['id_marca'] ?? 0);
        $id_c   = (int)($_POST['id_categoria'] ?? 0);

        // Normaliza id_dispositivo
        $id_d = isset($_POST['id_dispositivo']) && $_POST['id_dispositivo'] !== '' && $_POST['id_dispositivo'] !== '0'
                  ? (int)$_POST['id_dispositivo']
                  : null;

        // Si la categoría es Accesorios => id_dispositivo = NULL
        $accId = $pdo->query("SELECT id_categoria FROM categorias WHERE LOWER(nombre)='accesorios' LIMIT 1")->fetchColumn();
        if ($accId && (int)$id_c === (int)$accId) {
            $id_d = null;
        }

        $precio = (float)($_POST['precio'] ?? 0);
        $costo  = (float)($_POST['costo'] ?? 0);
        $gasto  = (float)($_POST['gasto'] ?? 0);

        if ($sku === '' || $nombre === '') throw new RuntimeException('SKU y Nombre son obligatorios.');

        $ruta = !empty($_FILES['imagen']) ? guardarImagenProducto($_FILES['imagen'], $sku) : null;

        $sql = "INSERT INTO productos (sku, nombre, id_marca, id_categoria, id_dispositivo, precio, costo, gasto, imagen)
                VALUES (?,?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$sku,$nombre,$id_m,$id_c,$id_d,$precio,$costo,$gasto,$ruta]);

        $msg = 'Producto creado correctamente.';
    }

    if ($action === 'update_product') {
        $id     = (int)($_POST['id_producto'] ?? 0);
        $sku    = trim($_POST['sku'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $id_m   = (int)($_POST['id_marca'] ?? 0);
        $id_c   = (int)($_POST['id_categoria'] ?? 0);

        // Normaliza id_dispositivo
        $id_d = (isset($_POST['id_dispositivo']) && $_POST['id_dispositivo'] !== '' && $_POST['id_dispositivo'] !== '0')
                  ? (int)$_POST['id_dispositivo']
                  : null;

        // Si la categoría es Accesorios => id_dispositivo = NULL
        $accId = $pdo->query("SELECT id_categoria FROM categorias WHERE LOWER(nombre)='accesorios' LIMIT 1")->fetchColumn();
        if ($accId && (int)$id_c === (int)$accId) {
            $id_d = null;
        }

        $precio = (float)($_POST['precio'] ?? 0);
        $costo  = (float)($_POST['costo'] ?? 0);
        $gasto  = (float)($_POST['gasto'] ?? 0);

        if ($id <= 0) throw new RuntimeException('ID inválido.');
        if ($sku === '' || $nombre === '') throw new RuntimeException('SKU y Nombre son obligatorios.');

        $ruta = (!empty($_FILES['imagen']) && $_FILES['imagen']['error']!==UPLOAD_ERR_NO_FILE)
                ? guardarImagenProducto($_FILES['imagen'], $sku)
                : null;

        if ($ruta) {
            $sql = "UPDATE productos
                    SET sku=?, nombre=?, id_marca=?, id_categoria=?, id_dispositivo=?, precio=?, costo=?, gasto=?, imagen=?
                    WHERE id_producto=?";
            $params = [$sku,$nombre,$id_m,$id_c,$id_d,$precio,$costo,$gasto,$ruta,$id];
        } else {
            $sql = "UPDATE productos
                    SET sku=?, nombre=?, id_marca=?, id_categoria=?, id_dispositivo=?, precio=?, costo=?, gasto=?
                    WHERE id_producto=?";
            $params = [$sku,$nombre,$id_m,$id_c,$id_d,$precio,$costo,$gasto,$id];
        }
        $pdo->prepare($sql)->execute($params);
        $msg = 'Producto actualizado correctamente.';
    }

    if ($action === 'delete_product') {
        $id = (int)($_POST['id_producto'] ?? 0);
        if ($id <= 0) throw new RuntimeException('ID inválido.');
        $pdo->prepare("DELETE FROM productos WHERE id_producto=?")->execute([$id]);
        $msg = 'Producto eliminado.';
    }

} catch (Throwable $e) {
    $err = $e->getMessage();
}

// ======= Listado (Productos) con filtros =======
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
<title>Panel de Administrador - Negocio de Fundas</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <div class="menu">
       <!-- <a href="VistaAdm.php">Catálogo</a>-->
      <a href="VistaAdmUsuario.php">👤 Usuarios</a>
      <a href="VistaAdmProducto.php" class="active">🛍 Productos</a>
      <a href="VistaAdmVentas.php">📊 Reporte de Ventas</a>
      <a href="VistaAdmInventario.php">📋 Inventario</a>
      <a href="../index.php">Vista de Usuario</a>
    </div>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="topbar">
      <h3>Panel de Administración</h3>
      <div class="user"><span>Administrador</span></div>
    </div>

    <!-- ===== Productos (con BD) ===== -->
    <section id="productos" class="active">
      <h1>Gestión de Productos</h1>

      <div class="prod-sidebar">
        <!-- Importante: acción a esta misma página para no saltar a catálogo -->
        <form method="get" action="VistaAdmProducto.php#productos" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <input type="hidden" name="sec" value="productos">
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
          <button type="button" class="btn" style="background:#00ccff" onclick="toggleFormCrear()">+ Agregar producto</button>
        </form>
      </div>

      <?php if($msg): ?>
        <div class="producto-item" style="background:#ecfffa;border:1px solid #a7f3d0;color:#065f46"><?=h($msg)?></div>
      <?php endif; ?>
      <?php if($err): ?>
        <div class="producto-item" style="background:#fff5f5;border:1px solid #fecaca;color:#991b1b">Error: <?=h($err)?></div>
      <?php endif; ?>

      <!-- Form Crear/Editar -->
      <div id="formProducto" class="form-agregar" style="display:none">
        <form method="post" enctype="multipart/form-data" action="VistaAdmProducto.php#productos">
          <input type="hidden" name="action" id="formAction" value="create_product">
          <input type="hidden" name="id_producto" id="id_producto">
          <h4 id="formTitulo">Agregar nuevo producto</h4>

          <label>SKU*</label>
          <input name="sku" id="sku" required>

          <label>Nombre*</label>
          <input name="nombre" id="nombre" required>

          <label>Marca</label>
          <select name="id_marca" id="id_marca">
            <option value="0">—</option>
            <?php foreach($marcas as $mm): ?>
              <option value="<?=$mm['id_marca']?>"><?=h($mm['nombre'])?></option>
            <?php endforeach; ?>
          </select>

          <label>Categoría</label>
          <select name="id_categoria" id="id_categoria">
            <option value="0">—</option>
            <?php foreach($categorias as $cc): ?>
              <option value="<?=$cc['id_categoria']?>"><?=h($cc['nombre'])?></option>
            <?php endforeach; ?>
          </select>

          <label>Dispositivo (modelo)</label>
          <select name="id_dispositivo" id="id_dispositivo">
            <option value="0">—</option>
            <?php foreach($dispositivos as $dd): ?>
              <option value="<?=$dd['id_dispositivo']?>"><?=h($dd['modelo'])?></option>
            <?php endforeach; ?>
          </select>

          <label>Precio</label>
          <input type="number" step="0.01" name="precio" id="precio">

          <label>Costo</label>
          <input type="number" step="0.01" name="costo" id="costo">

          <label>Gasto</label>
          <input type="number" step="0.01" name="gasto" id="gasto">

          <label>Imagen (opcional)</label>
          <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp">

          <div style="margin-top:10px;display:flex;gap:8px">
            <button class="btn" type="submit">Guardar</button>
            <button class="btn" type="button" style="background:#ccc;color:#000" onclick="toggleCerrar()">Cancelar</button>
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
                <button class="btn" style="background:#f59e0b" onclick='editarProducto(
                  <?= (int)$p["id_producto"] ?>,
                  <?= json_encode($p["sku"]) ?>,
                  <?= json_encode($p["nombre"]) ?>,
                  <?= (int)$p["id_marca"] ?>,
                  <?= (int)$p["id_categoria"] ?>,
                  <?= (int)$p["id_dispositivo"] ?>,
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
    </section>
  </div>

<script>
  // Mantener la sección visible (esta página solo muestra productos)
  function showSection(){}

  // Form Crear/Editar
function toggleCerrar() {
  const f = document.getElementById('formProducto');
  if (f.style.display === 'block') {
    // Si ya está abierto → solo ocultar
    f.style.display = 'none';
    return;
  }}

  function toggleFormCrear(){
    const f = document.getElementById('formProducto');
    resetFormProducto();
    f.style.display = (f.style.display==='block' ? 'none' : 'block');
  }
  function resetFormProducto(){
    const f = document.getElementById('formProducto');
    document.getElementById('formTitulo').textContent = 'Agregar nuevo producto';
    document.getElementById('formAction').value = 'create_product';
    document.getElementById('id_producto').value = '';
    ['sku','nombre','precio','costo','gasto'].forEach(id=>{
      const el=document.getElementById(id); if(el) el.value='';
    });
    ['id_marca','id_categoria','id_dispositivo'].forEach(id=>{
      const el=document.getElementById(id); if(el) el.value='0';
    });
  }
  function editarProducto(id, sku, nombre, id_marca, id_categoria, id_dispositivo, precio, costo, gasto){
    const f = document.getElementById('formProducto');
    document.getElementById('formTitulo').textContent = 'Editar producto';
    document.getElementById('formAction').value = 'update_product';
    document.getElementById('id_producto').value = id;
    document.getElementById('sku').value = sku || '';
    document.getElementById('nombre').value = nombre || '';
    document.getElementById('id_marca').value = id_marca || 0;
    document.getElementById('id_categoria').value = id_categoria || 0;
    document.getElementById('id_dispositivo').value = id_dispositivo || 0;
    document.getElementById('precio').value = (precio ?? '') === 0 ? '' : precio;
    document.getElementById('costo').value  = (costo  ?? '') === 0 ? '' : costo;
    document.getElementById('gasto').value  = (gasto  ?? '') === 0 ? '' : gasto;
    f.style.display = 'block';
    f.scrollIntoView({behavior:'smooth', block:'center'});
  }
</script>
</body>
</html>
