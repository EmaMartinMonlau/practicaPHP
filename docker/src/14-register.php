<?php
require_once __DIR__ . '/lib/sesion.php';

$errores = [];
$alias = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $tokenRecibido = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $tokenRecibido)) {
        http_response_code(403);
        echo "<h1 style='color:red;'>Error 403: Petición rechazada (CSRF inválido)</h1>";
        exit;
    }

    $alias = trim($_POST['alias'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? '')); // Correo siempre en minúsculas
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    if (mb_strlen($alias) < 2 || mb_strlen($alias) > 30) {
        $errores['alias'] = 'El alias debe tener entre 2 y 30 caracteres.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El formato del correo no es válido.';
    }

    if (strlen($password) < 12 || strlen($password) > 64) {
        $errores['password'] = 'La contraseña debe tener entre 12 y 64 caracteres.';
    } elseif ($password !== $passwordConfirm) {
        $errores['password'] = 'Las contraseñas no coinciden.';
    }

    if (empty($errores)) {
        $rutaJson = '/var/www/data/usuarios.json';
        
        $archivo = fopen($rutaJson, 'r+');
        
        if ($archivo) {
            if (flock($archivo, LOCK_EX)) {
                
                $contenido = stream_get_contents($archivo);
                $usuarios = json_decode($contenido, true) ?? [];
                
                $correoDuplicado = false;
                $maxId = 0;
                
                foreach ($usuarios as $user) {
                    if ($user['email'] === $email) {
                        $correoDuplicado = true;
                    }
                    if ($user['id'] > $maxId) {
                        $maxId = $user['id']; 
                    }
                }
                
                if ($correoDuplicado) {
                    $errores['email'] = 'Ese correo ya está registrado.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $nuevoUsuario = [
                        'id' => $maxId + 1, // Le asignamos el siguiente ID disponible
                        'alias' => $alias,
                        'email' => $email,
                        'password_hash' => $hash // GUARDAMOS EL HASH, NUNCA LA CONTRASEÑA REAL
                    ];
                    
                    $usuarios[] = $nuevoUsuario;
                    
                    $nuevoContenido = json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    
                    rewind($archivo);
                    fwrite($archivo, $nuevoContenido); 
                    ftruncate($archivo, ftell($archivo)); 
                    fflush($archivo); 
                    
                    flock($archivo, LOCK_UN);
                    fclose($archivo);
                    
                    header('Location: 15-login.php', true, 303);
                    exit;
                }
                
                flock($archivo, LOCK_UN);
            }
            fclose($archivo);
        } else {
            $errores['general'] = 'Error técnico: No se pudo abrir la base de datos.';
        }
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registro de Asociación Blend</title>
</head>
<body>
    <h1>Crear una cuenta de prueba</h1>
    
    <?php if (isset($errores['general'])): ?>
        <p style="color:red;"><?= $errores['general'] ?></p>
    <?php endif; ?>

    <form method="post">
        <!-- Nuestro escudo CSRF invisible -->
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div>
            <label>Alias (2-30 caracteres):</label><br>
            <input type="text" name="alias" value="<?= htmlspecialchars($alias, ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errores['alias'])) echo "<span style='color:red;'>{$errores['alias']}</span>"; ?>
        </div>
        <br>
        
        <div>
            <label>Correo Electrónico:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
            <?php if (isset($errores['email'])) echo "<span style='color:red;'>{$errores['email']}</span>"; ?>
        </div>
        <br>
        
        <div>
            <label>Contraseña (Mín. 12 caracteres):</label><br>
            <!-- Las contraseñas NUNCA se recuerdan en el value por seguridad -->
            <input type="password" name="password">
            <?php if (isset($errores['password'])) echo "<span style='color:red;'>{$errores['password']}</span>"; ?>
        </div>
        <br>
        
        <div>
            <label>Confirmar Contraseña:</label><br>
            <input type="password" name="password_confirm">
        </div>
        <br>
        
        <button type="submit">Registrarme</button>
    </form>
</body>
</html>