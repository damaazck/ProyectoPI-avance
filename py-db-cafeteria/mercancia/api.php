<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respuesta(int $codigo, array $contenido): void {
    http_response_code($codigo);
    echo json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function fallo(string $mensaje, int $codigo = 422): void {
    throw new InvalidArgumentException($mensaje, $codigo);
}
function entero($valor, string $campo, int $min = 0, int $max = 100000): int {
    $n = filter_var($valor, FILTER_VALIDATE_INT);
    if ($n === false || $n < $min || $n > $max) fallo("$campo debe ser un número entero entre $min y $max.");
    return $n;
}
function texto($valor, string $campo, int $min, int $max): string {
    if (!is_string($valor)) fallo("$campo no es válido.");
    $s = trim($valor);
    $longitud = function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
    if ($longitud < $min || $longitud > $max) fallo("$campo debe tener entre $min y $max caracteres.");
    return $s;
}
function precio($valor): float {
    if ((!is_int($valor) && !is_float($valor) && !is_string($valor)) || !is_numeric($valor)) fallo('Precio inválido.');
    $n = (float) $valor;
    if (!is_finite($n) || $n < 0 || $n > 1000000 || round($n, 2) != $n) fallo('El precio debe estar entre $0 y $1,000,000 con máximo 2 decimales.');
    return round($n, 2);
}
function enviarEstado(array $db): void {
    respuesta(200, [
        'ok' => true,
        'productos' => $db['productos'],
        'ventas' => array_slice(array_reverse($db['ventas']), 0, 8),
        'totalVentas' => count($db['ventas'])
    ]);
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($metodo, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    respuesta(405, ['ok'=>false, 'mensaje'=>'Método no permitido.']);
}
if ($metodo === 'POST') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_mercancia']) || !is_string($token) || !hash_equals($_SESSION['csrf_mercancia'], $token)) {
        respuesta(403, ['ok'=>false, 'mensaje'=>'Sesión caducada. Actualiza la página e inténtalo de nuevo.']);
    }
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 65536) {
        respuesta(413, ['ok'=>false, 'mensaje'=>'Solicitud demasiado grande.']);
    }
    $datos = json_decode(file_get_contents('php://input'), true);
    if (!is_array($datos)) respuesta(400, ['ok'=>false, 'mensaje'=>'Se requiere JSON válido.']);
}

$ruta = __DIR__ . '/data/inventario.json';
$archivo = fopen($ruta, 'c+');
if ($archivo === false) respuesta(500, ['ok'=>false, 'mensaje'=>'No se pudo abrir el inventario. Verifica permisos de escritura.']);
if (!flock($archivo, $metodo === 'POST' ? LOCK_EX : LOCK_SH)) {
    fclose($archivo);
    respuesta(500, ['ok'=>false, 'mensaje'=>'No se pudo bloquear el inventario.']);
}
try {
    rewind($archivo);
    $raw = stream_get_contents($archivo);
    $db = json_decode($raw, true);
    if (!is_array($db) || !isset($db['productos'], $db['ventas'], $db['siguiente_id'], $db['siguiente_venta'])) {
        throw new RuntimeException('El inventario JSON está vacío o dañado.');
    }
    if ($metodo === 'GET') {
        flock($archivo, LOCK_UN);
        fclose($archivo);
        enviarEstado($db);
    }

    switch ($datos['accion'] ?? '') {
        case 'guardar':
            $p = $datos['producto'] ?? null;
            if (!is_array($p)) fallo('Faltan los datos del producto.');
            $categorias = ['Tazas', 'Café', 'Termos', 'Peluches', 'Accesorios'];
            $categoria = texto($p['categoria'] ?? null, 'Categoría', 2, 30);
            if (!in_array($categoria, $categorias, true)) fallo('Categoría no permitida.');
            $iconos = ['Tazas'=>'☕', 'Café'=>'🫘', 'Termos'=>'🧉', 'Peluches'=>'🧸', 'Accesorios'=>'🛍️'];
            $producto = [
                'id'=>0,
                'nombre'=>texto($p['nombre'] ?? null, 'Nombre', 2, 90),
                'categoria'=>$categoria,
                'descripcion'=>texto($p['descripcion'] ?? null, 'Descripción', 3, 500),
                'precio'=>precio($p['precio'] ?? null),
                'cantidad'=>entero($p['cantidad'] ?? null, 'Cantidad', 0, 100000),
                'icono'=>$iconos[$categoria]
            ];
            $id = entero($p['id'] ?? 0, 'ID', 0);
            if ($id === 0) {
                $producto['id'] = (int) $db['siguiente_id']++;
                $db['productos'][] = $producto;
                $mensaje = 'Artículo agregado correctamente.';
            } else {
                $encontrado = false;
                foreach ($db['productos'] as &$actual) {
                    if ((int) $actual['id'] === $id) {
                        $producto['id'] = $id;
                        $actual = $producto;
                        $encontrado = true;
                        break;
                    }
                }
                unset($actual);
                if (!$encontrado) fallo('El artículo ya no existe.', 404);
                $mensaje = 'Cambios guardados en el JSON.';
            }
            break;

        case 'eliminar':
            $id = entero($datos['id'] ?? null, 'ID', 1);
            $antes = count($db['productos']);
            $db['productos'] = array_values(array_filter($db['productos'], static function ($p) use ($id) {
                return (int) $p['id'] !== $id;
            }));
            if (count($db['productos']) === $antes) fallo('Artículo no encontrado.', 404);
            $mensaje = 'Artículo eliminado.';
            break;

        case 'vender':
            $items = $datos['items'] ?? null;
            if (!is_array($items) || count($items) < 1 || count($items) > 100) fallo('Agrega artículos al carrito.');
            $cantidades = [];
            foreach ($items as $item) {
                if (!is_array($item)) fallo('Artículo de venta inválido.');
                $id = entero($item['id'] ?? null, 'ID', 1);
                $cantidad = entero($item['cantidad'] ?? null, 'Cantidad', 1, 10000);
                $cantidades[$id] = ($cantidades[$id] ?? 0) + $cantidad;
                if ($cantidades[$id] > 10000) fallo('Cantidad excesiva para un artículo.');
            }
            $detalle = [];
            $total = 0;
            foreach ($cantidades as $id => $cantidad) {
                $indice = null;
                foreach ($db['productos'] as $i => $producto) {
                    if ((int) $producto['id'] === (int) $id) { $indice = $i; break; }
                }
                if ($indice === null) fallo('Un artículo del carrito ya no existe.', 409);
                $p = $db['productos'][$indice];
                if ($p['cantidad'] < $cantidad) fallo('No hay existencias suficientes de: ' . $p['nombre'], 409);
                $subtotal = round($p['precio'] * $cantidad, 2);
                $total = round($total + $subtotal, 2);
                $detalle[] = [
                    'id_producto'=>$p['id'], 'nombre'=>$p['nombre'], 'cantidad'=>$cantidad,
                    'precio_unitario'=>$p['precio'], 'subtotal'=>$subtotal
                ];
            }
            // Se descuenta stock solo cuando TODOS los artículos se validaron.
            foreach ($cantidades as $id => $cantidad) {
                foreach ($db['productos'] as &$p) {
                    if ((int) $p['id'] === (int) $id) { $p['cantidad'] -= $cantidad; break; }
                }
                unset($p);
            }
            $ventaId = (int) $db['siguiente_venta']++;
            $db['ventas'][] = [
                'id'=>$ventaId, 'fecha'=>date(DATE_ATOM), 'items'=>$detalle, 'total'=>$total
            ];
            $mensaje = 'Venta #' . $ventaId . ' registrada correctamente.';
            break;

        default:
            fallo('Acción no reconocida.', 400);
    }

    $json = json_encode($db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) throw new RuntimeException('Error al serializar el inventario.');
    rewind($archivo);
    if (!ftruncate($archivo, 0)) throw new RuntimeException('No se pudo actualizar el inventario.');
    $tam = strlen($json);
    $escritos = 0;
    while ($escritos < $tam) {
        $n = fwrite($archivo, substr($json, $escritos));
        if ($n === false || $n === 0) throw new RuntimeException('No se pudo guardar el JSON.');
        $escritos += $n;
    }
    fflush($archivo);
    flock($archivo, LOCK_UN);
    fclose($archivo);
    respuesta(200, ['ok'=>true, 'mensaje'=>$mensaje, 'venta_id'=>$ventaId ?? null]);
} catch (InvalidArgumentException $e) {
    flock($archivo, LOCK_UN);
    fclose($archivo);
    respuesta(in_array($e->getCode(), [400,404,409,422], true) ? $e->getCode() : 422, ['ok'=>false, 'mensaje'=>$e->getMessage()]);
} catch (Throwable $e) {
    error_log('Mercancia API: ' . $e->getMessage());
    flock($archivo, LOCK_UN);
    fclose($archivo);
    respuesta(500, ['ok'=>false, 'mensaje'=>'Error interno del inventario. Revisa los permisos o el archivo JSON.']);
}
