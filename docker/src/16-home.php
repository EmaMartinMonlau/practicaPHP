<?php

// 1. INVOCAMOS AL SEGURATA
require_once __DIR__ . '/lib/acceso.php';
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Home Privada - Blend</title>
</head>
<body>
    <h1>Bienvenido a la zona VIP, <?= htmlspecialchars($_SESSION['alias'], ENT_QUOTES, 'UTF-8') ?></h1>
    
    <p>Desde aquí podrás gestionar el <a href="12-catalogo-json.php">Catálogo de Canciones</a> (próximamente).</p>
    
    <!-- Cierre de sesión seguro mediante POST -->
    <form action="17-logout.php" method="post" style="margin-top: 20px;">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Cerrar Sesión</button>
    </form>
</body>
</html>