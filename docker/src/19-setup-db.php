<?php
// INVOCAMOS AL SEGURIDAD
// Crear tablas es peligroso, solo un administrador logueado debería poder ejecutar esto
require_once __DIR__ . '/lib/acceso.php';

$mensaje = "";

// RECUPERAMOS LAS CLAVES DESDE DOCKER (Ocultas en el .env)
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASSWORD');

// LA DIRECCIÓN POSTAL DE LA BASE DE DATOS (DSN)
// Le decimos que conecte a PostgreSQL (pgsql), en el contenedor 'postgres', base de datos 'blend_pg'
$dsn = "pgsql:host=postgres;port=5432;dbname=blend_pg";

try {
    // ABRIMOS LA CONEXIÓN (El teléfono directo con el Archivero)
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    
    // Configuramos el teléfono para que nos grite si hay cualquier error SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ESCRIBIMOS LAS INSTRUCCIONES SQL (El plano del cajón)
    // Usamos CREATE TABLE IF NOT EXISTS para que no dé error si ya estaba creada
    $sqlCanciones = "
        CREATE TABLE IF NOT EXISTS canciones (
            id SERIAL PRIMARY KEY,
            title VARCHAR(150) NOT NULL,
            artist VARCHAR(150) NOT NULL,
            category VARCHAR(50) NOT NULL,
            duration INT NOT NULL,
            src VARCHAR(255) NOT NULL
        )
    ";

    // ENVIAMOS LA ORDEN AL ARCHIVERO
    // exec() se usa para comandos que NO devuelven datos (como crear, insertar o borrar)
    $pdo->exec($sqlCanciones);

    $mensaje = "¡Éxito! La tabla 'canciones' ha sido creada (o ya existía) en PostgreSQL.";

} catch (PDOException $e) {
    // Si la conexión o la creación falla, capturamos la explosión
    $mensaje = "Error crítico de Base de Datos: " . $e->getMessage();
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Instalador de Base de Datos</title>
</head>
<body>
    <h1>Configuración Inicial de la BBDD</h1>
    
    <!-- Imprimimos el resultado de nuestra operación en verde o rojo -->
    <p style="font-weight: bold; color: <?= strpos($mensaje, 'Error') === false ? 'green' : 'red' ?>;">
        <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p><a href="16-home.php">← Volver a la Home</a></p>
</body>
</html>