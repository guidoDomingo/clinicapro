<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Tipos de Formularios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="home">Home</a></li>
                        <li class="breadcrumb-item">Referenciales</li>
                        <li class="breadcrumb-item active">Tipos de Formularios</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Botón para agregar nuevo tipo -->
            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalNuevoTipo">
                        <i class="fas fa-plus"></i> Nuevo Tipo de Formulario
                    </button>
                </div>
            </div>

            <!-- Tabla de tipos de formularios -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lista de Tipos de Formularios</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped" id="tabla-tipos-formularios">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Código</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-tipos-formularios">
                                    <!-- Los datos se cargarán aquí via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para nuevo tipo de formulario -->
<div class="modal fade" id="modalNuevoTipo" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="form-tipo-formulario">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo Tipo de Formulario</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="tipo-id" name="tipo-id">
                    <div class="form-group">
                        <label for="tipo-nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tipo-nombre" name="tipo-nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="tipo-codigo">Código <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="tipo-codigo" name="tipo-codigo" required>
                        <small class="form-text text-muted">Solo letras, números y guiones bajos. Sin espacios.</small>
                    </div>
                    <div class="form-group">
                        <label for="tipo-descripcion">Descripción</label>
                        <textarea class="form-control" id="tipo-descripcion" name="tipo-descripcion" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btn-cancelar-tipo">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-tipo">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
