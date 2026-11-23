# Bloque 15 · Consulta AJAX del catálogo

## 1. Qué cambia y por qué
Se implementa la primera operación completa del CRUD con AJAX (`list`), permitiendo consultar el catálogo desde el navegador sin recargar la página y mostrarlo en una tabla HTML segura:

- **`docker/src/lib/almacen-canciones.php`**: Centraliza la lectura de `/var/www/data/canciones.json` mediante bloqueo compartido (`LOCK_SH`). Si el archivo no existe o el JSON está dañado, lanza una excepción en lugar de sustituirlo silenciosamente por una lista vacía `[]`.
- **`docker/src/api/catalogo.php`**: Valida la sesión devolviendo `401` en JSON (sin redirigir a HTML), comprueba que `action` sea una cadena permitida (`list` por defecto), restringe `list` únicamente al método `GET` y devuelve `200` con `{"ok": true, "data": [...]}` usando `array_values()` para mantener índices consecutivos sin alterar los `id` internos.
- **`docker/src/23-canciones-ajax.php`**: Nueva pantalla privada enlazada desde `16-home.php`. Incluye un área de mensajes fuera del `tbody` con `role="status"` y `aria-live="polite"`, una tabla con `tbody` identificable y la carga de `scripts/catalogo-ajax.js` con el atributo `defer` para asegurar que el HTML esté analizado antes de ejecutar el script.
- **`docker/src/scripts/catalogo-ajax.js`**: Consulta la API con `fetch()` dentro de una función `async` (`cargarCatalogo`) protegida con `try / catch / finally` y dibuja los resultados con `renderizarCanciones()`.

## 2. Esperar la respuesta HTTP frente a interpretar el cuerpo JSON
En `cargarCatalogo()` se utilizan dos `await` consecutivos porque son dos fases distintas:
1. **`await fetch('api/catalogo.php?action=list')`**: Espera a que el servidor conteste y lleguen las cabeceras y el código de estado HTTP (`200`, `401`, `500`, etc.). Un código de error HTTP no hace que `fetch` falle por sí solo, por lo que se comprueba `respuesta.ok`.
2. **`await respuesta.json()`**: Lee y traduce el cuerpo de la respuesta a un objeto JavaScript. Solo se puede llamar una vez porque el cuerpo se consume al leerlo. Tras interpretarlo, se valida `contenido.ok === true` y `Array.isArray(contenido.data)`.

## 3. Seguridad y fiabilidad al dibujar la tabla
- **Por qué `textContent` en lugar de `innerHTML`**: `textContent` inserta texto plano y no interpreta etiquetas HTML. Así, si un título contiene caracteres como `<` o `>`, se muestran literalmente y se evita la ejecución de código malicioso (XSS).
- **Por qué `dataset.id` en lugar del número de fila**: El `id` identifica de forma única cada canción aunque su posición en la tabla cambie al eliminar o añadir otras fichas. Además, se conserva la lista en una variable en memoria (`cancionesActuales`) para localizar cada ficha por su `id` en los siguientes bloques.
- **Limpieza y estado vacío**: Antes de pintar, se vacía el `tbody` con `replaceChildren()`. Si la lista llega vacía, se reconstruye dinámicamente una fila que ocupa todas las columnas con el mensaje *"Todavía no hay canciones"*.

## 4. Comprobaciones realizadas
- **API con sesión (`200`)**: `GET /api/catalogo.php?action=list` devuelve `{"ok": true, "data": [...]}` en formato JSON.
- **API sin sesión (`401`)**: En ventana privada devuelve estado `401` y JSON de error, sin redirigir al login.
- **Red y consola en `23-canciones-ajax.php`**: Petición `list` con estado `200` en Network y carga correcta del array.
- **Tabla con datos y tabla vacía**: Se verifica que se pintan todas las canciones con botones de acciones deshabilitados, que al vaciar temporalmente `canciones.json` a `[]` aparece el aviso *"Todavía no hay canciones"*, y que al restaurar los datos el aviso desaparece.