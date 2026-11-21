<?php
// docker/src/api/canciones.php
require_once __DIR__ . '/../lib/sesion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Debes iniciar sesión para acceder al catálogo.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$rutaJson = '/var/www/data/canciones.json';
$textoJson = @file_get_contents($rutaJson);

if ($textoJson === false) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo leer el archivo canciones.json.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $songs = json_decode($textoJson, true, 512, JSON_THROW_ON_ERROR);
    echo json_encode(array_values($songs), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'El archivo canciones.json está dañado.'], JSON_UNESCAPED_UNICODE);
    exit;
}