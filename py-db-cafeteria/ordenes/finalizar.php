<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE,
    EMPLEADO
]);

require '../config/conexion.php';

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    header("Location: listar.php");
    exit();

}

$idOrden = (int)$_GET['id'];

$sql = "
SELECT
    id_orden,
    estado
FROM ordenes
WHERE id_orden = ?
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$idOrden]);

$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {

    die("
        <h2>Error</h2>
        <p>La orden no existe.</p>
        <a href='listar.php'>Volver</a>
    ");

}

if ($orden['estado'] === 'Finalizada') {

    die("
        <h2>Información</h2>
        <p>La orden ya está finalizada.</p>
        <a href='listar.php'>Volver</a>
    ");

}

$sql = "
UPDATE ordenes
SET estado = 'Finalizada'
WHERE id_orden = ?
";

$stmt = $conexion->prepare($sql);
$stmt->execute([$idOrden]);

header("Location: listar.php");
exit();

?>