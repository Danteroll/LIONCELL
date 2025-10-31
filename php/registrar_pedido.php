<?php
require_once __DIR__ . '/../inc/init.php'; // conexión PDO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre_cliente'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $productos = $_POST['productos'] ?? []; 

        if ($nombre === '' || empty($productos)) {
            throw new Exception('Debe indicar nombre y al menos un producto.');
        }

        $pdo->beginTransaction();

        // Crear pedido
        $pdo->prepare("INSERT INTO pedidos (nombre_cliente, telefono, correo, total, estado)
                       VALUES (?, ?, ?, 0, 'reservado')")
            ->execute([$nombre, $telefono, $correo]);

        $idPedido = $pdo->lastInsertId();
        $total = 0;

        // Insertar productos del pedido
        $stmtProd = $pdo->prepare("SELECT id_producto, precio FROM productos WHERE id_producto = ?");
        $stmtItem = $pdo->prepare("INSERT INTO pedido_items (id_pedido, id_producto, cantidad, precio_unit)
                                   VALUES (?, ?, ?, ?)");

        foreach ($productos as $idProducto => $cantidad) {
            $cantidad = (int)$cantidad;
            if ($cantidad <= 0) continue;

            $stmtProd->execute([$idProducto]);
            $producto = $stmtProd->fetch();
            if (!$producto) continue;

            $precio = (float)$producto['precio'];
            $total += $precio * $cantidad;

            $stmtItem->execute([$idPedido, $idProducto, $cantidad, $precio]);
        }

        // Actualizar total del pedido
        $pdo->prepare("UPDATE pedidos SET total = ? WHERE id_pedido = ?")->execute([$total, $idPedido]);

        $pdo->commit();
        echo "✅ Pedido registrado correctamente con ID #$idPedido, Total: $$total";

    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "❌ Error: " . htmlspecialchars($e->getMessage());
    }
}
?>
