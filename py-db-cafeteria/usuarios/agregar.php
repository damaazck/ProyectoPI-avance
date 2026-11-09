<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE
]);

require '../config/conexion.php';

$sql = "
SELECT *
FROM rol
ORDER BY nombre_rol
";

$stmt = $conexion->query($sql);

$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);
    $idRol = (int)$_POST['id_rol'];

    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    }

    if (empty($apellido)) {
        $errores[] = "El apellido es obligatorio.";
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo inválido.";
    }

    if (empty($contrasena)) {
        $errores[] = "La contraseña es obligatoria.";
    }

    $sql = "
    SELECT COUNT(*)
    FROM usuario
    WHERE correo = ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $correo
    ]);

    if ($stmt->fetchColumn() > 0) {

        $errores[] =
        "Ya existe un usuario con ese correo.";

    }

    if (empty($errores)) {

        $sql = "
        INSERT INTO usuario
        (
            nombre,
            apellido,
            correo,
            contrasena,
            id_rol,
            estado
        )
        VALUES
        (
            ?, ?, ?, ?, ?, 'Activo'
        )
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            $nombre,
            $apellido,
            $correo,
            $contrasena,
            $idRol
        ]);

        header("Location: listar.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo usuario | Cafetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>
<body class="pagina-usuarios">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<section class="cabecera-pagina">
        <div>
            <span class="etiqueta">Administración / Usuarios</span>
            <h1>Nuevo usuario</h1>
            <p class="subtexto">Crea una cuenta y selecciona el rol que tendrá dentro del sistema.</p>
        </div>
        <a class="enlace-secundario" href="listar.php">← Volver al listado</a>
    </section>

    <?php foreach($errores as $error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <div class="formulario-panel">
        <form method="POST">
            <div class="form-grid">
                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" type="text" name="nombre" required autocomplete="given-name"
                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" type="text" name="apellido" required autocomplete="family-name"
                           value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" type="email" name="correo" required autocomplete="email"
                           value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>">
                </div>
                <div class="campo">
                    <label for="contrasena">Contraseña</label>
                    <input id="contrasena" type="password" name="contrasena" required autocomplete="new-password">
                </div>
                <div class="campo campo-completo">
                    <label for="id_rol">Rol de usuario</label>
                    <select id="id_rol" name="id_rol">
                        <?php foreach($roles as $rol): ?>
                            <option value="<?= (int)$rol['id_rol'] ?>"
                                <?= (int)$rol['id_rol'] === (int)($_POST['id_rol'] ?? 0) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombre_rol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-acciones">
                <button type="submit">Guardar usuario</button>
                <a class="boton boton-secundario" href="listar.php">Cancelar</a>
            </div>
        </form>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
