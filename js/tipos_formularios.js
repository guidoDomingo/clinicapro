/**
 * JavaScript para la gestión de tipos de formularios
 */

// Variables globales
let tablaTiposFormularios;
let modalTipoFormulario;

$(document).ready(function() {
    // Inicializar componentes cuando estemos en la página de preformatos
    if ($('#tablaTiposFormularios').length > 0) {
        inicializarTiposFormularios();
    }
});

function inicializarTiposFormularios() {
    try {
        // Inicializar modal
        modalTipoFormulario = new bootstrap.Modal(document.getElementById('modalTipoFormulario'));
        
        // Inicializar DataTable
        tablaTiposFormularios = $('#tablaTiposFormularios').DataTable({
            ajax: {
                url: 'ajax/tipos_formularios.php',
                type: 'GET',
                data: { accion: 'listar' },
                dataSrc: function(json) {
                    if (json.success) {
                        return json.data;
                    } else {
                        console.error('Error al cargar tipos de formularios:', json.message);
                        mostrarNotificacion('Error al cargar tipos de formularios: ' + json.message, 'error');
                        return [];
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error('Error al cargar tipos de formularios:', error);
                    mostrarNotificacion('Error de conexión al cargar tipos de formularios', 'error');
                }
            },
            columns: [
                { data: 'id', title: 'ID' },
                { data: 'nombre', title: 'Nombre' },
                { data: 'codigo', title: 'Código' },
                { data: 'descripcion', title: 'Descripción' },
                { 
                    data: 'activo',
                    title: 'Estado',
                    render: function(data, type, row) {
                        const activo = data === 't' || data === true || data === '1';
                        const badgeClass = activo ? 'bg-success' : 'bg-secondary';
                        const texto = activo ? 'Activo' : 'Inactivo';
                        return `<span class="badge ${badgeClass}">${texto}</span>`;
                    }
                },
                {
                    data: null,
                    title: 'Acciones',
                    orderable: false,
                    render: function(data, type, row) {
                        const activo = row.activo === 't' || row.activo === true || row.activo === '1';
                        let botones = `
                            <button class="btn btn-sm btn-outline-primary" onclick="editarTipoFormulario(${row.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                        `;
                        
                        if (activo) {
                            botones += `
                                <button class="btn btn-sm btn-outline-warning" onclick="desactivarTipoFormulario(${row.id})" title="Desactivar">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                        } else {
                            botones += `
                                <button class="btn btn-sm btn-outline-success" onclick="activarTipoFormulario(${row.id})" title="Activar">
                                    <i class="fas fa-check"></i>
                                </button>
                            `;
                        }
                        
                        botones += `
                            <button class="btn btn-sm btn-outline-danger" onclick="eliminarTipoFormulario(${row.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                        
                        return botones;
                    }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
            },
            responsive: true,
            pageLength: 10
        });
        
        // Cargar tipos para el selector de preformatos
        cargarTiposParaSelector();
        
        console.log('Tipos de formularios inicializados correctamente');
        
    } catch (error) {
        console.error('Error al inicializar tipos de formularios:', error);
    }
}

function cargarTiposParaSelector() {
    $.ajax({
        url: 'ajax/tipos_formularios.php',
        type: 'GET',
        data: { accion: 'listar' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                actualizarSelectorTipos(response.data);
            }
        },
        error: function(xhr, error, thrown) {
            console.error('Error al cargar tipos de formularios para select:', error);
        }
    });
}

function actualizarSelectorTipos(tipos) {
    const selector = $('#tipoFormulario');
    if (selector.length > 0) {
        selector.empty();
        selector.append('<option value="">Seleccionar tipo de formulario...</option>');
        
        tipos.forEach(function(tipo) {
            if (tipo.activo === 't' || tipo.activo === true || tipo.activo === '1') {
                selector.append(`<option value="${tipo.codigo}">${tipo.nombre}</option>`);
            }
        });
        
        // Refrescar Select2 si está inicializado
        if (selector.hasClass('select2-hidden-accessible')) {
            selector.trigger('change');
        }
    }
}

function abrirModalNuevoTipo() {
    $('#formTipoFormulario')[0].reset();
    $('#tipoFormularioId').val('');
    $('#modalTipoFormularioLabel').text('Nuevo Tipo de Formulario');
    $('#btnGuardarTipo').text('Crear');
    modalTipoFormulario.show();
}

function editarTipoFormulario(id) {
    $.ajax({
        url: 'ajax/tipos_formularios.php',
        type: 'GET',
        data: { 
            accion: 'obtener',
            id: id 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const tipo = response.data;
                $('#tipoFormularioId').val(tipo.id);
                $('#nombreTipo').val(tipo.nombre);
                $('#codigoTipo').val(tipo.codigo);
                $('#descripcionTipo').val(tipo.descripcion);
                $('#activoTipo').prop('checked', tipo.activo === 't' || tipo.activo === true || tipo.activo === '1');
                
                $('#modalTipoFormularioLabel').text('Editar Tipo de Formulario');
                $('#btnGuardarTipo').text('Actualizar');
                modalTipoFormulario.show();
            } else {
                mostrarNotificacion('Error al cargar los datos del tipo de formulario', 'error');
            }
        },
        error: function(xhr, error, thrown) {
            console.error('Error al cargar tipo de formulario:', error);
            mostrarNotificacion('Error de conexión al cargar el tipo de formulario', 'error');
        }
    });
}

function guardarTipoFormulario() {
    const formData = new FormData($('#formTipoFormulario')[0]);
    const id = $('#tipoFormularioId').val();
    const accion = id ? 'actualizar' : 'crear';
    
    formData.append('accion', accion);
    if (id) {
        formData.append('id', id);
    }
    
    $.ajax({
        url: 'ajax/tipos_formularios.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarNotificacion(response.message, 'success');
                modalTipoFormulario.hide();
                tablaTiposFormularios.ajax.reload();
                cargarTiposParaSelector();
            } else {
                mostrarNotificacion('Error: ' + response.message, 'error');
            }
        },
        error: function(xhr, error, thrown) {
            console.error('Error al guardar tipo de formulario:', error);
            mostrarNotificacion('Error de conexión al guardar', 'error');
        }
    });
}

function activarTipoFormulario(id) {
    cambiarEstadoTipo(id, 'activar');
}

function desactivarTipoFormulario(id) {
    cambiarEstadoTipo(id, 'desactivar');
}

function cambiarEstadoTipo(id, accion) {
    $.ajax({
        url: 'ajax/tipos_formularios.php',
        type: 'POST',
        data: {
            accion: accion,
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarNotificacion(response.message, 'success');
                tablaTiposFormularios.ajax.reload();
                cargarTiposParaSelector();
            } else {
                mostrarNotificacion('Error: ' + response.message, 'error');
            }
        },
        error: function(xhr, error, thrown) {
            console.error('Error al cambiar estado:', error);
            mostrarNotificacion('Error de conexión', 'error');
        }
    });
}

function eliminarTipoFormulario(id) {
    if (confirm('¿Está seguro de que desea eliminar este tipo de formulario? Esta acción no se puede deshacer.')) {
        $.ajax({
            url: 'ajax/tipos_formularios.php',
            type: 'POST',
            data: {
                accion: 'eliminar',
                id: id
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    mostrarNotificacion(response.message, 'success');
                    tablaTiposFormularios.ajax.reload();
                    cargarTiposParaSelector();
                } else {
                    mostrarNotificacion('Error: ' + response.message, 'error');
                }
            },
            error: function(xhr, error, thrown) {
                console.error('Error al eliminar tipo de formulario:', error);
                mostrarNotificacion('Error de conexión', 'error');
            }
        });
    }
}

function mostrarNotificacion(mensaje, tipo) {
    // Usar el sistema de notificaciones existente del sistema
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            text: mensaje,
            icon: tipo === 'success' ? 'success' : 'error',
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        // Fallback a alert si no está disponible SweetAlert
        alert(mensaje);
    }
}
