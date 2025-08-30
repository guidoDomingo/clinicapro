/**
 * JavaScript para Consultas v3.0 - Sistema Livewire CRUD
 * Funcionalidades específicas del módulo de consultas con Livewire
 */

// Variables globales del módulo
let consultasV3 = {
    pacienteSeleccionado: null,
    consultaActual: null,
    historialCargado: false
};

/**
 * Inicialización principal del módulo
 */
function initConsultasV3() {
    console.log('Inicializando Consultas v3.0 Livewire...');
    
    initBusquedaPacientes();
    initFormularioConsulta();
    initEventosGenerales();
    initDataTables();
    
    mostrarMensajeBienvenida();
}

/**
 * Inicializar sistema de búsqueda de pacientes
 */
function initBusquedaPacientes() {
    const buscarInput = document.getElementById('buscar-paciente');
    
    if (buscarInput) {
        // Debounce para optimizar las búsquedas
        let timeoutId;
        
        buscarInput.addEventListener('input', function(e) {
            clearTimeout(timeoutId);
            const searchTerm = e.target.value.trim();
            
            timeoutId = setTimeout(() => {
                if (searchTerm.length >= 3) {
                    buscarPacientes(searchTerm);
                } else if (searchTerm.length === 0) {
                    limpiarResultadosBusqueda();
                }
            }, 500);
        });
        
        // Búsqueda al presionar Enter
        buscarInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const searchTerm = e.target.value.trim();
                if (searchTerm.length >= 3) {
                    buscarPacientes(searchTerm);
                }
            }
        });
    }
}

/**
 * Buscar pacientes (integración con Livewire pendiente)
 */
function buscarPacientes(termino) {
    console.log('Buscando pacientes con término:', termino);
    
    // Mostrar indicador de carga
    mostrarCargando('Buscando pacientes...');
    
    // Simulación de búsqueda (reemplazar con llamada Livewire)
    setTimeout(() => {
        const pacientesEjemplo = [
            {
                id: 1,
                nombre: 'Juan Pérez',
                cedula: '12345678',
                telefono: '0991234567',
                edad: 35
            },
            {
                id: 2,
                nombre: 'María González',
                cedula: '87654321',
                telefono: '0997654321',
                edad: 28
            }
        ];
        
        mostrarResultadosPacientes(pacientesEjemplo);
        ocultarCargando();
    }, 1000);
}

/**
 * Mostrar resultados de búsqueda de pacientes
 */
function mostrarResultadosPacientes(pacientes) {
    const infoPaciente = document.getElementById('info-paciente');
    
    if (pacientes.length === 0) {
        infoPaciente.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se encontraron pacientes con el criterio de búsqueda.
            </div>
        `;
        return;
    }
    
    let html = '<div class="list-group">';
    pacientes.forEach(paciente => {
        html += `
            <div class="list-group-item list-group-item-action" 
                 onclick="seleccionarPaciente(${paciente.id}, '${paciente.nombre}', '${paciente.cedula}')">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${paciente.nombre}</h6>
                    <small class="text-muted">CI: ${paciente.cedula}</small>
                </div>
                <p class="mb-1">
                    <i class="fas fa-phone text-muted"></i> ${paciente.telefono}
                    <span class="ml-3"><i class="fas fa-calendar text-muted"></i> ${paciente.edad} años</span>
                </p>
            </div>
        `;
    });
    html += '</div>';
    
    infoPaciente.innerHTML = html;
}

/**
 * Seleccionar paciente
 */
function seleccionarPaciente(id, nombre, cedula) {
    consultasV3.pacienteSeleccionado = { id, nombre, cedula };
    
    // Actualizar interfaz
    const infoPaciente = document.getElementById('info-paciente');
    infoPaciente.innerHTML = `
        <div class="alert-paciente-seleccionado fade-in">
            <h5><i class="fas fa-user-check"></i> Paciente Seleccionado</h5>
            <strong>${nombre}</strong><br>
            <small>CI: ${cedula}</small>
            <div class="mt-3">
                <button class="btn btn-sm btn-info mr-2" onclick="verHistorial(${id})">
                    <i class="fas fa-history"></i> Ver Historial
                </button>
                <button class="btn btn-sm btn-warning" onclick="editarPaciente(${id})">
                    <i class="fas fa-edit"></i> Editar
                </button>
            </div>
        </div>
    `;
    
    // Cargar historial
    cargarHistorialPaciente(id);
    
    // Habilitar formulario de consulta
    habilitarFormularioConsulta();
    
    toastr.success(`Paciente ${nombre} seleccionado correctamente`, 'Paciente Seleccionado');
}

/**
 * Cargar historial del paciente
 */
function cargarHistorialPaciente(pacienteId) {
    console.log('Cargando historial para paciente:', pacienteId);
    
    // Simulación de carga de historial
    const historialEjemplo = [
        {
            fecha: '2024-01-15',
            profesional: 'Dr. García',
            motivo: 'Control rutinario',
            diagnostico: 'Estado normal'
        },
        {
            fecha: '2023-12-10',
            profesional: 'Dra. Martínez',
            motivo: 'Dolor de cabeza',
            diagnostico: 'Migraña leve'
        }
    ];
    
    actualizarTablaHistorial(historialEjemplo);
    consultasV3.historialCargado = true;
}

/**
 * Actualizar tabla de historial
 */
function actualizarTablaHistorial(historial) {
    const tbody = document.querySelector('#tabla-historial tbody');
    
    if (historial.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-muted py-4">
                    No hay consultas registradas para este paciente
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    historial.forEach(consulta => {
        html += `
            <tr>
                <td>${formatearFecha(consulta.fecha)}</td>
                <td>${consulta.profesional}</td>
                <td>${consulta.motivo}</td>
                <td>${consulta.diagnostico}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="verDetalleConsulta('${consulta.fecha}')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning ml-1" onclick="editarConsulta('${consulta.fecha}')">
                        <i class="fas fa-edit"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

/**
 * Inicializar formulario de consulta
 */
function initFormularioConsulta() {
    const form = document.getElementById('form-nueva-consulta');
    
    if (form) {
        // Deshabilitar inicialmente
        deshabilitarFormularioConsulta();
        
        // Eventos del formulario
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarConsulta();
        });
    }
}

/**
 * Habilitar formulario de consulta
 */
function habilitarFormularioConsulta() {
    const form = document.getElementById('form-nueva-consulta');
    if (form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => input.disabled = false);
        
        form.classList.remove('estado-deshabilitado');
        form.classList.add('estado-resaltado', 'pulso-suave');
        
        setTimeout(() => {
            form.classList.remove('pulso-suave');
        }, 3000);
    }
}

/**
 * Deshabilitar formulario de consulta
 */
function deshabilitarFormularioConsulta() {
    const form = document.getElementById('form-nueva-consulta');
    if (form) {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => input.disabled = true);
        
        form.classList.add('estado-deshabilitado');
        form.classList.remove('estado-resaltado', 'pulso-suave');
    }
}

/**
 * Guardar nueva consulta
 */
function guardarConsulta() {
    if (!consultasV3.pacienteSeleccionado) {
        toastr.error('Debe seleccionar un paciente primero', 'Error');
        return;
    }
    
    const form = document.getElementById('form-nueva-consulta');
    const formData = new FormData(form);
    
    // Validaciones básicas
    const motivo = form.querySelector('textarea[placeholder*="motivo"]').value.trim();
    if (!motivo) {
        toastr.error('El motivo de consulta es requerido', 'Validación');
        return;
    }
    
    // Mostrar confirmación
    Swal.fire({
        title: '¿Guardar consulta?',
        text: `Se guardará la consulta para ${consultasV3.pacienteSeleccionado.nombre}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            procesarGuardadoConsulta(formData);
        }
    });
}

/**
 * Procesar guardado de consulta
 */
function procesarGuardadoConsulta(formData) {
    mostrarCargando('Guardando consulta...');
    
    // Simulación de guardado (reemplazar con Livewire)
    setTimeout(() => {
        ocultarCargando();
        
        Swal.fire({
            title: '¡Consulta guardada!',
            text: 'La consulta ha sido registrada exitosamente',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
        
        // Limpiar formulario y recargar historial
        limpiarFormularioConsulta();
        cargarHistorialPaciente(consultasV3.pacienteSeleccionado.id);
        
    }, 1500);
}

/**
 * Limpiar formulario de consulta
 */
function limpiarFormularioConsulta() {
    const form = document.getElementById('form-nueva-consulta');
    if (form) {
        form.reset();
    }
}

/**
 * Inicializar eventos generales
 */
function initEventosGenerales() {
    // Botón limpiar todo
    document.addEventListener('click', function(e) {
        if (e.target.textContent.includes('Limpiar Todo')) {
            limpiarTodo();
        }
        
        if (e.target.textContent.includes('Nueva Consulta')) {
            nuevaConsulta();
        }
        
        if (e.target.textContent.includes('Buscar Pacientes')) {
            abrirBuscadorPacientes();
        }
    });
}

/**
 * Limpiar todo el módulo
 */
function limpiarTodo() {
    Swal.fire({
        title: '¿Limpiar todo?',
        text: 'Se perderán todos los datos no guardados',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Limpiar datos
            consultasV3.pacienteSeleccionado = null;
            consultasV3.consultaActual = null;
            consultasV3.historialCargado = false;
            
            // Limpiar interfaz
            document.getElementById('buscar-paciente').value = '';
            limpiarResultadosBusqueda();
            limpiarFormularioConsulta();
            deshabilitarFormularioConsulta();
            
            const tbody = document.querySelector('#tabla-historial tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="alert-sin-datos">
                        No hay consultas registradas
                    </td>
                </tr>
            `;
            
            toastr.success('Módulo limpiado correctamente', 'Limpiar');
        }
    });
}

/**
 * Limpiar resultados de búsqueda
 */
function limpiarResultadosBusqueda() {
    const infoPaciente = document.getElementById('info-paciente');
    infoPaciente.innerHTML = `
        <div class="alert-sin-datos">
            Seleccione un paciente para ver su información
        </div>
    `;
}

/**
 * Inicializar DataTables
 */
function initDataTables() {
    // Las tablas se manejarán dinámicamente, no como DataTable estático
    console.log('DataTables configurado para modo dinámico');
}

/**
 * Mostrar indicador de carga
 */
function mostrarCargando(mensaje = 'Cargando...') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: mensaje,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

/**
 * Ocultar indicador de carga
 */
function ocultarCargando() {
    if (typeof Swal !== 'undefined') {
        Swal.close();
    }
}

/**
 * Formatear fecha
 */
function formatearFecha(fecha) {
    const date = new Date(fecha);
    return date.toLocaleDateString('es-PY', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

/**
 * Mostrar mensaje de bienvenida
 */
function mostrarMensajeBienvenida() {
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "4000"
        };
        
        toastr.info(
            'Sistema de Consultas v3.0 con Livewire cargado correctamente. ¡Explore las nuevas funcionalidades!', 
            'Bienvenido al Sistema v3.0'
        );
    }
}

/**
 * Funciones adicionales (stubs para futuras implementaciones)
 */
function nuevaConsulta() {
    console.log('Nueva consulta - función a implementar');
}

function abrirBuscadorPacientes() {
    console.log('Abrir buscador avanzado - función a implementar');
}

function verHistorial(pacienteId) {
    console.log('Ver historial completo - función a implementar');
}

function editarPaciente(pacienteId) {
    console.log('Editar paciente - función a implementar');
}

function verDetalleConsulta(fecha) {
    console.log('Ver detalle de consulta - función a implementar');
}

function editarConsulta(fecha) {
    console.log('Editar consulta - función a implementar');
}

// Exportar funciones principales para uso externo
window.consultasV3 = consultasV3;
window.initConsultasV3 = initConsultasV3;