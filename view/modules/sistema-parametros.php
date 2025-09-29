<?php
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    echo '<script>window.location.href = "login";</script>';
    exit();
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Parámetros del Sistema</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
                        <li class="breadcrumb-item active">Parámetros del Sistema</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cogs"></i> Gestión de Parámetros
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" id="btnNuevoParametro">
                                    <i class="fas fa-plus"></i> Nuevo Parámetro
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filtros -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="filtroCategoria">Categoría:</label>
                                    <select class="form-control" id="filtroCategoria">
                                        <option value="">Todas las categorías</option>
                                        <option value="sistema">Sistema</option>
                                        <option value="interfaz">Interfaz</option>
                                        <option value="notificaciones">Notificaciones</option>
                                        <option value="seguridad">Seguridad</option>
                                        <option value="reportes">Reportes</option>
                                        <option value="integracion">Integración</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroTipo">Tipo:</label>
                                    <select class="form-control" id="filtroTipo">
                                        <option value="">Todos los tipos</option>
                                        <option value="texto">Texto</option>
                                        <option value="numero">Número</option>
                                        <option value="booleano">Booleano</option>
                                        <option value="fecha">Fecha</option>
                                        <option value="email">Email</option>
                                        <option value="url">URL</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroEstado">Estado:</label>
                                    <select class="form-control" id="filtroEstado">
                                        <option value="">Todos los estados</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroBusqueda">Buscar:</label>
                                    <input type="text" class="form-control" id="filtroBusqueda" placeholder="Buscar por código o nombre...">
                                </div>
                            </div>

                            <!-- Tabla de parámetros -->
                            <table id="tablaParametros" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th width="120">Código</th>
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th width="80">Tipo</th>
                                        <th width="100">Categoría</th>
                                        <th width="70">Estado</th>
                                        <th width="100">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para Crear/Editar Parámetro -->
<div class="modal fade" id="modalParametro" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="tituloModal">Nuevo Parámetro</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formParametro" novalidate>
                <input type="hidden" id="parametroId" name="parametro_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parametroCodigo">Código del Parámetro <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="parametroCodigo" name="parametro_codigo" required>
                                <small class="form-text text-muted">Código único identificador (ej: SISTEMA_NOMBRE)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="parametroTipo">Tipo <span class="text-danger">*</span></label>
                                <select class="form-control" id="parametroTipo" name="parametro_tipo" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="texto">Texto</option>
                                    <option value="numero">Número</option>
                                    <option value="booleano">Booleano</option>
                                    <option value="fecha">Fecha</option>
                                    <option value="email">Email</option>
                                    <option value="url">URL</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="parametroNombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="parametroNombre" name="parametro_nombre" required>
                                <small class="form-text text-muted">Nombre descriptivo del parámetro</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="parametroCategoria">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control" id="parametroCategoria" name="parametro_categoria" required>
                                    <option value="">Seleccione una categoría</option>
                                    <option value="sistema">Sistema</option>
                                    <option value="interfaz">Interfaz</option>
                                    <option value="notificaciones">Notificaciones</option>
                                    <option value="seguridad">Seguridad</option>
                                    <option value="reportes">Reportes</option>
                                    <option value="integracion">Integración</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="parametroValor">Valor <span class="text-danger">*</span></label>
                        <div id="inputContainer">
                            <!-- El input se generará dinámicamente según el tipo -->
                            <input type="text" class="form-control" id="parametroValor" name="parametro_valor" required>
                        </div>
                        <small class="form-text text-muted" id="valorAyuda">Ingrese el valor del parámetro</small>
                    </div>

                    <div class="form-group">
                        <label for="parametroDescripcion">Descripción</label>
                        <textarea class="form-control" id="parametroDescripcion" name="parametro_descripcion" rows="3" placeholder="Descripción detallada del parámetro y su uso"></textarea>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="parametroActivo" name="is_active" value="1" checked>
                            <label class="form-check-label" for="parametroActivo">
                                Parámetro activo
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarParametro">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmar Eliminación</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el parámetro?</p>
                <div class="alert alert-warning">
                    <strong>Código:</strong> <span id="eliminarCodigo"></span><br>
                    <strong>Nombre:</strong> <span id="eliminarNombre"></span>
                </div>
                <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos del módulo -->
<script src="view/js/sistema-parametros.js"></script>