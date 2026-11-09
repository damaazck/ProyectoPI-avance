<?php
/*session_start();

if (!isset($_SESSION['id_usuario'])) {

    header("Location: ../login/login.php");
    exit();

}
*/
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['id_usuario'] = 1;
    $_SESSION['nombre'] = 'Visitante';
    $_SESSION['rol'] = 'Administrador';
    $_SESSION['id_rol'] = 1;
}
