<?php
// Importamos nuestra función para mostrar la duración legible
require_once __DIR__ . '/lib/canciones.php';

$errores = []; // Aquí guardaremos los posibles errores
$vistaPrevia = null;

// 1. ¿Nos están enviando el formulario?
// Si el método de la petición es POST, significa que el usuario ha hecho clic en "Enviar"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. RECOGIDA DE DATOS
    // Usamos $_POST para recoger los datos enviados de forma "oculta"
    $title = $_POST['title'] ?? '';
    $artist = $_POST['artist'] ?? '';
    $category = $_POST['category'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $src = $_POST['src'] ?? '';

    // 3. VALIDACIÓN (¡Muy importante!)
    // a) Título y Artista: No pueden estar vacíos
    $title = trim($title); // Quitamos espacios en blanco al principio y al final
    $artist = trim($artist);
    if ($title === '') {
        $errores['title'] = 'El título es obligatorio.';
    }
    if ($artist === '') {
        $errores['artist'] = 'El artista es obligatorio.';
    }

    // b) Categoría: Debe ser una de las permitidas
    $allowedCategories = ['electronica', 'rock', 'relax'];
    if (!in_array($category, $allowedCategories, true)) {
        $errores['category'] = 'Categoría no válida.';
    }

    // c) Duración: Debe ser un número entero entre 1 y 1800 segundos (30 min)
    // filter_var con FILTER_VALIDATE_INT nos asegura que es un número entero válido
    $durationInt = filter_var($duration, FILTER_VALIDATE_INT);
    if ($durationInt === false || $durationInt < 1 || $durationInt > 1800) {
        $errores['duration'] = 'La duración debe ser un número entre 1 y 1800 segundos.';
    }

    // d) Origen (src): Por ahora, solo permitimos elegir de una lista predefinida
    $allowedSrc = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];
    if (!in_array($src, $allowedSrc, true)) {
        $errores['src'] = 'El archivo de audio no es válido.';
    }

    // 4. VISTA PREVIA (Si no hay errores)
    if (empty($errores)) {
        $vistaPrevia = [
            'title' => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
            'artist' => htmlspecialchars($artist, ENT_QUOTES, 'UTF-8'),
            'category' => htmlspecialchars($category, ENT_QUOTES, 'UTF-8'),
            'duration' => duracionLegible($durationInt),
            'src' => htmlspecialchars($src, ENT_QUOTES, 'UTF-8')
        ];
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Añadir Canción - Blend</title>
    <style>
        .error { color: red; font-size: 0.9em; }
        .vista-previa { background: #f0f0f0; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Añadir Nueva Canción</h1>

    <!-- Si hay vista previa, la mostramos -->
    <?php if ($vistaPrevia): ?>
        <div class="vista-previa">
            <h2>Vista previa, canción no guardada</h2>
            <ul>
                <li><strong>Título:</strong> <?= $vistaPrevia['title'] ?></li>
                <li><strong>Artista:</strong> <?= $vistaPrevia['artist'] ?></li>
                <li><strong>Categoría:</strong> <?= $vistaPrevia['category'] ?></li>
                <li><strong>Duración:</strong> <?= $vistaPrevia['duration'] ?></li>
                <li><strong>Archivo:</strong> <?= $vistaPrevia['src'] ?></li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- EL FORMULARIO -->
    <!-- method="post" asegura que los datos vayan en el cuerpo de la petición, no en la URL -->
    <form method="post">
        <div>
            <label for="title">Título:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES) ?>">
            <?php if (isset($errores['title'])): ?>
                <span class="error"><?= $errores['title'] ?></span>
            <?php endif; ?>
        </div>
        <br>

        <div>
            <label for="artist">Artista:</label>
            <input type="text" id="artist" name="artist" value="<?= htmlspecialchars($_POST['artist'] ?? '', ENT_QUOTES) ?>">
            <?php if (isset($errores['artist'])): ?>
                <span class="error"><?= $errores['artist'] ?></span>
            <?php endif; ?>
        </div>
        <br>

        <div>
            <label for="category">Categoría:</label>
            <select id="category" name="category">
                <option value="">Selecciona...</option>
                <option value="electronica" <?= (($_POST['category'] ?? '') === 'electronica') ? 'selected' : '' ?>>Electrónica</option>
                <option value="rock" <?= (($_POST['category'] ?? '') === 'rock') ? 'selected' : '' ?>>Rock</option>
                <option value="relax" <?= (($_POST['category'] ?? '') === 'relax') ? 'selected' : '' ?>>Relax</option>
            </select>
            <?php if (isset($errores['category'])): ?>
                <span class="error"><?= $errores['category'] ?></span>
            <?php endif; ?>
        </div>
        <br>

        <div>
            <label for="duration">Duración (segundos):</label>
            <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($_POST['duration'] ?? '', ENT_QUOTES) ?>">
            <?php if (isset($errores['duration'])): ?>
                <span class="error"><?= $errores['duration'] ?></span>
            <?php endif; ?>
        </div>
        <br>

        <div>
            <label for="src">Archivo de audio:</label>
            <select id="src" name="src">
                <option value="">Selecciona...</option>
                <option value="audio/demo-01.mp3" <?= (($_POST['src'] ?? '') === 'audio/demo-01.mp3') ? 'selected' : '' ?>>Demo 01</option>
                <option value="audio/demo-02.mp3" <?= (($_POST['src'] ?? '') === 'audio/demo-02.mp3') ? 'selected' : '' ?>>Demo 02</option>
            </select>
            <?php if (isset($errores['src'])): ?>
                <span class="error"><?= $errores['src'] ?></span>
            <?php endif; ?>
        </div>
        <br>

        <button type="submit">Generar Vista Previa</button>
    </form>
</body>
</html>