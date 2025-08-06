<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Configuraciones de Formularios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="referenciales">Referenciales</a></li>
                        <li class="breadcrumb-item active">Configuraciones</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Botones de acción -->
            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevaConfiguracion">
                        <i class="fas fa-plus"></i> Nueva Configuración
                    </button>
                    <a href="referenciales" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Referenciales
                    </a>
                </div>
            </div>

            <!-- Tabla de configuraciones -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Configuraciones de Formularios</h3>
                        </div>
                        <div class="card-body">
                            <table id="tablaConfiguraciones" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Formulario</th>
                                        <th>Clave</th>
                                        <th>Valor</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para nueva/editar configuración -->
<div class="modal fade" id="modalNuevaConfiguracion" tabindex="-1" role="dialog" aria-labelledby="modalNuevaConfiguracionLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalNuevaConfiguracionLabel">Nueva Configuración</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="formConfiguracion">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="tipoFormulario">Tipo de Formulario *</label>
                                <select class="form-control select2" id="tipoFormulario" name="tipoFormulario" required>
                                    <option value="">Seleccione un tipo de formulario</option>
                                    <!-- Los tipos se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="clave">Clave de Configuración *</label>
                                <input type="text" class="form-control" id="clave" name="clave" required 
                                       placeholder="nombre_configuracion">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipoValor">Tipo de Valor</label>
                                <select class="form-control" id="tipoValor" name="tipoValor">
                                    <option value="string">Texto</option>
                                    <option value="number">Número</option>
                                    <option value="boolean">Booleano</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="valor">Valor de Configuración *</label>
                                <textarea class="form-control" id="valor" name="valor" rows="4" required 
                                          placeholder="Valor de la configuración"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" 
                                          placeholder="Descripción de qué hace esta configuración"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" checked>
                                    <label class="form-check-label" for="activo">Configuración Activa</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" id="idConfiguracion" name="idConfiguracion">
                    <input type="hidden" name="accion" value="crearConfiguracion">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts específicos para esta página -->
<script>
$(document).ready(function() {
    // Configurar DataTable
    $('#tablaConfiguraciones').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "ajax/referenciales.ajax.php",
            "type": "POST",
            "data": {"accion": "listarConfiguraciones"}
        },
        "columns": [
            {"data": "id"},
            {"data": "tipo_formulario"},
            {"data": "clave"},
            {
                "data": "valor",
                "render": function(data) {
                    if (data.length > 50) {
                        return data.substring(0, 50) + '...';
                    }
                    return data;
                }
            },
            {"data": "descripcion"},
            {
                "data": "activo",
                "render": function(data) {
                    return data == 1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>';
                }
            },
            {
                "data": null,
                "render": function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning editarConfiguracion" data-id="${row.id}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger eliminarConfiguracion" data-id="${row.id}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "responsive": true,
        "autoWidth": false
    });

    // Configurar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Cargar tipos de formularios
    cargarTiposFormularios();

    function cargarTiposFormularios() {
        $.ajax({
            url: 'ajax/referenciales.ajax.php',
            type: 'POST',
            data: {accion: 'listarTiposFormularios'},
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    var select = $('#tipoFormulario');
                    select.empty().append('<option value="">Seleccione un tipo de formulario</option>');
                    
                    result.data.forEach(function(tipo) {
                        select.append('<option value="' + tipo.id + '">' + tipo.nombre + '</option>');
                    });
                }
            }
        });
    }

    // Manejar envío del formulario
    $('#formConfiguracion').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: 'ajax/referenciales.ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: result.message
                    }).then(function() {
                        $('#modalNuevaConfiguracion').modal('hide');
                        $('#tablaConfiguraciones').DataTable().ajax.reload();
                        $('#formConfiguracion')[0].reset();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de comunicación con el servidor'
                });
            }
        });
    });

    // Editar configuración
    $(document).on('click', '.editarConfiguracion', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: 'ajax/referenciales.ajax.php',
            type: 'POST',
            data: {
                accion: 'obtenerConfiguracion',
                id: id
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    var config = result.data;
                    
                    $('#idConfiguracion').val(config.id);
                    $('#tipoFormulario').val(config.tipo_formulario_id).trigger('change');
                    $('#clave').val(config.clave);
                    $('#valor').val(config.valor);
                    $('#descripcion').val(config.descripcion);
                    $('#activo').prop('checked', config.activo == 1);
                    
                    $('#modalNuevaConfiguracionLabel').text('Editar Configuración');
                    $('input[name="accion"]').val('editarConfiguracion');
                    $('#modalNuevaConfiguracion').modal('show');
                }
            }
        });
    });

    // Eliminar configuración
    $(document).on('click', '.eliminarConfiguracion', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'ajax/referenciales.ajax.php',
                    type: 'POST',
                    data: {
                        accion: 'eliminarConfiguracion',
                        id: id
                    },
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: result.message
                            }).then(function() {
                                $('#tablaConfiguraciones').DataTable().ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.message
                            });
                        }
                    }
                });
            }
        });
    });

    // Reset modal when hidden
    $('#modalNuevaConfiguracion').on('hidden.bs.modal', function() {
        $('#formConfiguracion')[0].reset();
        $('#idConfiguracion').val('');
        $('#modalNuevaConfiguracionLabel').text('Nueva Configuración');
        $('input[name="accion"]').val('crearConfiguracion');
        $('.select2').val(null).trigger('change');
    });
});
</script>
