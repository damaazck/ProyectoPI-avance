<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE,
    EMPLEADO
]);

require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar.php');
    exit();
}

$idOrden = filter_var($_POST['id_orden'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
$idProducto = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
$cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
$subtotal = trim((string) ($_POST['subtotal'] ?? ''));
$token = (string) ($_POST['token'] ?? '');
$tokenSesion = (string) ($_SESSION['token_quitar_producto'] ?? '');

if (!$idOrden) {
    header('Location: listar.php');
    exit();
}

if ($tokenSesion === '' || !hash_equals($tokenSesion, $token)) {
    $_SESSION['error_quitar_producto'] = 'No se pudo validar el formulario. Actualiza la página e intenta de nuevo.';
    header('Location: detalle.php?id=' . $idOrden);
    exit();
}

if (!$idProducto || !$cantidad || !preg_match('/^\d+(?:\.\d{1,2})?$/', $subtotal)) {
    $_SESSION['error_quitar_producto'] = 'No se pudo identificar el producto a quitar.';
    header('Location: detalle.php?id=' . $idOrden);
    exit();
}

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare(
        'SELECT estado FROM ordenes WHERE id_orden = ? FOR UPDATE'
    );
    $stmt->execute([$idOrden]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        throw new DomainException('La orden no existe.');
    }

    if ($orden['estado'] !== 'Activa') {
        throw new DomainException('No puedes quitar productos de una orden finalizada.');
    }

    $stmt = $conexion->prepare(
        'DELETE FROM detalle_orden
         WHERE id_orden = ?
           AND id_producto = ?
           AND cantidad = ?
           AND subtotal = ?
         LIMIT 1'
    );
    $stmt->execute([$idOrden, $idProducto, $cantidad, $subtotal]);

    if ($stmt->rowCount() !== 1) {
        throw new DomainException('El producto ya no está en la orden. Actualiza el detalle.');
    }

    $stmt = $conexion->prepare(
        'UPDATE ordenes
         SET total = (
             SELECT COALESCE(SUM(subtotal), 0)
             FROM detalle_orden
             WHERE id_orden = ?
         )
         WHERE id_orden = ?'
    );
    $stmt->execute([$idOrden, $idOrden]);

    $conexion->commit();
    header('Location: detalle.php?id=' . $idOrden . '&quitado=1');
    exit();
} catch (DomainException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    $_SESSION['error_quitar_producto'] = $e->getMessage();
} catch (Throwable $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    error_log('Error al quitar producto de la orden: ' . $e->getMessage());
    $_SESSION['error_quitar_producto'] = 'No se pudo modificar la orden. Inténtalo de nuevo.';
}

header('Location: detalle.php?id=' . $idOrden);
exit();
