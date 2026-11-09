<?php
// Módulo público de demostración: sin login, ventas y edición de inventario de prueba.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_mercancia'])) {
    $_SESSION['csrf_mercancia'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_mercancia'], ENT_QUOTES, 'UTF-8') ?>">
    <title>Mercancía | Catfetería</title>
    <link rel="stylesheet" href="../assets/css/estiloou.css" >
    <script src="../assets/js/mercancia.js" defer></script>
</head>
<body class="pagina-mercancia">
<?php require __DIR__ . '/../includes/header.php'; ?>
<main class="contenedor">
    <section class="merch-intro">
        <div>
            <p><a href="../dashboard/index.php" class="enlace-secundario">← Volver al dashboard</a></p>
            <span class="etiqueta">Tienda · Inventario de prueba</span>
            <h1>Mercancía de la cafetería</h1>
            <p class="subtexto">Tazas, café en bolsa, termos, peluches y pequeños regalos.</p>
        </div>
        <button type="button" id="nuevoProducto" class="boton merch-nuevo">+ Añadir producto</button>
    </section>

    <section class="merch-estadisticas" aria-label="Resumen de mercancía">
        <div><span>Productos</span><strong id="statProductos">—</strong></div>
        <div><span>Unidades disponibles</span><strong id="statExistencias">—</strong></div>
        <div><span>Ventas registradas</span><strong id="statVentas">—</strong></div>
    </section>

    <div class="merch-layout">
        <section class="merch-catalogo" aria-labelledby="tituloCatalogo">
            <div class="merch-controles">
                <div><label for="buscarMercancia">Buscar mercancía</label><input type="search" id="buscarMercancia" placeholder="Tazas, café, peluches..." autocomplete="off"></div>
                <div><label for="filtrarCategoria">Categoría</label><select id="filtrarCategoria"><option value="">Todas</option><option value="Tazas">Tazas</option><option value="Café">Café</option><option value="Termos">Termos</option><option value="Peluches">Peluches</option><option value="Accesorios">Accesorios</option></select></div>
            </div>
            <div class="merch-seccion-titulo"><h2 id="tituloCatalogo">Nuestro catálogo</h2><span id="contadorMercancia">10 productos</span></div>
            <p id="estadoMercancia" class="merch-mensaje" role="status" aria-live="polite">Cargando productos...</p>
            <div class="merch-grid" id="rejillaMercancia"></div>
            <p id="mercanciaVacia" class="merch-mensaje" hidden>No hay artículos que coincidan con tu búsqueda.</p>
        </section>

        <aside class="merch-lateral" aria-label="Venta de mercancía">
            <section class="merch-carrito" aria-labelledby="tituloCarrito">
                <div class="merch-lateral-cabecera"><div><span class="etiqueta">Punto de venta</span><h2 id="tituloCarrito">Tu carrito</h2></div><span class="merch-cart-count" id="cartCount">0</span></div>
                <p id="carritoVacio" class="merch-carrito-vacio">Añade un producto para comenzar una venta.</p>
                <div id="listaCarrito" class="merch-carrito-items"></div>
                <div class="merch-carrito-final">
                    <div class="merch-total"><span>Total</span><strong id="totalCarrito">$0.00</strong></div>
                    <button type="button" id="confirmarVenta" class="boton merch-vender" disabled>Registrar venta</button>
                    <p class="merch-ayuda">La venta descuenta automáticamente las existencias del JSON.</p>
                </div>
            </section>
            <section class="merch-historial" aria-labelledby="tituloHistorial">
                <div class="merch-seccion-titulo"><h2 id="tituloHistorial">Últimas ventas</h2></div>
                <div id="historialVentas"><p class="merch-carrito-vacio">Todavía no hay ventas registradas.</p></div>
            </section>
        </aside>
    </div>

<dialog id="modalProducto" class="merch-modal" aria-labelledby="tituloModal">
    <form id="formProducto" novalidate>
        <div class="merch-modal-header"><div><span class="etiqueta">Inventario de demostración</span><h2 id="tituloModal">Editar mercancía</h2></div><button type="button" class="boton-secundario" id="cerrarModal">Cerrar</button></div>
        <input type="hidden" name="id" id="productoId" value="0">
        <div class="merch-form-grid">
            <div class="merch-campo merch-full"><label for="productoNombre">Nombre del artículo</label><input id="productoNombre" name="nombre" type="text" maxlength="90" required></div>
            <div class="merch-campo"><label for="productoCategoria">Categoría</label><select id="productoCategoria" name="categoria" required><option value="Tazas">Tazas</option><option value="Café">Café</option><option value="Termos">Termos</option><option value="Peluches">Peluches</option><option value="Accesorios">Accesorios</option></select></div>
            <div class="merch-campo"><label for="productoCantidad">Cantidad en stock</label><input id="productoCantidad" name="cantidad" type="number" min="0" max="100000" step="1" required></div>
            <div class="merch-campo"><label for="productoPrecio">Precio (MXN)</label><input id="productoPrecio" name="precio" type="number" min="0" max="1000000" step="0.01" required></div>
            <div class="merch-campo merch-full"><label for="productoDescripcion">Descripción</label><textarea id="productoDescripcion" name="descripcion" maxlength="500" minlength="3" rows="3" required></textarea></div>
        </div>
        <div class="merch-modal-acciones"><button type="button" class="boton-secundario merch-borrar" id="eliminarProducto" hidden>Eliminar artículo</button><button type="button" class="boton-secundario" id="cancelarProducto">Cancelar</button><button type="submit" class="boton" id="guardarProducto">Guardar cambios</button></div>
    </form>
</dialog>

<div class="merch-toast" id="avisoMercancia" role="status" aria-live="polite" hidden></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
