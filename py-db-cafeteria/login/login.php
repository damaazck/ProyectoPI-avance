<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header('Location: ../dashboard/index.php');
    exit();
}

$errores = $_SESSION['login_errores'] ?? [];
$correoGuardado = $_SESSION['login_correo'] ?? '';
unset($_SESSION['login_errores'], $_SESSION['login_correo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Cafetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
    <link rel="stylesheet" href="../assets/css/login-validacion.css">
</head>
<body class="pagina-login">
    <?php require __DIR__ . '/../includes/header.php'; ?>

    <main class="contenedor">
        <section class="login-tarjeta" aria-labelledby="titulo-login">
            <span class="etiqueta">Acceso al sistema</span>
            <h1 id="titulo-login">Bienvenido</h1>
            <p class="subtexto">Inicia sesión para acceder a la cafetería.</p>

            <?php if (isset($errores['general'])): ?>
                <p class="alerta-login" role="alert">
                    <?= htmlspecialchars($errores['general'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form action="validar_login.php" method="POST" novalidate>
                <label for="correo">Correo electrónico</label>
                <input
                    type="email"
                    id="correo"
                    name="correo"
                    autocomplete="username"
                    placeholder="correo@ejemplo.com"
                    value="<?= htmlspecialchars($correoGuardado, ENT_QUOTES, 'UTF-8') ?>"
                    <?= isset($errores['correo']) ? 'class="input-error" aria-invalid="true" aria-describedby="correo-error"' : '' ?>
                >
                <?php if (isset($errores['correo'])): ?>
                    <small id="correo-error" class="mensaje-campo" role="alert">
                        <?= htmlspecialchars($errores['correo'], ENT_QUOTES, 'UTF-8') ?>
                    </small>
                <?php endif; ?>

                <label for="contrasena">Contraseña</label>
                <input
                    type="password"
                    id="contrasena"
                    name="contrasena"
                    autocomplete="current-password"
                    placeholder="Ingresa tu contraseña"
                    <?= isset($errores['contrasena']) ? 'class="input-error" aria-invalid="true" aria-describedby="contrasena-error"' : '' ?>
                >
                <?php if (isset($errores['contrasena'])): ?>
                    <small id="contrasena-error" class="mensaje-campo" role="alert">
                        <?= htmlspecialchars($errores['contrasena'], ENT_QUOTES, 'UTF-8') ?>
                    </small>
                <?php endif; ?>

                <button type="submit" class="boton-secundario">Iniciar sesión</button>
            </form>

            <hr>
            <p class="subtexto texto-demo">¿Solo quieres probar nuestra cafetería?</p>
            <form action="entrar_demo.php" method="POST">
                <button type="submit" class="btn-visitante">Ingresar como visitante</button>
            </form>
        </section>
    </main>

    <footer class="pie-pagina">
        <p>&copy; <?= date('Y') ?> Cafetería · Sistema de administración</p>
    </footer>
</body>
</html>
