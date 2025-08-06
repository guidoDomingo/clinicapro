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
                    <h1>Campos de Formularios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="referenciales">Referenciales</a></li>
                        <li class="breadcrumb-item active">Campos de Formularios</li>
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
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevoCampo">
                        <i class="fas fa-plus"></i> Nuevo Campo
                    </button>
                    <a href="referenciales" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Referenciales
                    </a>
                </div>
            </div>

            <!-- Tabla de campos de formularios -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Campos de Formularios</h3>
                        </div>
                        <div class="card-body">
                            <table id="tablaCamposFormularios" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Formulario</th>
                                        <th>Nombre Campo</th>
                                        <th>Etiqueta</th>
                                        <th>Tipo Campo</th>
                                        <th>Orden</th>
                                        <th>Requerido</th>
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

<!-- Modal para nuevo/editar campo -->
<div class="modal fade" id="modalNuevoCampo" tabindex="-1" role="dialog" aria-labelledby="modalNuevoCampoLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalNuevoCampoLabel">Nuevo Campo de Formulario</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="formCampoFormulario">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipoFormulario">Tipo de Formulario *</label>
                                <select class="form-control select2" id="tipoFormulario" name="tipoFormulario" required>
                                    <option value="">Seleccione un tipo de formulario</option>
                                    <?php
                                    $tiposFormularios = ControllerReferenciales::ctrMostrarTiposFormularios();
                                    if ($tiposFormularios) {
                                        foreach ($tiposFormularios as $tipo) {
                                            echo '<option value="' . $tipo["id"] . '">' . $tipo["nombre"] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipoCampo">Tipo de Campo *</label>
                                <select class="form-control select2" id="tipoCampo" name="tipoCampo" required>
                                    <option value="">Seleccione un tipo de campo</option>
                                    <?php
                                    $tiposCampos = ControllerReferenciales::ctrMostrarTiposCampos();
                                    if ($tiposCampos) {
                                        foreach ($tiposCampos as $tipo) {
                                            echo '<option value="' . $tipo["id"] . '" data-requiere-opciones="' . ($tipo["requiere_opciones"] ? '1' : '0') . '">' . $tipo["nombre"] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreCampo">Nombre del Campo *</label>
                                <input type="text" class="form-control" id="nombreCampo" name="nombreCampo" required 
                                       placeholder="nombre_del_campo (sin espacios ni caracteres especiales)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etiquetaCampo">Etiqueta del Campo *</label>
                                <input type="text" class="form-control" id="etiquetaCampo" name="etiquetaCampo" required 
                                       placeholder="Etiqueta que verá el usuario">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="placeholder">Placeholder</label>
                                <input type="text" class="form-control" id="placeholder" name="placeholder" 
                                       placeholder="Texto de ejemplo en el campo">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ordenVisualizacion">Orden de Visualización</label>
                                <input type="number" class="form-control" id="ordenVisualizacion" name="ordenVisualizacion" 
                                       value="1" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="grupoSeccion">Grupo/Sección</label>
                                <input type="text" class="form-control" id="grupoSeccion" name="grupoSeccion" 
                                       placeholder="Para agrupar campos">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="descripcionAyuda">Descripción de Ayuda</label>
                                <textarea class="form-control" id="descripcionAyuda" name="descripcionAyuda" rows="2" 
                                          placeholder="Texto de ayuda para el usuario"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="requerido" name="requerido" value="1">
                                    <label class="form-check-label" for="requerido">Campo Requerido</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" checked>
                                    <label class="form-check-label" for="activo">Campo Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="validaciones">Validaciones (JSON)</label>
                                <textarea class="form-control" id="validaciones" name="validaciones" rows="3" 
                                          placeholder='{"required": true, "minLength": 3, "maxLength": 50}'></textarea>
                                <small class="form-text text-muted">Formato JSON para reglas de validación</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="atributosHtml">Atributos HTML (JSON)</label>
                                <textarea class="form-control" id="atributosHtml" name="atributosHtml" rows="3" 
                                          placeholder='{"class": "form-control", "data-toggle": "tooltip"}'></textarea>
                                <small class="form-text text-muted">Atributos adicionales para el elemento HTML</small>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" id="idCampo" name="idCampo">
                    <input type="hidden" name="accion" value="crearCampoFormulario">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Campo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts específicos para esta página -->
<script>
$(document).ready(function() {
    // Configurar DataTable
    $('#tablaCamposFormularios').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "ajax/referenciales.ajax.php",
            "type": "POST",
            "data": {"accion": "listarCamposFormularios"}
        },
        "columns": [
            {"data": "id"},
            {"data": "tipo_formulario"},
            {"data": "nombre_campo"},
            {"data": "etiqueta"},
            {"data": "tipo_campo"},
            {"data": "orden_visualizacion"},
            {
                "data": "requerido",
                "render": function(data) {
                    return data == 1 ? '<span class="badge badge-warning">Sí</span>' : '<span class="badge badge-secondary">No</span>';
                }
            },
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
                        <button class="btn btn-sm btn-warning editarCampo" data-id="${row.id}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger eliminarCampo" data-id="${row.id}" title="Eliminar">
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

    // Manejar envío del formulario
    $('#formCampoFormulario').on('submit', function(e) {
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
                        $('#modalNuevoCampo').modal('hide');
                        $('#tablaCamposFormularios').DataTable().ajax.reload();
                        $('#formCampoFormulario')[0].reset();
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

    // Editar campo
    $(document).on('click', '.editarCampo', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: 'ajax/referenciales.ajax.php',
            type: 'POST',
            data: {
                accion: 'obtenerCampoFormulario',
                id: id
            },
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    var campo = result.data;
                    
                    $('#idCampo').val(campo.id);
                    $('#tipoFormulario').val(campo.tipo_formulario_id).trigger('change');
                    $('#tipoCampo').val(campo.tipo_campo_id).trigger('change');
                    $('#nombreCampo').val(campo.nombre_campo);
                    $('#etiquetaCampo').val(campo.etiqueta);
                    $('#placeholder').val(campo.placeholder);
                    $('#ordenVisualizacion').val(campo.orden_visualizacion);
                    $('#grupoSeccion').val(campo.grupo_seccion);
                    $('#descripcionAyuda').val(campo.descripcion_ayuda);
                    $('#requerido').prop('checked', campo.requerido == 1);
                    $('#activo').prop('checked', campo.activo == 1);
                    $('#validaciones').val(campo.validaciones);
                    $('#atributosHtml').val(campo.atributos_html);
                    
                    $('#modalNuevoCampoLabel').text('Editar Campo de Formulario');
                    $('input[name="accion"]').val('editarCampoFormulario');
                    $('#modalNuevoCampo').modal('show');
                }
            }
        });
    });

    // Eliminar campo
    $(document).on('click', '.eliminarCampo', function() {
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
                        accion: 'eliminarCampoFormulario',
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
                                $('#tablaCamposFormularios').DataTable().ajax.reload();
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
    $('#modalNuevoCampo').on('hidden.bs.modal', function() {
        $('#formCampoFormulario')[0].reset();
        $('#idCampo').val('');
        $('#modalNuevoCampoLabel').text('Nuevo Campo de Formulario');
        $('input[name="accion"]').val('crearCampoFormulario');
        $('.select2').val(null).trigger('change');
    });
});
</script>
