<?php
require '../includes/permisos.php';
verificarRol([
    ADMIN,
    GERENTE,
    SUBGERENTE
]);
require '../config/conexion.php';
$sql = "SELECT * FROM ingrediente";
$stmt = $conexion->query($sql);
$ingredientes =
$stmt->fetchAll(PDO::FETCH_ASSOC);?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<title>Ingredientes</title>
<link rel="stylesheet" href="../assets/css/estiloou.css" >
<body class="pagina-ingredientes">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<h1>Ingredientes</h1>
<br><br>
<a href="agregar.php" class="boton">
    Nuevo Ingrediente
</a>
<br><br>
<div class="tabla-responsive">
<table>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Cantidad Disponible</th>
<th>Acciones</th>
</tr>
<?php foreach($ingredientes as $ingrediente): ?>
<tr>
<td>
<?= $ingrediente['id_ingrediente'] ?>
</td>
<td>
<?= htmlspecialchars(
    $ingrediente['nombre_ingrediente']
) ?>
</td>
<td>
<?= htmlspecialchars(
    $ingrediente['cantidad_disponible']
) ?>
</td>
<td>
<a href="editar.php?id=<?= $ingrediente['id_ingrediente'] ?>">Editar</a>
|
<a
href="eliminar.php?id=<?= $ingrediente['id_ingrediente'] ?>"
onclick="return confirm('¿Eliminar ingrediente?')"
>Eliminar</a>
</td>
</tr>
<?php endforeach; ?>
</table></div>
<a class="enlace-secundario" href="../dashboard/index.php">← Volver al dashboard</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>>
</body>
</html>