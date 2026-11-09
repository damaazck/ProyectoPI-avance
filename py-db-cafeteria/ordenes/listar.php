<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE,
    EMPLEADO
]);

require '../config/conexion.php';

$sql = "
SELECT
    o.id_orden,
    o.fecha,
    o.estado,
    o.total,
    u.nombre,
    u.apellido
FROM ordenes o
INNER JOIN usuario u
ON o.id_usuario = u.id_usuario
ORDER BY o.id_orden DESC
";

$stmt = $conexion->query($sql);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Órdenes</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>

<body class="pagina-ordenes">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
    <h1>Historial de Órdenes</h1>
    <a href="nueva.php" class="boton">Nueva Orden</a>
    <br><br>
    <a href="../dashboard/index.php" class="enlace-secundario">Volver al Dashboard </a>
    <hr>
    <?php if(empty($ordenes)): ?>
        <p>No hay órdenes registradas.</p>
    <?php else: ?>
        <div class="tabla-responsive">
<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Empleado</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($ordenes as $orden): ?>
                <tr>
                    <td>
                        <?= $orden['id_orden'] ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($orden['fecha']) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars(
                            $orden['nombre'] . ' ' .
                            $orden['apellido']
                        ) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($orden['estado']) ?>
                    </td>
                    <td>
                        $
                        <?= number_format(
                            $orden['total'],
                            2
                        ) ?>

                    </td>
                    <td>
                        <a href="detalle.php?id=<?= $orden['id_orden'] ?>">Ver Detalle</a>
                        <?php if(
                            $orden['estado'] == 'Activa'
                        ): ?>
                            |
                            <a href="agregar_productos.php?id=<?= (int) $orden['id_orden'] ?>">  + Añadir productos  </a>
                            |
                            <a
                                href="finalizar.php?id=<?= $orden['id_orden'] ?>" onclick="return confirm('¿Finalizar la orden?')"  > Finalizar  </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
</div>
    <?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>