<?php

// SEGURIDAD: Solo usuarios registrados pueden añadir música
require_once __DIR__ . '/lib/acceso.php';

$errores = [];
$title = '';
$artist = '';
$category = '';
$duration = '';
$src = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. ESCUDO CSRF
    $tokenRecibido = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        http_response_code(403);
        echo "<h1 style='color:red;'>Error 403: CSRF inválido</h1>";
        exit;
    }

    // B. RECOGIDA DE DATOS
    $title = trim($_POST['title'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $category = $_POST['category'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $src = $_POST['src'] ?? '';

    // C. VALIDACIONES (Aduana)
    if ($title === '') $errores['title'] = 'El título es obligatorio.';
    if ($artist === '') $errores['artist'] = 'El artista es obligatorio.';
    
    $allowedCategories = ['electronica', 'rock', 'relax'];
    if (!in_array($category, $allowedCategories, true)) {
        $errores['category'] = 'Categoría no válida.';
    }

    $durationInt = filter_var($duration, FILTER_VALIDATE_INT);
    if ($durationInt === false || $durationInt < 1 || $durationInt > 1800) {
        $errores['duration'] = 'Debe ser entre 1 y 1800 segundos.';
    }

    $allowedSrc = ['audio/demo-01.mp3', 'audio/demo-02.mp3'];
    if (!in_array($src, $allowedSrc, true)) {
        $errores['src'] = 'Audio no válido.';
    }

    // D. GUARDADO EN LA BASE DE DATOS (El Archivero)
    if (empty($errores)) {
        $dbUser = getenv('DB_USER');
        $dbPass = getenv('DB_PASSWORD');
        $dsn = "pgsql:host=postgres;port=5432;dbname=blend_pg";

        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 1. EL ESCUDO ANTI-HACKER (Sentencias Preparadas)
            // En lugar de inyectar variables, ponemos interrogaciones (?)
            $sql = "INSERT INTO canciones (title, artist, category, duration, src) 
                    VALUES (?, ?, ?, ?, ?)";
            
            // 2. PREPARAMOS EL MOLDE
            $stmt = $pdo->prepare($sql);
            
            // 3. VERTEMOS LOS DATOS Y EJECUTAMOS
            // El archivero se encarga de que ninguna comilla maliciosa rompa la consulta
            $stmt->execute([
                $title,
                $artist,
                $category,
                $durationInt,
                $src
            ]);

            // ¡Magia! No hay flock, no hay maxId, la BBDD lo hace todo.
            // Redirigimos al catálogo público
            header('Location: 20-catalogo-db.php', true, 303);
            exit;

        } catch (PDOException $e) {
            $errores['general'] = "Error de Base de Datos: No se pudo guardar la canción.";
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Añadir Canción (BBDD) - Blend</title>
</head>
<body>
    <h1>Añadir Canción Directo a PostgreSQL</h1>
    <p><a href="16-home.php">← Volver a la Home</a></p>

    <?php if (isset($errores['general'])): ?>
        <p style="color:red;"><?= $errores['general'] ?></p>
    <?php endif; ?>

    <form method="post">
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
        
        <button type="submit">Guardar en PostgreSQL</button>
    </form>
</body>
</html>