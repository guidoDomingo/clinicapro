/**
 * JavaScript para gestión de tipos de formularios
 */

// Variables globales
let editandoTipoFormulario = false;
let tipoFormularioActual = null;

// Cargar cuando el documento esté listo
$(document).ready(function() {
    // Cargar tipos de formularios al cargar la página
    cargarTiposFormularios();
    
    // Cargar tipos de formularios para el selector de preformatos
    cargarTiposFormulariosSelect();
    
    // Configurar eventos
    configurarEventos();
    
    // Configurar validaciones en tiempo real
    configurarValidaciones();
});

/**
 * Configurar eventos de la página
 */
function configurarEventos() {
    // Evento para enviar formulario de tipo de formulario
    $('#form-tipo-formulario').on('submit', function(e) {
        e.preventDefault();
        guardarTipoFormulario();
    });
    
    // Evento para cancelar edición
    $('#btn-cancelar-tipo').on('click', function() {
        cancelarEdicionTipoFormulario();
    });
    
    // Evento para generar código automáticamente
    $('#tipo-nombre').on('blur', function() {
        const nombre = $(this).val().trim();
        const codigo = $('#tipo-codigo').val().trim();
        
        if (nombre && !codigo) {
            generarCodigoAutomatico(nombre);
        }
    });
    
    // Evento para el tab de tipos de formularios
    $('a[href="#tipos-formularios"]').on('shown.bs.tab', function() {
        cargarTiposFormularios();
    });
    
    // Evento para limpiar formulario al abrir modal de nuevo
    $('#modalNuevoTipo').on('show.bs.modal', function() {
        if (!editandoTipoFormulario) {
            limpiarFormularioTipoFormulario();
            $('#modalNuevoTipo .modal-title').text('Nuevo Tipo de Formulario');
            $('#btn-guardar-tipo').text('Guardar');
        }
    });
    
    // Evento para limpiar al cerrar modal
    $('#modalNuevoTipo').on('hidden.bs.modal', function() {
        cancelarEdicionTipoFormulario();
    });
}

/**
 * Configurar validaciones en tiempo real
 */
function configurarValidaciones() {
    // Validar nombre en tiempo real
    $('#tipo-nombre').on('input', function() {
        const nombre = $(this).val().trim();
        if (nombre.length > 0) {
            validarNombreEnTiempoReal(nombre);
        } else {
            limpiarErrores('nombre');
        }
    });
    
    // Validar código en tiempo real
    $('#tipo-codigo').on('input', function() {
        const codigo = $(this).val().trim();
        if (codigo.length > 0) {
            validarCodigoEnTiempoReal(codigo);
        } else {
            limpiarErrores('codigo');
        }
    });
}

/**
 * Cargar lista de tipos de formularios
 */
function cargarTiposFormularios() {
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'POST',
        data: { accion: 'listar' },
        dataType: 'json',
        beforeSend: function() {
            $('#tbody-tipos-formularios').html(`
                <tr>
                    <td colspan="6" class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Cargando tipos de formularios...
                    </td>
                </tr>
            `);
        },
        success: function(response) {
            console.log('Respuesta recibida:', response);
            
            if (response.success && response.data) {
                let html = '';
                
                if (response.data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="fas fa-info-circle"></i> No hay tipos de formularios registrados
                            </td>
                        </tr>
                    `;
                } else {
                    response.data.forEach(function(tipo) {
                        const estadoBadge = tipo.activo ? 
                            '<span class="badge badge-success">Activo</span>' : 
                            '<span class="badge badge-secondary">Inactivo</span>';
                        
                        html += `
                            <tr>
                                <td>${tipo.id}</td>
                                <td>${tipo.nombre}</td>
                                <td>${tipo.codigo}</td>
                                <td>${tipo.descripcion || '-'}</td>
                                <td>${estadoBadge}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" onclick="editarTipoFormulario(${tipo.id})">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarTipoFormulario(${tipo.id})">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                
                $('#tbody-tipos-formularios').html(html);
                
                // Inicializar DataTable si no existe
                if (!$.fn.DataTable.isDataTable('#tabla-tipos-formularios')) {
                    $('#tabla-tipos-formularios').DataTable({
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                        },
                        responsive: true,
                        pageLength: 10,
                        order: [[1, 'asc']] // Ordenar por nombre
                    });
                }
            } else {
                $('#tbody-tipos-formularios').html(`
                    <tr>
                        <td colspan="6" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle"></i> ${response.message || 'Error al cargar tipos de formularios'}
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar tipos de formularios:', error);
            console.error('Respuesta:', xhr.responseText);
            $('#tbody-tipos-formularios').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar tipos de formularios: ${error}
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * Cargar tipos de formularios para el selector de preformatos
 */
function cargarTiposFormulariosSelect() {
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'POST',
        data: { accion: 'obtener_para_select' },
        success: function(response) {
            $('#tipo-formulario').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar tipos de formularios para select:', error);
        }
    });
}

/**
 * Guardar tipo de formulario (crear o actualizar)
 */
function guardarTipoFormulario() {
    const formData = new FormData();
    const accion = editandoTipoFormulario ? 'actualizar' : 'crear';
    
    formData.append('accion', accion);
    formData.append('nombre', $('#tipo-nombre').val().trim());
    formData.append('codigo', $('#tipo-codigo').val().trim());
    formData.append('descripcion', $('#tipo-descripcion').val().trim());
    
    if (editandoTipoFormulario && tipoFormularioActual) {
        formData.append('id', tipoFormularioActual.id);
    }
    
    // Deshabilitar botón de envío
    $('#btn-guardar-tipo').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Mostrar mensaje de éxito
                mostrarNotificacion('success', response.message);
                
                // Limpiar formulario
                limpiarFormularioTipoFormulario();
                
                // Recargar lista
                cargarTiposFormularios();
                
                // Recargar selector de preformatos
                cargarTiposFormulariosSelect();
                
                // Cancelar modo edición
                cancelarEdicionTipoFormulario();
            } else {
                mostrarNotificacion('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al guardar tipo de formulario:', error);
            mostrarNotificacion('error', 'Error al procesar la solicitud');
        },
        complete: function() {
            // Rehabilitar botón
            const textoBoton = editandoTipoFormulario ? 'Actualizar Tipo' : 'Crear Tipo';
            $('#btn-guardar-tipo').prop('disabled', false).html(textoBoton);
        }
    });
}

/**
 * Editar tipo de formulario
 */
function editarTipoFormulario(id) {
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'GET',
        data: { 
            accion: 'obtener_por_id',
            id: id 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const tipo = response.data;
                
                // Llenar formulario
                $('#tipo-id').val(tipo.id);
                $('#tipo-nombre').val(tipo.nombre);
                $('#tipo-codigo').val(tipo.codigo);
                $('#tipo-descripcion').val(tipo.descripcion);
                
                // Cambiar a modo edición
                editandoTipoFormulario = true;
                tipoFormularioActual = tipo;
                
                // Actualizar interfaz del modal
                $('#modalNuevoTipo .modal-title').text('Editar Tipo de Formulario');
                $('#btn-guardar-tipo').text('Actualizar');
                
                // Mostrar modal
                $('#modalNuevoTipo').modal('show');
                
                // Limpiar errores
                limpiarTodosLosErrores();
                
                // Hacer scroll al formulario
                $('html, body').animate({
                    scrollTop: $('#form-tipo-formulario').offset().top - 100
                }, 500);
                
            } else {
                mostrarNotificacion('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener tipo de formulario:', error);
            mostrarNotificacion('error', 'Error al cargar los datos');
        }
    });
}

/**
 * Eliminar tipo de formulario
 */
function eliminarTipoFormulario(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción no se puede deshacer. El tipo de formulario será eliminado permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/tipos-formularios.php',
                type: 'POST',
                data: {
                    accion: 'eliminar',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: response.message || 'El tipo de formulario ha sido eliminado.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        cargarTiposFormularios();
                        cargarTiposFormulariosSelect(); // Actualizar también el selector
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'Error al eliminar el tipo de formulario',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al eliminar:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al comunicarse con el servidor',
                        icon: 'error'
                    });
                }
            });
        }
    });
}

/**
 * Cambiar estado de tipo de formulario
 */
function cambiarEstadoTipoFormulario(id, activo) {
    const accionTexto = activo ? 'activar' : 'desactivar';
    
    if (!confirm(`¿Está seguro de ${accionTexto} este tipo de formulario?`)) {
        return;
    }
    
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'POST',
        data: {
            accion: 'cambiar_estado',
            id: id,
            activo: activo
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarNotificacion('success', response.message);
                cargarTiposFormularios();
                cargarTiposFormulariosSelect(); // Actualizar también el selector
            } else {
                mostrarNotificacion('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cambiar estado:', error);
            mostrarNotificacion('error', 'Error al procesar la solicitud');
        }
    });
}

/**
 * Cancelar edición de tipo de formulario
 */
function cancelarEdicionTipoFormulario() {
    editandoTipoFormulario = false;
    tipoFormularioActual = null;
    
    // Limpiar formulario
    limpiarFormularioTipoFormulario();
    
    // Restaurar interfaz
    $('#titulo-formulario-tipo').text('Crear Tipo de Formulario');
    $('#btn-guardar-tipo').text('Crear Tipo');
    $('#btn-cancelar-tipo').hide();
    
    // Limpiar errores
    limpiarTodosLosErrores();
}

/**
 * Limpiar formulario de tipo de formulario
 */
function limpiarFormularioTipoFormulario() {
    $('#form-tipo-formulario')[0].reset();
    $('#tipo-id').val('');
}

/**
 * Generar código automático basado en el nombre
 */
function generarCodigoAutomatico(nombre) {
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'POST',
        data: {
            accion: 'generar_codigo',
            nombre: nombre
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#tipo-codigo').val(response.codigo);
                limpiarErrores('codigo');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al generar código:', error);
        }
    });
}

/**
 * Validar nombre en tiempo real
 */
function validarNombreEnTiempoReal(nombre) {
    const id = editandoTipoFormulario && tipoFormularioActual ? tipoFormularioActual.id : null;
    
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'POST',
        data: {
            accion: 'validar_nombre',
            nombre: nombre,
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.existe) {
                mostrarError('nombre', 'Este nombre ya está en uso');
            } else {
                limpiarErrores('nombre');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al validar nombre:', error);
        }
    });
}

/**
 * Validar código en tiempo real
 */
function validarCodigoEnTiempoReal(codigo) {
    // Validar formato
    if (!/^[a-z0-9_]+$/.test(codigo)) {
        mostrarError('codigo', 'Solo se permiten letras minúsculas, números y guiones bajos');
        return;
    }
    
    const id = editandoTipoFormulario && tipoFormularioActual ? tipoFormularioActual.id : null;
    
    $.ajax({
        url: 'ajax/tipos-formularios.php',
        type: 'POST',
        data: {
            accion: 'validar_codigo',
            codigo: codigo,
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.existe) {
                mostrarError('codigo', 'Este código ya está en uso');
            } else {
                limpiarErrores('codigo');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al validar código:', error);
        }
    });
}

/**
 * Mostrar error en campo específico
 */
function mostrarError(campo, mensaje) {
    const input = $('#tipo-' + campo);
    const errorDiv = $('#error-' + campo);
    
    input.addClass('is-invalid');
    errorDiv.text(mensaje).show();
}

/**
 * Limpiar errores de campo específico
 */
function limpiarErrores(campo) {
    const input = $('#tipo-' + campo);
    const errorDiv = $('#error-' + campo);
    
    input.removeClass('is-invalid');
    errorDiv.text('').hide();
}

/**
 * Limpiar todos los errores
 */
function limpiarTodosLosErrores() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('').hide();
}

/**
 * Mostrar notificación
 */
function mostrarNotificacion(tipo, mensaje) {
    const iconos = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    const colores = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    // Crear elemento de notificación
    const notificacion = $(`
        <div class="alert ${colores[tipo]} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="${iconos[tipo]}"></i> ${mensaje}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);
    
    // Agregar al body
    $('body').append(notificacion);
    
    // Auto-cerrar después de 5 segundos
    setTimeout(function() {
        notificacion.alert('close');
    }, 5000);
}

// Exponer funciones globalmente para uso desde HTML
window.editarTipoFormulario = editarTipoFormulario;
window.cambiarEstadoTipoFormulario = cambiarEstadoTipoFormulario;
window.cargarTiposFormularios = cargarTiposFormularios;
