<?php


// SEGURIDAD: Nadie entra sin login
require_once __DIR__ . '/lib/acceso.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blend VIP - Reproductor</title>
    
    <!-- Cargamos los estilos de tu reproductor Cliente -->
    <link rel="stylesheet" href="css/styles.css">
    
    <!-- Fuentes originales -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Limelight&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=BBH+Bartle&display=swap" rel="stylesheet">

    <!-- Librería externa Howler -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js"></script>
    
    <!-- TUS SCRIPTS ORIGINALES -->
    <script src="scripts/scripts.js" defer></script>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo"><img src="imagenes/logo.png" alt="Logo"></div>
            <div style="flex-grow: 1;">
                <h1 id="app-title">Blend</h1>
                <p id="subtitle">Transforma tu experiencia musical</p>
            </div>
            
            <!-- MENÚ VIP INYECTADO -->
            <div class="vip-menu" style="text-align: right;">
                <p style="color:var(--text-muted); font-size:0.8rem; margin-bottom:5px;">
                    DJ: <strong><?= htmlspecialchars($_SESSION['alias'], ENT_QUOTES, 'UTF-8') ?></strong>
                </p>
                <a href="12-catalogo-json.php" style="color:var(--border-color); text-decoration:none; margin-right:15px; font-size:0.9rem;">Catálogo</a>
                <form action="17-logout.php" method="post" style="display:inline;">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" style="background:none; border:none; color:var(--text-main); cursor:pointer; font-size:0.9rem;">Salir</button>
                </form>
            </div>
        </header>

        <!-- AQUÍ PEGAMOS EXACTAMENTE TU MAIN ORIGINAL DEL INDEX.HTML -->
        <main class="app-main">
            <!-- Columna Izquierda: Lista de Reproducción -->
            <section class="playlist-section">
                <header class="playlist-header">
                    <h2>Lista de reproducción</h2>
                    <span id="song-count">0 canciones</span>
                </header>
                <ul id="playlist" class="playlist">
                    <!-- Javascript llenará esto -->
                </ul>
            </section>

            <!-- Columna Derecha: Sonando ahora y Ecualizador -->
            <aside class="details-section">
                <div class="now-playing-card">
                    <img src="imagenes/logo.png" alt="Carátula actual" id="current-img">
                    <div class="current-info">
                        <span class="badge">SONANDO AHORA</span>
                        <h2 id="current-title">Cargando...</h2>
                        <p id="current-artist">Por favor espera</p>
                    </div>
                </div>

                <div class="equalizer-card">
                    <header>
                        <h2>Ecualizador</h2>
                    </header>
                    <div id="visualizer" class="visualizer"></div>
                    <div class="audio-controls">
                        <div class="control-group">
                            <label>Graves</label>
                            <input type="range" id="eq-bass" min="-10" max="10" value="0">
                        </div>
                        <div class="control-group">
                            <label>Medios</label>
                            <input type="range" id="eq-mid" min="-10" max="10" value="0">
                        </div>
                        <div class="control-group">
                            <label>Agudos</label>
                            <input type="range" id="eq-treble" min="-10" max="10" value="0">
                        </div>
                    </div>
                    <button id="reset-eq">Restablecer ecualizador</button>
                </div>
            </aside>
        </main>

        <!-- FOOTER ORIGINAL DEL REPRODUCTOR -->
        <footer class="player-bar">
            <div class="player-info">
                <img src="imagenes/logo.png" alt="" id="player-thumb">
                <div>
                    <h4 id="player-title">Título</h4>
                    <p id="player-artist">Artista</p>
                </div>
            </div>
            <div class="player-controls">
                <div class="buttons">
                    <button id="btn-prev">⏮</button>
                    <button id="btn-play-pause">▶</button>
                    <button id="btn-next">⏭</button>
                </div>
                <div class="progress-container">
                    <span id="time-current">0:00</span>
                    <input type="range" id="progress-bar" min="0" max="100" value="0">
                    <span id="time-total">0:00</span>
                </div>
            </div>
            <div class="player-volume">
                <span><img src="imagenes/volumen.png" id="volume-icon" alt="Volumen"></span>
                <input type="range" id="volume-bar" min="0" max="1" step="0.01" value="0.8">
            </div>
        </footer>
    </div>
</body>
</html>