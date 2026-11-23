<?php

const RUTA_CATALOGO_JSON = '/var/www/data/canciones.json';

function leerCanciones(): array
{
    $archivo = @fopen(RUTA_CATALOGO_JSON, 'r');
    if ($archivo === false) {
        throw new RuntimeException('No se pudo abrir el archivo del catálogo.');
    }

    try {
        // LOCK_SH (Shared Lock): permite que varios lean a la vez,
        // pero espera si alguien está escribiendo con LOCK_EX.
        if (!flock($archivo, LOCK_SH)) {
            throw new RuntimeException('No se pudo bloquear el catálogo para lectura.');
        }

        $contenido = stream_get_contents($archivo);
        flock($archivo, LOCK_UN);
    } finally {
        fclose($archivo);
    }

    if ($contenido === false) {
        throw new RuntimeException('Error al leer el contenido del catálogo.');
    }

    $canciones = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);

    if (!is_array($canciones)) {
        throw new RuntimeException('El formato del catálogo JSON no es una lista válida.');
    }

    return $canciones;
}

const CATEGORIAS_PERMITIDAS = ['rock', 'electronica', 'relax', 'pop'];

function obtenerAudiosAutorizados(): array
{
    $archivos = glob(__DIR__ . '/../canciones/*.mp3') ?: [];
    $rutas = [];
    foreach ($archivos as $archivo) {
        $rutas[] = 'canciones/' . basename($archivo);
    }
    sort($rutas);
    return $rutas;
}

function validarDatosCancion(array $datos): array
{
    // 1. Validar tipos ANTES de hacer trim() o comprobar valores
    foreach (['title', 'artist', 'category', 'src'] as $campo) {
        if (!isset($datos[$campo]) || !is_string($datos[$campo])) {
            throw new InvalidArgumentException("El campo '$campo' debe ser una cadena de texto.");
        }
    }

    if (!isset($datos['duration']) || !is_int($datos['duration'])) {
        throw new InvalidArgumentException('La duración debe ser un entero entre 1 y 1800 segundos.');
    }

    // 2. Recortar textos tras confirmar que son cadenas
    $title = trim($datos['title']);
    $artist = trim($datos['artist']);
    $category = trim($datos['category']);
    $src = trim($datos['src']);
    $duration = $datos['duration'];

    // 3. Validar reglas de negocio del catálogo
    if ($title === '' || mb_strlen($title) > 80) {
        throw new InvalidArgumentException('El título es obligatorio y no puede superar los 80 caracteres.');
    }

    if ($artist === '' || mb_strlen($artist) > 80) {
        throw new InvalidArgumentException('El artista es obligatorio y no puede superar los 80 caracteres.');
    }

    if (!in_array($category, CATEGORIAS_PERMITIDAS, true)) {
        throw new InvalidArgumentException('La categoría seleccionada no está admitida.');
    }

    if ($duration < 1 || $duration > 1800) {
        throw new InvalidArgumentException('La duración debe ser un entero entre 1 y 1800 segundos.');
    }

    if (!in_array($src, obtenerAudiosAutorizados(), true)) {
        throw new InvalidArgumentException('El archivo de audio seleccionado no está autorizado.');
    }

    return [
        'title' => $title,
        'artist' => $artist,
        'category' => $category,
        'duration' => $duration,
        'src' => $src,
    ];
}

function crearCancion(array $datosValidos): array
{
    // Abrimos en modo c+ (lectura/escritura sin truncar al abrir)
    $archivo = @fopen(RUTA_CATALOGO_JSON, 'c+');
    if ($archivo === false) {
        throw new RuntimeException('No se pudo abrir el archivo del catálogo para escritura.');
    }

    try {
        // LOCK_EX: bloqueo exclusivo que abarca lectura, cambio y escritura
        if (!flock($archivo, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear el catálogo para escritura.');
        }

        rewind($archivo);
        $contenido = stream_get_contents($archivo);
        if ($contenido === false) {
            throw new RuntimeException('Error al leer el catálogo antes de guardar.');
        }

        $canciones = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($canciones)) {
            throw new RuntimeException('El formato del catálogo JSON no es válido.');
        }

        // El servidor asigna el siguiente id (nunca se acepta uno del cliente)
        $maxId = 0;
        foreach ($canciones as $c) {
            if (isset($c['id']) && is_int($c['id']) && $c['id'] > $maxId) {
                $maxId = $c['id'];
            }
        }

        $nuevaFicha = [
            'id' => $maxId + 1,
            'title' => $datosValidos['title'],
            'artist' => $datosValidos['artist'],
            'category' => $datosValidos['category'],
            'duration' => $datosValidos['duration'],
            'src' => $datosValidos['src'],
        ];

        $canciones[] = $nuevaFicha;

        $jsonNuevo = json_encode(array_values($canciones), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        rewind($archivo);
        ftruncate($archivo, 0);
        if (fwrite($archivo, $jsonNuevo) === false) {
            throw new RuntimeException('Error al escribir en el catálogo.');
        }
        fflush($archivo);
        flock($archivo, LOCK_UN);
    } finally {
        fclose($archivo);
    }

    return array_values($canciones);
}

function actualizarCancion(int $id, array $datosValidos): ?array
{
    $archivo = @fopen(RUTA_CATALOGO_JSON, 'c+');
    if ($archivo === false) {
        throw new RuntimeException('No se pudo abrir el archivo del catálogo para escritura.');
    }

    try {
        // LOCK_EX: protege lectura, búsqueda, sustitución y escritura en un solo bloque
        if (!flock($archivo, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear el catálogo para escritura.');
        }

        rewind($archivo);
        $contenido = stream_get_contents($archivo);
        if ($contenido === false) {
            throw new RuntimeException('Error al leer el catálogo antes de actualizar.');
        }

        $canciones = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($canciones)) {
            throw new RuntimeException('El formato del catálogo JSON no es válido.');
        }

        // Buscamos la ficha por su id real (nunca por posición)
        $indiceEncontrado = null;
        foreach ($canciones as $indice => $cancion) {
            if (isset($cancion['id']) && $cancion['id'] === $id) {
                $indiceEncontrado = $indice;
                break;
            }
        }

        // Si ya no existe, soltamos el bloqueo y devolvemos null (la API responderá 404)
        if ($indiceEncontrado === null) {
            flock($archivo, LOCK_UN);
            return null;
        }

        // Conservamos el id original (y la portada si la tenía) y actualizamos los 5 campos
        $fichaAnterior = $canciones[$indiceEncontrado];
        $fichaActualizada = [
            'id' => $id,
            'title' => $datosValidos['title'],
            'artist' => $datosValidos['artist'],
            'category' => $datosValidos['category'],
            'duration' => $datosValidos['duration'],
            'src' => $datosValidos['src'],
        ];
        if (isset($fichaAnterior['cover'])) {
            $fichaActualizada['cover'] = $fichaAnterior['cover'];
        }

        $canciones[$indiceEncontrado] = $fichaActualizada;

        $jsonNuevo = json_encode(array_values($canciones), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        rewind($archivo);
        ftruncate($archivo, 0);
        if (fwrite($archivo, $jsonNuevo) === false) {
            throw new RuntimeException('Error al escribir en el catálogo.');
        }
        fflush($archivo);
        flock($archivo, LOCK_UN);
    } finally {
        fclose($archivo);
    }

    return array_values($canciones);
}

function eliminarCancion(int $id): ?array
{
    $archivo = @fopen(RUTA_CATALOGO_JSON, 'c+');
    if ($archivo === false) {
        throw new RuntimeException('No se pudo abrir el archivo del catálogo para escritura.');
    }

    try {
        // LOCK_EX: protege lectura, búsqueda, borrado y escritura en un solo bloque
        if (!flock($archivo, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear el catálogo para escritura.');
        }

        rewind($archivo);
        $contenido = stream_get_contents($archivo);
        if ($contenido === false) {
            throw new RuntimeException('Error al leer el catálogo antes de eliminar.');
        }

        $canciones = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($canciones)) {
            throw new RuntimeException('El formato del catálogo JSON no es válido.');
        }

        // Buscamos la ficha por su id real
        $indiceEncontrado = null;
        foreach ($canciones as $indice => $cancion) {
            if (isset($cancion['id']) && $cancion['id'] === $id) {
                $indiceEncontrado = $indice;
                break;
            }
        }

        // Si no existe, soltamos el bloqueo y devolvemos null (la API responderá 404)
        if ($indiceEncontrado === null) {
            flock($archivo, LOCK_UN);
            return null;
        }

        // Eliminamos solo esa ficha y reindexamos las posiciones sin alterar los id restantes
        unset($canciones[$indiceEncontrado]);
        $cancionesReordenadas = array_values($canciones);

        $jsonNuevo = json_encode($cancionesReordenadas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        rewind($archivo);
        ftruncate($archivo, 0);
        if (fwrite($archivo, $jsonNuevo) === false) {
            throw new RuntimeException('Error al escribir en el catálogo.');
        }
        fflush($archivo);
        flock($archivo, LOCK_UN);
    } finally {
        fclose($archivo);
    }

    return $cancionesReordenadas;
}