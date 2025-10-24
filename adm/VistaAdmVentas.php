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

    <!-- ===== Catálogo (placeholder con tu estilo) ===== -->
    <section id="catalogo" class="active">
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
    </section>

      <form action="/pagina/LIONCELL/php/marcar_vendido.php" method="post">
  <input type="hidden" name="id_pedido" value="1">
  <button type="submit">Marcar como vendido</button>
</form>

</body>
</html>
