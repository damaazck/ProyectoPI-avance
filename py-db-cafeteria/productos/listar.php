<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE
]);

require '../config/conexion.php';

$sql = "
SELECT *
FROM producto
ORDER BY id_producto DESC
";

$stmt = $conexion->query($sql);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | Cafetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>
<body class="pagina-productos">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<section class="cabecera-pagina">
        <div>
            <span class="etiqueta">Catálogo de la cafetería</span>
            <h1>Productos</h1>
            <p class="subtexto">Consulta y administra los productos, sus precios y su disponibilidad.</p>
        </div>
        <div class="barra-acciones">
            <a class="boton" href="agregar.php">+ Nuevo producto</a>
        </div>
    </section>

    <div class="tabla-responsive">
        <table>
            <thead><tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?= (int)$producto['id_producto'] ?></td>
                    <td><strong><?= htmlspecialchars($producto['nombre_producto']) ?></strong></td>
                    <td>$<?= number_format((float)$producto['precio'], 2) ?></td>
                    <td><span class="estado-etiqueta"><?= htmlspecialchars($producto['estado']) ?></span></td>
                    <td>
                        <div class="acciones-tabla">
                            <a href="editar.php?id=<?= (int)$producto['id_producto'] ?>">Editar</a>
                            <a class="enlace-eliminar" href="eliminar.php?id=<?= (int)$producto['id_producto'] ?>"
                               onclick="return confirm('¿Eliminar producto?')">Eliminar</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($productos)): ?>
        <p class="aviso-vacio">Todavía no hay productos registrados.</p>
    <?php endif; ?>
    <a class="enlace-secundario" href="../dashboard/index.php">← Volver al dashboard</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
