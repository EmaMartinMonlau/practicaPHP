# Bloque 14 · Peticiones AJAX y contrato de la API

## 1. Recorrido de una petición AJAX
Acción del usuario (navegador) → JavaScript con `fetch()` → `api/catalogo.php` (PHP en Docker) → `/var/www/data/canciones.json` (archivo privado) → Respuesta JSON → JavaScript actualiza la tabla sin recargar la página.

## 2. Dos direcciones, dos responsabilidades
Ambas APIs leen el mismo archivo privado `/var/www/data/canciones.json` mediante funciones compartidas:
- **`api/canciones.php`**: Alimenta al reproductor de `16-home.php`. Devuelve directamente el array JSON de canciones (`[{...}]`).
- **`api/catalogo.php`**: Gestiona el CRUD desde `23-canciones-ajax.php`. Devuelve un objeto con `ok` y `data` (`{"ok": true, "data": [...]}`) o `ok` y `error` (`{"ok": false, "error": "..."}`).

## 3. Contrato de operaciones (`api/catalogo.php`)
| Operación | Método y URL | Cuerpo JSON enviado | Respuesta correcta |
| :--- | :--- | :--- | :--- |
| **Consultar** | `GET api/catalogo.php?action=list` | Ninguno | `200` + `{"ok": true, "data": [...]}` |
| **Crear** | `POST api/catalogo.php?action=create` | `title`, `artist`, `category`, `duration`, `src` | `201` + `{"ok": true, "data": [...]}` |
| **Editar** | `POST api/catalogo.php?action=update` | `id`, `title`, `artist`, `category`, `duration`, `src` | `200` + `{"ok": true, "data": [...]}` |
| **Eliminar** | `POST api/catalogo.php?action=delete` | `id` | `200` + `{"ok": true, "data": [...]}` |

*(Ninguna petición `GET` podrá crear, editar ni eliminar canciones).*

## 4. Códigos de error acordados
- **400 Bad Request**: JSON mal escrito o acción desconocida.
- **401 Unauthorized**: Falta una sesión válida.
- **403 Forbidden**: Token CSRF ausente o incorrecto.
- **404 Not Found**: La canción indicada no existe.
- **405 Method Not Allowed**: Método HTTP no permitido para esa acción (incluye cabecera `Allow`).
- **415 Unsupported Media Type**: El cuerpo no se envía como `application/json`.
- **422 Unprocessable Entity**: Campos con valores no válidos (ej. *"La duración debe ser un entero entre 1 y 1800 segundos."*).
- **500 Internal Server Error**: No se puede leer o guardar el catálogo (sin exponer rutas internas ni contraseñas).

## 5. Comprobaciones del contrato
1. **Consultar sin sesión**: `401` → `{"ok": false, "error": "Debes iniciar sesión."}`
2. **Editar un id inexistente**: `404` → `{"ok": false, "error": "La canción solicitada no existe."}`
3. **Enviar una duración negativa**: `422` → `{"ok": false, "error": "La duración debe ser un entero entre 1 y 1800 segundos."}`