<div class="content-wrapper">
    <!-- Añadir script para obtener el ID del usuario logueado al inicio de la página -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener el ID del usuario logueado de la sesión PHP
            const usuarioId = '<?php echo isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : ""; ?>';
            
            // Asignar el ID del usuario como atributo de datos al body para acceso desde JavaScript
            document.body.setAttribute('data-user-id', usuarioId);
            
            console.log('ID de usuario logueado:', usuarioId);
        });
    </script>
    
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Preformatos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home">Home</a></li>
                        <li class="breadcrumb-item active">Preformatos</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Formulario para crear/editar preformatos -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item"><a class="nav-link active" href="#preformato-textarea" data-toggle="tab">Preformato textarea</a></li>
                                <li class="nav-item"><a class="nav-link" href="#tipos-formularios" data-toggle="tab">Tipos de Formularios</a></li>
                                <li class="nav-item"><a class="nav-link" href="#preformato-generico" data-toggle="tab">Preformato genérico</a></li>
                                <li class="nav-item"><a class="nav-link" href="#otros" data-toggle="tab">Otros</a></li>
                            </ul>
                        </div><!-- /.card-header -->
                        
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Tab para preformatos de textarea -->
                                <div class="active tab-pane" id="preformato-textarea">
                                    <form id="form-preformato-textarea">
                                        <input type="hidden" id="id-preformato" value="">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="propietario">Propietario</label>
                                                    <select class="form-control" id="propietario" required>
                                                        <option value="" selected disabled>Seleccionar</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="aplicar-a">Aplicar a:</label>
                                                    <select class="form-control" id="aplicar-a" required>
                                                        <option value="" selected disabled>Seleccionar área de aplicación...</option>
                                                        <option value="consulta">Área de Consulta/Observaciones</option>
                                                        <option value="receta">Receta de medicamentos</option>
                                                        <option value="orden_estudios">Orden de Estudios</option>
                                                        <option value="orden_cirugias">Orden de cirugías</option>
                                                        <option value="recomendaciones">Recomendaciones</option>
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Define en qué área del formulario aparecerá este preformato
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="titulo-preformato">Título</label>
                                                    <input type="text" class="form-control" id="titulo-preformato" placeholder="Título del preformato" required>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="tipo-formulario">Tipo de Formulario</label>
                                                    <select class="form-control" id="tipo-formulario" required>
                                                        <option value="" selected disabled>Seleccionar tipo de formulario...</option>
                                                        <!-- Las opciones se cargarán dinámicamente -->
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Este preformato se mostrará en el formulario de consultas del tipo seleccionado
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="obs-preformato">Obs:</label>
                                            <textarea id="obs-preformato" class="form-control" rows="10" required></textarea>
                                        </div>
                                        <div class="form-group text-right">
                                            <button type="button" class="btn btn-default" id="btn-limpiar-preformato">Limpiar</button>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Tab para gestión de tipos de formularios -->
                                <div class="tab-pane" id="tipos-formularios">
                                    <div class="row">
                                        <!-- Formulario para crear/editar tipos de formularios -->
                                        <div class="col-md-5">
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h3 class="card-title" id="titulo-formulario-tipo">Crear Tipo de Formulario</h3>
                                                </div>
                                                <form id="form-tipo-formulario">
                                                    <input type="hidden" id="tipo-id" name="tipo-id">
                                                    <div class="card-body">
                                                        <div class="form-group">
                                                            <label for="tipo-nombre">Nombre</label>
                                                            <input type="text" class="form-control" id="tipo-nombre" name="tipo-nombre" placeholder="Ej: Cardiología" required>
                                                            <div class="invalid-feedback" id="error-nombre"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="tipo-codigo">Código</label>
                                                            <input type="text" class="form-control" id="tipo-codigo" name="tipo-codigo" placeholder="Ej: cardiologia">
                                                            <small class="form-text text-muted">Solo letras, números y guiones bajos. Se genera automáticamente si se deja vacío.</small>
                                                            <div class="invalid-feedback" id="error-codigo"></div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="tipo-descripcion">Descripción</label>
                                                            <textarea class="form-control" id="tipo-descripcion" name="tipo-descripcion" rows="3" placeholder="Descripción del tipo de formulario"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <button type="button" class="btn btn-secondary" id="btn-cancelar-tipo">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary" id="btn-guardar-tipo">Crear Tipo</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        
                                        <!-- Lista de tipos de formularios -->
                                        <div class="col-md-7">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h3 class="card-title">Tipos de Formularios Existentes</h3>
                                                    <div class="card-tools">
                                                        <button type="button" class="btn btn-sm btn-info" onclick="cargarTiposFormularios()">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped table-sm" id="tabla-tipos-formularios">
                                                            <thead>
                                                                <tr>
                                                                    <th>#</th>
                                                                    <th>Nombre</th>
                                                                    <th>Código</th>
                                                                    <th>Descripción</th>
                                                                    <th>Estado</th>
                                                                    <th>Fecha</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="tbody-tipos-formularios">
                                                                <tr>
                                                                    <td colspan="7" class="text-center">
                                                                        <i class="fas fa-spinner fa-spin"></i> Cargando tipos de formularios...
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tab para preformatos genéricos -->
                                <div class="tab-pane" id="preformato-generico">
                                    <div class="form-group">
                                        <p>Configuración de preformatos genéricos (para futuras implementaciones)</p>
                                    </div>
                                </div>
                                
                                <!-- Tab para otros tipos de preformatos -->
                                <div class="tab-pane" id="otros">
                                    <div class="form-group">
                                        <p>Otras configuraciones de preformatos (para futuras implementaciones)</p>
                                    </div>
                                </div>
                            </div>
                            <!-- /.tab-content -->
                        </div><!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            
            <!-- Lista de Preformatos -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Preformatos</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="tabla-preformatos" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Título</th>
                                        <th>Aplicar a</th>
                                        <th>Tipo de Formulario</th>
                                        <th>Propietario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-preformatos">
                                    <!-- Aquí se cargarán los preformatos -->
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal para ver preformato -->
<div class="modal fade" id="modalVerPreformato" tabindex="-1" role="dialog" aria-labelledby="modalVerPreformatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-titulo-preformato"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="modal-contenido-preformato"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar tipo de formulario -->
<div class="modal fade" id="modalEditarTipoFormulario" tabindex="-1" role="dialog" aria-labelledby="modalEditarTipoFormularioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Tipo de Formulario</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editarId" name="editarId">
                    <div class="form-group">
                        <label for="editarNombre">Nombre</label>
                        <input type="text" class="form-control" id="editarNombre" name="editarNombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editarCodigo">Código</label>
                        <input type="text" class="form-control" id="editarCodigo" name="editarCodigo" required>
                        <small class="form-text text-muted">Solo letras, números y guiones bajos. Sin espacios.</small>
                    </div>
                    <div class="form-group">
                        <label for="editarDescripcion">Descripción</label>
                        <textarea class="form-control" id="editarDescripcion" name="editarDescripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script para preformatos -->
<script src="view/js/preformatos.js"></script>