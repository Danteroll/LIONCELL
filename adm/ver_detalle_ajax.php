<?php
require_once __DIR__ . '/../inc/init.php';
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('<p>Pedido no válido.</p>');
}

$stmt = $pdo->prepare("
    SELECT p.nombre_producto, i.cantidad, i.precio_unit, i.subtotal
    FROM pedido_items i
    JOIN productos p ON i.id_producto = p.id_producto
    WHERE i.id_pedido = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    echo '<p>Este pedido no tiene productos.</p>';
    exit;
}
?>
<table style="width:100%;border-collapse:collapse;">
  <thead>
    <tr style="background:#f3f4f6;">
      <th>Producto</th>
      <th>Cantidad</th>
      <th>Precio unitario</th>
      <th>Subtotal</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $it): ?>
    <tr>
      <td><?= htmlspecialchars($it['nombre_producto']) ?></td>
      <td><?= (int)$it['cantidad'] ?></td>
      <td>$<?= number_format((float)$it['precio_unit'], 2) ?></td>
      <td>$<?= number_format((float)$it['subtotal'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
