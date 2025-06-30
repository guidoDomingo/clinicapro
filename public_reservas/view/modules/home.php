<?php
/**
 * Página de inicio para reservas públicas
 * Presenta opciones para iniciar sesión o registrarse antes de continuar con la reserva
 */
?>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Reserva de Citas Médicas</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
              <li class="breadcrumb-item active">Reservas</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <!-- Hero section -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card bg-gradient-primary text-white">
              <div class="card-body">
                <h2>Sistema de Reservas Online</h2>
                <p class="lead">Para agendar una cita médica, primero debe iniciar sesión o registrarse en nuestro sistema.</p>
                <div class="mt-4">
                  <a href="index.php?view=login" class="btn btn-light btn-lg mr-2">
                    <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                  </a>
                  <a href="index.php?view=register" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-user-plus mr-2"></i>Registrarse
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Features section -->
        <div class="row">
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-calendar-check fa-4x mb-3 text-primary"></i>
                <h5 class="card-title">Reservas Simples</h5>
                <p class="card-text">
                  Agende sus citas médicas en línea de forma rápida y sencilla, sin llamadas telefónicas ni esperas.
                </p>
              </div>
            </div>
          </div>
          
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-clock fa-4x mb-3 text-primary"></i>
                <h5 class="card-title">Horarios Flexibles</h5>
                <p class="card-text">
                  Encuentre el horario que mejor se adapte a sus necesidades, con disponibilidad actualizada en tiempo real.
                </p>
              </div>
            </div>
          </div>
          
          <div class="col-lg-4">
            <div class="card">
              <div class="card-body text-center">
                <i class="fas fa-bell fa-4x mb-3 text-primary"></i>
                <h5 class="card-title">Recordatorios</h5>
                <p class="card-text">
                  Reciba recordatorios automáticos de sus citas para no olvidar sus compromisos médicos.
                </p>
              </div>
            </div>
          </div>
        </div>
        <!-- /.row -->
        
        <!-- Steps section -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">¿Cómo funciona el sistema de reservas?</h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 text-center">
                    <div class="info-box bg-light">
                      <span class="info-box-icon bg-info"><i class="fas fa-user-plus"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Paso 1</span>
                        <span class="info-box-number">Regístrese o inicie sesión</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-3 text-center">
                    <div class="info-box bg-light">
                      <span class="info-box-icon bg-success"><i class="fas fa-calendar-alt"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Paso 2</span>
                        <span class="info-box-number">Seleccione fecha y especialidad</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-3 text-center">
                    <div class="info-box bg-light">
                      <span class="info-box-icon bg-warning"><i class="fas fa-user-md"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Paso 3</span>
                        <span class="info-box-number">Elija médico y horario</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-3 text-center">
                    <div class="info-box bg-light">
                      <span class="info-box-icon bg-danger"><i class="fas fa-check-circle"></i></span>
                      <div class="info-box-content">
                        <span class="info-box-text">Paso 4</span>
                        <span class="info-box-number">Confirme su reserva</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
