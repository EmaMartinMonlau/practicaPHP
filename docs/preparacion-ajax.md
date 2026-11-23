# Bloque 13 · Preparación de la ampliación AJAX

## Qué cambia y por qué
Se añaden los archivos base (`canciones-ajax.php`, `api/catalogo.php` y `scripts/catalogo-ajax.js`) para implementar la gestión del catálogo sin recargas, conservando intactos la primera etapa (puerto 8090), el CRUD clásico y el reproductor en el puerto 8091.

## Punto de recuperación
- Commit Git: `326dd97`

## Comprobación previa
- Servicios levantados desde `docker/` con `docker compose up -d` y verificados con `docker compose ps`.
- Acceso comprobado en `http://localhost:8091` (login, `16-home.php` con reproducción de audio y catálogo clásico).
- Los datos privados (`canciones.json` y `usuarios.json`) permanecen aislados en `/var/www/data`.