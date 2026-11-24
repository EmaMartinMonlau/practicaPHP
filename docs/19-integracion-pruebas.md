# Bloque 19 · Integración y pruebas de fallos

## Qué cambia y por qué
Se limpia el código de `catalogo-ajax.js` agrupando el envío de peticiones en una función común para evitar repeticiones y mejorar el uso con teclado. También se comprueba que la Home (reproductor), el catálogo clásico y la gestión AJAX convivan sobre el mismo archivo `canciones.json` sin romperse entre sí.

## Qué probar
- Crear una canción desde AJAX y comprobar que aparece en el catálogo clásico y se reproduce en la Home.
- Probar fallos controlados: método incorrecto (`405`), formato incorrecto (`415`), modo sin conexión (*Offline*) y un JSON dañado en copia de pruebas (`500`).

## Resultado esperado
Las tres pantallas muestran los mismos datos actualizados y el reproductor sigue recibiendo su formato habitual. Ante cualquier fallo, la API responde con el error adecuado sin vaciar ni dañar el catálogo.