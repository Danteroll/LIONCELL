<form action="/pagina/LIONCELL/php/registrar_pedido.php" method="post">
  <input name="nombre_cliente" placeholder="Nombre del cliente" required>
  <input name="telefono" placeholder="Teléfono">
  <input name="correo" placeholder="Correo">

  <h4>Productos</h4>
  <label>Producto 1: <input type="number" name="productos[1]" value="2"></label><br>
  <label>Producto 2: <input type="number" name="productos[5]" value="1"></label><br>

  <button type="submit">Guardar pedido</button>
</form>
