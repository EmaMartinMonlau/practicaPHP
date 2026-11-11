<?php
require_once __DIR__ . '/lib/sesion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $tokenRecibido = $_POST['csrf'] ?? '';
    
    if (!hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        http_response_code(403);
        echo "<h1 style='color:red;'>Error 403: Intento de falsificación detectado (CSRF inválido)</h1>";
        exit; 
    }

    $mensaje = "¡Formulario aceptado! El sello CSRF es válido.";
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Prueba de Seguridad CSRF</title>
</head>
<body>
    <h1>Prueba del Escudo CSRF</h1>
    
    <p>Tu sello secreto actual es: <strong><?= $_SESSION['csrf'] ?></strong></p>
    
    <?php if ($mensaje): ?>
        <p style="color:green; font-weight:bold;"><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Escribe algo: <input type="text" name="dato"></label>
        
        <!-- ESTO ES VITAL: Inyectamos el sello en un input invisible -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
        
        <button type="submit">Enviar Seguro</button>
    </form>
</body>
</html>