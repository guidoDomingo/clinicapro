/**
 * Archivo JavaScript para la gestión de turnos
 */

$(document).ready(function() {
    // Cargar DataTable de turnos
    cargarTablaTurnos();
    
    // Validar nombre único al escribirlo
    validarNombreUnico();
    
    // Configurar eventos de botones
    configurarEventos();
});

/**
 * Función para cargar la tabla de turnos utilizando DataTables
 */
function cargarTablaTurnos() {
    // Destruir tabla si ya existe
    if ($.fn.DataTable.isDataTable('#tblTurnos')) {
        $('#tblTurnos').DataTable().destroy();
    }
    
    // Inicializar con nuevas opciones
    $('#tblTurnos').DataTable({
        "processing": true,
        "ajax": {
            "url": "ajax/turnos.ajax.php",
            "type": "POST",
            "data": function(d) {
                return { "accion": "obtenerTurnos" };
            },
            "dataType": "json",
            "dataSrc": function(json) {
                console.log("Respuesta AJAX:", json);
                // Asegurarse de devolver un array vacío si no hay datos
                return json || [];
            },
            "error": function(xhr, error, thrown) {
                console.error("Error en DataTable: ", error);
                console.error("Detalle: ", thrown);
                console.error("Respuesta: ", xhr.responseText);
                // Mostrar alerta
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar datos',
                    text: 'No se pudieron cargar los turnos. Verifique la conexión.'
                });
            }
        },
        "columns": [
            { 
                "data": null,
                "render": function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { "data": "turno_nombre" },
            { 
                "data": "turno_descripcion",
                "render": function(data, type, row) {
                    return data || 'Sin descripción';
                }
            },
            { 
                "data": "turno_estado",
                "render": function(data, type, row) {
                    if(data == 1 || data === true) {
                        return '<span class="badge badge-success">Activo</span>';
                    } else {
                        return '<span class="badge badge-danger">Inactivo</span>';
                    }
                }
            },
            { 
                "data": "fecha_creacion",
                "render": function(data, type, row) {
                    if(data) {
                        return new Date(data).toLocaleDateString('es-ES');
                    }
                    return '';
                }
            },
            { 
                "data": null,
                "render": function(data, type, row) {
                    let botones = '';
                    
                    // Botón editar
                    botones += '<button class="btn btn-warning btn-sm btnEditarTurno" ' +
                              'data-id="' + row.turno_id + '" title="Editar">' +
                              '<i class="fas fa-edit"></i></button> ';
                    
                    // Botón cambiar estado
                    if(row.turno_estado == 1 || row.turno_estado === true) {
                        botones += '<button class="btn btn-secondary btn-sm btnCambiarEstadoTurno" ' +
                                  'data-id="' + row.turno_id + '" data-estado="0" title="Desactivar">' +
                                  '<i class="fas fa-toggle-off"></i></button> ';
                    } else {
                        botones += '<button class="btn btn-success btn-sm btnCambiarEstadoTurno" ' +
                                  'data-id="' + row.turno_id + '" data-estado="1" title="Activar">' +
                                  '<i class="fas fa-toggle-on"></i></button> ';
                    }
                    
                    // Botón eliminar
                    botones += '<button class="btn btn-danger btn-sm btnEliminarTurno" ' +
                              'data-id="' + row.turno_id + '" title="Eliminar">' +
                              '<i class="fas fa-trash"></i></button>';
                    
                    return botones;
                }
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        "responsive": true,
        "pageLength": 10,
        "order": [[0, "desc"]]
    });
}

/**
 * Configurar eventos de botones
 */
function configurarEventos() {
    // Botón agregar turno
    $(document).on('click', '#btnAgregarTurno', function() {
        limpiarFormulario();
        $('#modalFormTurnoLabel').text('Agregar Turno');
        $('#modalFormTurno').modal('show');
    });
    
    // Botón editar turno
    $(document).on('click', '.btnEditarTurno', function() {
        let idTurno = $(this).data('id');
        editarTurno(idTurno);
    });
    
    // Botón cambiar estado turno
    $(document).on('click', '.btnCambiarEstadoTurno', function() {
        let idTurno = $(this).data('id');
        let nuevoEstado = $(this).data('estado');
        cambiarEstadoTurno(idTurno, nuevoEstado);
    });
    
    // Botón eliminar turno
    $(document).on('click', '.btnEliminarTurno', function() {
        let idTurno = $(this).data('id');
        eliminarTurno(idTurno);
    });
    
    // Submit del formulario
    $('#frmTurno').on('submit', function(e) {
        e.preventDefault();
        guardarTurno();
    });
    
    // Botón filtrar
    $('#btnFiltrarTurnos').on('click', function() {
        filtrarTurnos();
    });
    
    // Botón limpiar
    $('#btnLimpiarTurnos').on('click', function() {
        limpiarFiltros();
    });
    
    // Filtro por estado
    $('#filtroEstado').on('change', function() {
        filtrarPorEstado();
    });
}

/**
 * Validar nombre único de turno
 */
function validarNombreUnico() {
    $('#turno_nombre').on('blur', function() {
        let nombre = $(this).val();
        let turnoId = $('#turno_id').val();
        
        if(nombre.trim() !== '') {
            $.ajax({
                url: 'ajax/turnos.ajax.php',
                type: 'POST',
                data: {
                    'validarNombreTurno': nombre
                },
                dataType: 'json',
                success: function(respuesta) {
                    if(respuesta && respuesta.turno_id != turnoId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Nombre duplicado',
                            text: 'Este nombre ya está en uso por otro turno'
                        });
                        $('#turno_nombre').addClass('is-invalid');
                    } else {
                        $('#turno_nombre').removeClass('is-invalid');
                    }
                }
            });
        }
    });
}

/**
 * Limpiar formulario
 */
function limpiarFormulario() {
    $('#frmTurno')[0].reset();
    $('#turno_id').val('');
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();
}

/**
 * Editar turno
 */
function editarTurno(idTurno) {
    $.ajax({
        url: 'ajax/turnos.ajax.php',
        type: 'POST',
        data: {
            'idTurno': idTurno
        },
        dataType: 'json',
        success: function(respuesta) {
            if(respuesta) {
                $('#turno_id').val(respuesta.turno_id);
                $('#turno_nombre').val(respuesta.turno_nombre);
                $('#turno_descripcion').val(respuesta.turno_descripcion);
                $('#turno_estado').val(respuesta.turno_estado);
                
                $('#modalFormTurnoLabel').text('Editar Turno');
                $('#modalFormTurno').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información del turno'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor'
            });
        }
    });
}

/**
 * Guardar turno (crear o editar)
 */
function guardarTurno() {
    // Validar formulario
    if(!validarFormulario()) {
        return;
    }
    
    let formData = new FormData($('#frmTurno')[0]);
    let esEdicion = $('#turno_id').val() !== '';
    
    // Determinar la acción
    if(esEdicion) {
        formData.append('accion', 'editarTurno');
    } else {
        formData.append('accion', 'crearTurno');
    }
    
    // Mostrar indicador de carga
    $('#btnGuardarTurno').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: 'ajax/turnos.ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta del servidor:', respuesta);
            
            if(respuesta.status === 'success') {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: respuesta.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(function(){
                    $('#modalFormTurno').modal('hide');
                    cargarTablaTurnos();
                    limpiarFormulario();
                });
            } else {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: respuesta.message,
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            console.error('Respuesta completa:', xhr.responseText);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor: ' + error
            });
        },
        complete: function() {
            // Restaurar botón
            $('#btnGuardarTurno').prop('disabled', false).html('Guardar');
        }
    });
}

/**
 * Validar formulario
 */
function validarFormulario() {
    let esValido = true;
    
    // Validar nombre
    if($('#turno_nombre').val().trim() === '') {
        $('#turno_nombre').addClass('is-invalid');
        esValido = false;
    } else if($('#turno_nombre').val().trim().length > 50) {
        $('#turno_nombre').addClass('is-invalid');
        Swal.fire({
            icon: 'warning',
            title: 'Nombre muy largo',
            text: 'El nombre del turno no puede exceder los 50 caracteres'
        });
        esValido = false;
    } else {
        $('#turno_nombre').removeClass('is-invalid');
    }
    
    // Validar descripción (opcional, pero si existe no debe ser muy larga)
    if($('#turno_descripcion').val().trim().length > 255) {
        $('#turno_descripcion').addClass('is-invalid');
        Swal.fire({
            icon: 'warning',
            title: 'Descripción muy larga',
            text: 'La descripción no puede exceder los 255 caracteres'
        });
        esValido = false;
    } else {
        $('#turno_descripcion').removeClass('is-invalid');
    }
    
    return esValido;
}

/**
 * Cambiar estado de turno (activar/desactivar)
 */
function cambiarEstadoTurno(idTurno, nuevoEstado) {
    let accionTexto = nuevoEstado == 1 ? 'activar' : 'desactivar';
    let colorBoton = nuevoEstado == 1 ? '#28a745' : '#6c757d';
    
    Swal.fire({
        title: '¿Está seguro?',
        text: `Esta acción va a ${accionTexto} el turno`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: colorBoton,
        cancelButtonColor: '#d33',
        confirmButtonText: `Sí, ${accionTexto}`,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/turnos.ajax.php',
                type: 'POST',
                data: {
                    'accion': 'cambiarEstadoTurno',
                    'turno_id': idTurno,
                    'nuevo_estado': nuevoEstado
                },
                dataType: 'json',
                success: function(respuesta) {
                    if(respuesta.status === 'success') {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: respuesta.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function(){
                            cargarTablaTurnos();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: respuesta.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al comunicarse con el servidor'
                    });
                }
            });
        }
    });
}

/**
 * Eliminar turno
 */
function eliminarTurno(idTurno) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción eliminará permanentemente el turno. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax/turnos.ajax.php',
                type: 'POST',
                data: {
                    'accion': 'eliminarTurno',
                    'turno_id': idTurno
                },
                dataType: 'json',
                success: function(respuesta) {
                    if(respuesta.status === 'success') {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: respuesta.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function(){
                            cargarTablaTurnos();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: respuesta.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al comunicarse con el servidor'
                    });
                }
            });
        }
    });
}

/**
 * Filtrar turnos por búsqueda
 */
function filtrarTurnos() {
    let termino = $('#validarBusquedaTurno').val();
    
    if(termino.trim() !== '') {
        $('#tblTurnos').DataTable().search(termino).draw();
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Campo vacío',
            text: 'Ingrese un término de búsqueda'
        });
    }
}

/**
 * Limpiar filtros
 */
function limpiarFiltros() {
    $('#validarBusquedaTurno').val('');
    $('#filtroEstado').val('');
    $('#tblTurnos').DataTable().search('').draw();
    cargarTablaTurnos();
}

/**
 * Filtrar por estado
 */
function filtrarPorEstado() {
    let estado = $('#filtroEstado').val();
    
    if(estado !== '') {
        // Filtrar usando la columna de estado (columna 3)
        $('#tblTurnos').DataTable().column(3).search(estado == 1 ? 'Activo' : 'Inactivo').draw();
    } else {
        $('#tblTurnos').DataTable().column(3).search('').draw();
    }
}

/**
 * Función auxiliar para buscar turnos mediante AJAX
 */
function buscarTurnosAjax(termino) {
    $.ajax({
        url: 'ajax/turnos.ajax.php',
        type: 'POST',
        data: {
            'accion': 'buscarTurnos',
            'termino_busqueda': termino
        },
        dataType: 'json',
        success: function(respuesta) {
            if(respuesta.status === 'success') {
                // Actualizar DataTable con los resultados
                $('#tblTurnos').DataTable().clear().rows.add(respuesta.data).draw();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin resultados',
                    text: respuesta.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al realizar la búsqueda'
            });
        }
    });
}
