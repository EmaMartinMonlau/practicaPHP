# Bloque 17 · Edición de canciones

## Qué cambia y por qué
Se activa el botón *Editar* de la tabla y se reutiliza el mismo formulario para modificar canciones ya existentes. Usamos el `id` de la canción para saber si estamos creando o editando y así no duplicar filas. También se añade un botón *Cancelar* para salir de la edición sin guardar cambios.

## Qué probar
- Editar el título o la duración de una canción y guardar los cambios.
- Pulsar *Editar*, cambiar algún campo y pulsar *Cancelar*.
- Borrar manualmente una canción del archivo mientras se está editando e intentar guardarla.

## Resultado esperado
Al guardar devuelve `200`, actualiza la fila manteniendo el mismo `id` y vuelve al modo alta. *Cancelar* limpia el formulario sin enviar peticiones. Si la canción ya no existía, responde `404` y no la vuelve a crear.