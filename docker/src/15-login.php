<?php
require_once __DIR__ . '/lib/sesion.php';

$mensajeError = '';
$email = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: 16-home.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenRecibido = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        http_response_code(403);
        echo "<h1 style='color:red;'>Error 403: CSRF inválido</h1>";
        exit;
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // 3. BUSCAMOS AL USUARIO EN EL JSON
    $rutaJson = '/var/www/data/usuarios.json';
    $textoJson = @file_get_contents($rutaJson); // El @ oculta warnings si el archivo no existe aún
    $usuarios = $textoJson ? json_decode($textoJson, true) : [];

    $usuarioEncontrado = null;
    foreach ($usuarios as $user) {
        if ($user['email'] === $email) {
            $usuarioEncontrado = $user;
            break;
        }
    }

    // 4. VERIFICAMOS LAS CREDENCIALES
    // Comprobamos si el usuario existe y si la contraseña coincide con el hash guardado
    if ($usuarioEncontrado && password_verify($password, $usuarioEncontrado['password_hash'])) {
        
        // 5. MEDIDA ANTIRROBO (Session Fixation)
        // Le arrancamos la pulsera vieja y le ponemos una nueva al entrar
        session_regenerate_id(true);

        // 6. ANOTAMOS SUS DATOS EN LA LISTA VIP (La sesión)
        $_SESSION['usuario_id'] = $usuarioEncontrado['id'];
        $_SESSION['alias'] = $usuarioEncontrado['alias'];

        // 7. LO DEJAMOS PASAR
        header('Location: 16-home.php', true, 303);
        exit;
    } else {
        // 8. MENSAJE DISUASORIO (Ambigüedad intencionada)
        // Nunca decimos "Correo no encontrado" o "Contraseña incorrecta", decimos "Credenciales no válidas".
        $mensajeError = 'Credenciales no válidas.';
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Iniciar Sesión - Blend</title>
</head>
<body>
    <h1>Iniciar Sesión</h1>
    
    <?php if ($mensajeError): ?>
        <p style="color:red;"><?= $mensajeError ?></p>
    <?php endif; ?>

    <form method="post">
        <!-- Token CSRF -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div>
            <label>Correo Electrónico:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <br>
        
        <div>
            <label>Contraseña:</label><br>
            <input type="password" name="password">
        </div>
        <br>
        
        <button type="submit">Entrar</button>
    </form>
    
    <p>¿No tienes cuenta? <a href="14-register.php">Regístrate aquí</a></p>
</body>
</html>