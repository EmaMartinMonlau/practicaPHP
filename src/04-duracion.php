<?php
$duration = 185; 

// Calculamos los minutos (división entera)
$minutes = intdiv($duration, 60); 

// Calculamos los segundos sobrantes (módulo/resto)
$seconds = $duration % 60; 
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cálculo de Duración</title>
</head>
<body>
    <h1>Duración de la canción</h1>
    
    <p>Duración original: <?= $duration ?> segundos.</p>
    <p><strong>Formato amigable:</strong> <?= $minutes ?> minutos y <?= $seconds ?> segundos.</p>
</body>
</html>