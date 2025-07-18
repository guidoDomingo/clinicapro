<?php
// Módulo de gestión de turnos para la clínica
require_once "controller/TurnosController.php";
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Administrar Turnos</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Turnos</li>
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
            <!-- Botón para agregar turno -->
            <div class="col-md-1">
              <button type="button" class="btn btn-info" id="btnAgregarTurno">
                <i class="fas fa-plus-circle"></i> Crear
              </button>
            </div>

            <!-- Campo para buscar por nombre -->
            <div class="col-md-5">
              <input type="text" class="form-control" id="validarBusquedaTurno" placeholder="Buscar por nombre o descripción" required>
            </div>

            <!-- Botones de Acción -->
            <div class="col-md-2">
              <div class="btn-group" role="group">
                <button class="btn btn-primary" type="button" id="btnFiltrarTurnos">
                  <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" class="btn btn-secondary" id="btnLimpiarTurnos">
                  <i class="fas fa-sync-alt"></i> Limpiar
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table class="table table-striped dt-responsive nowrap" id="tblTurnos" width="100%">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha Creación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Los datos se cargan dinámicamente via AJAX -->
            </tbody>
          </table>
        </div>
        <!-- /.card-body -->
      </div>
      <!-- /.card -->

    </section>
    <!-- /.content -->
</div>

<!-- MODAL PARA AGREGAR/EDITAR TURNO -->
<div class="modal fade" id="modalFormTurno" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="post" id="frmTurno">
        <div class="modal-header">
          <h5 class="modal-title" id="modalFormTurnoLabel">Agregar Turno</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <!-- Campo oculto para el ID del turno -->
          <input type="hidden" id="turno_id" name="turno_id" value="">

          <div class="row">
            <!-- Nombre del turno -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="turno_nombre">Nombre del Turno <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="turno_nombre" name="turno_nombre" placeholder="Ej: Mañana, Tarde, Noche" required maxlength="50">
              </div>
            </div>

            <!-- Estado -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="turno_estado">Estado</label>
                <select class="form-control" id="turno_estado" name="turno_estado">
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <!-- Descripción -->
            <div class="col-md-12">
              <div class="form-group">
                <label for="turno_descripcion">Descripción</label>
                <textarea class="form-control" id="turno_descripcion" name="turno_descripcion" rows="3" placeholder="Descripción opcional del turno" maxlength="255"></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btnGuardarTurno">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="view/js/turnos.js"></script>
