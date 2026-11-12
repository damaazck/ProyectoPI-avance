<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$correo = trim((string) ($_POST['correo'] ?? ''));
$contrasena = (string) ($_POST['contrasena'] ?? '');
$errores = [];

if ($correo === '') {
    $errores['correo'] = 'Escribe tu correo electrónico.';
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores['correo'] = 'Escribe un correo electrónico válido.';
}

if ($contrasena === '') {
    $errores['contrasena'] = 'Escribe tu contraseña.';
}

function volverAlLogin(array $errores, string $correo): void
{
    $_SESSION['login_errores'] = $errores;
    $_SESSION['login_correo'] = $correo;
    header('Location: login.php');
    exit();
}

if ($errores) {
    volverAlLogin($errores, $correo);
}

require '../config/conexion.php';

try {
    $sql = "SELECT u.*, r.nombre_rol
            FROM usuario u
            INNER JOIN rol r ON u.id_rol = r.id_rol
            WHERE u.correo = ? AND u.estado = 'Activo'
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error al validar login: ' . $e->getMessage());
    volverAlLogin(['general' => 'No se pudo validar el acceso. Intenta nuevamente.'], $correo);
}

if (!$usuario || !hash_equals((string) $usuario['contrasena'], $contrasena)) {
    volverAlLogin(['general' => 'Correo o contraseña incorrectos, o cuenta inactiva.'], $correo);
}

session_regenerate_id(true);
unset($_SESSION['login_errores'], $_SESSION['login_correo']);

$_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
$_SESSION['nombre'] = $usuario['nombre'];
$_SESSION['correo'] = $usuario['correo'];
$_SESSION['id_rol'] = (int) $usuario['id_rol'];
$_SESSION['rol'] = $usuario['nombre_rol'];

header('Location: ../dashboard/index.php');
exit();
