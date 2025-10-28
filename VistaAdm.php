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


?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Panel de Administrador - Negocio de Fundas</title>
<link rel="stylesheet" href="adm/estilos.css">
<link rel="icon" href="imagenes/LogoLionCell.ico">
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Administración</h2>
    <div class="menu">
      <!-- <a href="VistaAdm.php">Catálogo</a>-->
      <a href="adm/VistaAdmUsuario.php">👤 Usuarios</a>
      <a href="adm/VistaAdmProducto.php">🛍 Productos</a>
      <a href="adm/VistaAdmPedidos.php">📦 Pedidos</a>
      <a href="adm/VistaAdmVentas.php">📊 Reporte de Ventas</a>
      <a href="adm/VistaAdmInventario.php">📋 Inventario</a>
      <a href="index.php">Vista de usuario</a>
    </div>
  </div>

  <!-- Main -->
  <div class="main-content">
    <div class="topbar">
      <h3>Panel de administración</h3>
      <div class="user"><span>Administrador</span></div>
    </div>
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
</script>
</body>
</html>
