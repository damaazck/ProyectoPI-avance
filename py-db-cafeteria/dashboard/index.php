<?php

require '../includes/verificar_sesion.php';
require '../includes/roles.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>

<body class="pagina-dashboard">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
    <section class="intro-dashboard" aria-label="Bienvenida">
        <div>
            <span class="etiqueta">Panel de control</span>
            <h1>Bienvenido a Cafetería</h1>
            <p class="subtexto">Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>. Gestiona tus tareas desde un solo lugar.</p>
        </div>
        <span class="etiqueta-rol">Rol: <?= htmlspecialchars($_SESSION['rol']) ?></span>
    </section>

    <h2 class="titulo-seccion">Menú principal</h2>
    <section class="rejilla-modulos" aria-label="Accesos del sistema">
        <?php if (in_array((int)$_SESSION['id_rol'], [ADMIN, GERENTE, SUBGERENTE], true)): ?>
            <a class="tarjeta" href="../productos/listar.php">
                <span class="tarjeta-icono" aria-hidden="true">▦</span>
                <h3>Productos</h3>
                <p>Administra los productos y precios de la cafetería.</p>
                <span class="tarjeta-cta">Ver productos</span>
            </a>
            <a class="tarjeta" href="../ingredientes/listar.php">
                <span class="tarjeta-icono" aria-hidden="true">◫</span>
                <h3>Ingredientes</h3>
                <p>Consulta y actualiza el inventario disponible.</p>
                <span class="tarjeta-cta">Ver ingredientes</span>
            </a>
        <?php endif; ?>
        <?php if (in_array((int)$_SESSION['id_rol'], [ADMIN, GERENTE], true)): ?>
            <a class="tarjeta" href="../usuarios/listar.php">
                <span class="tarjeta-icono" aria-hidden="true">♙</span>
                <h3>Usuarios</h3>
                <p>Gestiona las cuentas y los permisos de acceso.</p>
                <span class="tarjeta-cta">Ver usuarios</span>
            </a>
        <?php endif; ?>
        <!-- Mercancía: catálogo alimentado por JSON y ventas de prueba -->
        <a class="tarjeta" href="../mercancia/index.php">
            <span class="tarjeta-icono" aria-hidden="true">▤</span>
            <h3>Mercancía</h3>
            <p>Consulta tazas, bolsas de café, termos y peluches. Edita artículos y registra ventas.</p>
            <span class="tarjeta-cta">Ver mercancía</span>
        </a>
        <article class="tarjeta tarjeta-destacada">
            <span class="tarjeta-icono" aria-hidden="true">＋</span>
            <h3>Nueva orden</h3>
            <p>Registra una venta y añade productos al pedido.</p>
            <a class="boton" href="../ordenes/nueva.php">Crear orden</a>
        </article>
        <a class="tarjeta" href="../ordenes/listar.php">
            <span class="tarjeta-icono" aria-hidden="true">≡</span>
            <h3>Historial de órdenes</h3>
            <p>Revisa el detalle y el estado de las órdenes.</p>
            <span class="tarjeta-cta">Ver historial</span>
        </a>
    </section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
