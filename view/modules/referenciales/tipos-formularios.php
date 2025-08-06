<?php
// Incluir los controladores necesarios
require_once "controller/referenciales.controller.php";
require_once "model/referenciales.model.php";

// Procesar acciones
ControllerReferenciales::ctrCrearTipoFormulario();
ControllerReferenciales::ctrEditarRegistro();
ControllerReferenciales::ctrEliminarRegistro();

// Obtener datos
$tiposFormularios = ControllerReferenciales::ctrObtenerTiposFormularios();
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>📝 Gestión de Tipos de Formularios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="#">Referenciales</a></li>
                        <li class="breadcrumb-item active">Tipos de Formularios</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Información sobre tipos de formularios -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> ¿Qué son los Tipos de Formularios?</h5>
                        Los tipos de formularios definen las diferentes categorías de consultas médicas disponibles en el sistema.
                        Cada tipo puede tener campos específicos y configuraciones particulares. Ejemplos: General, Anteojos, 
                        Estudios Médicos, Cardiología, etc.
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo count($tiposFormularios); ?></h3>
                            <p>Tipos de Formularios</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo count(array_filter($tiposFormularios, function($t) { return $t['activo'] == 1; })); ?></h3>
                            <p>Activos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo count(array_filter($tiposFormularios, function($t) { return $t['activo'] == 0; })); ?></h3>
                            <p>Inactivos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>0</h3>
                            <p>Con Errores</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de tipos de formularios -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Tipos de Formularios</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCrearTipoFormulario">
                                    <i class="fas fa-plus"></i> Nuevo Tipo de Formulario
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tablaTiposFormularios" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($tiposFormularios)) {
                                        foreach ($tiposFormularios as $key => $value) {
                                            echo '<tr>
                                                <td>'.$value["id"].'</td>
                                                <td><strong>'.$value["nombre"].'</strong></td>
                                                <td><code>'.$value["codigo"].'</code></td>
                                                <td>'.substr($value["descripcion"], 0, 80).'...</td>
                                                <td>';
                                            
                                            if ($value["activo"] == 1) {
                                                echo '<span class="badge badge-success">Activo</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">Inactivo</span>';
                                            }
                                            
                                            echo '</td>
                                                <td>'.date('d/m/Y', strtotime($value["fecha_creacion"])).'</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditarTipoFormulario" 
                                                                onclick="editarTipoFormulario('.$value["id"].', \''.$value["nombre"].'\', \''.$value["codigo"].'\', \''.$value["descripcion"].'\', '.$value["activo"].')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="index.php?ruta=campos-formularios&form_id='.$value["id"].'" class="btn btn-info btn-sm" title="Ver campos">
                                                            <i class="fas fa-puzzle-piece"></i>
                                                        </a>
                                                        <a href="index.php?ruta=configuraciones-formularios&form_id='.$value["id"].'" class="btn btn-secondary btn-sm" title="Configuraciones">
                                                            <i class="fas fa-cog"></i>
                                                        </a>
                                                        <a href="index.php?ruta=tipos-formularios&idEliminar='.$value["id"].'&tabla=tipos_formularios&campo_id=id" 
                                                           class="btn btn-danger btn-sm" 
                                                           onclick="return confirm(\'¿Está seguro de eliminar este tipo de formulario?\')">
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
        </div>
    </section>
</div>

<!-- Modal Crear Tipo de Formulario -->
<div class="modal fade" id="modalCrearTipoFormulario" tabindex="-1" role="dialog" aria-labelledby="modalCrearTipoFormularioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearTipoFormularioLabel">Crear Nuevo Tipo de Formulario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                                <small class="form-text text-muted">Nombre descriptivo del tipo de formulario</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código *</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required>
                                <small class="form-text text-muted">Código único para identificar el formulario</small>
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
                        <small class="form-text text-muted">Descripción detallada del tipo de formulario y su uso</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="icon fas fa-exclamation-triangle"></i> Importante:</h6>
                        <ul class="mb-0">
                            <li>El código debe ser único y no contener espacios ni caracteres especiales</li>
                            <li>Una vez creado, se recomienda no cambiar el código para evitar problemas</li>
                            <li>Después de crear el tipo, podrá agregar campos específicos desde "Campos de Formularios"</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Tipo de Formulario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Tipo de Formulario -->
<div class="modal fade" id="modalEditarTipoFormulario" tabindex="-1" role="dialog" aria-labelledby="modalEditarTipoFormularioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarTipoFormularioLabel">Editar Tipo de Formulario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idEditar" name="idEditar">
                    <input type="hidden" name="tabla" value="tipos_formularios">
                    <input type="hidden" name="campo_id" value="id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarNombre">Nombre *</label>
                                <input type="text" class="form-control" id="editarNombre" name="editarNombre" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarCodigo">Código *</label>
                                <input type="text" class="form-control" id="editarCodigo" name="editarCodigo" required>
                                <small class="form-text text-muted">⚠️ Cambiar el código puede afectar formularios existentes</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarActivo">Estado</label>
                                <select class="form-control" id="editarActivo" name="editarActivo">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editarDescripcion">Descripción</label>
                        <textarea class="form-control" id="editarDescripcion" name="editarDescripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Tipo de Formulario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $("#tablaTiposFormularios").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#tablaTiposFormularios_wrapper .col-md-6:eq(0)');
    
    // Generar código automáticamente basado en el nombre
    $('#nombre').on('input', function() {
        var nombre = $(this).val();
        var codigo = nombre.toLowerCase()
                          .replace(/[áàäâ]/g, 'a')
                          .replace(/[éèëê]/g, 'e')
                          .replace(/[íìïî]/g, 'i')
                          .replace(/[óòöô]/g, 'o')
                          .replace(/[úùüû]/g, 'u')
                          .replace(/[ñ]/g, 'n')
                          .replace(/[^a-z0-9]/g, '_')
                          .replace(/_+/g, '_')
                          .replace(/^_|_$/g, '');
        $('#codigo').val(codigo);
    });
});

function editarTipoFormulario(id, nombre, codigo, descripcion, activo) {
    $("#idEditar").val(id);
    $("#editarNombre").val(nombre);
    $("#editarCodigo").val(codigo);
    $("#editarDescripcion").val(descripcion);
    $("#editarActivo").val(activo);
}
</script>
