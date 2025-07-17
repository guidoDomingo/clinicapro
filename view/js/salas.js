/**
 * Archivo JavaScript para la gestión de salas
 */

$(document).ready(function() {
    // Cargar DataTable de salas
    cargarTablaSalas();
    
    // Validar código único al escribirlo
    validarCodigoUnico();
    
    // Configurar eventos de botones
    configurarEventos();
});

/**
 * Función para cargar la tabla de salas utilizando DataTables
 */
function cargarTablaSalas() {
    // Destruir tabla si ya existe
    if ($.fn.DataTable.isDataTable('#tblSalas')) {
        $('#tblSalas').DataTable().destroy();
    }
    
    // Inicializar con nuevas opciones
    $('#tblSalas').DataTable({
        "processing": true,
        "ajax": {
            "url": "ajax/salas.ajax.php",
            "type": "POST",
            "data": function(d) {
                return { "accion": "obtenerSalas" };
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
                    text: 'No se pudieron cargar las salas. Verifique la conexión.'
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
            { "data": "sala_codigo" },
            { "data": "sala_nombre" },
            { 
                "data": "sala_descripcion",
                "render": function(data, type, row) {
                    return data || 'Sin descripción';
                }
            },
            { 
                "data": "sala_estado",
                "render": function(data, type, row) {
                    if(data == 1) {
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
                    botones += '<button class="btn btn-warning btn-sm btnEditarSala" ' +
                              'data-id="' + row.sala_id + '" title="Editar">' +
                              '<i class="fas fa-edit"></i></button> ';
                    
                    // Botón eliminar/desactivar
                    if(row.sala_estado == 1) {
                        botones += '<button class="btn btn-danger btn-sm btnEliminarSala" ' +
                                  'data-id="' + row.sala_id + '" title="Desactivar">' +
                                  '<i class="fas fa-trash"></i></button>';
                    } else {
                        botones += '<button class="btn btn-success btn-sm btnActivarSala" ' +
                                  'data-id="' + row.sala_id + '" title="Activar">' +
                                  '<i class="fas fa-check"></i></button>';
                    }
                    
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
    // Botón agregar sala
    $(document).on('click', '#btnAgregarSala', function() {
        limpiarFormulario();
        $('#modalFormSalaLabel').text('Agregar Sala');
        $('#modalFormSala').modal('show');
    });
    
    // Botón editar sala
    $(document).on('click', '.btnEditarSala', function() {
        let idSala = $(this).data('id');
        editarSala(idSala);
    });
    
    // Botón eliminar sala
    $(document).on('click', '.btnEliminarSala', function() {
        let idSala = $(this).data('id');
        eliminarSala(idSala);
    });
    
    // Botón activar sala
    $(document).on('click', '.btnActivarSala', function() {
        let idSala = $(this).data('id');
        activarSala(idSala);
    });
    
    // Submit del formulario
    $('#frmSala').on('submit', function(e) {
        e.preventDefault();
        guardarSala();
    });
    
    // Botón filtrar
    $('#btnFiltrarSalas').on('click', function() {
        filtrarSalas();
    });
    
    // Botón limpiar
    $('#btnLimpiarSalas').on('click', function() {
        limpiarFiltros();
    });
    
    // Filtro por estado
    $('#filtroEstado').on('change', function() {
        filtrarPorEstado();
    });
}

/**
 * Validar código único de sala
 */
function validarCodigoUnico() {
    $('#sala_codigo').on('blur', function() {
        let codigo = $(this).val();
        let salaId = $('#sala_id').val();
        
        if(codigo.trim() !== '') {
            $.ajax({
                url: 'ajax/salas.ajax.php',
                type: 'POST',
                data: {
                    'validarCodigoSala': codigo
                },
                dataType: 'json',
                success: function(respuesta) {
                    if(respuesta && respuesta.sala_id != salaId) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Código duplicado',
                            text: 'Este código ya está en uso por otra sala'
                        });
                        $('#sala_codigo').addClass('is-invalid');
                    } else {
                        $('#sala_codigo').removeClass('is-invalid');
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
    $('#frmSala')[0].reset();
    $('#sala_id').val('');
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').hide();
}

/**
 * Editar sala
 */
function editarSala(idSala) {
    $.ajax({
        url: 'ajax/salas.ajax.php',
        type: 'POST',
        data: {
            'idSala': idSala
        },
        dataType: 'json',
        success: function(respuesta) {
            if(respuesta) {
                $('#sala_id').val(respuesta.sala_id);
                $('#sala_codigo').val(respuesta.sala_codigo);
                $('#sala_nombre').val(respuesta.sala_nombre);
                $('#sala_descripcion').val(respuesta.sala_descripcion);
                $('#sala_estado').val(respuesta.sala_estado);
                
                $('#modalFormSalaLabel').text('Editar Sala');
                $('#modalFormSala').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información de la sala'
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
 * Guardar sala (crear o editar)
 */
function guardarSala() {
    // Validar formulario
    if(!validarFormulario()) {
        return;
    }
    
    let formData = new FormData($('#frmSala')[0]);
    let esEdicion = $('#sala_id').val() !== '';
    
    // Determinar la acción
    if(esEdicion) {
        formData.append('accion', 'editarSala');
    } else {
        formData.append('accion', 'crearSala');
    }
    
    // Mostrar indicador de carga
    $('#btnGuardarSala').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: 'ajax/salas.ajax.php',
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
                    $('#modalFormSala').modal('hide');
                    cargarTablaSalas();
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
            $('#btnGuardarSala').prop('disabled', false).html('Guardar');
        }
    });
}

/**
 * Validar formulario
 */
function validarFormulario() {
    let esValido = true;
    
    // Validar código
    if($('#sala_codigo').val().trim() === '') {
        $('#sala_codigo').addClass('is-invalid');
        esValido = false;
    } else {
        $('#sala_codigo').removeClass('is-invalid');
    }
    
    // Validar nombre
    if($('#sala_nombre').val().trim() === '') {
        $('#sala_nombre').addClass('is-invalid');
        esValido = false;
    } else {
        $('#sala_nombre').removeClass('is-invalid');
    }
    
    return esValido;
}

/**
 * Eliminar (desactivar) sala
 */
function eliminarSala(idSala) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción desactivará la sala',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = 'index.php?ruta=salas&idSala=' + idSala;
        }
    });
}

/**
 * Activar sala
 */
function activarSala(idSala) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'Esta acción activará la sala',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aquí podrías hacer una petición AJAX para activar
            // Por simplicidad, redirigimos con un parámetro
            window.location = 'index.php?ruta=salas&activarSala=' + idSala;
        }
    });
}

/**
 * Filtrar salas por búsqueda
 */
function filtrarSalas() {
    let termino = $('#validarBusquedaSala').val();
    
    if(termino.trim() !== '') {
        $('#tblSalas').DataTable().search(termino).draw();
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
    $('#validarBusquedaSala').val('');
    $('#filtroEstado').val('');
    $('#tblSalas').DataTable().search('').draw();
    cargarTablaSalas();
}

/**
 * Filtrar por estado
 */
function filtrarPorEstado() {
    let estado = $('#filtroEstado').val();
    
    if(estado !== '') {
        // Filtrar usando la columna de estado (columna 4)
        $('#tblSalas').DataTable().column(4).search(estado == 1 ? 'Activo' : 'Inactivo').draw();
    } else {
        $('#tblSalas').DataTable().column(4).search('').draw();
    }
}
