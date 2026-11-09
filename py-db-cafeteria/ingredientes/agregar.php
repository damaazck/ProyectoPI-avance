<?php
require '../includes/permisos.php';
verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE
]);
require '../config/conexion.php';
$errores = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre =
    trim($_POST['nombre']);
    $cantidad =
    trim($_POST['cantidad']);
    if(empty($nombre))
        $errores[] =
        "El nombre es obligatorio.";
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
    ";
    $stmt =
    $conexion->prepare($sql);
    $stmt->execute([$nombre]);
    if($stmt->fetchColumn() > 0)
        $errores[] =
        "Ya existe un ingrediente con ese nombre.";
    if(empty($errores)){
        $sql = "
        INSERT INTO ingrediente
        (
            nombre_ingrediente,
            cantidad_disponible
        )
        VALUES
        (
            ?,?
        )
        ";
        $stmt =
        $conexion->prepare($sql);
        $stmt->execute([
            $nombre,
            $cantidad
        ]);
        header(
            "Location: listar.php"
        );
        exit();
    }
}?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Agregar Ingrediente</title>
<link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>
<body class="pagina-ingredientes">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<h1>Nuevo Ingrediente</h1>
<a href="listar.php" class="enlace-secundario">
Volver al listado
</a>
<a href="../dashboard/index.php" class="enlace-secundario">Dashboard</a><br><br>
<?php foreach($errores as $error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endforeach; ?>
<form method="POST">
<input
type="text"
name="nombre"
placeholder="Nombre"
maxlength="100"
required
><br><br>
<input
type="number"
name="cantidad"
step="0.01"
min="0"
required
>
<br><br>
<button type="submit">Guardar</button></form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>