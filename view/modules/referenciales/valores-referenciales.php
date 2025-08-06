<?php
// Incluir los controladores necesarios
require_once "controller/referenciales.controller.php";
require_once "model/referenciales.model.php";

// Procesar acciones
ControllerReferenciales::ctrCrearValorReferencial();
ControllerReferenciales::ctrEditarRegistro();
ControllerReferenciales::ctrEliminarRegistro();

// Obtener datos
$referenciales = ControllerReferenciales::ctrObtenerReferenciales();
$referencialSeleccionado = isset($_GET['ref_id']) ? $_GET['ref_id'] : null;
$valores = ModelReferenciales::mdlObtenerValoresReferencial($referencialSeleccionado);
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>🏷️ Gestión de Valores de Referenciales</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?ruta=referenciales">Referenciales</a></li>
                        <li class="breadcrumb-item active">Valores</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Selector de referencial -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Seleccionar Referencial</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="selectorReferencial">Referencial:</label>
                                <select class="form-control select2" id="selectorReferencial" onchange="cambiarReferencial(this.value)">
                                    <option value="">Seleccione un referencial</option>
                                    <?php
                                    if (!empty($referenciales)) {
                                        foreach ($referenciales as $ref) {
                                            $selected = ($referencialSeleccionado == $ref['id']) ? 'selected' : '';
                                            echo '<option value="'.$ref['id'].'" '.$selected.'>'.$ref['nombre'].' ('.$ref['categoria'].')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($referencialSeleccionado): ?>
            <!-- Información del referencial seleccionado -->
            <?php 
            $referencialInfo = null;
            foreach ($referenciales as $ref) {
                if ($ref['id'] == $referencialSeleccionado) {
                    $referencialInfo = $ref;
                    break;
                }
            }
            ?>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Referencial: <?php echo $referencialInfo['nombre']; ?></h5>
                        <strong>Código:</strong> <?php echo $referencialInfo['codigo']; ?><br>
                        <strong>Categoría:</strong> <?php echo $referencialInfo['categoria']; ?><br>
                        <strong>Descripción:</strong> <?php echo $referencialInfo['descripcion']; ?>
                    </div>
                </div>
            </div>

            <!-- Tabla de valores -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Valores del Referencial</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCrearValor">
                                    <i class="fas fa-plus"></i> Nuevo Valor
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tablaValores" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Valor</th>
                                        <th>Etiqueta</th>
                                        <th>Valor Numérico</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($valores)) {
                                        foreach ($valores as $key => $value) {
                                            echo '<tr>
                                                <td><span class="badge badge-secondary">'.$value["orden_visualizacion"].'</span></td>
                                                <td><code>'.$value["valor"].'</code></td>
                                                <td><strong>'.$value["etiqueta"].'</strong></td>
                                                <td>'.($value["valor_numerico"] ? $value["valor_numerico"] : '-').'</td>
                                                <td>'.(isset($value["descripcion"]) && $value["descripcion"] ? substr($value["descripcion"], 0, 50).'...' : '-').'</td>
                                                <td>';
                                            
                                            if ($value["activo"] == 1) {
                                                echo '<span class="badge badge-success">Activo</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Inactivo</span>';
                                            }
                                            
                                            echo '</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditarValor" 
                                                                onclick="editarValor('.$value["id"].', \''.$value["valor"].'\', \''.$value["etiqueta"].'\', \''.$value["valor_numerico"].'\', '.$value["orden_visualizacion"].', \''.$value["descripcion"].'\', '.$value["activo"].')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="index.php?ruta=valores-referenciales&ref_id='.$referencialSeleccionado.'&idEliminar='.$value["id"].'&tabla=referencial_valores&campo_id=id" 
                                                           class="btn btn-danger btn-sm" 
                                                           onclick="return confirm(\'¿Está seguro de eliminar este valor?\')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
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
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Modal Crear Valor -->
<div class="modal fade" id="modalCrearValor" tabindex="-1" role="dialog" aria-labelledby="modalCrearValorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearValorLabel">Crear Nuevo Valor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="referencial_id" value="<?php echo $referencialSeleccionado; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valor">Valor *</label>
                                <input type="text" class="form-control" id="valor" name="valor" required>
                                <small class="form-text text-muted">Valor que se guardará en la base de datos</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="etiqueta">Etiqueta *</label>
                                <input type="text" class="form-control" id="etiqueta" name="etiqueta" required>
                                <small class="form-text text-muted">Texto que verá el usuario</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="valor_numerico">Valor Numérico</label>
                                <input type="number" step="0.0001" class="form-control" id="valor_numerico" name="valor_numerico">
                                <small class="form-text text-muted">Para valores que requieren cálculos (ej: dioptrías)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="orden_visualizacion">Orden</label>
                                <input type="number" class="form-control" id="orden_visualizacion" name="orden_visualizacion" value="1">
                                <small class="form-text text-muted">Orden de aparición en listas</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activo">Estado</label>
                                <select class="form-control" id="activo" name="activo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        <small class="form-text text-muted">Descripción adicional del valor</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Valor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Valor -->
<div class="modal fade" id="modalEditarValor" tabindex="-1" role="dialog" aria-labelledby="modalEditarValorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarValorLabel">Editar Valor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idEditarValor" name="idEditar">
                    <input type="hidden" name="tabla" value="referencial_valores">
                    <input type="hidden" name="campo_id" value="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarValor">Valor *</label>
                                <input type="text" class="form-control" id="editarValor" name="editarValor" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarEtiqueta">Etiqueta *</label>
                                <input type="text" class="form-control" id="editarEtiqueta" name="editarEtiqueta" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarValorNumerico">Valor Numérico</label>
                                <input type="number" step="0.0001" class="form-control" id="editarValorNumerico" name="editarValorNumerico">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarOrden">Orden</label>
                                <input type="number" class="form-control" id="editarOrden" name="editarOrden">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarActivoValor">Estado</label>
                                <select class="form-control" id="editarActivoValor" name="editarActivo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editarDescripcionValor">Descripción</label>
                        <textarea class="form-control" id="editarDescripcionValor" name="editarDescripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Valor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $("#tablaValores").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "order": [[ 0, "asc" ]], // Ordenar por columna de orden
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#tablaValores_wrapper .col-md-6:eq(0)');
    
    // Inicializar Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Sincronizar valor y etiqueta si son iguales
    $('#valor').on('input', function() {
        var valor = $(this).val();
        if ($('#etiqueta').val() === '') {
            $('#etiqueta').val(valor);
        }
    });
});

function cambiarReferencial(referencialId) {
    if (referencialId) {
        window.location.href = 'index.php?ruta=valores-referenciales&ref_id=' + referencialId;
    } else {
        window.location.href = 'index.php?ruta=valores-referenciales';
    }
}

function editarValor(id, valor, etiqueta, valorNumerico, orden, descripcion, activo) {
    $("#idEditarValor").val(id);
    $("#editarValor").val(valor);
    $("#editarEtiqueta").val(etiqueta);
    $("#editarValorNumerico").val(valorNumerico);
    $("#editarOrden").val(orden);
    $("#editarDescripcionValor").val(descripcion);
    $("#editarActivoValor").val(activo);
}
</script>
