<?php
// Módulo de turnos temporal sin verificación de permisos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test Turnos</title>
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="view/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="view/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="view/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="view/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    
    <!-- jQuery -->
    <script src="view/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="view/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="view/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="view/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="view/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="view/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="hold-transition layout-top-nav">

<div class="wrapper">
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Administrar Turnos (TEST)</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active">Turnos</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info" id="btnAgregarTurno">
                                    <i class="fas fa-plus"></i> Crear
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="validarBusquedaTurno" placeholder="Buscar por nombre o descripción">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" id="btnFiltrarTurnos">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                        <button class="btn btn-secondary" type="button" id="btnLimpiarTurnos">
                                            <i class="fas fa-eraser"></i> Limpiar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="tblTurnos">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th style="width: 120px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar turnos -->
<div class="modal fade" id="modalFormTurno" tabindex="-1" role="dialog" aria-labelledby="modalFormTurnoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormTurnoLabel">Agregar Turno</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="frmTurno">
                <div class="modal-body">
                    <input type="hidden" id="turno_id" name="turno_id">
                    
                    <div class="form-group">
                        <label for="turno_nombre">Nombre del Turno *</label>
                        <input type="text" class="form-control" id="turno_nombre" name="turno_nombre" 
                               placeholder="Ej: Mañana, Tarde, Noche" maxlength="50" required>
                        <div class="invalid-feedback">
                            El nombre es obligatorio y no puede exceder 50 caracteres
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="turno_descripcion">Descripción</label>
                        <textarea class="form-control" id="turno_descripcion" name="turno_descripcion" 
                                  rows="3" maxlength="255" placeholder="Descripción opcional del turno"></textarea>
                        <div class="invalid-feedback">
                            La descripción no puede exceder 255 caracteres
                        </div>
                        <small class="form-text text-muted">Opcional. Máximo 255 caracteres.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="turno_estado">Estado</label>
                        <select class="form-control" id="turno_estado" name="turno_estado">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarTurno">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log("Iniciando módulo de turnos...");
    
    // Cargar DataTable
    cargarTablaTurnos();
    
    // Configurar eventos
    configurarEventos();
});

function cargarTablaTurnos() {
    console.log("Cargando tabla de turnos...");
    
    if ($.fn.DataTable.isDataTable('#tblTurnos')) {
        $('#tblTurnos').DataTable().destroy();
    }
    
    $('#tblTurnos').DataTable({
        "processing": true,
        "ajax": {
            "url": "ajax/turnos.ajax.php",
            "type": "POST",
            "data": function(d) {
                console.log("Enviando request:", { "accion": "obtenerTurnos" });
                return { "accion": "obtenerTurnos" };
            },
            "dataType": "json",
            "dataSrc": function(json) {
                console.log("Respuesta recibida:", json);
                return json || [];
            },
            "error": function(xhr, error, thrown) {
                console.error("Error AJAX:", error);
                console.error("Status:", xhr.status);
                console.error("Response:", xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error al cargar datos',
                    text: 'Error: ' + error + ' - Status: ' + xhr.status,
                    footer: '<pre>' + xhr.responseText + '</pre>'
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
                    if(data == 1 || data === true || data === 't') {
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
                    return '<button class="btn btn-warning btn-sm btnEditarTurno" data-id="' + row.turno_id + '"><i class="fas fa-edit"></i></button> ' +
                           '<button class="btn btn-danger btn-sm btnEliminarTurno" data-id="' + row.turno_id + '"><i class="fas fa-trash"></i></button>';
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

function configurarEventos() {
    // Botón agregar
    $(document).on('click', '#btnAgregarTurno', function() {
        $('#frmTurno')[0].reset();
        $('#turno_id').val('');
        $('#modalFormTurnoLabel').text('Agregar Turno');
        $('#modalFormTurno').modal('show');
    });
    
    // Submit formulario
    $('#frmTurno').on('submit', function(e) {
        e.preventDefault();
        guardarTurno();
    });
}

function guardarTurno() {
    console.log("Guardando turno...");
    
    let formData = new FormData($('#frmTurno')[0]);
    let esEdicion = $('#turno_id').val() !== '';
    
    formData.append('accion', esEdicion ? 'editarTurno' : 'crearTurno');
    
    $('#btnGuardarTurno').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
    
    $.ajax({
        url: 'ajax/turnos.ajax.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(respuesta) {
            console.log('Respuesta:', respuesta);
            
            if(respuesta.status === 'success') {
                Swal.fire({
                    icon: "success",
                    title: respuesta.message,
                    timer: 1500
                }).then(function(){
                    $('#modalFormTurno').modal('hide');
                    cargarTablaTurnos();
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: respuesta.message
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error: ' + error,
                footer: '<pre>' + xhr.responseText + '</pre>'
            });
        },
        complete: function() {
            $('#btnGuardarTurno').prop('disabled', false).html('Guardar');
        }
    });
}
</script>

</body>
</html>
