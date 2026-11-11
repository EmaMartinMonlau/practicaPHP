<?php

// HERRAMIENTAS
require_once __DIR__ . '/lib/canciones.php';

$songs = [];
$mensajeError = "";

// CONEXIÓN A LA BASE DE DATOS
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');
$dsn = "pgsql:host=postgres;port=5432;dbname=blend_pg";

try {
    // Abrimos el canal de comunicación con el Archivero
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // LA PREGUNTA (Consulta SQL)
    $sql = "SELECT * FROM canciones ORDER BY id ASC";
    
    // EJECUTAMOS Y RECOGEMOS LOS DATOS
    // Usamos query() porque esta consulta es estática (no depende de lo que escriba el usuario)
    $stmt = $pdo->query($sql);
    
    // fetchAll() saca todas las filas del cajón y las convierte en un Array Asociativo de PHP
    $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si la base de datos está caída, atrapamos el error para que la página no explote
    $mensajeError = "No se pudo conectar al catálogo digital en este momento.";
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Catálogo desde BBDD - Blend</title>
</head>
<body>
    <h1>Catálogo Oficial Blend (Vía PostgreSQL)</h1>
    
    <p><a href="21-nueva-cancion-db.php">Añadir Canción </a></p>

    <?php if ($mensajeError): ?>
        <p style="color:red;"><strong><?= $mensajeError ?></strong></p>
    <?php endif; ?>

    <!-- SI LA BBDD ESTÁ VACÍA -->
    <?php if (empty($songs) && !$mensajeError): ?>
        <p><strong>El archivero de PostgreSQL está vacío. ¡Añade tu primera canción!</strong></p>
    
    <!-- SI LA BBDD TIENE DATOS -->
    <?php else: ?>
        <ul>
            <?php foreach ($songs as $song): ?>
                <li>
                    <strong><?= htmlspecialchars($song['title'], ENT_QUOTES, 'UTF-8') ?></strong> 
                    por <?= htmlspecialchars($song['artist'], ENT_QUOTES, 'UTF-8') ?> 
                    (<?= duracionLegible($song['duration']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>