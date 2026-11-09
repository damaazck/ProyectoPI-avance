<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE,
    EMPLEADO
]);

require '../config/conexion.php';

if (
    !isset($_POST['detalle']) ||
    empty($_POST['detalle'])
) {

    die("La orden no contiene productos.");

}

$detalle = json_decode(
    $_POST['detalle'],
    true
);

if (!$detalle) {

    die("Error al procesar la orden.");

}

try {
    $conexion->beginTransaction();
    $total = 0;
    foreach ($detalle as $item) {
        $total += $item['subtotal'];
    }

    $sql = "
    INSERT INTO ordenes
    (
        id_usuario,
        total
    )
    VALUES
    (
        ?, ?
    )
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $_SESSION['id_usuario'],
        $total
    ]);

    $idOrden = $conexion->lastInsertId();

    $sql = "
    INSERT INTO detalle_orden
    (
        id_orden,
        id_producto,
        cantidad,
        subtotal
    )
    VALUES
    (
        ?, ?, ?, ?
    )
    ";

    $stmt = $conexion->prepare($sql);

    foreach ($detalle as $item) {

        $stmt->execute([

            $idOrden,

            $item['id_producto'],

            $item['cantidad'],

            $item['subtotal']

        ]);

    }

    $conexion->commit();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Orden Guardada</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>

<body class="pagina-ordenes">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<h1>Orden Registrada</h1>
<p>Número de orden: <strong> <?= $idOrden ?> </strong> </p>
<p> Total: <strong> $<?= number_format($total, 2) ?> </strong> </p>
<br>

<a href="../dashboard/index.php" class="enlace-secundario">
    Dashboard
</a>
|
<a href="nueva.php" class="enlace-secundario">
    Nueva Orden
</a>
|
<a href="agregar_productos.php?id=<?= (int) $idOrden ?>" class="boton"> + Añadir más productos a esta orden</a>
|
<a href="listar.php" class="enlace-secundario">Ver Órdenes</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
<?php

} catch (Exception $e) {
    $conexion->rollBack();
    die(
        "Error al guardar la orden: "
        . $e->getMessage()
    );
}