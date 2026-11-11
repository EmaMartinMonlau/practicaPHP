<?php
// Traemos nuestras herramientas de formato (para que duracionLegible funcione)
require_once __DIR__ . '/lib/canciones.php';

// Le pedimos al "chico de los recados" que nos traiga el array desde la despensa JSON
$songs = require __DIR__ . '/lib/almacen-json.php';
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Catálogo Oficial JSON</title>
</head>
<body>
    <h1>Catálogo de Canciones Blend (Desde JSON)</h1>
    
    <!-- SI LA BANDEJA LLEGÓ VACÍA -->
    <?php if (empty($songs)): ?>
        <p><strong>Todavía no hay canciones o hubo un problema técnico en la despensa.</strong></p>
    <!-- SI LA BANDEJA LLEGÓ LLENA -->
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