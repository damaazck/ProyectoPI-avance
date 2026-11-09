<?php
require_once __DIR__ . '/roles.php';
$sesionActiva = isset($_SESSION['id_usuario']);
$rolActual = (int) ($_SESSION['id_rol'] ?? 0);
$seccionActual = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
?>
<header class="encabezado">
    <div class="barra-navegacion">
        <a class="marca" href="<?= $sesionActiva ? '../dashboard/index.php' : '../login/login.php' ?>" aria-label="Cafetería - Inicio">
            <span class="marca-logo" aria-label="logo">
                <img src="../assets/img/logocafeteria.svg" alt="logo">
            </span>
            <span class="marca-nombre">Catfetería<small>Panel de administración</small></span>
        </a>

        <nav class="menu" aria-label="Navegación principal">
            <?php if ($sesionActiva): ?>
                <a href="../dashboard/index.php" <?= $seccionActual === 'dashboard' ? 'class="activo" aria-current="page"' : '' ?>>Inicio</a>
                <?php if (in_array($rolActual, [ADMIN, GERENTE, SUBGERENTE], true)): ?>
                    <a href="../productos/listar.php" <?= $seccionActual === 'productos' ? 'class="activo" aria-current="page"' : '' ?>>Productos</a>
                    <a href="../ingredientes/listar.php" <?= $seccionActual === 'ingredientes' ? 'class="activo" aria-current="page"' : '' ?>>Ingredientes</a>
                <?php endif; ?>
                <?php if (in_array($rolActual, [ADMIN, GERENTE], true)): ?>
                    <a href="../usuarios/listar.php" <?= $seccionActual === 'usuarios' ? 'class="activo" aria-current="page"' : '' ?>>Usuarios</a>
                <?php endif; ?>
                <a href="../ordenes/listar.php" <?= $seccionActual === 'ordenes' && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'nueva.php' ? 'class="activo" aria-current="page"' : '' ?>>Órdenes</a>
                <a class="menu-cta" href="../ordenes/nueva.php">+ Nueva orden</a>
                <a class="menu-salir" href="../logout.php">Salir</a>
            <?php else: ?>
                <a href="../login/login.php" class="activo" aria-current="page">Iniciar sesión</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
