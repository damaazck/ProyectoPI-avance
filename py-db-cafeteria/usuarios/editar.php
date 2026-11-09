<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE
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
FROM usuario
WHERE id_usuario = ?
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {

    die("Usuario no encontrado.");

}

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

    $sql = "
    SELECT COUNT(*)
    FROM usuario
    WHERE correo = ?
    AND id_usuario != ?
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        $correo,
        $id
    ]);

    if ($stmt->fetchColumn() > 0) {

        $errores[] =
        "Ya existe otro usuario con ese correo.";

    }

    if (empty($errores)) {

        $sql = "
        UPDATE usuario
        SET
            nombre = ?,
            apellido = ?,
            correo = ?,
            id_rol = ?
        WHERE id_usuario = ?
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->execute([
            $nombre,
            $apellido,
            $correo,
            $idRol,
            $id
        ]);

        header("Location: listar.php");
        exit();

    }

    $usuario['nombre'] = $nombre;
    $usuario['apellido'] = $apellido;
    $usuario['correo'] = $correo;
    $usuario['id_rol'] = $idRol;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuario | Cafetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>
<body class="pagina-usuarios">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<section class="cabecera-pagina">
        <div>
            <span class="etiqueta">Administración / Usuarios</span>
            <h1>Editar usuario</h1>
            <p class="subtexto">Actualiza los datos y el rol de acceso de este usuario.</p>
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
                           value="<?= htmlspecialchars($usuario['nombre']) ?>">
                </div>
                <div class="campo">
                    <label for="apellido">Apellido</label>
                    <input id="apellido" type="text" name="apellido" required autocomplete="family-name"
                           value="<?= htmlspecialchars($usuario['apellido']) ?>">
                </div>
                <div class="campo campo-completo">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" type="email" name="correo" required autocomplete="email"
                           value="<?= htmlspecialchars($usuario['correo']) ?>">
                </div>
                <div class="campo campo-completo">
                    <label for="id_rol">Rol de usuario</label>
                    <select id="id_rol" name="id_rol">
                        <?php foreach($roles as $rol): ?>
                            <option value="<?= (int)$rol['id_rol'] ?>"
                                    <?= $rol['id_rol'] == $usuario['id_rol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombre_rol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-acciones">
                <button type="submit">Actualizar usuario</button>
                <a class="boton boton-secundario" href="listar.php">Cancelar</a>
            </div>
        </form>
    </div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
