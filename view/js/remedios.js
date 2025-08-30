/**
 * Script para manejar la búsqueda de remedios
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la funcionalidad de búsqueda de remedios
    inicializarBuscadorRemedios();
});

/**
 * Inicializa el buscador de remedios
 */
function inicializarBuscadorRemedios() {
    const btnBuscarRemedio = document.getElementById('btnBuscarRemedio');
    const inputBuscarRemedio = document.getElementById('txtBuscarRemedio');
    
    if (btnBuscarRemedio && inputBuscarRemedio) {
        // Manejar clic en el botón de búsqueda
        btnBuscarRemedio.addEventListener('click', function() {
            const terminoBusqueda = inputBuscarRemedio.value.trim();
            if (terminoBusqueda) {
                buscarRemedios(terminoBusqueda);
            } else {
                mostrarMensaje('warning', 'Por favor ingrese un término de búsqueda');
            }
        });
        
        // Manejar tecla Enter en el campo de búsqueda
        inputBuscarRemedio.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnBuscarRemedio.click();
            }
        });
    }
}

/**
 * Realiza la búsqueda de remedios en la API
 * @param {string} termino - Término de búsqueda
 */
function buscarRemedios(termino) {
    // Mostrar indicador de carga
    const resultadosContainer = document.getElementById('resultadosBusquedaRemedios');
    resultadosContainer.innerHTML = `
        <div class="text-center p-3">
            <i class="fas fa-spinner fa-pulse fa-2x"></i>
            <p class="mt-2">Buscando medicamentos...</p>
        </div>
    `;
    
    // Realizar la petición AJAX
    $.ajax({
        url: 'ajax/remedios.ajax.php',
        method: 'GET',
        data: { query: termino },
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta de API de remedios:', respuesta);
            
            if (respuesta.status === 'success' && respuesta.data) {
                mostrarResultadosRemedios(respuesta.data);
            } else {
                resultadosContainer.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        No se encontraron resultados para "${termino}"
                    </div>
                `;
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en búsqueda de remedios:', error);
            resultadosContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    Error al buscar medicamentos: ${error}
                </div>
            `;
        }
    });
}

/**
 * Muestra los resultados de la búsqueda de remedios
 * @param {Array|Object} datos - Datos de los medicamentos encontrados
 */
function mostrarResultadosRemedios(datos) {
    const resultadosContainer = document.getElementById('resultadosBusquedaRemedios');
    
    // Si no hay datos o es un objeto vacío
    if (!datos || (Array.isArray(datos) && datos.length === 0) || 
        (typeof datos === 'object' && Object.keys(datos).length === 0)) {
        resultadosContainer.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                No se encontraron medicamentos que coincidan con su búsqueda
            </div>
        `;
        return;
    }
    
    // Convertir a array si es un objeto
    const items = Array.isArray(datos) ? datos : [datos];
    
    // Crear la estructura HTML para los resultados
    let html = `
        <div class="list-group">
    `;
    
    // Iterar sobre los resultados
    items.forEach(item => {
        // Verificar qué campos tiene el objeto
        const nombre = item.nombre || item.name || 'Nombre no disponible';
        const descripcion = item.similar || item.similar || '';
        const presentacion = item.fabricante || item.fabricante || '';
        const laboratorio = item.laboratorio || item.laboratory || '';
        const principioActivo = item.principio_activo || item.active_ingredient || '';
        
        html += `
            <div class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">${nombre}</h5>
                    ${laboratorio ? `<small class="text-muted">${laboratorio}</small>` : ''}
                </div>
                ${presentacion ? `<p class="mb-1"><strong>Presentación:</strong> ${presentacion}</p>` : ''}
                ${principioActivo ? `<p class="mb-1"><strong>Principio activo:</strong> ${principioActivo}</p>` : ''}
                ${descripcion ? `<p class="mb-1">${descripcion}</p>` : ''}
                <button class="btn btn-sm btn-outline-primary mt-2 btn-seleccionar-remedio" 
                        data-nombre="${nombre}" 
                        data-presentacion="${presentacion}">
                    <i class="fas fa-plus-circle"></i> Agregar a receta
                </button>
            </div>
        `;
    });
    
    html += `</div>`;
    
    // Actualizar el contenedor de resultados
    resultadosContainer.innerHTML = html;
    
    // Agregar evento para seleccionar un medicamento
    document.querySelectorAll('.btn-seleccionar-remedio').forEach(btn => {
        btn.addEventListener('click', function() {
            const nombreMedicamento = this.getAttribute('data-nombre');
            const presentacion = this.getAttribute('data-presentacion');
            
            agregarMedicamentoAReceta(nombreMedicamento, presentacion);
        });
    });
}

/**
 * Agrega un medicamento al área de receta
 * @param {string} nombre - Nombre del medicamento
 * @param {string} presentacion - Presentación del medicamento
 */
function agregarMedicamentoAReceta(nombre, presentacion) {
    console.log('🔍 Buscando campo de receta para medicamento...');
    
    // Buscar el textarea de receta por ID y name
    let recetaTextarea = document.getElementById('receta');
    if (!recetaTextarea) {
        recetaTextarea = document.querySelector('textarea[name="receta_textarea"]');
    }
    
    console.log('📝 Campo de receta encontrado:', {
        id: recetaTextarea ? recetaTextarea.id : 'no encontrado',
        name: recetaTextarea ? recetaTextarea.name : 'no encontrado',
        hasSummernote: recetaTextarea ? $(recetaTextarea).hasClass('summernote') : false
    });
    
    if (recetaTextarea) {
        // Formatear el texto del medicamento
        const medicamentoTexto = `${nombre}${presentacion ? ` (${presentacion})` : ''}`;
        
        // Si es un editor Summernote
        if ($(recetaTextarea).hasClass('summernote')) {
            console.log('📝 Insertando medicamento en Summernote de receta...');
            
            const contenidoActual = $(recetaTextarea).summernote('code');
            let nuevoContenido = '';
            
            // Si ya hay contenido, agregar separación
            if (contenidoActual && contenidoActual.trim() !== '' && contenidoActual !== '<p><br></p>') {
                nuevoContenido = contenidoActual + '<br>';
            }
            
            // Agregar el medicamento con formato
            nuevoContenido += `<p><strong>💊 ${medicamentoTexto}</strong></p>`;
            nuevoContenido += `<p><em>Indicaciones: </em></p>`;
            nuevoContenido += `<p><br></p>`;
            
            $(recetaTextarea).summernote('code', nuevoContenido);
            
            // Enfocar en el editor para que el usuario pueda continuar escribiendo
            $(recetaTextarea).summernote('focus');
            
        } else {
            // Si es un textarea normal
            console.log('📝 Insertando medicamento en textarea normal de receta...');
            
            const contenidoActual = recetaTextarea.value || '';
            let nuevoContenido = '';
            
            if (contenidoActual.trim() !== '') {
                nuevoContenido = contenidoActual + '\n\n';
            }
            
            nuevoContenido += `💊 ${medicamentoTexto}\n`;
            nuevoContenido += `Indicaciones: \n\n`;
            
            recetaTextarea.value = nuevoContenido;
            recetaTextarea.focus();
        }
        
        // Mostrar mensaje de confirmación
        mostrarMensaje('success', `Medicamento "${nombre}" agregado a la receta exitosamente`);
        
        // Establecer bandera global para evitar reset del formulario
        window.insertingMedicamentoContent = true;
        
        // Cambiar a la pestaña de creación para que el usuario vea el resultado
        if (typeof showTab === 'function') {
            showTab('create');
        }
        
        console.log('✅ Medicamento agregado exitosamente a la receta');
        
    } else {
        console.error('❌ No se encontró el campo de receta');
        mostrarMensaje('error', 'No se pudo encontrar el campo de receta. Por favor, asegúrese de estar en la pestaña correcta.');
    }
}

/**
 * Muestra un mensaje utilizando el sistema de alertas disponible
 * @param {string} tipo - Tipo de mensaje (success, error, warning, info)
 * @param {string} mensaje - Texto del mensaje
 */
function mostrarMensaje(tipo, mensaje) {
    // Verificar si existe alertify (sistema de alertas preferido)
    if (typeof alertify !== 'undefined') {
        switch(tipo) {
            case 'success':
                alertify.success(mensaje);
                break;
            case 'error':
                alertify.error(mensaje);
                break;
            case 'warning':
                alertify.warning(mensaje);
                break;
            default:
                alertify.message(mensaje);
        }
    }
    // Fallback a SweetAlert2 si está disponible
    else if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: tipo,
            title: tipo === 'success' ? 'Éxito' : 'Atención',
            text: mensaje,
            timer: 3000,
            timerProgressBar: true
        });
    }
    // Fallback a alert nativo
    else {
        alert(mensaje);
    }
}
