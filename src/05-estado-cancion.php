<?php
$duration = 300; 

if ($duration === 0) {
    $estado = 'Sin duración';
} elseif ($duration <= 240) {
    $estado = 'Hasta cuatro minutos';
} else {
    $estado = 'Más de cuatro minutos';
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de la canción</title>
</head>
<body>
    <h1>Clasificación de la pista</h1>
    <p>Duración: <?= $duration ?> segundos.</p>
    <p>Estado automático: <strong><?= $estado ?></strong></p>
</body>
</html> 