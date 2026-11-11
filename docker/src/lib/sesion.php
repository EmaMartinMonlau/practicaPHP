<?php

ini_set('session.use_strict_mode', '1');

session_name('JUKEBOXSESSID');

session_set_cookie_params([
    'path' => '/',
    'httponly' => true, 
    'samesite' => 'Lax',
    'secure' => false 
]);

session_start();

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>