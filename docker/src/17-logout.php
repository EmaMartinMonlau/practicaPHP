<?php

require_once __DIR__ . '/lib/sesion.php';

// Solo aceptamos cierres de sesión mediante el botón POST (para evitar cierres maliciosos por URL)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRecibido = $_POST['csrf'] ?? '';
    if (hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        
        // 1. Vaciamos las variables de la sesión
        $_SESSION = [];
        
        // 2. Destruimos la sesión en el servidor
        session_destroy();
        
        // 3. Caducamos la cookie en el navegador del usuario (le quitamos la pulsera)
        setcookie(session_name(), '', time() - 3600, '/');
    }
}

// Lo enviamos de vuelta a la calle
header('Location: 15-login.php', true, 303);
exit;
?>