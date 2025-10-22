<?php
// esto session_star es para entrar a páginas de admin
session_start();
if (empty($_SESSION['usuario']) || ($_SESSION['role_id'] ?? 0) != 1) {
    header("Location: ../formulario.php");
    exit;
}
?>
<!DOCTYPE html> 
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel de Administrador - Negocio de Fundas</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
    body { display: flex; height: 100vh; background: #f4f6f8; }

    /* === SIDEBAR === */
    .sidebar {
      width: 250px;
      background: linear-gradient(180deg, #1e3a8a 0%, #2563eb 45%, #e6c065 100%);
      color: white;
      display: flex;
      flex-direction: column;
      padding: 20px;
    }
    .sidebar h2 { text-align: center; margin-bottom: 30px; }
    .menu a {
      display: block;
      padding: 12px;
      color: white;
      text-decoration: none;
      border-radius: 8px;
      transition: background 0.3s;
    }
    .menu a:hover, .menu a.active { background: rgba(255,255,255,0.2); }

    /* === CONTENIDO PRINCIPAL === */
    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      background: white;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .topbar h3 { color: #333; }
    .topbar .user { display: flex; align-items: center; gap: 10px; }
    .topbar img { width: 35px; height: 35px; border-radius: 50%; border: 2px solid #00ccff; }

    section {
      flex: 1;
      padding: 25px;
      display: none;
      overflow-y: auto;
    }
    section.active { display: block; }
    h1 { color: #0066ff; margin-bottom: 15px; }

    /* === BOTONES === */
    button, .btn {
      background: #0066ff;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover, .btn:hover { background: #0052cc; }

    /* === PRODUCTOS === */
    .prod-sidebar {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 15px;
    }
    .prod-sidebar button { flex: 1; background: #0066ff; color: white; border-radius: 8px; }

    .producto-item {
      background: white;
      border: 1px solid #ddd;
      padding: 15px;
      border-radius: 10px;
      margin-bottom: 10px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .producto-item input, select {
      margin-top: 5px;
      padding: 5px;
      width: 100%;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .filtros { display: flex; gap: 10px; margin-bottom: 15px; }
    .filtros select, .filtros input {
      padding: 8px; border-radius: 5px; border: 1px solid #ccc; flex: 1;
    }

    .form-agregar {
      background: #fff; border: 1px solid #ccc;
      padding: 15px; border-radius: 10px;
      margin-top: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      display: none; animation: fadeIn 0.3s ease;
    }

    .form-editar-cantidad {
      margin-top: 10px;
      background: #eef;
      padding: 10px;
      border-radius: 8px;
      display: none;
    }

    /* tabla reporte */
    table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; border-radius: 8px; overflow: hidden; }
    th, td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
    thead { background: #f0f8ff; color: #0066ff; }
    tbody tr:hover { background: #fafcff; }

    @keyframes fadeIn {
      from {opacity: 0; transform: translateY(-10px);}
      to {opacity: 1; transform: translateY(0);}
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <h2>Admin Panel</h2>
    <div class="menu">
      <a href="#" class="active" onclick="showSection('catalogo')">📦 Catálogo</a>
      <a href="#" onclick="showSection('usuarios')">👤 Usuarios</a>
      <a href="#" onclick="showSection('productos')">🛍 Productos</a>
      <a href="#" onclick="showSection('reporte')">📊 Reporte de Ventas</a>
      <a href="#" onclick="showSection('inventario')">📋 Inventario</a>
    </div>
  </div>

  <div class="main-content">
    <div class="topbar">
      <h3>Panel de Administración</h3>
      <div class="user">
        <span>Administrador</span>
      </div>
    </div>

    <!-- === CATALOGO === -->
    <section id="catalogo" class="active">
      <h1>Catálogo de Productos</h1>
      <div class="prod-sidebar">
        <button onclick="mostrarCatalogoCategoria('fundas')">Fundas</button>
        <button onclick="mostrarCatalogoCategoria('micas')">Micas</button>
        <button onclick="mostrarCatalogoCategoria('audifonos')">Audífonos</button>
        <button onclick="mostrarCatalogoCategoria('cargadores')">Cargadores</button>
        <button onclick="mostrarCatalogoCategoria('soportes')">Soportes</button>
        <button onclick="mostrarCatalogoCategoria('memoria')">Memoria</button>
        <button onclick="mostrarCatalogoCategoria('tarjeteros')">Tarjeteros</button>
      </div>
      <div id="contenido-catalogo">
        <p>Selecciona una categoría para ver los productos disponibles.</p>
      </div>
    </section>

    <!-- === PRODUCTOS === -->
    <section id="productos">
      <h1>Gestión de Productos</h1>
      <div class="prod-sidebar">
        <button onclick="mostrarCategoria('fundas')">Fundas</button>
        <button onclick="mostrarCategoria('micas')">Micas</button>
        <button onclick="mostrarCategoria('audifonos')">Audífonos</button>
        <button onclick="mostrarCategoria('cargadores')">Cargadores</button>
        <button onclick="mostrarCategoria('soportes')">Soportes</button>
        <button onclick="mostrarCategoria('memoria')">Memoria</button>
        <button onclick="mostrarCategoria('tarjeteros')">Tarjeteros</button>
      </div>
      <div id="contenido-productos">
        <p>Selecciona una categoría para ver o editar productos.</p>
      </div>
    </section>

    <!-- === REPORTE DE VENTAS === -->
    <section id="reporte">
      <h1>Reporte de Ventas</h1>
      <div id="contenido-reporte">
        <p>No hay ventas registradas aún.</p>
      </div>
    </section>

    <!-- === INVENTARIO === -->
    <section id="inventario">
      <h1>Inventario de Productos</h1>
      <div class="prod-sidebar">
        <button onclick="mostrarInventarioCategoria('fundas')">Fundas</button>
        <button onclick="mostrarInventarioCategoria('micas')">Micas</button>
        <button onclick="mostrarInventarioCategoria('audifonos')">Audífonos</button>
        <button onclick="mostrarInventarioCategoria('cargadores')">Cargadores</button>
        <button onclick="mostrarInventarioCategoria('soportes')">Soportes</button>
        <button onclick="mostrarInventarioCategoria('memoria')">Memoria</button>
        <button onclick="mostrarInventarioCategoria('tarjeteros')">Tarjeteros</button>
      </div>
      <div id="contenido-inventario">
        <p>Selecciona una categoría para ver el inventario.</p>
      </div>
    </section>
  </div>

  <script>
    // === DATOS INICIALES (NO MODIFICADOS) ===
    const productos = {
      fundas: [
        {id: 1, marca: "Samsung", modelo: "S23", cantidad: 12, precioCompra: 50, precioVenta: 100},
        {id: 2, marca: "iPhone", modelo: "14 Pro", cantidad: 8, precioCompra: 80, precioVenta: 150},
      ],
      micas: [],
      audifonos: [],
      cargadores: [],
      soportes: [],
      memoria: [],
      tarjeteros: []
    };

    let marcasDisponibles = ["Samsung", "iPhone", "Honor"];

    // === REPORTE DE VENTAS (nuevo) ===
    // cada venta: {fecha, producto, precioUnitario, cantidad, total}
    const ventas = [];

    // === UTILIDADES ===
    function showSection(id) {
      document.querySelectorAll('section').forEach(s => s.classList.remove('active'));
      document.getElementById(id).classList.add('active');

      // actualizar la clase active del menú (sidebar)
      document.querySelectorAll('.menu a').forEach(a => a.classList.remove('active'));
      const link = Array.from(document.querySelectorAll('.menu a')).find(a => a.getAttribute('onclick') && a.getAttribute('onclick').includes(`'${id}'`));
      if (link) link.classList.add('active');

      // si abrimos reporte, mostrar tabla actualizada
      if (id === 'reporte') mostrarReporte();
    }

    // =========================
    // === CATALOGO (nuevo) ====
    // =========================
    function mostrarCatalogoCategoria(cat) {
      const cont = document.getElementById("contenido-catalogo");
      cont.innerHTML = `<h2 style="color:#0066ff;">${cat.charAt(0).toUpperCase() + cat.slice(1)}</h2>`;

      if (productos[cat] && productos[cat].length > 0) {
        cont.innerHTML += `
          <div class="filtros">
            ${cat === "fundas"
              ? `<select id="filtroCatMarca" onchange="filtrarCatalogo('${cat}')">
                  <option value="todas">Todas las marcas</option>
                  ${marcasDisponibles.map(m => `<option>${m}</option>`).join('')}
                </select>` : ''}
            <input type="text" id="filtroCatNombre" placeholder="Buscar..." oninput="filtrarCatalogo('${cat}')">
          </div>
          <div id="listaCatalogo${cat.charAt(0).toUpperCase() + cat.slice(1)}"></div>
        `;
        mostrarCatalogo(cat);
      } else {
        cont.innerHTML += `<p>No hay productos disponibles en esta categoría.</p>`;
      }
    }

    function mostrarCatalogo(cat) {
      const lista = document.getElementById(`listaCatalogo${cat.charAt(0).toUpperCase() + cat.slice(1)}`);
      if (!lista) return;
      lista.innerHTML = productos[cat].map(p => `
        <div class="producto-item">
          <b>${p.nombre || (p.marca + " " + (p.modelo || ''))}</b><br>
          Precio venta: $${p.precioVenta}<br>
          Stock: <b>${p.cantidad}</b><br><br>
          <button class="btn" ${p.cantidad <= 0 ? 'disabled style="background:gray;"' : ''} onclick="venderProducto('${cat}', ${p.id})">
            ${p.cantidad <= 0 ? 'Agotado' : 'Vender'}
          </button>
        </div>
      `).join('');
    }

    function filtrarCatalogo(cat) {
      const marca = document.getElementById("filtroCatMarca") ? document.getElementById("filtroCatMarca").value : "todas";
      const nombre = document.getElementById("filtroCatNombre").value.toLowerCase();
      const lista = document.getElementById(`listaCatalogo${cat.charAt(0).toUpperCase() + cat.slice(1)}`);

      const filtrados = productos[cat].filter(p =>
        (marca === "todas" || p.marca === marca) &&
        (nombre === "" || (p.nombre || (p.marca + " " + (p.modelo || ''))).toLowerCase().includes(nombre))
      );

      lista.innerHTML = filtrados.length ? filtrados.map(p => `
        <div class="producto-item">
          <b>${p.nombre || (p.marca + " " + (p.modelo || ''))}</b><br>
          Precio venta: $${p.precioVenta}<br>
          Stock: <b>${p.cantidad}</b><br><br>
          <button class="btn" ${p.cantidad <= 0 ? 'disabled style="background:gray;"' : ''} onclick="venderProducto('${cat}', ${p.id})">
            ${p.cantidad <= 0 ? 'Agotado' : 'Vender'}
          </button>
        </div>
      `).join('') : `<p>No hay productos que coincidan con la búsqueda.</p>`;
    }

    /**
     * VENDER PRODUCTO (OPCIÓN B)
     * - Pide la cantidad a vender (prompt).
     * - Valida que sea entero positivo y <= stock.
     * - Resta cantidad del inventario.
     * - Registra la venta con cantidad y total.
     * - Actualiza catálogo, inventario y reporte si están activos.
     */
    function venderProducto(cat, id) {
      const producto = productos[cat].find(p => p.id === id);
      if (!producto) { alert("Producto no encontrado."); return; }
      if (producto.cantidad <= 0) { alert("Producto agotado."); return; }

      // pedir cantidad a vender (opción B)
      let entrada = prompt(`¿Cuántas unidades de "${producto.nombre || (producto.marca + " " + (producto.modelo || ''))}" desea vender?\nStock disponible: ${producto.cantidad}`, "1");
      if (entrada === null) return; // usuario canceló
      entrada = entrada.trim();
      if (entrada === "") { alert("Debe ingresar una cantidad válida."); return; }

      const cantidad = parseInt(entrada, 10);
      if (isNaN(cantidad) || cantidad <= 0) {
        alert("Ingrese un número entero mayor que 0.");
        return;
      }
      if (cantidad > producto.cantidad) {
        alert(`Cantidad insuficiente en inventario. Stock actual: ${producto.cantidad}`);
        return;
      }

      // restar del inventario
      producto.cantidad -= cantidad;

      // registrar venta: incluimos precio unitario y total
      const nombreProd = producto.nombre || (producto.marca + " " + (producto.modelo || ''));
      const precioUnitario = producto.precioVenta || 0;
      const total = precioUnitario * cantidad;
      ventas.push({
        fecha: new Date().toLocaleString(),
        producto: nombreProd,
        precioUnitario,
        cantidad,
        total
      });

      alert(`Venta registrada:\n${nombreProd}\nCantidad: ${cantidad}\nPrecio unitario: $${precioUnitario}\nTotal: $${total}`);

      // refrescar vistas si están activas
      // refrescar catálogo si está mostrando esta categoría
      const catSection = document.getElementById('catalogo');
      if (catSection.classList.contains('active')) {
        const lista = document.getElementById(`listaCatalogo${cat.charAt(0).toUpperCase() + cat.slice(1)}`);
        if (lista) mostrarCatalogo(cat);
      }

      // refrescar inventario si sección inventario está activa
      if (document.getElementById('inventario').classList.contains('active')) {
        // se vuelve a mostrar la categoría actual en inventario para refrescar lista
        mostrarInventarioCategoria(cat);
      }

      // refrescar reporte si sección reporte está activa
      if (document.getElementById('reporte').classList.contains('active')) {
        mostrarReporte();
      }
    }

    // =========================
    // === PRODUCTOS ===
    // =========================
    function mostrarCategoria(cat) {
      const cont = document.getElementById("contenido-productos");
      cont.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <h2 style="color:#0066ff;">${cat.charAt(0).toUpperCase() + cat.slice(1)}</h2>
          <button class="btn" onclick="toggleFormulario('${cat}')">+ Agregar producto</button>
        </div>
        <div id="formAgregar-${cat}" class="form-agregar">${crearFormularioAgregar(cat)}</div>
      `;

      if (cat === "fundas") {
        cont.innerHTML += `
          <div class="filtros">
            <select id="filtroMarca" onchange="filtrarFundas()">
              <option value="todas">Todas las marcas</option>
              ${marcasDisponibles.map(m => `<option>${m}</option>`).join('')}
            </select>
            <input type="text" id="filtroModelo" placeholder="Buscar por modelo..." oninput="filtrarFundas()">
          </div>
          <div id="listaFundas"></div>
        `;
        mostrarFundas();
      } else {
        cont.innerHTML += `<div id="lista${cat.charAt(0).toUpperCase()+cat.slice(1)}"></div>`;
      }
    }

    function agregarMarca() {
      const nueva = prompt("Ingrese el nombre de la nueva marca:");
      if (nueva && !marcasDisponibles.includes(nueva)) {
        marcasDisponibles.push(nueva);
        alert("Marca agregada con éxito.");
        mostrarCategoria('fundas');
      }
    }

    function toggleFormulario(cat) {
      const form = document.getElementById(`formAgregar-${cat}`);
      form.style.display = form.style.display === "none" || form.style.display === "" ? "block" : "none";
    }

    function crearFormularioAgregar(cat) {
      return `
        <h4>Agregar nuevo producto</h4>
        ${cat === 'fundas'
          ? `<label>Marca:</label>
             <select id="marcaNueva">${marcasDisponibles.map(m => `<option>${m}</option>`).join('')}</select>
             <button class="btn" style="margin-top:5px;" onclick="agregarMarca()">+ Agregar marca</button>
             <label>Modelo:</label><input id="modeloNuevo" placeholder="Ej. S23">`
          : `<label>Nombre:</label><input id="nombreNuevo" placeholder="Ej. Producto">`}
        <label>Cantidad:</label><input id="cantidadNueva" type="number">
        <label>Precio compra:</label><input id="compraNueva" type="number">
        <label>Precio venta:</label><input id="ventaNueva" type="number">
        <br><br><button class="btn" onclick="agregar('${cat}')">Guardar</button>`;
    }

    function agregar(cat) {
      const nuevo = {
        id: Date.now(),
        cantidad: +document.getElementById("cantidadNueva").value || 0,
        precioCompra: +document.getElementById("compraNueva").value || 0,
        precioVenta: +document.getElementById("ventaNueva").value || 0
      };
      if (cat === "fundas") {
        nuevo.marca = document.getElementById("marcaNueva").value;
        nuevo.modelo = document.getElementById("modeloNuevo").value;
      } else {
        nuevo.nombre = document.getElementById("nombreNuevo").value;
      }
      productos[cat].push(nuevo);
      mostrarCategoria(cat);
    }

    function mostrarFundas() {
      const lista = document.getElementById("listaFundas");
      if (!lista) return;
      lista.innerHTML = productos.fundas.map(p => `
        <div class="producto-item">
          <b>${p.marca} ${p.modelo}</b><br>
          Cantidad actual: <b>${p.cantidad}</b><br>
          <div id="editarCant-${p.id}" class="form-editar-cantidad">
            <label>Agregar o quitar unidades:</label>
            <input type="number" id="nuevaCant-${p.id}" value="0">
            <button class="btn" onclick="guardarNuevaCantidad(${p.id})">Guardar</button>
          </div>
          <button class="btn" onclick="toggleEditarCantidad(${p.id})">Editar cantidad</button>
          <br><br>
          Precio compra: <input type="number" value="${p.precioCompra}" onchange="actualizar('fundas', ${p.id}, 'precioCompra', this.value)">
          Precio venta: <input type="number" value="${p.precioVenta}" onchange="actualizar('fundas', ${p.id}, 'precioVenta', this.value)">
          <br><br>
          <button class="btn" onclick="guardarCambios('fundas', ${p.id})">Guardar</button>
          <button class="btn" style="background:red;" onclick="eliminar('fundas', ${p.id})">Eliminar</button>
        </div>`).join('');
    }

    function toggleEditarCantidad(id) {
      const div = document.getElementById(`editarCant-${id}`);
      if (!div) return;
      div.style.display = div.style.display === "none" || div.style.display === "" ? "block" : "none";
    }

    function guardarNuevaCantidad(id) {
      const producto = productos.fundas.find(p => p.id === id);
      const cambio = parseInt(document.getElementById(`nuevaCant-${id}`).value) || 0;
      producto.cantidad += cambio;
      alert("Cantidad actualizada correctamente.");
      mostrarFundas();
    }

    function actualizar(cat, id, campo, valor) {
      const prod = productos[cat].find(p => p.id === id);
      prod[campo] = parseFloat(valor);
    }

    function guardarCambios(cat, id) {
      alert("Cambios guardados correctamente para el producto ID " + id);
    }

    function eliminar(cat, id) {
      const confirmar = confirm("¿Estás seguro de eliminar este producto?");
      if (!confirmar) return;
      productos[cat] = productos[cat].filter(p => p.id !== id);
      alert("Producto eliminado correctamente.");
      mostrarCategoria(cat);
    }

    function filtrarFundas() {
      const filtroMarcaEl = document.getElementById("filtroMarca");
      const filtroModeloEl = document.getElementById("filtroModelo");
      if (!filtroMarcaEl || !filtroModeloEl) return;
      const marca = filtroMarcaEl.value;
      const modelo = filtroModeloEl.value.toLowerCase();
      const lista = document.getElementById("listaFundas");
      const filtrados = productos.fundas.filter(p =>
        (marca === "todas" || p.marca === marca) &&
        (modelo === "" || p.modelo.toLowerCase().includes(modelo))
      );
      lista.innerHTML = filtrados.length === 0
        ? `<div class="sin-productos">No hay fundas que coincidan con la búsqueda.</div>`
        : filtrados.map(p => `
          <div class="producto-item">
            <b>${p.marca} ${p.modelo}</b><br>
            Cantidad: <b>${p.cantidad}</b><br>
            <button class="btn" onclick="toggleEditarCantidad(${p.id})">Editar cantidad</button>
          </div>`).join('');
    }

    // =========================
    // === INVENTARIO  ===
    // =========================
    function mostrarInventarioCategoria(cat) {
      const cont = document.getElementById("contenido-inventario");
      cont.innerHTML = `<h2 style="color:#0066ff;">${cat.charAt(0).toUpperCase() + cat.slice(1)}</h2>`;

      if (productos[cat] && productos[cat].length > 0) {
        cont.innerHTML += `
          <div class="filtros">
            ${cat === "fundas"
              ? `<select id="filtroInvMarca" onchange="filtrarInventario('${cat}')">
                  <option value="todas">Todas las marcas</option>
                  ${marcasDisponibles.map(m => `<option>${m}</option>`).join('')}
                </select>`
              : ''}
            <input type="text" id="filtroInvNombre" placeholder="Buscar..." oninput="filtrarInventario('${cat}')">
          </div>
          <ul id="listaInventario${cat.charAt(0).toUpperCase() + cat.slice(1)}" style="list-style:none; padding-left:0;"></ul>
        `;
        mostrarInventario(cat);
      } else {
        cont.innerHTML += `<p>No hay productos en esta categoría.</p>`;
      }
    }

    function mostrarInventario(cat) {
      const lista = document.getElementById(`listaInventario${cat.charAt(0).toUpperCase() + cat.slice(1)}`);
      if (!lista) return;
      lista.innerHTML = productos[cat].map(p => `
        <li style="padding:5px 0; border-bottom:1px solid #ddd;">
          ${p.nombre || (p.marca + " " + (p.modelo || ''))} - Cantidad: <b>${p.cantidad}</b>
        </li>
      `).join('');
    }

    function filtrarInventario(cat) {
      const marca = document.getElementById("filtroInvMarca") ? document.getElementById("filtroInvMarca").value : "todas";
      const nombre = document.getElementById("filtroInvNombre").value.toLowerCase();
      const lista = document.getElementById(`listaInventario${cat.charAt(0).toUpperCase() + cat.slice(1)}`);

      const filtrados = productos[cat].filter(p =>
        (marca === "todas" || p.marca === marca) &&
        (nombre === "" || (p.nombre || (p.marca + " " + (p.modelo || ''))).toLowerCase().includes(nombre))
      );

      lista.innerHTML = filtrados.length ? filtrados.map(p => `
        <li style="padding:5px 0; border-bottom:1px solid #ddd;">
          ${p.nombre || (p.marca + " " + (p.modelo || ''))} - Cantidad: <b>${p.cantidad}</b>
        </li>
      `).join('') : `<li>No hay productos que coincidan con la búsqueda.</li>`;
    }

    // =========================
    // === REPORTE: mostrar tabla ===
    // =========================
    function mostrarReporte() {
      const cont = document.getElementById("contenido-reporte");
      if (ventas.length === 0) {
        cont.innerHTML = '<p>No hay ventas registradas aún.</p>';
        return;
      }

      const rows = ventas.map(v => `
        <tr>
          <td>${v.fecha}</td>
          <td>${v.producto}</td>
          <td>${v.cantidad}</td>
          <td>$${v.precioUnitario}</td>
          <td>$${v.total}</td>
        </tr>
      `).join('');

      cont.innerHTML = `
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Producto</th>
              <th>Cantidad</th>
              <th>Precio unitario</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            ${rows}
          </tbody>
        </table>
        <br>
        <button class="btn" onclick="exportarCSV()">Exportar CSV</button>
      `;
    }

    // pequeña utilidad para exportar reporte a CSV
    function exportarCSV() {
      if (ventas.length === 0) { alert("No hay ventas para exportar."); return; }
      const header = ['Fecha','Producto','Cantidad','Precio_unitario','Total'];
      const csvRows = [header.join(',')];
      ventas.forEach(v => {
        // escape comas si necesario
        const prod = `"${String(v.producto).replace(/"/g,'""')}"`;
        const fecha = `"${String(v.fecha).replace(/"/g,'""')}"`;
        csvRows.push([fecha, prod, v.cantidad, v.precioUnitario, v.total].join(','));
      });
      const csv = csvRows.join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `reporte_ventas_${new Date().toISOString().slice(0,10)}.csv`;
      a.click();
      URL.revokeObjectURL(url);
    }

    // Inicialización: mostrar sección por defecto
    showSection('catalogo');
  </script>
</body>
</html>
