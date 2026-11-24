# Bloque 20 · Entrega y presentación técnica

## 1. Qué cambia y por qué
Se cierra la ampliación de RA2 integrando la gestión AJAX (`23-canciones-ajax.php`, `api/catalogo.php` y `scripts/catalogo-ajax.js`) con las pantallas previas (`16-home.php` y `12-catalogo-json.php`), sin duplicar el almacén ni romper el reproductor ni el CRUD clásico.

## 2. Cómo preparar y arrancar una copia limpia
1. Copiar `docker/.env.example` a `docker/.env` con credenciales ficticias locales.
2. Copiar el catálogo de ejemplo de `docker/data-ejemplo/canciones.json` a `docker/data/canciones.json`.
3. Desde la carpeta `docker/`, levantar los contenedores con `docker compose up -d`.
4. Acceder a `http://localhost:8091`, iniciar sesión con una cuenta ficticia y navegar entre la Home, el Catálogo clásico y la Gestión AJAX.

## 3. Qué probar en la demostración y resultado esperado
- **Alta sin recarga (`201`)**: Añadir una canción en `23-canciones-ajax.php` con Network abierto (filtro Fetch/XHR); aparece la nueva fila sin recarga de tipo `Document`.
- **Edición y borrado (`200`)**: Editar la ficha manteniendo su `id` y eliminar otra tras confirmación.
- **Validación rechazada (`422` / `403`)**: Probar un dato inválido o sin token y confirmar que `canciones.json` no se modifica.
- **Integración y cierre de sesión (`401`)**: Ver los cambios reflejados en `12-catalogo-json.php` y `16-home.php`; al cerrar sesión, la API rechaza cualquier consulta o cambio con `401`.