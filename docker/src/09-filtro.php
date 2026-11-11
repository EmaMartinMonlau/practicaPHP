<?php
// 1. Importamos todas las canciones
$songs = require __DIR__ . '/lib/catalogo-inicial.php';

// 2. Leemos la URL usando $_GET
// Los símbolos "??" significan: "Si el usuario no ha escrito ninguna categoría en la URL, usa 'todas' por defecto".
$category = $_GET['category'] ?? 'todas';

// 3. SEGURIDAD: Definimos una lista VIP de categorías permitidas
$allowed = ['todas', 'electronica', 'rock', 'relax'];

// Si lo que pide el usuario NO es un texto válido, o NO está en nuestra lista VIP...
if (!is_string($category) || !in_array($category, $allowed, true)) {
    // Le devolvemos un error 400 (Petición incorrecta) y detenemos la página (exit)
    http_response_code(400);
    echo "<h1>Error 400: Categoría no válida</h1>";
    exit;
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Filtro de Canciones</title>
</head>
<body>
    <h1>Catálogo filtrado por: <?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></h1>
    
    <ul>
        <?php 
        $hayCanciones = false;
        
        // Recorremos todas las canciones
        foreach ($songs as $song) {
            // Si el usuario pidió 'todas' O si la categoría de la canción coincide con lo que pidió el usuario...
            if ($category === 'todas' || $song['category'] === $category) {
                $hayCanciones = true; // Marcamos que al menos hemos encontrado una
                echo "<li><strong>" . htmlspecialchars($song['title'], ENT_QUOTES, 'UTF-8') . "</strong> (" . $song['category'] . ")</li>";
            }
        }

        // Si después de buscar no encontramos nada...
        if (!$hayCanciones) {
            echo "<p>No hay canciones en esta categoría.</p>";
        }
        ?>
    </ul>
</body>
</html>