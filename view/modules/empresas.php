<?php
// Módulo de gestión de empresas para la clínica
require_once "controller/EmpresasController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Empresas</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Empresas</li>
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
            <!-- Botón para agregar empresa -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarEmpresa">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por nombre -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarNombreEmpresa" placeholder="Buscar por nombre o RUC" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarEmpresas">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarEmpresas">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
          
        <div class="card-body">
          <table id="tblEmpresas" class="table table-bordered table-striped dt-responsive tblEmpresas" width="100%">
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Nombre</th>
                <th>RUC</th>
                <th>Email</th>
                <th>Teléfono</th>
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
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar empresas.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>

<!-- Modal Agregar Empresa -->
<div class="modal fade" id="modalAgregarEmpresa" tabindex="-1" role="dialog" aria-labelledby="modalAgregarEmpresaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarEmpresaLabel">Registrar Empresa</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="empresaForm" method="post">
          <div class="form-group">
            <label for="nombreEmpresa">Nombre de la empresa</label>
            <input type="text" class="form-control" id="nombreEmpresa" name="nombreEmpresa" placeholder="Ingrese el nombre de la empresa" required>
          </div>
          <div class="form-group">
            <label for="rucEmpresa">RUC</label>
            <input type="text" class="form-control" id="rucEmpresa" name="rucEmpresa" placeholder="Ingrese el RUC de la empresa" required>
          </div>
          <div class="form-group">
            <label for="emailEmpresa">Email</label>
            <input type="email" class="form-control" id="emailEmpresa" name="emailEmpresa" placeholder="Ingrese el email de la empresa">
          </div>
          <div class="form-group">
            <label for="telefonoEmpresa">Teléfono</label>
            <input type="text" class="form-control" id="telefonoEmpresa" name="telefonoEmpresa" placeholder="Ingrese el teléfono de la empresa">
          </div>
          <div class="form-group">
            <label for="direccionEmpresa">Dirección</label>
            <textarea class="form-control" id="direccionEmpresa" name="direccionEmpresa" placeholder="Ingrese la dirección" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="estadoEmpresa">Estado</label>
            <select id="estadoEmpresa" name="estadoEmpresa" class="form-control">
              <option value="1" selected>Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarEmpresa">Guardar</button>
          </div>
          <?php
          // Crear la empresa usando el controlador
          $crearEmpresa = new EmpresasController();
          $crearEmpresa->ctrCrearEmpresa();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Empresa -->
<div class="modal fade" id="modalEditarEmpresa" tabindex="-1" role="dialog" aria-labelledby="modalEditarEmpresaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarEmpresaLabel">Editar Empresa</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editarEmpresaForm" method="post">
          <input type="hidden" id="idEmpresa" name="idEmpresa">
          <div class="form-group">
            <label for="editarNombreEmpresa">Nombre de la empresa</label>
            <input type="text" class="form-control" id="editarNombreEmpresa" name="editarNombreEmpresa" placeholder="Edite el nombre de la empresa" required>
          </div>
          <div class="form-group">
            <label for="editarRucEmpresa">RUC</label>
            <input type="text" class="form-control" id="editarRucEmpresa" name="editarRucEmpresa" placeholder="Edite el RUC de la empresa" required>
          </div>
          <div class="form-group">
            <label for="editarEmailEmpresa">Email</label>
            <input type="email" class="form-control" id="editarEmailEmpresa" name="editarEmailEmpresa" placeholder="Edite el email de la empresa">
          </div>
          <div class="form-group">
            <label for="editarTelefonoEmpresa">Teléfono</label>
            <input type="text" class="form-control" id="editarTelefonoEmpresa" name="editarTelefonoEmpresa" placeholder="Edite el teléfono de la empresa">
          </div>
          <div class="form-group">
            <label for="editarDireccionEmpresa">Dirección</label>
            <textarea class="form-control" id="editarDireccionEmpresa" name="editarDireccionEmpresa" placeholder="Edite la dirección" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="editarEstadoEmpresa">Estado</label>
            <select id="editarEstadoEmpresa" name="editarEstadoEmpresa" class="form-control">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
          <?php
          // Editar la empresa usando el controlador
          $editarEmpresa = new EmpresasController();
          $editarEmpresa->ctrEditarEmpresa();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$borrarEmpresa = new EmpresasController();
$borrarEmpresa->ctrBorrarEmpresa();
?>
