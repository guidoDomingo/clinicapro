<!-- ======= ESTILOS SUAVES (no rompen AdminLTE) ======= -->
<style>
  /* Encabezado más pro con degradado */
  .content-header {
    /* background: linear-gradient(135deg, #1e40af 0%, #0ea5e9 100%);
    color: #fff;
    border-radius: 1rem;
    padding-top: 1.25rem;
    padding-bottom: 1rem;
    margin-top: .5rem; */
  }
  .content-header .breadcrumb .breadcrumb-item a,
  .content-header .breadcrumb .breadcrumb-item.active {
    color: rgba(255,255,255,.95);
  }

  /* Tarjetas con mejor presencia */
  .card.pro {
    border: 0;
    border-radius: 1rem;
    box-shadow: 0 10px 28px rgba(2, 6, 23, .08);
  }
  .card.pro .card-title {
    font-weight: 700;
    letter-spacing: .2px;
  }
  .card.pro .card-text {
    color: #6b7280; /* gris elegante */
  }

  /* Badges de icono */
  .icon-badge {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    background: #eef2ff; /* azul muy claro */
    color: #3b82f6;      /* azul principal */
    margin-right: .75rem;
    flex: 0 0 auto;
    font-size: 1.35rem;
  }

  /* Botón call-to-action */
  .btn-cta {
    border-radius: .75rem;
    padding: .5rem .9rem;
  }

  /* Hover sutil en tarjetas */
  .card.pro:hover {
    transform: translateY(-2px);
    transition: transform .2s ease, box-shadow .2s ease;
    box-shadow: 0 14px 36px rgba(2, 6, 23, .12);
  }
</style>

<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container">
      <!-- <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
          <h1 class="m-0">
            Top Navigation <small>Example 3.0</small>
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Layout</a></li>
            <li class="breadcrumb-item active">Top Navigation</li>
          </ol>
        </div>
      </div> -->
    </div>
  </div>
  <!-- /.content-header -->

  <div class="content">
    <div class="container">
      <!-- Sección: Nuestros Servicios -->
      <div class="row">
        <div class="col-lg-12">
          <div class="card pro">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <div class="icon-badge">
                  <i class="fas fa-concierge-bell"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Nuestros Servicios</h5>
                  <p class="card-text mb-3">
                    Ofrecemos una amplia gama de servicios para satisfacer tus necesidades.
                  </p>
                  <a href="https://www.centro-oftalmologico.com.py/servicios"
                        class="btn btn-primary btn-cta"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fas fa-info-circle mr-1"></i> Más información
                        </a>

                </div>
              </div>
            </div><!-- /.card-body -->
          </div><!-- /.card -->
        </div><!-- /.col -->
      </div><!-- /.row -->

      <!-- Sección: Registros (dos tarjetas) -->
      <div class="row">
        <!-- Registro de Médico -->
        <div class="col-lg-6">
          <div class="card pro h-100">
            <div class="card-body">
              <div class="d-flex">
                <div class="icon-badge">
                  <i class="fas fa-user-md"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Registro de Médico</h5>
                  <p class="card-text mb-3">
                    Crea la cuenta del profesional para administrar agenda, salas y servicios.
                  </p>
                  <a href="index.php?ruta=register" class="btn btn-outline-primary btn-cta">
                    <i class="fas fa-external-link-alt mr-1"></i> Ir al registro de médico
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Registro de Paciente -->
        <div class="col-lg-6">
          <div class="card pro h-100">
            <div class="card-body">
              <div class="d-flex">
                <div class="icon-badge">
                  <i class="fas fa-id-card"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">Registro de Paciente</h5>
                  <p class="card-text mb-3">
                    Crea el perfil del paciente para habilitar turnos online y ver su historial.
                  </p>
                  <a href="public_reservas" class="btn btn-outline-primary btn-cta">
                    <i class="fas fa-external-link-alt mr-1"></i> Ir al registro de paciente
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /.row -->
    </div><!-- /.container -->
  </div><!-- /.content -->
</div><!-- /.content-wrapper -->
