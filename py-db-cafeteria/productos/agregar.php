<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE
]);

require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = trim($_POST['nombre']);
    $precio = trim($_POST['precio']);
    $descripcion = trim($_POST['descripcion']);
    $estado = $_POST['estado'];

    $sql = "
    INSERT INTO producto
    (
        nombre_producto,
        precio,
        descripcion,
        estado
    )
    VALUES
    (
        ?, ?, ?, ?
    )
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $nombre,
        $precio,
        $descripcion,
        $estado
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
    <title>Nuevo producto | Cafetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css">
</head>

<body class="pagina-productos">
    <?php require __DIR__ . '/../includes/header.php'; ?>
    <main class="contenedor">
        <section class="cabecera-pagina">
            <div>
                <span class="etiqueta">Catálogo / Productos</span>
                <h1>Nuevo producto</h1>
                <p class="subtexto">Completa los datos para incorporar un producto al menú.</p>
            </div>
            <a class="enlace-secundario" href="listar.php">← Volver al listado</a>
        </section>

        <div class="formulario-panel">
            <form method="POST">
                <div class="form-grid">
                    <div class="campo">
                        <label for="nombre">Nombre del producto</label>
                        <input id="nombre" type="text" name="nombre" placeholder="Ej. Café americano" required
                            maxlength="100">
                    </div>
                    <div class="campo">
                        <label for="precio">Precio ($)</label>
                        <input id="precio" type="number" name="precio" step="0.01" min="0" placeholder="Ej. 99.90"
                            required>
                    </div>
                    <div class="campo campo-completo">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion"
                            placeholder="Describe brevemente el producto"></textarea>
                    </div>
                    <div class="campo campo-completo">
                        <label for="estado">Disponibilidad</label>
                        <select id="estado" name="estado">
                            <option value="Disponible">Disponible</option>
                            <option value="Agotado">Agotado</option>
                        </select>
                    </div>
                </div>
                <div class="form-acciones">
                    <button type="submit">Guardar producto</button>
                    <a class="boton boton-secundario" href="listar.php">Cancelar</a>
                </div>
            </form>
        </div>
        <?php require __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>