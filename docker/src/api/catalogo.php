<?php

require_once __DIR__ . '/../lib/sesion.php';
require_once __DIR__ . '/../lib/respuesta-json.php';
require_once __DIR__ . '/../lib/almacen-canciones.php';

// 1. Control de acceso para API (401 + JSON, nunca redirigir a HTML)
if (!isset($_SESSION['usuario_id'])) {
    responderJson(401, [
        'ok' => false,
        'error' => 'Debes iniciar sesión.'
    ]);
}

// 2. Leer y validar la acción (?action=list por defecto)
$action = $_GET['action'] ?? 'list';
$accionesPermitidas = ['list', 'create', 'update', 'delete'];

// Comprobamos is_string() para rechazar ataques tipo ?action[]=list
if (!is_string($action) || !in_array($action, $accionesPermitidas, true)) {
    responderJson(400, [
        'ok' => false,
        'error' => 'Acción desconocida.'
    ]);
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 3. Acción LIST (solo permitida por GET)
if ($action === 'list') {
    if ($metodo !== 'GET') {
        responderJson(405, [
            'ok' => false,
            'error' => 'Método no permitido. Usa GET para consultar.'
        ], ['Allow' => 'GET']);
    }

    try {
        $canciones = leerCanciones();
        responderJson(200, [
            'ok' => true,
            'data' => array_values($canciones)
        ]);
    } catch (Throwable $e) {
        // Guardamos el detalle técnico en el log de Docker, nunca en la respuesta
        error_log('Error al leer canciones.json: ' . $e->getMessage());
        responderJson(500, [
            'ok' => false,
            'error' => 'No se pudo leer el catálogo de canciones.'
        ]);
    }
}

// Funciones auxiliares para peticiones POST de modificación
function verificarCsrf(): void
{
    $tokenSesion = $_SESSION['csrf'] ?? null;
    $tokenRecibido = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

    if (!is_string($tokenSesion) || !is_string($tokenRecibido) || !hash_equals($tokenSesion, $tokenRecibido)) {
        responderJson(403, [
            'ok' => false,
            'error' => 'Token de protección ausente o incorrecto.'
        ]);
    }
}

function leerCuerpoJson(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    // Acepta "application/json" y también "application/json; charset=utf-8"
    if (!is_string($contentType) || stripos($contentType, 'application/json') !== 0) {
        responderJson(415, [
            'ok' => false,
            'error' => 'El cuerpo de la petición debe enviarse como application/json.'
        ]);
    }

    $cuerpo = file_get_contents('php://input');
    if ($cuerpo === false || trim($cuerpo) === '') {
        responderJson(400, [
            'ok' => false,
            'error' => 'El cuerpo JSON está vacío o no pudo leerse.'
        ]);
    }

    try {
        // Decodificamos como objeto (false) para distinguir {} de [] o valores sueltos
        $objeto = json_decode($cuerpo, false, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        responderJson(400, [
            'ok' => false,
            'error' => 'El JSON enviado está mal formado.'
        ]);
    }

    if (!is_object($objeto)) {
        responderJson(400, [
            'ok' => false,
            'error' => 'Se esperaba un objeto JSON con los datos.'
        ]);
    }

    return get_object_vars($objeto);
}

// 4. Acción CREATE (solo permitida por POST)
if ($action === 'create') {
    if ($metodo !== 'POST') {
        responderJson(405, [
            'ok' => false,
            'error' => 'Método no permitido. Usa POST para crear.'
        ], ['Allow' => 'POST']);
    }

    verificarCsrf();
    $datos = leerCuerpoJson();

    try {
        $datosValidos = validarDatosCancion($datos);
    } catch (InvalidArgumentException $e) {
        responderJson(422, [
            'ok' => false,
            'error' => $e->getMessage()
        ]);
    }

    try {
        $listaActualizada = crearCancion($datosValidos);
        responderJson(201, [
            'ok' => true,
            'data' => $listaActualizada
        ]);
    } catch (Throwable $e) {
        error_log('Error al crear canción: ' . $e->getMessage());
        responderJson(500, [
            'ok' => false,
            'error' => 'No se pudo guardar la canción en el catálogo.'
        ]);
    }
}
// 5. Acción UPDATE (solo permitida por POST)
if ($action === 'update') {
    if ($metodo !== 'POST') {
        responderJson(405, [
            'ok' => false,
            'error' => 'Método no permitido. Usa POST para editar.'
        ], ['Allow' => 'POST']);
    }

    verificarCsrf();
    $datos = leerCuerpoJson();

    // Validamos que el id sea un entero válido (>= 0 porque tu catálogo original empieza en id 0)
    if (!isset($datos['id']) || !is_int($datos['id']) || $datos['id'] < 0) {
        responderJson(422, [
            'ok' => false,
            'error' => 'El identificador de la canción no es válido.'
        ]);
    }

    $id = $datos['id'];

    try {
        $datosValidos = validarDatosCancion($datos);
    } catch (InvalidArgumentException $e) {
        responderJson(422, [
            'ok' => false,
            'error' => $e->getMessage()
        ]);
    }

    try {
        $listaActualizada = actualizarCancion($id, $datosValidos);

        if ($listaActualizada === null) {
            responderJson(404, [
                'ok' => false,
                'error' => 'La canción solicitada no existe.'
            ]);
        }

        responderJson(200, [
            'ok' => true,
            'data' => $listaActualizada
        ]);
    } catch (Throwable $e) {
        error_log('Error al actualizar canción: ' . $e->getMessage());
        responderJson(500, [
            'ok' => false,
            'error' => 'No se pudo actualizar la canción en el catálogo.'
        ]);
    }
}
// 6. Acción DELETE (solo permitida por POST)
if ($action === 'delete') {
    if ($metodo !== 'POST') {
        responderJson(405, [
            'ok' => false,
            'error' => 'Método no permitido. Usa POST para eliminar.'
        ], ['Allow' => 'POST']);
    }

    verificarCsrf();
    $datos = leerCuerpoJson();

    if (!isset($datos['id']) || !is_int($datos['id']) || $datos['id'] < 0) {
        responderJson(422, [
            'ok' => false,
            'error' => 'El identificador de la canción no es válido.'
        ]);
    }

    $id = $datos['id'];

    try {
        $listaActualizada = eliminarCancion($id);

        if ($listaActualizada === null) {
            responderJson(404, [
                'ok' => false,
                'error' => 'La canción solicitada no existe.'
            ]);
        }

        responderJson(200, [
            'ok' => true,
            'data' => $listaActualizada
        ]);
    } catch (Throwable $e) {
        error_log('Error al eliminar canción: ' . $e->getMessage());
        responderJson(500, [
            'ok' => false,
            'error' => 'No se pudo eliminar la canción del catálogo.'
        ]);
    }
};