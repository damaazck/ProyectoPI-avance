<?php
require '../includes/permisos.php';

verificarRol([ADMIN, GERENTE, SUBGERENTE, EMPLEADO]);
require '../config/conexion.php';

$idOrden = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
if (!$idOrden) {
    header('Location: listar.php');
    exit();
}

if (empty($_SESSION['token_agregar_orden'])) {
    $_SESSION['token_agregar_orden'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['token'] ?? '');
    $productoId = filter_var($_POST['id_producto'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 1000]
    ]);

    if (!hash_equals($_SESSION['token_agregar_orden'], $token)) {
        $error = 'La sesión del formulario caducó. Actualiza la página e intenta de nuevo.';
    } elseif (!$productoId || !$cantidad) {
        $error = 'Selecciona un producto e introduce una cantidad válida (1 a 1000).';
    } else {
        try {
            $conexion->beginTransaction();

            $stmt = $conexion->prepare(
                'SELECT estado FROM ordenes WHERE id_orden = ? FOR UPDATE'
            );
            $stmt->execute([$idOrden]);
            $ordenBloqueada = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$ordenBloqueada) {
                throw new DomainException('La orden ya no existe.');
            }
            if ($ordenBloqueada['estado'] !== 'Activa') {
                throw new DomainException('Esta orden ya fue finalizada. No admite nuevos productos.');
            }

            $stmt = $conexion->prepare(
                "SELECT id_producto, precio FROM producto WHERE id_producto = ? AND estado = 'Disponible'"
            );
            $stmt->execute([$productoId]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new DomainException('Este producto no está disponible.');
            }

            $subtotal = number_format(round((float) $producto['precio'] * $cantidad, 2), 2, '.', '');
            $stmt = $conexion->prepare(
                'INSERT INTO detalle_orden (id_orden, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$idOrden, $productoId, $cantidad, $subtotal]);

            $stmt = $conexion->prepare('UPDATE ordenes SET total = total + ? WHERE id_orden = ?');
            $stmt->execute([$subtotal, $idOrden]);

            $conexion->commit();
            header('Location: detalle.php?id=' . $idOrden . '&agregado=1');
            exit();
        } catch (DomainException $e) {
            if ($conexion->inTransaction()) $conexion->rollBack();
            $error = $e->getMessage();
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) $conexion->rollBack();
            error_log('Error al añadir producto a la orden: ' . $e->getMessage());
            $error = 'No se pudo actualizar la orden. Inténtalo otra vez.';
        }
    }
}

$stmt = $conexion->prepare('SELECT id_orden, estado, total FROM ordenes WHERE id_orden = ?');
$stmt->execute([$idOrden]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$orden) {
    header('Location: listar.php');
    exit();
}

$productos = [];
if ($orden['estado'] === 'Activa') {
    $stmt = $conexion->query(
        "SELECT id_producto, nombre_producto, precio FROM producto WHERE estado = 'Disponible' ORDER BY nombre_producto"
    );
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir productos | Orden #<?= (int) $idOrden ?></title>
    <link rel="stylesheet" href="../assets/css/estiloo.css">
</head>
<body class="pagina-ordenes">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
    <p><a class="enlace-secundario" href="detalle.php?id=<?= (int) $idOrden ?>">← Volver al detalle</a></p>
    <h1>Añadir productos a la orden #<?= (int) $idOrden ?></h1>
    <p class="subtexto">Estado: <strong><?= htmlspecialchars($orden['estado'], ENT_QUOTES, 'UTF-8') ?></strong> · Total actual: <strong>$<?= number_format((float) $orden['total'], 2) ?></strong></p>

    <?php if ($error !== ''): ?>
        <p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($orden['estado'] !== 'Activa'): ?>
        <p class="merch-mensaje">Esta orden ya está finalizada. No puedes añadir más productos.</p>
    <?php elseif (!$productos): ?>
        <p class="merch-mensaje">No hay productos disponibles para añadir. Registra uno desde Productos.</p>
    <?php else: ?>
        <section class="formulario-panel">
            <h2>Selecciona un producto</h2>
            <p class="subtexto">Puedes repetir este proceso tantas veces como necesites, hasta finalizar la orden.</p>
            <form method="post" action="agregar_productos.php?id=<?= (int) $idOrden ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token_agregar_orden'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="form-grid">
                    <div class="campo">
                        <label for="id_producto">Producto</label>
                        <select id="id_producto" name="id_producto" required>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?= (int) $producto['id_producto'] ?>"
                                        data-precio="<?= htmlspecialchars((string) $producto['precio'], ENT_QUOTES, 'UTF-8') ?>"
                                        <?= (string) ($producto['id_producto']) === (string) ($_POST['id_producto'] ?? '') ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($producto['nombre_producto'], ENT_QUOTES, 'UTF-8') ?> — $<?= number_format((float) $producto['precio'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="cantidad">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" min="1" max="1000" step="1" value="<?= (int) ($_POST['cantidad'] ?? 1) ?>" required>
                    </div>
                </div>
                <p>Subtotal estimado: <strong id="subtotalEstimado">$0.00</strong></p>
                <div class="form-acciones">
                    <button type="submit" class="boton">+ Añadir a la orden</button>
                    <a class="enlace-secundario" href="detalle.php?id=<?= (int) $idOrden ?>">Cancelar</a>
                </div>
            </form>
        </section>
        <script>
        (() => {
            const select = document.getElementById('id_producto');
            const cantidad = document.getElementById('cantidad');
            const texto = document.getElementById('subtotalEstimado');
            const dinero = valor => new Intl.NumberFormat('es-MX', {style: 'currency', currency: 'MXN'}).format(valor);
            function actualizar() {
                const opcion = select.selectedOptions[0];
                const precio = Number(opcion?.dataset.precio ?? 0);
                const unidades = Number(cantidad.value);
                texto.textContent = dinero(unidades >= 1 && Number.isInteger(unidades) ? precio * unidades : 0);
            }
            select.addEventListener('change', actualizar);
            cantidad.addEventListener('input', actualizar);
            actualizar();
        })();
        </script>
    <?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
