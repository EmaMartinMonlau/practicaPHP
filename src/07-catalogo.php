<?php
//$songs = require __DIR__ . '/lib/catalogo-inicial.php';
$songs = require __DIR__ . '/lib/catalogo-inicial.php';

?>

<!-- --- EL COMEDOR (Nuestra Presentación) --- -->
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Catálogo Blend</title>
</head>
<body>
    <h1>Catálogo de Canciones Blend</h1>
    
    <!-- Comprobamos si la lista está vacía antes de imprimir nada -->
    <?php if (empty($songs)): ?>
        <p>Todavía no hay canciones</p>
    <?php else: ?>
        <ul>
            <!-- Recorremos la colección. Por cada $song, generamos un <li> -->
            <?php foreach ($songs as $song): ?>
                <li>
                    <strong><?= htmlspecialchars($song['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong> 
                    por <?= htmlspecialchars($song['artist'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> 
                    (<?= $song['duration'] ?> seg)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>