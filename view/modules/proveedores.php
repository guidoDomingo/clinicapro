<?php
// Módulo de gestión de proveedores y acreedores para la clínica
require_once "controller/ProveedoresController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Proveedores y Acreedores</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Proveedores y Acreedores</li>
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
            <!-- Botón para agregar proveedor -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarProveedor">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por nombre o RUC -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarBusquedaProveedor" placeholder="Buscar por nombre o RUC" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarProveedores">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarProveedores">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>
          
        <div class="card-body">
          <table id="tblProveedores" class="table table-bordered table-striped dt-responsive tblProveedores" width="100%">
            <thead>
              <tr>
                <th style="width: 10px">#</th>
                <th>Razón Social</th>
                <th>RUC</th>
                <th>Tipo</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Tipo de Proveedor</th>
                <th>Empresa</th>
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
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar proveedores y acreedores.</span>
            </div>
          </div>
        </div>
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
  </div>

<!-- Modal Agregar Proveedor -->
<div class="modal fade" id="modalAgregarProveedor" tabindex="-1" role="dialog" aria-labelledby="modalAgregarProveedorLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalAgregarProveedorLabel">Registrar Proveedor/Acreedor</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="proveedorForm" method="post">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tipoPersonaProveedor">Tipo de Persona</label>
                <select id="tipoPersonaProveedor" name="tipoPersonaProveedor" class="form-control" required>
                  <option value="">Seleccione tipo</option>
                  <option value="FÍSICO">Física</option>
                  <option value="JURÍDICO">Jurídica</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="tipoCodProveedor">Tipo de Proveedor</label>
                <select id="tipoCodProveedor" name="tipoCodProveedor" class="form-control" required>
                  <option value="">Seleccione tipo de proveedor</option>
                  <!-- Se cargará dinámicamente -->
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nombreProveedor">Nombre</label>
                <input type="text" class="form-control" id="nombreProveedor" name="nombreProveedor" placeholder="Ingrese el nombre">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="apellidoProveedor">Apellido</label>
                <input type="text" class="form-control" id="apellidoProveedor" name="apellidoProveedor" placeholder="Ingrese el apellido">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="razonSocialProveedor">Razón Social</label>
            <input type="text" class="form-control" id="razonSocialProveedor" name="razonSocialProveedor" placeholder="Ingrese la razón social" required>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="rucProveedor">RUC</label>
                <input type="text" class="form-control" id="rucProveedor" name="rucProveedor" placeholder="Ingrese el RUC" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="dvProveedor">DV</label>
                <input type="text" class="form-control" id="dvProveedor" name="dvProveedor" placeholder="Ingrese el dígito verificador">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="timbradoProveedor">Timbrado</label>
                <input type="text" class="form-control" id="timbradoProveedor" name="timbradoProveedor" placeholder="Ingrese el timbrado">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="telefonoProveedor">Teléfono</label>
                <input type="text" class="form-control" id="telefonoProveedor" name="telefonoProveedor" placeholder="Ingrese el teléfono">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="emailProveedor">Email</label>
                <input type="email" class="form-control" id="emailProveedor" name="emailProveedor" placeholder="Ingrese el email">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="direccionProveedor">Dirección</label>
            <textarea class="form-control" id="direccionProveedor" name="direccionProveedor" placeholder="Ingrese la dirección" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label for="empresaProveedor">Empresa Asociada</label>
            <select id="empresaProveedor" name="empresaProveedor" class="form-control">
              <option value="">Seleccione una empresa (opcional)</option>
              <!-- Se cargará dinámicamente -->
            </select>
          </div>
          
          <div class="form-group">
            <label for="estadoProveedor">Estado</label>
            <select id="estadoProveedor" name="estadoProveedor" class="form-control">
              <option value="1" selected>Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary" id="btnGuardarProveedor">Guardar</button>
          </div>
          <?php
          // Crear el proveedor usando el controlador
          $crearProveedor = new ProveedoresController();
          $crearProveedor->ctrCrearProveedor();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" role="dialog" aria-labelledby="modalEditarProveedorLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEditarProveedorLabel">Editar Proveedor/Acreedor</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editarProveedorForm" method="post">
          <input type="hidden" id="idProveedor" name="idProveedor">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="editarTipoPersonaProveedor">Tipo de Persona</label>
                <select id="editarTipoPersonaProveedor" name="editarTipoPersonaProveedor" class="form-control" required>
                  <option value="">Seleccione tipo</option>
                  <option value="FÍSICO">Física</option>
                  <option value="JURÍDICO">Jurídica</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="editarTipoCodProveedor">Tipo de Proveedor</label>
                <select id="editarTipoCodProveedor" name="editarTipoCodProveedor" class="form-control" required>
                  <option value="">Seleccione tipo de proveedor</option>
                  <!-- Se cargará dinámicamente -->
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="editarNombreProveedor">Nombre</label>
                <input type="text" class="form-control" id="editarNombreProveedor" name="editarNombreProveedor" placeholder="Edite el nombre">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="editarApellidoProveedor">Apellido</label>
                <input type="text" class="form-control" id="editarApellidoProveedor" name="editarApellidoProveedor" placeholder="Edite el apellido">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="editarRazonSocialProveedor">Razón Social</label>
            <input type="text" class="form-control" id="editarRazonSocialProveedor" name="editarRazonSocialProveedor" placeholder="Edite la razón social" required>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label for="editarRucProveedor">RUC</label>
                <input type="text" class="form-control" id="editarRucProveedor" name="editarRucProveedor" placeholder="Edite el RUC" required>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="editarDvProveedor">DV</label>
                <input type="text" class="form-control" id="editarDvProveedor" name="editarDvProveedor" placeholder="Edite el dígito verificador">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="editarTimbradoProveedor">Timbrado</label>
                <input type="text" class="form-control" id="editarTimbradoProveedor" name="editarTimbradoProveedor" placeholder="Edite el timbrado">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="editarTelefonoProveedor">Teléfono</label>
                <input type="text" class="form-control" id="editarTelefonoProveedor" name="editarTelefonoProveedor" placeholder="Edite el teléfono">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="editarEmailProveedor">Email</label>
                <input type="email" class="form-control" id="editarEmailProveedor" name="editarEmailProveedor" placeholder="Edite el email">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="editarDireccionProveedor">Dirección</label>
            <textarea class="form-control" id="editarDireccionProveedor" name="editarDireccionProveedor" placeholder="Edite la dirección" rows="3"></textarea>
          </div>

          <div class="form-group">
            <label for="editarEmpresaProveedor">Empresa Asociada</label>
            <select id="editarEmpresaProveedor" name="editarEmpresaProveedor" class="form-control">
              <option value="">Seleccione una empresa (opcional)</option>
              <!-- Se cargará dinámicamente -->
            </select>
          </div>
          
          <div class="form-group">
            <label for="editarEstadoProveedor">Estado</label>
            <select id="editarEstadoProveedor" name="editarEstadoProveedor" class="form-control">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
          <?php
          // Editar el proveedor usando el controlador
          $editarProveedor = new ProveedoresController();
          $editarProveedor->ctrEditarProveedor();
          ?>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$borrarProveedor = new ProveedoresController();
$borrarProveedor->ctrBorrarProveedor();
?>
