<?php

$host = "sql212.infinityfree.com";
$db = "if0_42714501_py_cafeteria";
$user = "if0_42714501";
$pass = "6WNfBEF0GanALR";

try {

    $conexion = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    error_log($e->getMessage());
    die("No se pudo conectar a la base de datos.");

}
?>