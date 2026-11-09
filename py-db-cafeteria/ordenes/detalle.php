
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
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: listar.php");
    exit();
}
$idOrden = (int) $_GET['id'];
$sql = "
SELECT o.*, u.nombre, u.apellido
FROM ordenes o
INNER JOIN usuario u
ON o.id_usuario = u.id_usuario
WHERE o.id_orden = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$idOrden]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$orden) {
    die("Orden no encontrada.");
}
$sql = "
SELECT d.*, p.nombre_producto, p.precio
FROM detalle_orden d
INNER JOIN producto p
ON d.id_producto = p.id_producto
WHERE d.id_orden = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$idOrden]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($_SESSION['token_quitar_producto'])) {
    $_SESSION['token_quitar_producto'] = bin2hex(random_bytes(32));
}
$mensajeError = $_SESSION['error_quitar_producto'] ?? '';
unset($_SESSION['error_quitar_producto']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Orden</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
    <style>
        .pagina-ordenes .tabla-responsive button.btn-quitar {
            appearance: none;
            display: inline;
            width: auto;
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
            color: #2C2826;
            padding: 0;
            margin: 0;
            font-size: 14px;
            font-weight: 400;
            line-height: 1.4;
            text-decoration: underline;
            text-underline-offset: 3px;
            cursor: pointer;
            transform: none;
        }

        .pagina-ordenes .tabla-responsive button.btn-quitar:hover {
            background: transparent;
            color: #555;
            text-decoration: underline;
            transform: none;
        }
        .pagina-ordenes .tabla-responsive button.btn-quitar:focus-visible {
            outline: 2px solid #2C2826;
            outline-offset: 4px;
        }
    </style>
</head>
<body class="pagina-ordenes">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
    <h1>Detalle de Orden #<?= (int)$orden['id_orden'] ?></h1>
    <?php if (isset($_GET['agregado']) && $_GET['agregado'] === '1'): ?>
        <p class="success" role="status">
            Producto añadido correctamente a la orden.
        </p>
    <?php endif; ?>
    <?php if (isset($_GET['quitado']) && $_GET['quitado'] === '1'): ?>
        <p class="success" role="status">
            Producto retirado correctamente. El total se actualizó.
        </p>
    <?php endif; ?>
    <?php if ($mensajeError !== ''): ?>
        <p class="error" role="alert"> <?= htmlspecialchars($mensajeError) ?></p>
    <?php endif; ?>
    <a href="../dashboard/index.php" class="enlace-secundario"> Dashboard </a>
    |
    <a href="listar.php" class="enlace-secundario"> Historial</a>
    |
    <a href="nueva.php" class="boton"> Nueva Orden </a>
    <hr>

    <p>
        <strong>Empleado:</strong><?= htmlspecialchars($orden['nombre'] . ' ' . $orden['apellido']) ?>
    </p>
    <p>
        <strong>Fecha:</strong><?= htmlspecialchars($orden['fecha']) ?>
    </p>

    <p>
        <strong>Estado:</strong> <?= htmlspecialchars($orden['estado']) ?>
    </p>

    <p>
        <strong>Total:</strong> $<?= number_format($orden['total'], 2) ?>
    </p>

    <hr>

    <h2>Productos</h2>

    <?php if (empty($detalles)): ?>

        <p>Esta orden no contiene productos.</p>

    <?php else: ?>

        <div class="tabla-responsive">

            <table>
                <thead>
                    <tr>
                        <th>ID Producto</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>

                        <?php if ($orden['estado'] === 'Activa'): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td><?= (int)$detalle['id_producto'] ?></td>
                        <td>
                            <?= htmlspecialchars($detalle['nombre_producto']) ?>
                        </td>
                        <td>
                            $<?= number_format( (float)$detalle['cantidad'] > 0
                                    ? (float)$detalle['subtotal'] / (float)$detalle['cantidad']
                                    : 0,
                                2
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars((string)$detalle['cantidad']) ?>
                        </td>

                        <td>
                            $<?= number_format($detalle['subtotal'], 2) ?>
                        </td>

                        <?php if ($orden['estado'] === 'Activa'): ?>

                            <td>
                                <form
                                    method="POST"
                                    action="quitar_producto.php"
                                    onsubmit="return confirm('¿Quitar este producto de la orden?');"
                                    style="margin: 0;"
                                >
                                    <input
                                        type="hidden"
                                        name="id_orden"
                                        value="<?= (int)$idOrden ?>"
                                    >
                                    <input type="hidden" name="id_producto" value="<?= (int)$detalle['id_producto'] ?>" >
                                    <input type="hidden" name="cantidad" value="<?= htmlspecialchars((string)$detalle['cantidad']) ?>" >
                                    <input type="hidden" name="subtotal" value="<?= htmlspecialchars((string)$detalle['subtotal']) ?>"  >
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token_quitar_producto']) ?>">
                                    <button type="submit" class="btn-quitar">Quitar </button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <br>
    <a href="listar.php" class="enlace-secundario">Volver al Historial </a>
    <?php if ($orden['estado'] === 'Activa'): ?>
        |
        <a class="enlace-secundario"
            href="agregar_productos.php?id=<?= (int)$orden['id_orden'] ?>"> + Añadir productos </a>
        |
        <a class="boton" href="finalizar.php?id=<?= (int)$orden['id_orden'] ?>" onclick="return confirm('¿Finalizar orden?')"> Finalizar Orden</a>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
