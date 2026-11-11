<?php

$rutaJson = '/var/www/data/canciones.json';

$textoJson = file_get_contents($rutaJson);

$songs = []; 

if ($textoJson !== false) {
    try {
        
        $songs = json_decode($textoJson, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        echo "<p style='color:red;'>Error crítico: El archivo JSON está dañado. Revisa la sintaxis.</p>";
    }
} else {
    echo "<p style='color:red;'>Error: No se pudo encontrar o abrir el archivo canciones.json.</p>";
}

return $songs;
?>