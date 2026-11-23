<?php


function responderJson(int $codigoHttp, array $datos, array $cabecerasExtra = []): never
{
    http_response_code($codigoHttp);
    header('Content-Type: application/json; charset=utf-8');

    // Útil para añadir cabeceras como "Allow: POST" en errores 405
    foreach ($cabecerasExtra as $nombre => $valor) {
        header("$nombre: $valor");
    }

    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}