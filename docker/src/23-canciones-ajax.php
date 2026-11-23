<?php
// docker/src/23-canciones-ajax.php
require_once __DIR__ . '/lib/acceso.php';
require_once __DIR__ . '/lib/almacen-canciones.php';

// Aseguramos que exista el token CSRF en la sesión antes de generar el HTML
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$audiosDisponibles = obtenerAudiosAutorizados();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <title>Gestión de Canciones (AJAX) - Blend</title>
    <script src="scripts/catalogo-ajax.js" defer></script>
</head>
<body>
    <header>
        <h1>Gestión del Catálogo (AJAX)</h1>
        <nav>
            <a href="16-home.php">Volver a la Home</a> |
            <a href="12-catalogo-json.php">Catálogo clásico</a>
        </nav>
    </header>

    <main>
        <section>
            <h2 id="titulo-formulario">Añadir nueva canción</h2>
            <form id="form-cancion">
                <div>
                    <label for="campo-title">Título:</label>
                    <input type="text" id="campo-title" name="title" required maxlength="80">
                </div>
                <div>
                    <label for="campo-artist">Artista:</label>
                    <input type="text" id="campo-artist" name="artist" required maxlength="80">
                </div>
                <div>
                    <label for="campo-category">Categoría:</label>
                    <select id="campo-category" name="category" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach (CATEGORIAS_PERMITIDAS as $cat): ?>
                            <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(ucfirst($cat), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="campo-duration">Duración (segundos):</label>
                    <input type="number" id="campo-duration" name="duration" required min="1" max="1800" step="1">
                </div>
                <div>
                    <label for="campo-src">Archivo de audio:</label>
                    <select id="campo-src" name="src" required>
                        <option value="">Selecciona un audio</option>
                        <?php foreach ($audiosDisponibles as $ruta): ?>
                            <option value="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars(basename($ruta), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" id="btn-guardar">Guardar canción</button>
                <button type="button" id="btn-cancelar" hidden>Cancelar</button>
            </form>
        </section>

        <!-- Área de mensajes fuera del tbody -->
        <p id="estado-mensaje" role="status" aria-live="polite"></p>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Artista</th>
                    <th>Categoría</th>
                    <th>Duración</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-canciones">
                <!-- JavaScript rellenará las filas aquí -->
            </tbody>
        </table>
    </main>
</body>
</html>