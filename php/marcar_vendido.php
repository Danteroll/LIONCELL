<?php
require_once __DIR__ . '/../inc/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPedido = (int)($_POST['id_pedido'] ?? 0);

    if ($idPedido <= 0) {
        exit('ID de pedido inválido.');
    }

    try {
        $pdo->beginTransaction();

        // Obtener productos del pedido
        $stmt = $pdo->prepare("SELECT id_producto, cantidad, precio_unit FROM pedido_items WHERE id_pedido = ?");
        $stmt->execute([$idPedido]);
        $items = $stmt->fetchAll();

        if (!$items) throw new Exception('El pedido no tiene productos.');

        // Calcular total y costo
        $total = 0;
        $costoTotal = 0;
        $stmtCosto = $pdo->prepare("SELECT costo FROM productos WHERE id_producto = ?");

        foreach ($items as $it) {
            $total += $it['precio_unit'] * $it['cantidad'];
            $stmtCosto->execute([$it['id_producto']]);
            $c = $stmtCosto->fetchColumn() ?: 0;
            $costoTotal += $c * $it['cantidad'];

            // Restar del inventario
            $pdo->prepare("UPDATE inventario SET stock_actual = GREATEST(stock_actual - ?, 0)
                           WHERE id_producto = ?")
                ->execute([$it['cantidad'], $it['id_producto']]);
        }

        // Crear venta
        $pdo->prepare("INSERT INTO ventas (id_pedido, total, costo_total) VALUES (?, ?, ?)")
            ->execute([$idPedido, $total, $costoTotal]);
        $idVenta = $pdo->lastInsertId();

        // Pasar productos a venta_items
        $stmtInsert = $pdo->prepare("INSERT INTO venta_items (id_venta, id_producto, cantidad, precio_unit)
                                     VALUES (?, ?, ?, ?)");
        foreach ($items as $it) {
            $stmtInsert->execute([$idVenta, $it['id_producto'], $it['cantidad'], $it['precio_unit']]);
        }

        //Marcar pedido como vendido
        $pdo->prepare("UPDATE pedidos SET estado='vendido' WHERE id_pedido=?")->execute([$idPedido]);

        $pdo->commit();
        echo "✅ Pedido #$idPedido marcado como vendido (Venta #$idVenta)";

    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "❌ Error: " . htmlspecialchars($e->getMessage());
    }
}
?>
