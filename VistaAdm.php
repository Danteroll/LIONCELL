<?php
// ======= Autorización básica de admin =======
session_start();
if (empty($_SESSION['usuario']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    header("Location: ../formulario.php");
    exit;
}

// ======= Conexión PDO y utilidades =======
require_once __DIR__ . '/inc/init.php'; // crea $pdo
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
    if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) throw new RuntimeException('Extensión no permitida (jpg, jpeg, png, webp).');

    $dir = __DIR__ . '/imagenes/productos/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $dest = $dir . $sku . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException('No se pudo mover la imagen.');
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
        $id_d   = (int)($_POST['id_dispositivo'] ?? 0);
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
        $id_d   = (int)($_POST['id_dispositivo'] ?? 0);
        $id_d = isset($_POST['id_dispositivo']) && $_POST['id_dispositivo'] !== '' && $_POST['id_dispositivo'] !== '0'
                  ? (int)$_POST['id_dispositivo']
                  : null;
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
    // --- USUARIOS: eliminar o cambiar rol ---
if ($action === 'delete_user') {
    $id = (int)($_POST['id_usuario'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
        $msg = 'Usuario eliminado correctamente.';
    }
}

if ($action === 'make_admin') {
    $id = (int)($_POST['id_usuario'] ?? 0);
    if ($id > 0) {
        $pdo->prepare("UPDATE usuarios SET role_id = 1 WHERE id = ?")->execute([$id]);
        $msg = 'Usuario actualizado a administrador.';
    }
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

// ======= Inventario (todo, sin límite) =======
$inv_cat   = (int)($_GET['inv_cat'] ?? 0);
$inv_marca = (int)($_GET['inv_marca'] ?? 0);

$whereInv = []; $parInv = [];
if ($inv_cat > 0)   { $whereInv[]="p.id_categoria=?"; $parInv[]=$inv_cat; }
if ($inv_marca > 0) { $whereInv[]="p.id_marca=?";     $parInv[]=$inv_marca; }
$wInv = $whereInv ? 'WHERE '.implode(' AND ', $whereInv) : '';

$sqlInv = "SELECT p.*, m.nombre AS marca, c.nombre AS categoria, d.modelo
           FROM productos p
           LEFT JOIN marcas m ON m.id_marca = p.id_marca
           LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
           LEFT JOIN dispositivos d ON d.id_dispositivo = p.id_dispositivo
           $wInv
           ORDER BY c.nombre, m.nombre, d.modelo, p.nombre";
$sti = $pdo->prepare($sqlInv);
$sti->execute($parInv);
$inventarioListado = $sti->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Panel de Administrador - Negocio de Fundas</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
  body { display: flex; height: 100vh; background: #f4f6f8; }

  /* Sidebar con tu gradiente original */
  .sidebar {
    width: 250px;
    background: linear-gradient(180deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%);
    color: white;
    display: flex; flex-direction: column; padding: 20px;
  }
  .sidebar h2 { text-align: center; margin-bottom: 30px; }
  .menu a {
    display: block; padding: 12px; color: white; text-decoration: none;
    border-radius: 8px; transition: background 0.3s;
  }
  .menu a:hover, .menu a.active { background: rgba(255,255,255,0.2); }

  /* Contenido principal */
  .main-content { flex: 1; display: flex; flex-direction: column; }
  .topbar {
    background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 15px 25px; display: flex; justify-content: space-between; align-items: center;
  }
  .topbar h3 { color: #333; }

  section { flex: 1; padding: 25px; display: none; overflow-y: auto; }
  section.active { display: block; }
  h1 { color: #0066ff; margin-bottom: 15px; }

  /* Botones */
  button, .btn {
    background: #0066ff; color: white; border: none; padding: 8px 12px;
    border-radius: 6px; cursor: pointer; transition: background 0.3s;
  }
  button:hover, .btn:hover { background: #0052cc; }

  /* Tarjetas/Items */
  .producto-item {
    background: white; border: 1px solid #ddd; padding: 15px; border-radius: 10px;
    margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }
  .prod-sidebar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:15px; }
  .prod-sidebar button { background:#0066ff; color:#fff; border-radius:8px; }

  .filtros { display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap; }
  .filtros select, .filtros input {
    padding:8px; border-radius:5px; border:1px solid #ccc; flex:1; min-width:180px;
  }

  .form-agregar {
    background:#fff; border:1px solid #ccc; padding:15px; border-radius:10px;
    margin-top:10px; box-shadow:0 2px 5px rgba(0,0,0,0.05);
    display:none; animation:fadeIn 0.3s ease;
  }
  .form-agregar input, .form-agregar select { width:100%; padding:8px; margin-top:4px; }

  /* Tabla */
  table { width:100%; border-collapse:collapse; margin-top:10px; background:white; border-radius:8px; overflow:hidden; }
  th, td { padding:10px; border-bottom:1px solid #eee; text-align:left; }
  thead { background:#f0f8ff; color:#0066ff; }
  tbody tr:hover { background:#fafcff; }

  @keyframes fadeIn { from{opacity:0; transform:translateY(-10px);} to{opacity:1; transform:translateY(0);} }

  .badge { display:inline-block; background:#eef; color:#334; font-size:12px; padding:2px 6px; border-radius:6px; margin-top:2px; }
</style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <div class="menu">
      <a href="#" onclick="showSection('usuarios')">👤 Usuarios</a>
      <a href="#" onclick="showSection('productos')">🛍 Productos</a>
      <a href="#" onclick="showSection('reporte')">📊 Reporte de Ventas</a>
      <a href="#" onclick="showSection('inventario')">📋 Inventario</a>
      <a href="index.php">Vista de Usuario</a>
    </div>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="topbar">
      <h3>Panel de Administración</h3>
      <div class="user"><span>Administrador</span></div>
    </div>

    <!-- ===== Catálogo (placeholder con tu estilo) ===== -->
    <!--<section id="catalogo" class="active">
      <h1>Catálogo de Productos</h1>
      <div class="prod-sidebar">
        <button onclick="mostrarCatalogoCategoria('fundas')">Fundas</button>
        <button onclick="mostrarCatalogoCategoria('micas')">Micas</button>
        <button onclick="mostrarCatalogoCategoria('audifonos')">Audífonos</button>
        <button onclick="mostrarCatalogoCategoria('cargadores')">Cargadores</button>
        <button onclick="mostrarCatalogoCategoria('soportes')">Soportes</button>
        <button onclick="mostrarCatalogoCategoria('memoria')">Memoria</button>
      </div>
      <div id="contenido-catalogo">
        <p>Selecciona una categoría para ver los productos disponibles.</p>
      </div>
    </section>-->

        <!-- === USUARIOS === -->
    <section id="usuarios">
      <h1>Gestión de Usuarios</h1>

      <?php
      try {
          // Trae todos los usuarios registrados
          $usuarios = $pdo->query("
              SELECT 
                  nombre,
                  app,
                  apm,
                  correo,
                  usuario,
                  role_id,
                  id,
                  CASE role_id 
                      WHEN 1 THEN 'Administrador'
                      ELSE 'Usuario'
                  END AS rol
              FROM usuarios
              ORDER BY role_id DESC, nombre ASC
          ")->fetchAll();
      } catch (PDOException $e) {
          echo '<div class="producto-item" style="background:#fff5f5;border:1px solid #fecaca;color:#991b1b">
                  Error al obtener usuarios: ' . htmlspecialchars($e->getMessage()) . '
                </div>';
          $usuarios = [];
      }
      ?>

      <?php if (empty($usuarios)): ?>
        <p>No hay usuarios registrados aún.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombres</th>
              <th>Apellido paterno</th>
              <th>Apellido materno</th>
              <th>Usuario</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td><?= htmlspecialchars($u['id']) ?></td>
                <td><?= htmlspecialchars($u['nombre']) ?></td>
                <td><?= htmlspecialchars($u['app']) ?></td>
                <td><?= htmlspecialchars($u['apm']) ?></td>
                <td><?= htmlspecialchars($u['usuario']) ?></td>
                <td><?= htmlspecialchars($u['correo']) ?></td>
                <td><?= htmlspecialchars($u['rol']) ?></td>
                <td>
                  <!-- Botones de acción -->
                  <form method="post" style="display:inline" onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?')">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id'] ?>">
                    <button class="btn" style="background:#ef4444">Eliminar</button>
                  </form>

                  <?php if ((int)$u['role_id'] !== 1): ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('¿Hacer administrador a este usuario?')">
                      <input type="hidden" name="action" value="make_admin">
                      <input type="hidden" name="id_usuario" value="<?= (int)$u['id'] ?>">
                      <button class="btn" style="background:#00cc66">Hacer Admin</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>


    <!-- ===== Productos (con BD) ===== -->
    <section id="productos">
      <h1>Gestión de Productos</h1>

      <div class="prod-sidebar">
        <!-- Importante: ancla y sec para permanecer en la sección -->
        <form method="get" action="VistaAdm.php#productos" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <input type="hidden" name="sec" value="productos">
          <input type="text" name="q" placeholder="Buscar por nombre o SKU..." value="<?=h($q)?>" />
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
          <a class="btn" style="background:#ccc;color:#000" href="VistaAdm.php#productos">Limpiar</a>
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
      <div id="formProducto" class="form-agregar">
        <form method="post" enctype="multipart/form-data">
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
            <button class="btn" type="button" style="background:#ccc;color:#000" onclick="resetFormProducto()">Cancelar</button>
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
                <img src="<?=h($p['ruta_img'])?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:10px;border:1px solid #ddd">
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

                <form method="post" onsubmit="return confirm('¿Eliminar este producto?')">
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

    <!-- ===== Reporte (placeholder) ===== -->
    <section id="reporte">
      <h1>Reporte de Ventas</h1>
      <div id="contenido-reporte">
        <p>No hay ventas registradas aún.</p>
      </div>
    </section>

    <!-- ===== Inventario (desde BD) ===== -->
    <section id="inventario">
      <h1>Inventario de Productos</h1>

      <div class="prod-sidebar">
        <form method="get" action="VistaAdm.php#inventario" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
          <input type="hidden" name="sec" value="inventario">
          <select name="inv_cat">
            <option value="0">Todas las categorías</option>
            <?php foreach($categorias as $cc): ?>
              <option value="<?=$cc['id_categoria']?>" <?= ($inv_cat===$cc['id_categoria'])?'selected':''; ?>><?=h($cc['nombre'])?></option>
            <?php endforeach; ?>
          </select>

          <select name="inv_marca">
            <option value="0">Todas las marcas</option>
            <?php foreach($marcas as $mm): ?>
              <option value="<?=$mm['id_marca']?>" <?= ($inv_marca===$mm['id_marca'])?'selected':''; ?>><?=h($mm['nombre'])?></option>
            <?php endforeach; ?>
          </select>

          <button class="btn" type="submit">Filtrar</button>
          <a class="btn" style="background:#ccc;color:#000" href="VistaAdm.php#inventario">Limpiar</a>
        </form>
      </div>

      <div id="contenido-inventario">
        <?php if (empty($inventarioListado)): ?>
          <p>No hay productos en inventario.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>SKU</th>
                <th>Nombre</th>
                <th>Marca</th>
                <th>Categoría</th>
                <th>Modelo</th>
                <th>Precio</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($inventarioListado as $row): ?>
                <tr>
                  <td><?= (int)$row['id_producto'] ?></td>
                  <td><?= h($row['sku']) ?></td>
                  <td><?= h($row['nombre']) ?></td>
                  <td><?= h($row['marca'] ?: '—') ?></td>
                  <td><?= h($row['categoria'] ?: '—') ?></td>
                  <td><?= h($row['modelo'] ?: '—') ?></td>
                  <td>$<?= number_format((float)$row['precio'], 2) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </section>

  </div><!-- /main-content -->

<script>
  // Navegación de secciones
  function showSection(id) {
    document.querySelectorAll('section').forEach(s => s.classList.remove('active'));
    const el = document.getElementById(id);
    if (el) el.classList.add('active');

    // Sidebar activo
    document.querySelectorAll('.menu a').forEach(a => a.classList.remove('active'));
    const link = Array.from(document.querySelectorAll('.menu a')).find(a => a.getAttribute('onclick') && a.getAttribute('onclick').includes(`'${id}'`));
    if (link) link.classList.add('active');

    // hash para permitir refresh/compartir
    location.hash = id;
  }

  // Mantener la sección correcta al entrar por ?sec= o #hash
  (function initSection(){
    const params = new URLSearchParams(location.search);
    const secByParam = params.get('sec');
    const secByHash  = location.hash ? location.hash.substring(1) : '';
    const section    = secByParam || secByHash || 'catalogo';
    showSection(section);
  })();

  // Form Crear/Editar
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
    ['sku','nombre','precio','costo','gasto'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
    ['id_marca','id_categoria','id_dispositivo'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value='0'; });
    // No cierro el form aquí; solo se cierra si el usuario pulsa "Cancelar"
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
