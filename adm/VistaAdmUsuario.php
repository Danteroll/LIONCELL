<?php
/*************************************************
 * admin_usuarios.php  (Vista de administración: Usuarios)
 *************************************************/

// 1) Cargar init.php PRIMERO para evitar warnings de ini_set con la sesión
require_once __DIR__ . '/../inc/init.php';

// 2) Autorización (usa helper si existe)
$esAdmin = function_exists('isAdmin') ? isAdmin() : (isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1);
if (empty($_SESSION['usuario']) || !$esAdmin) {
    header("Location: ../formulario.php");
    exit;
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// 3) Acciones (PRG: procesar POST y redirigir)
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action       = $_POST['action'] ?? '';
    $idUsuario    = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
    $miUsuarioId  = $_SESSION['usuario_id'] ?? null;   // ajusta al nombre de tu sesión (si guardas el ID)
    $miRoleId     = $_SESSION['role_id'] ?? null;

    try {
        // Contar admins actuales (para salvaguardas)
        $totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE role_id = 1")->fetchColumn();

        if ($action === 'delete_user') {
            if ($idUsuario <= 0) {
                throw new RuntimeException('ID de usuario inválido.');
            }
            // Evitar borrarte a ti mismo
            if ($miUsuarioId !== null && (int)$miUsuarioId === $idUsuario) {
                throw new RuntimeException('No puedes eliminar tu propia cuenta.');
            }

            // Evitar borrar al último admin (si el usuario a borrar es admin)
            $esAdminObjetivo = (int)$pdo
                ->prepare("SELECT role_id FROM usuarios WHERE id = ?")
                ->execute([$idUsuario]) ? (int)$pdo->query("SELECT role_id FROM usuarios WHERE id = $idUsuario")->fetchColumn() : 0;

            if ($esAdminObjetivo === 1 && $totalAdmins <= 1) {
                throw new RuntimeException('No puedes eliminar al último administrador.');
            }

            $st = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $st->execute([$idUsuario]);
            $msg = 'Usuario eliminado correctamente.';

        } elseif ($action === 'make_admin') {
            if ($idUsuario <= 0) {
                throw new RuntimeException('ID de usuario inválido.');
            }
            $st = $pdo->prepare("UPDATE usuarios SET role_id = 1 WHERE id = ?");
            $st->execute([$idUsuario]);
            $msg = 'Usuario actualizado a administrador.';

        } elseif ($action === 'make_user') {
            if ($idUsuario <= 0) {
                throw new RuntimeException('ID de usuario inválido.');
            }

            // Evitar quitarte tu propio admin
            if ($miUsuarioId !== null && (int)$miUsuarioId === $idUsuario) {
                throw new RuntimeException('No puedes quitarte a ti mismo el rol de administrador.');
            }

            // Evitar dejar el sistema sin administradores
            if ($totalAdmins <= 1) {
                throw new RuntimeException('No puedes quitar el rol al último administrador.');
            }

            $st = $pdo->prepare("UPDATE usuarios SET role_id = 0 WHERE id = ?");
            $st->execute([$idUsuario]);
            $msg = 'Rol de administrador quitado. Ahora es usuario estándar.';

        } else {
            // Acción no reconocida: ignorar
        }

        // Redirección PRG para mostrar mensaje y evitar repost
        $qs = http_build_query(['sec' => 'usuarios', 'msg' => $msg]);
        header("Location: ".$_SERVER['PHP_SELF']."?".$qs);
        exit;

    } catch (Throwable $e) {
        $err = $e->getMessage();
        // Redirigir con error
        $qs = http_build_query(['sec' => 'usuarios', 'err' => $err]);
        header("Location: ".$_SERVER['PHP_SELF']."?".$qs);
        exit;
    }
}

// 4) Cargar mensajes por GET (PRG)
if (!empty($_GET['msg'])) $msg = $_GET['msg'];
if (!empty($_GET['err'])) $err = $_GET['err'];

// 5) Obtener usuarios para la tabla
try {
    $usuarios = $pdo->query("
        SELECT 
            id,
            nombre,
            app,
            apm,
            usuario,
            correo,
            role_id,
            CASE role_id WHEN 1 THEN 'Administrador' ELSE 'Usuario' END AS rol
        FROM usuarios
        ORDER BY role_id DESC, nombre ASC
    ")->fetchAll();
} catch (PDOException $e) {
    $err = 'Error al obtener usuarios: ' . $e->getMessage();
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin – Usuarios</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin Panel</h2>
    <div class="menu">
       <!-- <a href="VistaAdm.php">Catálogo</a>-->
      <a href="VistaAdmUsuario.php">👤 Usuarios</a>
      <a href="VistaAdmProducto.php">🛍 Productos</a>
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

    <!-- USUARIOS -->
    <section id="usuarios" class="active">
      <h1>Gestión de Usuarios</h1>

      <?php if ($msg): ?>
        <div class="alert-ok"><?= h($msg) ?></div>
      <?php endif; ?>
      <?php if ($err): ?>
        <div class="alert-err">Error: <?= h($err) ?></div>
      <?php endif; ?>

      <?php if (empty($usuarios)): ?>
        <p>No hay usuarios registrados aún.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre(s)</th>
              <th>Apellido Paterno</th>
              <th>Apellido Materno</th>
              <th>Usuario</th>
              <th>Correo</th>
              <th>Rol</th>
              <th style="width:260px">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= h($u['nombre']) ?></td>
                <td><?= h($u['app']) ?></td>
                <td><?= h($u['apm']) ?></td>
                <td><?= h($u['usuario']) ?></td>
                <td><?= h($u['correo']) ?></td>
                <td><?= h($u['rol']) ?></td>
                <td>
                  <!-- Eliminar -->
                  <form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar este usuario?')">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id'] ?>">
                    <button class="btn" style="background:#ef4444">Eliminar</button>
                  </form>

                  <!-- Hacer admin -->
                  <?php if ((int)$u['role_id'] !== 1): ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('¿Convertir en administrador?')">
                      <input type="hidden" name="action" value="make_admin">
                      <input type="hidden" name="id_usuario" value="<?= (int)$u['id'] ?>">
                      <button class="btn" style="background:#00cc66">Hacer Admin</button>
                    </form>
                  <?php endif; ?>

                  <!-- Quitar admin -->
                  <?php if ((int)$u['role_id'] === 1): ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('¿Quitar rol de administrador?')">
                      <input type="hidden" name="action" value="make_user">
                      <input type="hidden" name="id_usuario" value="<?= (int)$u['id'] ?>">
                      <button class="btn" style="background:#f59e0b">Quitar Admin</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </div>

<script>
  // Si luego agregas más secciones en este archivo, esto te sirve:
  function showSection(id) {
    document.querySelectorAll('section').forEach(s => s.classList.remove('active'));
    const el = document.getElementById(id);
    if (el) el.classList.add('active');

    document.querySelectorAll('.menu a').forEach(a => a.classList.remove('active'));
    const link = Array.from(document.querySelectorAll('.menu a')).find(a => a.getAttribute('onclick') && a.getAttribute('onclick').includes(`'${id}'`));
    if (link) link.classList.add('active');

    location.hash = id;
  }

  (function initSection(){
    const secByHash  = location.hash ? location.hash.substring(1) : '';
    const section    = secByHash || 'usuarios';
    showSection(section);
  })();
</script>
</body>
</html>
