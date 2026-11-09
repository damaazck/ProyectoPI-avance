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
SELECT *
FROM ingrediente
WHERE id_ingrediente = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$id]);
$ingrediente = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$ingrediente){
    die("Ingrediente no encontrado.");
}
$errores = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre =
    trim($_POST['nombre']);
    $cantidad =
    trim($_POST['cantidad']);
    if(empty($nombre))
        $errores[] =
        "Nombre obligatorio.";
    if(strlen($nombre) > 100)
        $errores[] =
        "Máximo 100 caracteres.";
    if(!is_numeric($cantidad))
        $errores[] =
        "Cantidad inválida.";
    if($cantidad < 0)
        $errores[] =
        "La cantidad no puede ser negativa.";
    $sql = "
    SELECT COUNT(*)
    FROM ingrediente
    WHERE nombre_ingrediente = ?
    AND id_ingrediente != ?
    ";
    $stmt =
    $conexion->prepare($sql);
    $stmt->execute([
        $nombre,
        $id
    ]);
    if($stmt->fetchColumn() > 0)
        $errores[] =
        "Ya existe otro ingrediente con ese nombre.";
    if(empty($errores)){
        $sql = "
        UPDATE ingrediente
        SET
            nombre_ingrediente = ?,
            cantidad_disponible = ?
        WHERE id_ingrediente = ?
        ";
        $stmt =
        $conexion->prepare($sql);
        $stmt->execute([
            $nombre,
            $cantidad,
            $id
        ]);
        header(
            "Location: listar.php"
        );
        exit();
    }
    $ingrediente['nombre_ingrediente'] = $nombre;
    $ingrediente['cantidad_disponible'] = $cantidad;
}?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Editar Ingrediente</title>
<link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>
<body class="pagina-ingredientes">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<h1>Editar Ingrediente</h1>
<a href="listar.php" class="enlace-secundario">Volver al listado</a>
<a href="../dashboard/index.php" class="enlace-secundario">Dashboard</a><br><br>
<?php foreach($errores as $error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>
<form method="POST">
<label>Nombre</label>
<br><input
    type="text"
    name="nombre"
    maxlength="100"
    required
    value="<?= htmlspecialchars($ingrediente['nombre_ingrediente']) ?>"
><br><br>
<label>Cantidad Disponible</label>
<br><input
    type="number"
    name="cantidad"
    min="0"
    step="0.01"
    required
    value="<?= htmlspecialchars($ingrediente['cantidad_disponible']) ?>"
><br><br>
<button type="submit">Actualizar</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>