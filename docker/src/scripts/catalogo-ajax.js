// 1. REFERENCIAS AL DOM
const mensajeEstado = document.getElementById('estado-mensaje');
const tbodyCanciones = document.getElementById('tabla-canciones');
const formCancion = document.getElementById('form-cancion');
const tituloFormulario = document.getElementById('titulo-formulario');
const btnGuardar = document.getElementById('btn-guardar');
const btnCancelar = document.getElementById('btn-cancelar');
const metaCsrf = document.querySelector('meta[name="csrf-token"]');

// 2. ESTADO EN MEMORIA
let cancionesActuales = [];
let operacionEnCurso = false;
let idEnEdicion = null; // null = modo alta | número = modo edición

// 3. FUNCIONES AUXILIARES Y COMUNICACIÓN CON LA API

/**
 * Centraliza todas las peticiones AJAX a api/catalogo.php.
 * Se extrae para evitar duplicar cabeceras, token CSRF y validaciones
 * de respuesta HTTP y del contrato JSON en las 4 operaciones del CRUD.
 */
async function solicitarApi(accion, metodo = 'GET', datos = null) {
    const opciones = { method: metodo };

    if (metodo === 'POST') {
        const token = metaCsrf ? metaCsrf.getAttribute('content') : '';
        opciones.headers = {
            'Content-Type': 'application/json',
            'X-CSRF-Token': token,
        };
        opciones.body = JSON.stringify(datos);
    }

    const respuesta = await fetch(`api/catalogo.php?action=${accion}`, opciones);
    const contenido = await respuesta.json();

    // Comprobamos tanto el código HTTP (200-299) como el campo ok del contrato
    if (!respuesta.ok || contenido.ok !== true) {
        throw new Error(contenido.error || 'No se pudo completar la operación.');
    }

    if (!Array.isArray(contenido.data)) {
        throw new Error('La respuesta del servidor no contiene una lista válida.');
    }

    return contenido.data;
}

/**
 * Bloquea o desbloquea los controles de modificación mientras hay una
 * petición en curso para impedir dobles clics o envíos simultáneos.
 */
function actualizarEstadoControles(deshabilitado) {
    btnGuardar.disabled = deshabilitado;
    btnCancelar.disabled = deshabilitado;

    tbodyCanciones
        .querySelectorAll('button[data-accion="editar"], button[data-accion="eliminar"]')
        .forEach((btn) => {
            btn.disabled = deshabilitado;
        });
}

function entrarModoEdicion(id) {
    if (!Number.isInteger(id) || id < 0) {
        return;
    }

    const elegida = cancionesActuales.find((cancion) => cancion.id === id);
    if (!elegida) {
        mensajeEstado.textContent = 'No se encontró la canción seleccionada.';
        return;
    }

    idEnEdicion = elegida.id;

    formCancion.elements.title.value = elegida.title ?? '';
    formCancion.elements.artist.value = elegida.artist ?? '';
    formCancion.elements.category.value = elegida.category ?? '';
    formCancion.elements.duration.value = elegida.duration ?? '';
    formCancion.elements.src.value = elegida.src ?? elegida.audio ?? '';

    tituloFormulario.textContent = `Editando: ${elegida.title}`;
    btnGuardar.textContent = 'Guardar cambios';
    btnCancelar.hidden = false;
    mensajeEstado.textContent = `Editando: ${elegida.title}`;

    // Llevamos el foco al primer campo para facilitar el uso con teclado
    formCancion.elements.title.focus();
}

function salirModoEdicion(devolverFoco = false) {
    idEnEdicion = null;
    formCancion.reset();
    tituloFormulario.textContent = 'Añadir nueva canción';
    btnGuardar.textContent = 'Guardar canción';
    btnCancelar.hidden = true;

    if (devolverFoco) {
        formCancion.elements.title.focus();
    }
}

function renderizarCanciones(canciones) {
    tbodyCanciones.replaceChildren();

    if (canciones.length === 0) {
        const filaVacia = document.createElement('tr');
        const celdaVacia = document.createElement('td');
        celdaVacia.colSpan = 5;
        celdaVacia.textContent = 'Todavía no hay canciones';
        filaVacia.append(celdaVacia);
        tbodyCanciones.append(filaVacia);
        return;
    }

    canciones.forEach((cancion) => {
        const fila = document.createElement('tr');

        const celdaTitulo = document.createElement('td');
        celdaTitulo.textContent = cancion.title;

        const celdaArtista = document.createElement('td');
        celdaArtista.textContent = cancion.artist;

        const celdaCategoria = document.createElement('td');
        celdaCategoria.textContent = cancion.category ?? 'Sin categoría';

        const celdaDuracion = document.createElement('td');
        celdaDuracion.textContent = `${cancion.duration ?? 0} s`;

        const celdaAcciones = document.createElement('td');

        const btnEditar = document.createElement('button');
        btnEditar.type = 'button';
        btnEditar.textContent = 'Editar';
        btnEditar.dataset.id = cancion.id;
        btnEditar.dataset.accion = 'editar';
        btnEditar.disabled = operacionEnCurso;

        const btnEliminar = document.createElement('button');
        btnEliminar.type = 'button';
        btnEliminar.textContent = 'Eliminar';
        btnEliminar.dataset.id = cancion.id;
        btnEliminar.dataset.accion = 'eliminar';
        btnEliminar.disabled = operacionEnCurso;

        celdaAcciones.append(btnEditar, ' ', btnEliminar);
        fila.append(celdaTitulo, celdaArtista, celdaCategoria, celdaDuracion, celdaAcciones);
        tbodyCanciones.append(fila);
    });
}

// 4. OPERACIONES DEL CRUD

async function cargarCatalogo() {
    mensajeEstado.textContent = 'Cargando catálogo...';

    try {
        cancionesActuales = await solicitarApi('list', 'GET');
        renderizarCanciones(cancionesActuales);
        mensajeEstado.textContent = '';
    } catch (error) {
        console.error('Error al cargar el catálogo:', error);
        mensajeEstado.textContent = error.message || 'No se pudo conectar con el servidor.';
    } finally {
        // Nunca mostramos mensajes de éxito en finally
        if (mensajeEstado.textContent === 'Cargando catálogo...') {
            mensajeEstado.textContent = '';
        }
    }
}

async function guardarCancion(evento) {
    evento.preventDefault();
    if (operacionEnCurso) {
        return;
    }

    const esEdicion = idEnEdicion !== null;
    const accion = esEdicion ? 'update' : 'create';

    const cargaDatos = {
        title: formCancion.elements.title.value.trim(),
        artist: formCancion.elements.artist.value.trim(),
        category: formCancion.elements.category.value,
        duration: Number(formCancion.elements.duration.value),
        src: formCancion.elements.src.value,
    };

    if (esEdicion) {
        cargaDatos.id = idEnEdicion;
    }

    operacionEnCurso = true;
    actualizarEstadoControles(true);
    mensajeEstado.textContent = esEdicion ? 'Guardando cambios...' : 'Guardando...';

    try {
        cancionesActuales = await solicitarApi(accion, 'POST', cargaDatos);
        renderizarCanciones(cancionesActuales);
        salirModoEdicion();
        mensajeEstado.textContent = esEdicion ? 'Cambios guardados' : 'Canción añadida';
    } catch (error) {
        console.error('Error al guardar:', error);
        mensajeEstado.textContent = error.message || 'No se pudo confirmar el guardado. Comprueba la lista antes de reenviar.';
    } finally {
        operacionEnCurso = false;
        actualizarEstadoControles(false);
    }
}

async function eliminarCancion(id) {
    if (!Number.isInteger(id) || id < 0 || operacionEnCurso) {
        return;
    }

    const elegida = cancionesActuales.find((cancion) => cancion.id === id);
    if (!elegida) {
        mensajeEstado.textContent = 'No se encontró la canción seleccionada.';
        return;
    }

    const confirmado = window.confirm(`¿Seguro que quieres eliminar "${elegida.title}"?`);
    if (!confirmado) {
        return;
    }

    operacionEnCurso = true;
    actualizarEstadoControles(true);
    mensajeEstado.textContent = 'Eliminando canción...';

    try {
        cancionesActuales = await solicitarApi('delete', 'POST', { id });
        if (idEnEdicion === id) {
            salirModoEdicion();
        }
        renderizarCanciones(cancionesActuales);
        mensajeEstado.textContent = 'Canción eliminada';
    } catch (error) {
        console.error('Error al eliminar:', error);
        mensajeEstado.textContent = error.message || 'No se pudo confirmar la eliminación.';
    } finally {
        operacionEnCurso = false;
        actualizarEstadoControles(false);
    }
}

// 5. EVENTOS Y ARRANQUE
tbodyCanciones.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton || operacionEnCurso) {
        return;
    }

    const id = Number(boton.dataset.id);
    if (boton.dataset.accion === 'editar') {
        entrarModoEdicion(id);
    } else if (boton.dataset.accion === 'eliminar') {
        eliminarCancion(id);
    }
});

btnCancelar.addEventListener('click', () => {
    salirModoEdicion(true);
    mensajeEstado.textContent = 'Edición cancelada.';
});

formCancion.addEventListener('submit', guardarCancion);

cargarCatalogo();