# Bloque 18 · Eliminación de fichas

## Qué cambia y por qué
Se activa el botón *Eliminar* con un cuadro de confirmación que muestra el título de la canción antes de borrarla. Al confirmar, PHP elimina únicamente la ficha del catálogo usando su `id`, sin borrar el archivo de audio `.mp3` del disco y sin cambiar los `id` del resto de canciones. La tabla solo se actualiza tras la confirmación del servidor.

## Qué probar
- Pulsar *Eliminar* y elegir *Cancelar* en el aviso.
- Borrar una canción intermedia y editar después la que quedó justo debajo.
- Eliminar la última canción disponible (en una copia de pruebas).

## Resultado esperado
Al cancelar no se envía ninguna petición. Al borrar devuelve `200`, los `id` restantes se mantienen estables y la canción siguiente se edita sin confusiones. Al quedar sin canciones, la tabla muestra *"Todavía no hay canciones"*.