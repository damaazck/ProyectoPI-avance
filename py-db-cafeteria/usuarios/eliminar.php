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

if ($id == $_SESSION['id_usuario']) {

    die(
        "No puede desactivar su propia cuenta."
    );

}

$sql = "
SELECT id_usuario
FROM usuario
WHERE id_usuario = ?
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    $id
]);

if (!$stmt->fetch()) {

    die("Usuario no encontrado.");

}

$sql = "
UPDATE usuario
SET estado = 'Inactivo'
WHERE id_usuario = ?
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    $id
]);

header("Location: listar.php");
exit();