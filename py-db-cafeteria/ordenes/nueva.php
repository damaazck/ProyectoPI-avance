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
SELECT *
FROM producto
WHERE estado = 'Disponible'
ORDER BY nombre_producto
";

$stmt = $conexion->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Nueva Orden</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css">
</head>

<body class="pagina-ordenes">
    <?php require __DIR__ . '/../includes/header.php'; ?>
    <main class="contenedor">
        <h1>Nueva Orden</h1>
        <a href="../dashboard/index.php" class="enlace-secundario"> Dashboard</a>
        |
        <a href="listar.php" class="enlace-secundario">Ver Órdenes</a>
        <hr>
        <form action="guardar.php" method="POST" id="formOrden">

            <input type="hidden" name="detalle" id="detalleInput">
            <label>Producto</label>
            <br>
            <select id="producto">
                <?php foreach ($productos as $producto): ?>
                    <option value="<?= $producto['id_producto'] ?>" data-precio="<?= $producto['precio'] ?>">
                        <?= htmlspecialchars($producto['nombre_producto']) ?> </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label>Cantidad</label>
            <br>
            <input type="number" id="cantidad" min="1" value="1">
            <br><br>
            <button type="button" onclick="agregarProducto()">Agregar </button>
            <hr>

            <div class="tabla-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detalleOrden">
                    </tbody>
                </table>
            </div>
            <h3> Total: $  <span id="total"> 0.00 </span> </h3>
            <button type="submit"> Guardar Orden </button>
        </form>

        <script>
            let total = 0;
            let detalle = [];
            function agregarProducto() {
                let selectProducto =
                    document.getElementById("producto");
                let cantidad =
                    parseInt(
                        document.getElementById("cantidad").value
                    );
                if (
                    isNaN(cantidad)
                    ||
                    cantidad <= 0
                ) {

                    alert(
                        "Ingrese una cantidad válida."
                    );
                    return;
                }

                let opcion =
                    selectProducto.options[
                    selectProducto.selectedIndex
                    ];

                let idProducto =
                    opcion.value;

                let nombre =
                    opcion.text;

                let precio =
                    parseFloat(
                        opcion.getAttribute(
                            "data-precio"
                        )
                    );

                let subtotal =
                    precio * cantidad;

                detalle.push({

                    id_producto:
                        idProducto,

                    cantidad:
                        cantidad,

                    subtotal:
                        subtotal

                });

                let fila =
                    "<tr>" +
                    "<td>" + nombre + "</td>" +
                    "<td>$" + precio.toFixed(2) + "</td>" +
                    "<td>" + cantidad + "</td>" +
                    "<td>$" + subtotal.toFixed(2) + "</td>" +
                    "</tr>";

                document
                    .getElementById("detalleOrden")
                    .insertAdjacentHTML(
                        "beforeend",
                        fila
                    );

                total += subtotal;

                document
                    .getElementById("total")
                    .textContent =
                    total.toFixed(2);

                document
                    .getElementById("detalleInput")
                    .value =
                    JSON.stringify(detalle);

                document
                    .getElementById("cantidad")
                    .value = 1;
            }

            document
                .getElementById("formOrden")
                .addEventListener(
                    "submit",
                    function (e) {

                        if (detalle.length === 0) {

                            alert(
                                "Debe agregar al menos un producto."
                            );

                            e.preventDefault();

                        }

                    }
                );

        </script>

        <?php require __DIR__ . '/../includes/footer.php'; ?>

</body>
</html>