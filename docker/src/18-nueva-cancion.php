<?php
// SEGURIDAD DE ENTRADA: Solo usuarios logueados pueden ver esta página
require_once __DIR__ . '/lib/acceso.php';

// FORMATEADOR DE SEGUNDOS
require_once __DIR__ . '/lib/canciones.php';

$errores = [];
$title = '';
$artist = '';
$category = '';
$duration = '';
$src = '';

// ¿EL USUARIO HA PULSADO EL BOTÓN DE GUARDAR?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. EL ESCUDO CSRF (Marca de agua)
    $tokenRecibido = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        http_response_code(403);
        echo "<h1 style='color:red;'>Error 403: Petición rechazada (CSRF inválido)</h1>";
        exit;
    }

    // B. RECOGIDA Y LIMPIEZA DE LOS DATOS
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $category = $_POST['category'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $src = $_POST['src'] ?? '';

    // C. VALIDACIÓN (Control de calidad)
    if ($title === '') {
        $errores['title'] = 'El título es obligatorio.';
    }
    if ($artist === '') {
        $errores['artist'] = 'El artista es obligatorio.';
    }

    $allowedCategories = ['electronica', 'rock', 'relax'];
    if (!in_array($category, $allowedCategories, true)) {
        $errores['category'] = 'Categoría no válida.';
    }

    $durationInt = filter_var($duration, FILTER_VALIDATE_INT);
    if ($durationInt === false || $durationInt < 1 || $durationInt > 1800) {
        $errores['duration'] = 'La duración debe ser un número entre 1 y 1800 segundos.';
    }

    $allowedSrc = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];
    if (!in_array($src, $allowedSrc, true)) {
        $errores['src'] = 'El archivo de audio no es válido.';
    }

    // D. SI TODO ESTÁ PERFECTO, PROCEDEMOS A GUARDAR EN EL DISCO DURO
    if (empty($errores)) {
        $rutaJson = '/var/www/data/canciones.json';
        
        // Abrimos la despensa en modo lectura/escritura sin borrar nada (r+)
        $archivo = fopen($rutaJson, 'r+');
        
        if ($archivo) {
            // COLOCAMOS EL PESTILLO (Nadie más puede editar el JSON ahora)
            if (flock($archivo, LOCK_EX)) {
                
                // Leemos todas las canciones actuales
                $contenido = stream_get_contents($archivo);
                $songs = json_decode($contenido, true) ?? [];
                
                // Buscamos cuál es el ID más alto para no repetir números
                $maxId = 0;
                foreach ($songs as $s) {
                    if (isset($s['id']) && $s['id'] > $maxId) {
                        $maxId = $s['id'];
                    }
                }
                
                // Fabricamos la nueva ficha de la canción
                $nuevaCancion = [
                    'id'       => $maxId + 1,
                    'title'    => htmlspecialchars($title, ENT_QUOTES, 'UTF-8'),
                    'artist'   => htmlspecialchars($artist, ENT_QUOTES, 'UTF-8'),
                    'category' => htmlspecialchars($category, ENT_QUOTES, 'UTF-8'),
                    'duration' => $durationInt,
                    'src'      => htmlspecialchars($src, ENT_QUOTES, 'UTF-8')
                ];
                
                // Añadimos la nueva canción al final de nuestra lista
                $songs[] = $nuevaCancion;
                
                // Empaquetamos la lista de nuevo a texto JSON bonito
                $nuevoContenido = json_encode($songs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
                // Sobrescribimos el archivo con precisión quirúrgica
                rewind($archivo); 
                fwrite($archivo, $nuevoContenido);
                ftruncate($archivo, ftell($archivo)); 
                fflush($archivo); 
                
                // Quitamos el pestillo
                flock($archivo, LOCK_UN);
                fclose($archivo);
                
                // Redirigimos al catálogo para que el usuario vea su nueva canción
                header('Location: 12-catalogo-json.php', true, 303);
                exit;
            }
            // Si no pudimos poner el pestillo, cerramos igual
            fclose($archivo);
        } else {
            $errores['general'] = 'Error técnico: No se pudo abrir el catálogo de canciones.';
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Añadir Canción - Blend</title>
</head>
<body>
    <h1>Añadir Nueva Canción al Catálogo</h1>
    
    <p><a href="16-home.php">← Volver a la Home VIP</a></p>

    <?php if (isset($errores['general'])): ?>
        <p style="color:red;"><?= $errores['general'] ?></p>
    <?php endif; ?>

    <form method="post">
        <!-- El escudo invisible -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        
        <div>
            <label>Título:</label><br>
            <input type="text" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errores['title'])) echo "<span style='color:red;'>{$errores['title']}</span>"; ?>
        </div>
        <br>
        
        <div>
            <label>Artista:</label><br>
            <input type="text" name="artist" value="<?= htmlspecialchars($artist, ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errores['artist'])) echo "<span style='color:red;'>{$errores['artist']}</span>"; ?>
        </div>
        <br>
        
        <div>
            <label>Categoría:</label><br>
            <select name="category">
                <option value="">Selecciona...</option>
                <option value="electronica" <?= $category === 'electronica' ? 'selected' : '' ?>>Electrónica</option>
                <option value="rock" <?= $category === 'rock' ? 'selected' : '' ?>>Rock</option>
                <option value="relax" <?= $category === 'relax' ? 'selected' : '' ?>>Relax</option>
            </select>
            <?php if (isset($errores['category'])) echo "<span style='color:red;'>{$errores['category']}</span>"; ?>
        </div>
        <br>
        
        <div>
            <label>Duración (segundos):</label><br>
            <input type="text" name="duration" value="<?= htmlspecialchars($duration, ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errores['duration'])) echo "<span style='color:red;'>{$errores['duration']}</span>"; ?>
        </div>
        <br>
        
        <div>
            <label>Archivo de audio:</label><br>
            <select name="src">
                <option value="">Selecciona...</option>
                <option value="audio/demo-01.mp3" <?= $src === 'audio/demo-01.mp3' ? 'selected' : '' ?>>Demo 01</option>
                <option value="audio/demo-02.mp3" <?= $src === 'audio/demo-02.mp3' ? 'selected' : '' ?>>Demo 02</option>
            </select>
            <?php if (isset($errores['src'])) echo "<span style='color:red;'>{$errores['src']}</span>"; ?>
        </div>
        <br>
        
        <button type="submit">Guardar Canción Oficialmente</button>
    </form>
</body>
</html>