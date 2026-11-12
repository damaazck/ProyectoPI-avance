
<?php
session_start();

require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$correoDemo = 'visitante@cafeteria.com';

$sql = "SELECT u.id_usuario, u.nombre, u.correo,
               u.id_rol, r.nombre_rol
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        WHERE u.correo = ?
        AND u.estado = 'Activo'
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute([$correoDemo]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die('No existe la cuenta de demostración.');
}

session_regenerate_id(true);

$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nombre'] = $usuario['nombre'];
$_SESSION['correo'] = $usuario['correo'];
$_SESSION['id_rol'] = $usuario['id_rol'];
$_SESSION['rol'] = $usuario['nombre_rol'];

header('Location: ../dashboard/index.php');
exit();
?>
