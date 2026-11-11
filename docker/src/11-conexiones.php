<?php
$resultadoPg = "Desconectado";
$resultadoMaria = "Desconectado";

// Rescatamos las credenciales de seguridad inyectadas por Docker (getenv)
// ¡Así nunca escribimos las contraseñas en el código PHP!
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');

// 1. Probamos conexión con PostgreSQL
try {
    $dsnPg = 'pgsql:host=postgres;port=5432;dbname=blend_pg';
    $dbPg = new PDO($dsnPg, $dbUser, $dbPass);
    $resultadoPg = $dbPg->query('SELECT current_database()')->fetchColumn();
} catch (PDOException $e) {
    // Si falla, el catch evita que la página colapse y guarda el error
    $resultadoPg = "Error: " . $e->getMessage();
}

// 2. Probamos conexión con MariaDB
try {
    $dsnMaria = 'mysql:host=mariadb;port=3306;dbname=blend_maria;charset=utf8mb4';
    $dbMaria = new PDO($dsnMaria, $dbUser, $dbPass);
    $resultadoMaria = $dbMaria->query('SELECT DATABASE()')->fetchColumn();
} catch (PDOException $e) {
    $resultadoMaria = "Error: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado de las Bases de Datos</title>
</head>
<body>
    <h1>Diagnóstico de Conexiones PDO</h1>
    <ul>
        <li><strong>Base de datos PostgreSQL:</strong> <?= htmlspecialchars($resultadoPg, ENT_QUOTES, 'UTF-8') ?></li>
        <li><strong>Base de datos MariaDB:</strong> <?= htmlspecialchars($resultadoMaria, ENT_QUOTES, 'UTF-8') ?></li>
    </ul>
</body>
</html>