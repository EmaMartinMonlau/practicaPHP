<?php
const APP_NAME = 'Blend - Tu experiencia musical';

// DATOS DE LA CANCIÓN (Variables)
$title = 'Indie Rock Dreamy'; // string
$artist = 'Alex Morgan';      // string
$duration = 185;              // int (Segundos enteros)
$volume = 0.5;                // float
$available = true;            // bool

// SEGURIDAD (Escapar el título por si tuviera caracteres raros)
$safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?= APP_NAME ?></title>
</head>
<body>
    <h1><?= APP_NAME ?></h1>
    
    <h2>Ficha de la canción:</h2>
    <ul>
        <li><strong>Título:</strong> <?= $safeTitle ?></li>
        <li><strong>Artista:</strong> <?= $artist ?></li>
        <li><strong>Duración (segundos):</strong> <?= $duration ?></li>
    </ul>

    <!-- Creamos un inspeccionador temporal para comprobar los tipos de datos -->
    <h3>Inspección de tipos (var_dump temporal):</h3>
    <pre>
        <?php 
        echo "Duración: ";
        var_dump($duration); 
        echo "Volumen: ";
        var_dump($volume);   
        ?>
    </pre>
</body>
</html>