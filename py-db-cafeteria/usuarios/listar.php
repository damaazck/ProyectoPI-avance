<?php

require '../includes/permisos.php';

verificarRol([
    ADMIN,
    GERENTE
]);

require '../config/conexion.php';

$sql = "
SELECT
    u.*,
    r.nombre_rol
FROM usuario u
INNER JOIN rol r
ON u.id_rol = r.id_rol
ORDER BY u.id_usuario DESC
";

$stmt = $conexion->query($sql);

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios | Cafetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
</head>
<body class="pagina-usuarios">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
<section class="cabecera-pagina">
        <div>
            <span class="etiqueta">Administración / Accesos</span>
            <h1>Usuarios</h1>
            <p class="subtexto">Administra las cuentas, sus roles y el acceso al sistema.</p>
        </div>
        <div class="barra-acciones">
            <a class="boton" href="agregar.php">+ Nuevo usuario</a>
        </div>
    </section>

    <div class="tabla-responsive">
        <table>
            <thead>
                <tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                    <tr>
                        <td><?= (int)$usuario['id_usuario'] ?></td>
                        <td><strong><?= htmlspecialchars($usuario['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($usuario['apellido']) ?></td>
                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td><?= htmlspecialchars($usuario['nombre_rol']) ?></td>
                        <td><span class="estado-etiqueta"><?= htmlspecialchars($usuario['estado']) ?></span></td>
                        <td>
                            <div class="acciones-tabla">
                                <a href="editar.php?id=<?= (int)$usuario['id_usuario'] ?>">Editar</a>
                                <?php if ($usuario['id_usuario'] != $_SESSION['id_usuario']): ?>
                                    <?php if ($usuario['estado'] === 'Activo'): ?>
                                        <a class="enlace-eliminar" href="eliminar.php?id=<?= (int)$usuario['id_usuario'] ?>"
                                           onclick="return confirm('¿Desactivar usuario?')">Desactivar</a>
                                    <?php else: ?>
                                        <a href="reactivar.php?id=<?= (int)$usuario['id_usuario'] ?>"
                                           onclick="return confirm('¿Reactivar usuario?')">Reactivar</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="sesion-actual">Sesión actual</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (empty($usuarios)): ?>
        <p class="aviso-vacio">Todavía no hay usuarios registrados.</p>
    <?php endif; ?>
    <a class="enlace-secundario" href="../dashboard/index.php">← Volver al dashboard</a>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
