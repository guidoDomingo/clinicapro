<?php
// Módulo de gestión de tipos de proveedores para la clínica
require_once "controller/TiposProveedoresController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Tipos de Proveedores</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Tipos de Proveedores</li>
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
            <!-- Botón para agregar tipo de proveedor -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarTipoProveedor">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por nombre -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarNombreTipoProveedor" placeholder="Buscar por nombre" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarTiposProveedores">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarTiposProveedores">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
          
        <div class="card-body">
          <table id="tblTiposProveedores" class="table table-bordered table-striped dt-responsive tblTiposProveedores" width="100%">
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Nombre</th>
                <th>Descripción</th>
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
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar tipos de proveedores.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>

<!-- Modal Agregar Tipo de Proveedor -->
<div class="modal fade" id="modalAgregarTipoProveedor" tabindex="-1" role="dialog" aria-labelledby="modalAgregarTipoProveedorLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarTipoProveedorLabel">Registrar Tipo de Proveedor</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="tipoProveedorForm" method="post">
          <div class="form-group">
            <label for="nombreTipoProveedor">Nombre del tipo de proveedor</label>
            <input type="text" class="form-control" id="nombreTipoProveedor" name="nombreTipoProveedor" placeholder="Ingrese el nombre del tipo de proveedor" required>
          </div>
          <div class="form-group">
            <label for="descripcionTipoProveedor">Descripción</label>
            <textarea class="form-control" id="descripcionTipoProveedor" name="descripcionTipoProveedor" placeholder="Ingrese una descripción" rows="3"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarTipoProveedor">Guardar</button>
          </div>
          <?php
          // Crear el tipo de proveedor usando el controlador
          $crearTipoProveedor = new TiposProveedoresController();
          $crearTipoProveedor->ctrCrearTipoProveedor();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Tipo de Proveedor -->
<div class="modal fade" id="modalEditarTipoProveedor" tabindex="-1" role="dialog" aria-labelledby="modalEditarTipoProveedorLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarTipoProveedorLabel">Editar Tipo de Proveedor</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editarTipoProveedorForm" method="post">
          <input type="hidden" id="idTipoProveedor" name="idTipoProveedor">
          <div class="form-group">
            <label for="editarNombreTipoProveedor">Nombre del tipo de proveedor</label>
            <input type="text" class="form-control" id="editarNombreTipoProveedor" name="editarNombreTipoProveedor" placeholder="Edite el nombre del tipo de proveedor" required>
          </div>
          <div class="form-group">
            <label for="editarDescripcionTipoProveedor">Descripción</label>
            <textarea class="form-control" id="editarDescripcionTipoProveedor" name="editarDescripcionTipoProveedor" placeholder="Edite la descripción" rows="3"></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
          <?php
          // Editar el tipo de proveedor usando el controlador
          $editarTipoProveedor = new TiposProveedoresController();
          $editarTipoProveedor->ctrEditarTipoProveedor();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$borrarTipoProveedor = new TiposProveedoresController();
$borrarTipoProveedor->ctrBorrarTipoProveedor();
?>
