<?php
// Incluir los controladores necesarios
require_once "controller/referenciales.controller.php";
require_once "model/referenciales.model.php";

// Procesar acciones
ControllerReferenciales::ctrCrearReferencial();
ControllerReferenciales::ctrEditarRegistro();
ControllerReferenciales::ctrEliminarRegistro();

// Obtener datos
$referenciales = ControllerReferenciales::ctrObtenerReferenciales();
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>📋 Gestión de Referenciales Dinámicos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="#">Referenciales</a></li>
                        <li class="breadcrumb-item active">Referenciales</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Tarjeta de información -->
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> ¿Qué son los Referenciales Dinámicos?</h5>
                        Los referenciales son catálogos de datos que alimentan las listas desplegables y opciones 
                        de los formularios de consultas médicas. Desde aquí puedes crear y gestionar valores como 
                        dioptrías para anteojos, equipos médicos, especialidades, etc.
                    </div>
                </div>
            </div>

            <!-- Botón para crear nuevo referencial -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Referenciales</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCrearReferencial">
                                    <i class="fas fa-plus"></i> Nuevo Referencial
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="tablaReferenciales" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Código</th>
                                        <th>Categoría</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($referenciales)) {
                                        foreach ($referenciales as $key => $value) {
                                            echo '<tr>
                                                <td>'.$value["id"].'</td>
                                                <td><strong>'.$value["nombre"].'</strong></td>
                                                <td><code>'.$value["codigo"].'</code></td>
                                                <td><span class="badge badge-info">'.$value["categoria"].'</span></td>
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
                                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditarReferencial" 
                                                                onclick="editarReferencial('.$value["id"].', \''.$value["nombre"].'\', \''.$value["codigo"].'\', \''.$value["categoria"].'\', \''.$value["descripcion"].'\', '.$value["activo"].')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="index.php?ruta=valores-referenciales&ref_id='.$value["id"].'" class="btn btn-info btn-sm" title="Ver valores">
                                                            <i class="fas fa-list"></i>
                                                        </a>
                                                        <a href="index.php?ruta=referenciales&idEliminar='.$value["id"].'&tabla=referenciales&campo_id=id" 
                                                           class="btn btn-danger btn-sm" 
                                                           onclick="return confirm(\'¿Está seguro de eliminar este referencial?\')">
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

<!-- Modal Crear Referencial -->
<div class="modal fade" id="modalCrearReferencial" tabindex="-1" role="dialog" aria-labelledby="modalCrearReferencialLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearReferencialLabel">Crear Nuevo Referencial</h5>
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
                                <small class="form-text text-muted">Nombre descriptivo del referencial</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="codigo">Código *</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required>
                                <small class="form-text text-muted">Código único para identificar el referencial</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoria">Categoría</label>
                                <select class="form-control" id="categoria" name="categoria">
                                    <option value="general">General</option>
                                    <option value="oftalmologia">Oftalmología</option>
                                    <option value="cardiologia">Cardiología</option>
                                    <option value="neurologia">Neurología</option>
                                    <option value="dermatologia">Dermatología</option>
                                    <option value="estudios_medicos">Estudios Médicos</option>
                                    <option value="equipos">Equipos</option>
                                    <option value="medicamentos">Medicamentos</option>
                                </select>
                            </div>
                        </div>
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
                        <small class="form-text text-muted">Descripción detallada del referencial y su uso</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Referencial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Referencial -->
<div class="modal fade" id="modalEditarReferencial" tabindex="-1" role="dialog" aria-labelledby="modalEditarReferencialLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarReferencialLabel">Editar Referencial</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idEditar" name="idEditar">
                    <input type="hidden" name="tabla" value="referenciales">
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
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editarCategoria">Categoría</label>
                                <select class="form-control" id="editarCategoria" name="editarCategoria">
                                    <option value="general">General</option>
                                    <option value="oftalmologia">Oftalmología</option>
                                    <option value="cardiologia">Cardiología</option>
                                    <option value="neurologia">Neurología</option>
                                    <option value="dermatologia">Dermatología</option>
                                    <option value="estudios_medicos">Estudios Médicos</option>
                                    <option value="equipos">Equipos</option>
                                    <option value="medicamentos">Medicamentos</option>
                                </select>
                            </div>
                        </div>
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
                    <button type="submit" class="btn btn-warning">Actualizar Referencial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $("#tablaReferenciales").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "buttons": ["copy", "csv", "excel", "pdf", "print"]
    }).buttons().container().appendTo('#tablaReferenciales_wrapper .col-md-6:eq(0)');
    
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

function editarReferencial(id, nombre, codigo, categoria, descripcion, activo) {
    $("#idEditar").val(id);
    $("#editarNombre").val(nombre);
    $("#editarCodigo").val(codigo);
    $("#editarCategoria").val(categoria);
    $("#editarDescripcion").val(descripcion);
    $("#editarActivo").val(activo);
}
</script>
