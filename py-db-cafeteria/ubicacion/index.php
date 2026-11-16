<?php
// Página pública: no solicita la ubicación del visitante.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = require __DIR__ . '/config.php';

$ubicacion = [
    'nombre' => (string) $config['sede'],
    'lat' => (float) $config['latitud'],
    'lng' => (float) $config['longitud'],
];
$ubicacionJson = json_encode(
    $ubicacion,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require __DIR__ . '/../includes/analytics.php'; ?>
    <meta name="description" content="Conoce la ubicación de referencia de Catfetería en UACJ IADA/IIT.">
    <title>¿Dónde nos encontramos? | Catfetería</title>
    <link rel="stylesheet" href="../assets/css/estiloouu.css" >
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="../assets/css/ubicacion.css?v=3">
    <script>window.UBICACION_CAFETERIA = <?= $ubicacionJson ?>;</script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
    <script src="../assets/js/geolocalizacion.js?v=3" defer></script>
</head>
<body class="pagina-ubicacion">
<?php require __DIR__ . '/../includes/header.php'; ?>

<main class="contenedor">
    <section class="ubicacion-intro" aria-labelledby="tituloUbicacion">
        <p>
            <a class="enlace-secundario" href="<?= isset($_SESSION['id_usuario']) ? '../dashboard/index.php' : '../login/login.php' ?>">
                ← Volver al inicio
            </a>
        </p>
        <span class="etiqueta">Catfetería · Ubicación</span>
        <h1 id="tituloUbicacion">¿Dónde nos encontramos?</h1>
        <p class="subtexto">Encuéntranos en el mapa de la UACJ, campus IADA/IIT.</p>
    </section>

    <section class="ubicacion-panel" aria-labelledby="tituloMapa">
        <div class="ubicacion-cabecera">
            <div>
                <span class="etiqueta">Mapa interactivo</span>
                <h2 id="tituloMapa">UACJ · IADA/IIT</h2>
                <p class="ubicacion-direccion">
                    <?= htmlspecialchars((string) $config['direccion'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
            <button class="boton ubicacion-boton" type="button" id="centrarMapa">
                <span aria-hidden="true">⌖</span> Centrar mapa
            </button>
        </div>
        <div
            id="mapaUbicacion"
            class="ubicacion-mapa"
            role="region"
            aria-label="Mapa interactivo centrado en UACJ IADA/IIT"
        ></div>
        <p id="estadoMapa" class="ubicacion-estado" role="status" aria-live="polite" hidden></p>
    </section>

    <p class="ubicacion-nota">Ubicación académica de referencia para esta demostración; no indica una sucursal real de Catfetería.</p>

<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
