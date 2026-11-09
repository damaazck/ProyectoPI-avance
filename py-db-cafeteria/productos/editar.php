<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE
]);

require '../config/conexion.php';

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {
    header("Location: listar.php");
    exit();
}

$id = (int)$_GET['id'];

$sql = "
SELECT *
FROM producto
WHERE id_producto = ?
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);

$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {

    die("Producto no encontrado.");

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = trim($_POST['nombre']);
    $precio = trim($_POST['precio']);
    $descripcion = trim($_POST['descripcion']);
    $estado = $_POST['estado'];

    $sql = "
    UPDATE producto
    SET
        nombre_producto = ?,
        precio = ?,
        descripcion = ?,
        estado = ?
    WHERE id_producto = ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $nombre,
        $precio,
        $descripcion,
        $estado,
        $id
    ]);

    header("Location: listar.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar producto | Cafetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>
<body class="pagina-productos">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<section class="cabecera-pagina">
        <div>
            <span class="etiqueta">Catálogo / Productos</span>
            <h1>Editar producto</h1>
            <p class="subtexto">Modifica los datos del producto seleccionado.</p>
        </div>
        <a class="enlace-secundario" href="listar.php">← Volver al listado</a>
    </section>
    <div class="formulario-panel">
        <form method="POST">
            <div class="form-grid">
                <div class="campo">
                    <label for="nombre">Nombre del producto</label>
                    <input id="nombre" type="text" name="nombre" required maxlength="100"
                           value="<?= htmlspecialchars($producto['nombre_producto']) ?>">
                </div>
                <div class="campo">
                    <label for="precio">Precio ($)</label>
                    <input id="precio" type="number" step="0.01" min="0" name="precio" required
                           value="<?= htmlspecialchars($producto['precio']) ?>">
                </div>
                <div class="campo campo-completo">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                </div>
                <div class="campo campo-completo">
                    <label for="estado">Disponibilidad</label>
                    <select id="estado" name="estado">
                        <option value="Disponible" <?= $producto['estado'] == 'Disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="Agotado" <?= $producto['estado'] == 'Agotado' ? 'selected' : '' ?>>Agotado</option>
                    </select>
                </div>
            </div>
            <div class="form-acciones">
                <button type="submit">Actualizar producto</button>
                <a class="boton boton-secundario" href="listar.php">Cancelar</a>
            </div>
        </form>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
