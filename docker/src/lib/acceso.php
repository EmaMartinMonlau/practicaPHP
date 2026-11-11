<?php
// Nos aseguramos de que el portero principal ya haya comprobado la pulsera
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/sesion.php';
}

// Si la lista VIP de este usuario no tiene el 'usuario_id' anotado...
if (!isset($_SESSION['usuario_id'])) {
    // ¡A la calle! (Lo enviamos al login)
    header('Location: 15-login.php', true, 303);
    exit;
}
// Si llega hasta aquí, el script simplemente termina y deja que la página continúe cargando
?>