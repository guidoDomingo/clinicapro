<?php
// Módulo de gestión de especialidades para la clínica
require_once "controller/EspecialidadesController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Especialidades</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Especialidades</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- Default box -->
      <div class="card">
        <div class="card-header">
          <div class="form-row">
            <!-- Botón para agregar especialidad -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarEspecialidad">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por nombre -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarNombreEspecialidad" placeholder="Buscar por nombre" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarEspecialidades">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarEspecialidades">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
          
        <div class="card-body">
          <table id="tblEspecialidades" class="table table-bordered table-striped dt-responsive tblEspecialidades" width="100%">
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha Creación</th>
                <th>Estado</th>
                <th style="width: 100px">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Los datos se cargarán dinámicamente -->
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-right">
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar especialidades.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>

<!-- Modal Agregar Especialidad -->
<div class="modal fade" id="modalAgregarEspecialidad" tabindex="-1" role="dialog" aria-labelledby="modalAgregarEspecialidadLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarEspecialidadLabel">Registrar Especialidad</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="especialidadForm" method="post">
          <div class="form-group">
            <label for="nuevaEspecialidad">Nombre de la especialidad</label>
            <input type="text" class="form-control" id="nuevaEspecialidad" name="nuevaEspecialidad" placeholder="Ingrese el nombre de la especialidad" required>
          </div>
          <div class="form-group">
            <label for="nuevaDescripcion">Descripción</label>
            <textarea class="form-control" id="nuevaDescripcion" name="nuevaDescripcion" placeholder="Ingrese una descripción" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="estadoEspecialidad">Estado</label>
            <select id="estadoEspecialidad" name="estadoEspecialidad" class="form-control">
              <option value="1" selected>Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarEspecialidad">Guardar</button>
          </div>
          <?php
          // Crear la especialidad usando el controlador
          $crearEspecialidad = new EspecialidadesController();
          $crearEspecialidad->ctrCrearEspecialidad();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Especialidad -->
<div class="modal fade" id="modalEditarEspecialidad" tabindex="-1" role="dialog" aria-labelledby="modalEditarEspecialidadLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarEspecialidadLabel">Editar Especialidad</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editarEspecialidadForm" method="post">
          <input type="hidden" id="idEspecialidad" name="idEspecialidad">
          <div class="form-group">
            <label for="editarEspecialidad">Nombre de la especialidad</label>
            <input type="text" class="form-control" id="editarEspecialidad" name="editarEspecialidad" placeholder="Edite el nombre de la especialidad" required>
          </div>
          <div class="form-group">
            <label for="editarDescripcion">Descripción</label>
            <textarea class="form-control" id="editarDescripcion" name="editarDescripcion" placeholder="Edite la descripción" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="editarEstadoEspecialidad">Estado</label>
            <select id="editarEstadoEspecialidad" name="editarEstadoEspecialidad" class="form-control">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
          <?php
          // Editar la especialidad usando el controlador
          $editarEspecialidad = new EspecialidadesController();
          $editarEspecialidad->ctrEditarEspecialidad();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$borrarEspecialidad = new EspecialidadesController();
$borrarEspecialidad->ctrBorrarEspecialidad();
?>
