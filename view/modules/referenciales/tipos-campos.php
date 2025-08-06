<?php
// Incluir los controladores necesarios
require_once "controller/referenciales.controller.php";
require_once "model/referenciales.model.php";

// Procesar acciones
ControllerReferenciales::ctrEditarRegistro();
ControllerReferenciales::ctrEliminarRegistro();

// Obtener datos
$tiposCampos = ControllerReferenciales::ctrObtenerTiposCampos();
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>⚙️ Gestión de Tipos de Campos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="#">Referenciales</a></li>
                        <li class="breadcrumb-item active">Tipos de Campos</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Información sobre tipos de campos -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> ¿Qué son los Tipos de Campos?</h5>
                        Los tipos de campos definen los diferentes elementos HTML que pueden ser utilizados en los formularios
                        dinámicos. Cada tipo especifica el comportamiento, validaciones y opciones disponibles para los campos.
                        <br><strong>Ejemplos:</strong> Text, Select, Textarea, Number, Date, Checkbox, etc.
                    </div>
                </div>
            </div>

            <!-- Tabla de tipos de campos -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Tipos de Campos Disponibles</h3>
                        </div>
                        <div class="card-body">
                            <table id="tablaTiposCampos" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Código</th>
                                        <th>HTML Input Type</th>
                                        <th>Requiere Opciones</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($tiposCampos)) {
                                        foreach ($tiposCampos as $key => $value) {
                                            echo '<tr>
                                                <td>'.$value["id"].'</td>
                                                <td><strong>'.$value["nombre"].'</strong></td>
                                                <td><code>'.$value["codigo"].'</code></td>
                                                <td><span class="badge badge-primary">'.$value["html_input_type"].'</span></td>
                                                <td>';
                                            
                                            if ($value["requiere_opciones"]) {
                                                echo '<span class="badge badge-warning">Sí</span>';
                                            } else {
                                                echo '<span class="badge badge-secondary">No</span>';
                                            }
                                            
                                            echo '</td>
                                                <td>'.substr($value["descripcion"], 0, 80).'...</td>
                                                <td>';
                                            
                                            if ($value["activo"] == 1) {
                                                echo '<span class="badge badge-success">Activo</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Inactivo</span>';
                                            }
                                            
                                            echo '</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalVerDetalle" 
                                                                onclick="verDetalleTipoCampo(\''.$value["nombre"].'\', \''.$value["codigo"].'\', \''.$value["html_input_type"].'\', \''.$value["descripcion"].'\', '.($value["requiere_opciones"] ? 'true' : 'false').')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditarTipoCampo" 
                                                                onclick="editarTipoCampo('.$value["id"].', \''.$value["nombre"].'\', \''.$value["codigo"].'\', \''.$value["html_input_type"].'\', \''.$value["descripcion"].'\', '.($value["requiere_opciones"] ? 'true' : 'false').', '.$value["activo"].')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">📋 Tipos de Campos Disponibles</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Campos de Texto:</h6>
                                    <ul>
                                        <li><code>text</code> - Texto simple de una línea</li>
                                        <li><code>textarea</code> - Texto multilínea</li>
                                        <li><code>email</code> - Correo electrónico</li>
                                        <li><code>tel</code> - Número de teléfono</li>
                                        <li><code>summernote</code> - Editor de texto enriquecido</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Campos Numéricos y Fechas:</h6>
                                    <ul>
                                        <li><code>number</code> - Número</li>
                                        <li><code>range</code> - Rango numérico</li>
                                        <li><code>date</code> - Fecha</li>
                                        <li><code>color</code> - Selector de color</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Campos de Selección:</h6>
                                    <ul>
                                        <li><code>select</code> - Lista desplegable</li>
                                        <li><code>select2</code> - Lista con búsqueda</li>
                                        <li><code>radio</code> - Botones de opción</li>
                                        <li><code>checkbox</code> - Casillas de verificación</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>Campos Especiales:</h6>
                                    <ul>
                                        <li><code>file</code> - Subida de archivos</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Ver Detalle -->
<div class="modal fade" id="modalVerDetalle" tabindex="-1" role="dialog" aria-labelledby="modalVerDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVerDetalleLabel">Detalle del Tipo de Campo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="200">Nombre:</th>
                        <td id="detalleNombre"></td>
                    </tr>
                    <tr>
                        <th>Código:</th>
                        <td id="detalleCodigo"></td>
                    </tr>
                    <tr>
                        <th>HTML Input Type:</th>
                        <td id="detalleHtmlType"></td>
                    </tr>
                    <tr>
                        <th>Requiere Opciones:</th>
                        <td id="detalleRequiereOpciones"></td>
                    </tr>
                    <tr>
                        <th>Descripción:</th>
                        <td id="detalleDescripcion"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Tipo de Campo -->
<div class="modal fade" id="modalEditarTipoCampo" tabindex="-1" role="dialog" aria-labelledby="modalEditarTipoCampoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarTipoCampoLabel">Editar Tipo de Campo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idEditarTipoCampo" name="idEditar">
                    <input type="hidden" name="tabla" value="tipos_campos">
                    <input type="hidden" name="campo_id" value="id">
                    
                    <div class="alert alert-warning">
                        <strong>Nota:</strong> Solo se permite editar la descripción y el estado. 
                        Cambiar otros campos podría afectar el funcionamiento del sistema.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarNombreTipoCampo">Nombre</label>
                                <input type="text" class="form-control" id="editarNombreTipoCampo" name="editarNombre" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarCodigoTipoCampo">Código</label>
                                <input type="text" class="form-control" id="editarCodigoTipoCampo" name="editarCodigo" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarHtmlTypeTipoCampo">HTML Input Type</label>
                                <input type="text" class="form-control" id="editarHtmlTypeTipoCampo" name="editarHtmlInputType" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarActivoTipoCampo">Estado</label>
                                <select class="form-control" id="editarActivoTipoCampo" name="editarActivo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editarDescripcionTipoCampo">Descripción</label>
                        <textarea class="form-control" id="editarDescripcionTipoCampo" name="editarDescripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $("#tablaTiposCampos").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#tablaTiposCampos_wrapper .col-md-6:eq(0)');
});

function verDetalleTipoCampo(nombre, codigo, htmlType, descripcion, requiereOpciones) {
    $("#detalleNombre").text(nombre);
    $("#detalleCodigo").html('<code>' + codigo + '</code>');
    $("#detalleHtmlType").html('<span class="badge badge-primary">' + htmlType + '</span>');
    $("#detalleRequiereOpciones").html(requiereOpciones ? '<span class="badge badge-warning">Sí</span>' : '<span class="badge badge-secondary">No</span>');
    $("#detalleDescripcion").text(descripcion);
}

function editarTipoCampo(id, nombre, codigo, htmlType, descripcion, requiereOpciones, activo) {
    $("#idEditarTipoCampo").val(id);
    $("#editarNombreTipoCampo").val(nombre);
    $("#editarCodigoTipoCampo").val(codigo);
    $("#editarHtmlTypeTipoCampo").val(htmlType);
    $("#editarDescripcionTipoCampo").val(descripcion);
    $("#editarActivoTipoCampo").val(activo);
}
</script>
