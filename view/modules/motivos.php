<?php
// Módulo de gestión de motivos comunes para la clínica
require_once "controller/MotivosController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Motivos Comunes</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Motivos Comunes</li>
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
            <!-- Botón para agregar motivo -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarMotivo">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por nombre -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarNombreMotivo" placeholder="Buscar por nombre" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarMotivos">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarMotivos">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
          
        <div class="card-body">
          <table id="tblMotivos" class="table table-bordered table-striped dt-responsive tblMotivos" width="100%">
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
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar motivos comunes.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>

<!-- Modal Agregar Motivo -->
<div class="modal fade" id="modalAgregarMotivo" tabindex="-1" role="dialog" aria-labelledby="modalAgregarMotivoLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarMotivoLabel">Registrar Motivo Común</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="motivoForm" method="post">
          <div class="form-group">
            <label for="nuevoMotivo">Nombre del motivo</label>
            <input type="text" class="form-control" id="nuevoMotivo" name="nuevoMotivo" placeholder="Ingrese el nombre del motivo" required>
          </div>
          <div class="form-group">
            <label for="nuevaDescripcionMotivo">Descripción</label>
            <textarea class="form-control" id="nuevaDescripcionMotivo" name="nuevaDescripcionMotivo" placeholder="Ingrese una descripción" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="estadoMotivo">Estado</label>
            <select id="estadoMotivo" name="estadoMotivo" class="form-control">
              <option value="1" selected>Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarMotivo">Guardar</button>
          </div>
          <?php
          // Crear el motivo usando el controlador
          $crearMotivo = new MotivosController();
          $crearMotivo->ctrCrearMotivo();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Motivo -->
<div class="modal fade" id="modalEditarMotivo" tabindex="-1" role="dialog" aria-labelledby="modalEditarMotivoLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarMotivoLabel">Editar Motivo Común</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editarMotivoForm" method="post">
          <input type="hidden" id="idMotivo" name="idMotivo">
          <div class="form-group">
            <label for="editarMotivo">Nombre del motivo</label>
            <input type="text" class="form-control" id="editarMotivo" name="editarMotivo" placeholder="Edite el nombre del motivo" required>
          </div>
          <div class="form-group">
            <label for="editarDescripcionMotivo">Descripción</label>
            <textarea class="form-control" id="editarDescripcionMotivo" name="editarDescripcionMotivo" placeholder="Edite la descripción" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="editarEstadoMotivo">Estado</label>
            <select id="editarEstadoMotivo" name="editarEstadoMotivo" class="form-control">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
          <?php
          // Editar el motivo usando el controlador
          $editarMotivo = new MotivosController();
          $editarMotivo->ctrEditarMotivo();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$borrarMotivo = new MotivosController();
$borrarMotivo->ctrBorrarMotivo();
?>
