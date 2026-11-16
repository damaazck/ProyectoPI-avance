<?php
require '../includes/permisos.php';
verificarRol([ADMIN, GERENTE, SUBGERENTE]);
require '../config/conexion.php';

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id']) || (int)$_GET['id'] <= 0) {
    header('Location: listar.php');
    exit();
}

$id = (int) $_GET['id'];
$sql = "DELETE FROM producto WHERE id_producto = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);

header('Location: listar.php');
exit();
