<?php
// HERRAMIENTAS
require_once __DIR__ . '/lib/canciones.php';

// ESCUCHAMOS LA URL (El método GET)
// Si no escriben nada en la URL, asumimos que quieren ver 'todas'
$category = $_GET['category'] ?? 'todas';

// LA ADUANA (Validación estricta de la URL)
$allowed = ['todas', 'electronica', 'rock', 'relax'];

if (!in_array($category, $allowed, true)) {
    http_response_code(400);
    echo "<h1 style='color:red;'>Error 400: Categoría no válida</h1>";
    exit;
}

$songs = [];
$mensajeError = "";

// PREPARAMOS EL TELÉFONO DEL ARCHIVERO
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');
$dsn = "pgsql:host=postgres;port=5432;dbname=blend_pg";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 5. LA BIFURCACIÓN DE LA CONSULTA (El cerebro lógico)
    if ($category === 'todas') {
        // Opción A: Quieren todo el catálogo
        $sql = "SELECT * FROM canciones ORDER BY id ASC";
        $stmt = $pdo->query($sql);
        $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        // Quieren una categoría específica
        // No concatenamos variables aquí, usamos la interrogación (?)
        $sql = "SELECT * FROM canciones WHERE category = ? ORDER BY id ASC";
        
        // Preparamos el molde por seguridad (Escudo Anti-Hacker)
        $stmt = $pdo->prepare($sql);
        
        // Disparamos la consulta metiendo la categoría real en la interrogación
        $stmt->execute([$category]);
        
        // Recogemos solo las canciones que superaron el filtro
        $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $mensajeError = "No se pudo conectar al catálogo digital en este momento.";
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Filtro BBDD - Blend</title>
</head>
<body>
    <h1>Catálogo Filtrado (PostgreSQL)</h1>
    
    <h2>Viendo categoría: <span style="color:blue;"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span></h2>

    <!-- Menú de navegación rápida -->
    <nav style="margin-bottom: 20px;">
        <a href="?category=todas">Ver Todas</a> | 
        <a href="?category=rock">Rock</a> | 
        <a href="?category=electronica">Electrónica</a> | 
        <a href="?category=relax">Relax</a>
    </nav>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><strong><?= $mensajeError ?></strong></p>
    <?php endif; ?>

    <!-- PINTAMOS LOS RESULTADOS -->
    <?php if (empty($songs) && !$mensajeError): ?>
        <p>No se encontraron canciones para esta categoría.</p>
    <?php elseif (!empty($songs)): ?>
        <ul>
            <?php foreach ($songs as $song): ?>
                <li>
                    <strong><?= htmlspecialchars($song['title'], ENT_QUOTES, 'UTF-8') ?></strong> 
                    (<?= duracionLegible($song['duration']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>