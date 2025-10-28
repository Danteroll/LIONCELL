<?php
require_once __DIR__ . '/../inc/init.php';
$id_pedido = (int)($_GET['id_pedido'] ?? 0);

if ($id_pedido <= 0) {
  echo "<p>❌ Pedido no válido.</p>";
  exit;
}

$stmt = $pdo->prepare("
  SELECT p.nombre, pi.cantidad, pi.precio_unit, pi.subtotal
  FROM pedido_items pi
  JOIN productos p ON p.id_producto = pi.id_producto
  WHERE pi.id_pedido = ?
");
$stmt->execute([$id_pedido]);
$items = $stmt->fetchAll();

if (!$items) {
  echo "<p>Sin productos registrados en este pedido.</p>";
  exit;
}

echo "<table style='width:100%;border-collapse:collapse;'>";
echo "<thead><tr style='background:#e5e7eb;'><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead><tbody>";
foreach($items as $i){
  echo "<tr>
          <td>".htmlspecialchars($i['nombre'])."</td>
          <td>".$i['cantidad']."</td>
          <td>$".number_format($i['precio_unit'],2)."</td>
          <td>$".number_format($i['subtotal'],2)."</td>
        </tr>";
}
echo "</tbody></table>";
