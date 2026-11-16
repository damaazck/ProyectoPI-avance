<?php
require '../includes/permisos.php';
verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE
]);
require '../config/conexion.php';
if(
    !isset($_GET['id'])
    ||
    !is_numeric($_GET['id'])
){
    header("Location: listar.php");
    exit();
}
$id = (int)$_GET['id'];
$sql = "
SELECT id_ingrediente
FROM ingrediente
WHERE id_ingrediente = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
if(!$stmt->fetch()){
    die("Ingrediente no encontrado.");
}
$sql = "
DELETE FROM ingrediente
WHERE id_ingrediente = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
header("Location: listar.php");
exit();