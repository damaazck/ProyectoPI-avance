/* MERCANCÍA · Los datos NO están escritos en HTML.
   Este JavaScript consume la API local (mercancia/api.php) que lee y escribe
   mercancia/data/inventario.json. Ventas y existencias se validan en PHP. */
(() => {
    'use strict';
    const API = 'api.php';
    const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const $ = id => document.getElementById(id);
    const dinero = valor => new Intl.NumberFormat('es-MX', {style:'currency', currency:'MXN'}).format(valor);
    const nodo = (tag, clase = '', texto = null) => {
        const el = document.createElement(tag);
        if (clase) el.className = clase;
        if (texto !== null) el.textContent = String(texto);
        return el;
    };
    const normalizar = valor => String(valor).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

    let productos = [];
    let carrito = new Map();
    let cargando = false;
    let toastTimeout;

    function aviso(mensaje, error = false) {
        const e = $('avisoMercancia');
        e.textContent = mensaje;
        e.classList.toggle('merch-toast-error', error);
        e.hidden = false;
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => { e.hidden = true; }, 5000);
    }
    async function solicitud(datos = null) {
        const configuracion = datos ? {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'X-CSRF-Token':token},
            body: JSON.stringify(datos)
        } : { cache: 'no-store' };
        let respuesta;
        try { respuesta = await fetch(API, configuracion); }
        catch (_) { throw new Error('No se pudo conectar con la API local. Revisa Apache y la ruta de la página.'); }
        const json = await respuesta.json().catch(() => null);
        if (!respuesta.ok || !json?.ok) {
            throw new Error(json?.mensaje || `Error HTTP ${respuesta.status}.`);
        }
        return json;
    }

    async function cargar() {
        if (cargando) return;
        cargando = true;
        $('estadoMercancia').hidden = false;
        $('estadoMercancia').textContent = 'Cargando mercancía desde el JSON...';
        try {
            const data = await solicitud();
            productos = Array.isArray(data.productos) ? data.productos : [];
            $('statProductos').textContent = productos.length;
            $('statExistencias').textContent = productos.reduce((total,p) => total + p.cantidad, 0);
            $('statVentas').textContent = data.totalVentas ?? 0;
            $('estadoMercancia').hidden = true;
            // Se respetan cambios en existencias hechos desde otra pestaña.
            for (const [id,cantidad] of carrito) {
                const p = productos.find(p => p.id === id);
                if (!p || p.cantidad === 0) carrito.delete(id);
                else if (cantidad > p.cantidad) carrito.set(id, p.cantidad);
            }
            mostrarProductos();
            mostrarCarrito();
            mostrarVentas(data.ventas ?? []);
        } catch (err) {
            $('estadoMercancia').textContent = err.message;
            aviso(err.message, true);
        } finally { cargando = false; }
    }

    function mostrarProductos() {
        const busqueda = normalizar($('buscarMercancia').value.trim());
        const categoria = $('filtrarCategoria').value;
        const lista = productos.filter(p =>
            (!categoria || p.categoria === categoria) &&
            normalizar(`${p.nombre} ${p.descripcion} ${p.categoria}`).includes(busqueda)
        );
        $('contadorMercancia').textContent = `${lista.length} artículo${lista.length === 1 ? '' : 's'}`;
        $('mercanciaVacia').hidden = lista.length !== 0;
        const rejilla = $('rejillaMercancia');
        rejilla.replaceChildren();
        lista.forEach(p => {
            const tarjeta = nodo('article', 'merch-tarjeta');
            const imagen = nodo('div', 'merch-imagen');
            imagen.setAttribute('aria-hidden','true');
            imagen.append(nodo('span', 'merch-imagen-emoji', p.icono || '☕'));
            const categoria = nodo('span', 'merch-categoria', p.categoria);
            const nombre = nodo('h3', '', p.nombre);
            const descripcion = nodo('p', 'merch-descripcion', p.descripcion);
            const abajo = nodo('div', 'merch-tarjeta-abajo');
            const precio = nodo('strong', 'merch-precio', dinero(p.precio));
            const stock = nodo('span', 'merch-stock', p.cantidad > 0 ? `${p.cantidad} disponibles` : 'Agotado');
            const acciones = nodo('div', 'merch-tarjeta-acciones');
            const editar = nodo('button', 'boton-secundario merch-editar', 'Editar');
            editar.type = 'button';
            editar.setAttribute('aria-label', 'Editar ' + p.nombre);
            editar.addEventListener('click', () => abrirEdicion(p.id));
            const comprar = nodo('button', 'boton merch-agregar', 'Añadir +');
            comprar.type = 'button';
            comprar.disabled = p.cantidad < 1;
            comprar.setAttribute('aria-label', 'Añadir al carrito: ' + p.nombre);
            comprar.addEventListener('click', () => agregarCarrito(p.id));
            acciones.append(editar, comprar);
            abajo.append(precio, stock);
            tarjeta.append(imagen, categoria, nombre, descripcion, abajo, acciones);
            rejilla.append(tarjeta);
        });
    }

    function agregarCarrito(id) {
        const p = productos.find(p => p.id === id);
        if (!p) return;
        const actual = carrito.get(id) || 0;
        if (actual >= p.cantidad) { aviso('Ya seleccionaste todas las existencias disponibles.', true); return; }
        carrito.set(id, actual + 1);
        mostrarCarrito();
        aviso(`${p.nombre} añadido al carrito.`);
    }
    function actualizarCantidad(id, diferencia) {
        const p = productos.find(p => p.id === id);
        if (!p) return;
        const nueva = (carrito.get(id) ?? 0) + diferencia;
        if (nueva <= 0) carrito.delete(id);
        else if (nueva <= p.cantidad) carrito.set(id, nueva);
        else aviso('No hay más unidades disponibles.', true);
        mostrarCarrito();
    }
    function mostrarCarrito() {
        const lista = $('listaCarrito');
        lista.replaceChildren();
        let total = 0;
        let unidades = 0;
        for (const [id,cantidad] of carrito) {
            const p = productos.find(p => p.id === id);
            if (!p) continue;
            total += p.precio * cantidad;
            unidades += cantidad;
            const fila = nodo('div', 'merch-linea-carrito');
            const icono = nodo('span', 'merch-mini-emoji', p.icono || '☕');
            icono.setAttribute('aria-hidden', 'true');
            const info = nodo('div', 'merch-linea-info');
            info.append(nodo('strong', '', p.nombre), nodo('span', '', dinero(p.precio)));
            const controles = nodo('div', 'merch-cantidad');
            const menos = nodo('button', 'boton-secundario', '−');
            menos.type = 'button';
            menos.setAttribute('aria-label', 'Quitar una unidad de ' + p.nombre);
            menos.addEventListener('click', () => actualizarCantidad(id, -1));
            const valor = nodo('span','',cantidad);
            const mas = nodo('button','boton-secundario','+');
            mas.type = 'button';
            mas.disabled = cantidad >= p.cantidad;
            mas.setAttribute('aria-label', 'Añadir una unidad de ' + p.nombre);
            mas.addEventListener('click', () => actualizarCantidad(id, +1));
            controles.append(menos, valor, mas);
            fila.append(icono, info, controles);
            lista.append(fila);
        }
        $('carritoVacio').hidden = unidades > 0;
        $('totalCarrito').textContent = dinero(total);
        $('cartCount').textContent = unidades;
        $('confirmarVenta').disabled = unidades === 0;
    }

    function mostrarVentas(ventas) {
        const contenedor = $('historialVentas');
        contenedor.replaceChildren();
        if (!ventas.length) {
            contenedor.append(nodo('p','merch-carrito-vacio','Todavía no hay ventas registradas.'));
            return;
        }
        for (const venta of ventas) {
            const fila = nodo('div','merch-venta-fila');
            const datos = nodo('div');
            datos.append(nodo('strong','',`Venta #${venta.id}`));
            const fecha = new Date(venta.fecha);
            datos.append(nodo('small','',Number.isNaN(fecha.getTime()) ? 'Fecha no disponible' : fecha.toLocaleString('es-MX',{dateStyle:'short',timeStyle:'short'})));
            fila.append(datos, nodo('strong','',dinero(venta.total)));
            contenedor.append(fila);
        }
    }

    function abrirEdicion(id = 0) {
        const p = productos.find(p => p.id === id);
        $('formProducto').reset();
        $('productoId').value = p?.id ?? 0;
        $('productoNombre').value = p?.nombre ?? '';
        $('productoCategoria').value = p?.categoria ?? 'Tazas';
        $('productoCantidad').value = p?.cantidad ?? 1;
        $('productoPrecio').value = p?.precio ?? '';
        $('productoDescripcion').value = p?.descripcion ?? '';
        $('tituloModal').textContent = p ? 'Editar artículo' : 'Agregar artículo';
        $('eliminarProducto').hidden = !p;
        $('guardarProducto').textContent = p ? 'Guardar cambios' : 'Añadir producto';
        $('modalProducto').showModal();
        $('productoNombre').focus();
    }

    $('formProducto').addEventListener('submit', async e => {
        e.preventDefault();
        const form = e.currentTarget;
        if (!form.reportValidity()) return;
        const producto = {
            id: Number($('productoId').value),
            nombre: $('productoNombre').value.trim(),
            categoria: $('productoCategoria').value,
            cantidad: Number($('productoCantidad').value),
            precio: Number($('productoPrecio').value),
            descripcion: $('productoDescripcion').value.trim()
        };
        const boton = $('guardarProducto');
        boton.disabled = true;
        try {
            const res = await solicitud({accion:'guardar',producto});
            $('modalProducto').close();
            await cargar();
            aviso(res.mensaje);
        } catch (err) { aviso(err.message, true); }
        finally { boton.disabled = false; }
    });

    $('confirmarVenta').addEventListener('click', async () => {
        if (!carrito.size) return;
        if (!window.confirm('¿Registrar esta venta de demostración y descontar existencias?')) return;
        const boton = $('confirmarVenta');
        boton.disabled = true;
        try {
            const items = [...carrito.entries()].map(([id,cantidad]) => ({id,cantidad}));
            const res = await solicitud({accion:'vender',items});
            carrito.clear();
            await cargar();
            aviso(res.mensaje);
        } catch (err) {
            aviso(err.message, true);
            await cargar();
        } finally { boton.disabled = carrito.size === 0; }
    });

    $('eliminarProducto').addEventListener('click', async () => {
        const id = Number($('productoId').value);
        const p = productos.find(x => x.id === id);
        if (!p || !window.confirm(`¿Eliminar ${p.nombre} del catálogo de demostración?`)) return;
        const boton = $('eliminarProducto');
        boton.disabled = true;
        try {
            const res = await solicitud({accion:'eliminar',id});
            $('modalProducto').close();
            carrito.delete(id);
            await cargar();
            aviso(res.mensaje);
        } catch (err) { aviso(err.message,true); }
        finally { boton.disabled = false; }
    });

    $('nuevoProducto').addEventListener('click', () => abrirEdicion());
    $('cerrarModal').addEventListener('click', () => $('modalProducto').close());
    $('cancelarProducto').addEventListener('click', () => $('modalProducto').close());
    $('buscarMercancia').addEventListener('input', mostrarProductos);
    $('filtrarCategoria').addEventListener('change', mostrarProductos);
    cargar();
})();
