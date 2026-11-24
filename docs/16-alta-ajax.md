# Bloque 16 · Alta segura con AJAX

## Qué cambia y por qué
Se añade un formulario en `23-canciones-ajax.php` para crear canciones nuevas sin recargar la página. Para que el alta sea segura, PHP comprueba la sesión del usuario, verifica el token de seguridad (CSRF) y valida todos los datos antes de guardarlos en `canciones.json` bajo bloqueo. Además, el botón se bloquea mientras guarda para evitar dobles clics.

## Qué probar
- Rellenar el formulario con datos válidos y pulsar *Guardar canción*.
- Enviar peticiones de prueba desde consola sin token, con duración negativa y con texto mal formado.

## Resultado esperado
El alta correcta responde `201`, añade la nueva fila a la tabla, vacía el formulario y se mantiene al recargar. Los intentos incorrectos devuelven `403`, `422` y `400` respectivamente, sin modificar el archivo.