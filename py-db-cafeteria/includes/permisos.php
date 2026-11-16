<?php
require_once 'verificar_sesion.php';
require_once 'roles.php';
function verificarRol(array $rolesPermitidos)
{
    if (
        !in_array(
            $_SESSION['id_rol'],
            $rolesPermitidos
        )
    ) {
        die("
            <h2>Acceso denegado</h2>
            <p>No tiene permisos para acceder a esta sección.</p>
        ");
    }
}