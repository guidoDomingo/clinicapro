<?php
// Módulo de gestión de profesiones para la clínica
require_once "controller/ProfesionesController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Profesiones</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Profesiones</li>
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
            <!-- Botón para agregar profesión -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarProfesion">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por nombre -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarNombreProfesion" placeholder="Buscar por nombre" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarProfesiones">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarProfesiones">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
          
        <div class="card-body">
          <table id="tblProfesiones" class="table table-bordered table-striped dt-responsive tblProfesiones" width="100%">
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Nombre</th>
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
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar profesiones.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>

<!-- Modal Agregar Profesión -->
<div class="modal fade" id="modalAgregarProfesion" tabindex="-1" role="dialog" aria-labelledby="modalAgregarProfesionLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarProfesionLabel">Registrar Profesión</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="profesionForm" method="post">
          <div class="form-group">
            <label for="nuevaProfesion">Nombre de la profesión</label>
            <input type="text" class="form-control" id="nuevaProfesion" name="nuevaProfesion" placeholder="Ingrese el nombre de la profesión" required>
          </div>
          <div class="form-group">
            <label for="estadoProfesion">Estado</label>
            <select id="estadoProfesion" name="estadoProfesion" class="form-control">
              <option value="1" selected>Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarProfesion">Guardar</button>
          </div>
          <?php
          // Crear la profesión usando el controlador
          $crearProfesion = new ProfesionesController();
          $crearProfesion->ctrCrearProfesion();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Profesión -->
<div class="modal fade" id="modalEditarProfesion" tabindex="-1" role="dialog" aria-labelledby="modalEditarProfesionLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarProfesionLabel">Editar Profesión</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editarProfesionForm" method="post">
          <input type="hidden" id="idProfesion" name="idProfesion">
          <div class="form-group">
            <label for="editarProfesion">Nombre de la profesión</label>
            <input type="text" class="form-control" id="editarProfesion" name="editarProfesion" placeholder="Edite el nombre de la profesión" required>
          </div>
          <div class="form-group">
            <label for="editarEstadoProfesion">Estado</label>
            <select id="editarEstadoProfesion" name="editarEstadoProfesion" class="form-control">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
          <?php
          // Editar la profesión usando el controlador
          $editarProfesion = new ProfesionesController();
          $editarProfesion->ctrEditarProfesion();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$borrarProfesion = new ProfesionesController();
$borrarProfesion->ctrBorrarProfesion();
?>
