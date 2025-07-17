<?php
// Módulo de gestión de salas para la clínica
require_once "controller/SalasController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Salas</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Salas</li>
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
            <!-- Botón para agregar sala -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarSala">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por código o nombre -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarBusquedaSala" placeholder="Buscar por código o nombre" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarSalas">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarSalas">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>

            <!-- Filtro de estado -->
            <div class="col-md-2">
              <select class="form-control" id="filtroEstado">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
              </select>
            </div>
          </div>
          <br>
          <div class="row">
            <div class="col-12">
              <span class="text-muted">Utilice la tabla para ver, editar o eliminar salas.</span>
            </div>
          </div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <table id="tblSalas" class="table table-striped table-bordered nowrap" style="width:100%">
            <thead class="bg-info text-left">
              <tr>
                <th>#</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha Creación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal para agregar/editar sala -->
<div class="modal fade" id="modalFormSala" role="dialog" aria-labelledby="modalFormSalaLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title" id="modalFormSalaLabel">Gestión de Salas</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmSala" novalidate>
        <div class="modal-body">
          <!-- ID oculto para edición -->
          <input type="hidden" id="sala_id" name="sala_id">
          
          <div class="row">
            <!-- Código de sala -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="sala_codigo">Código <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="sala_codigo" name="sala_codigo" required maxlength="20">
                <div class="invalid-feedback">Por favor, ingrese el código de la sala.</div>
              </div>
            </div>

            <!-- Nombre de sala -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="sala_nombre">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="sala_nombre" name="sala_nombre" required maxlength="100">
                <div class="invalid-feedback">Por favor, ingrese el nombre de la sala.</div>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Descripción -->
            <div class="col-md-8">
              <div class="form-group">
                <label for="sala_descripcion">Descripción</label>
                <textarea class="form-control" id="sala_descripcion" name="sala_descripcion" rows="3" maxlength="255"></textarea>
                <small class="form-text text-muted">Máximo 255 caracteres</small>
              </div>
            </div>

            <!-- Estado -->
            <div class="col-md-4">
              <div class="form-group">
                <label for="sala_estado">Estado</label>
                <select class="form-control" id="sala_estado" name="sala_estado">
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-info" id="btnGuardarSala">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="view/js/salas.js"></script>
