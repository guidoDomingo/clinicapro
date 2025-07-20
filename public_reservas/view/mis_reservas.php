<?php
// Verificar que el usuario esté autenticado
if (!AuthController::isAuthenticated()) {
    echo '<div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i> Debe iniciar sesión para ver sus reservas.
          </div>';
    include "view/modules/login.php";
    exit;
}
?>

<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><i class="fas fa-calendar-check mr-2"></i>Mis Reservas</h4>
    </div>
    <div class="card-body">
        <div id="cargando" class="text-center p-3">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Cargando sus reservas...</p>
        </div>

        <div id="reservasContainer" class="d-none">
            <ul class="nav nav-tabs" id="reservasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="pendientes-tab" data-toggle="tab" href="#pendientes" role="tab">
                        <i class="fas fa-clock mr-1"></i> Pendientes
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="completadas-tab" data-toggle="tab" href="#completadas" role="tab">
                        <i class="fas fa-check-circle mr-1"></i> Completadas
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="canceladas-tab" data-toggle="tab" href="#canceladas" role="tab">
                        <i class="fas fa-times-circle mr-1"></i> Canceladas
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="todas-tab" data-toggle="tab" href="#todas" role="tab">
                        <i class="fas fa-list mr-1"></i> Todas
                    </a>
                </li>
            </ul>
            
            <div class="tab-content pt-3" id="reservasTabContent">
                <div class="tab-pane fade show active" id="pendientes" role="tabpanel">
                    <div id="pendientes-container" class="reservas-list">
                        <!-- Se llenará con JavaScript -->
                    </div>
                </div>
                <div class="tab-pane fade" id="completadas" role="tabpanel">
                    <div id="completadas-container" class="reservas-list">
                        <!-- Se llenará con JavaScript -->
                    </div>
                </div>
                <div class="tab-pane fade" id="canceladas" role="tabpanel">
                    <div id="canceladas-container" class="reservas-list">
                        <!-- Se llenará con JavaScript -->
                    </div>
                </div>
                <div class="tab-pane fade" id="todas" role="tabpanel">
                    <div id="todas-container" class="reservas-list">
                        <!-- Se llenará con JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <div id="sinReservas" class="text-center p-5 d-none">
            <img src="assets/img/empty-calendar.svg" alt="Sin reservas" class="img-fluid mb-3" style="max-width: 150px;">
            <h4 class="mb-3">No tienes reservas registradas</h4>
            <p class="text-muted">Aún no has realizado ninguna reserva. ¡Programa tu primera cita ahora!</p>
            <a href="index.php?accion=reservar" class="btn btn-primary">
                <i class="fas fa-calendar-plus mr-2"></i>Nueva Reserva
            </a>
        </div>
        
        <!-- Template para las cards de reservas -->
        <template id="reserva-card-template">
            <div class="card mb-3 reserva-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fecha-reserva"><i class="far fa-calendar-alt mr-2"></i></span>
                    <span class="estado-badge"></span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="card-title servicio-nombre"></h5>
                            <p class="mb-1"><i class="fas fa-user-md mr-2"></i><span class="doctor-nombre"></span></p>
                            <p class="mb-1"><i class="far fa-clock mr-2"></i><span class="horario-reserva"></span></p>
                            <p class="mb-1"><i class="fas fa-map-marker-alt mr-2"></i><span class="sala-nombre"></span></p>
                            <p class="mb-2"><i class="fas fa-tag mr-2"></i>Monto: <span class="servicio-monto"></span> Gs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<!-- Cargar script específico para esta página -->
<script type="text/javascript" src="assets/js/mis_reservas.js"></script>
</script>

<style>
.reserva-card {
    transition: all 0.3s ease;
}

.reserva-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.estado-badge {
    font-size: 0.85rem;
    padding: 0.35em 0.65em;
}

.reservas-list {
    min-height: 100px;
}

/* Estilo para reservas canceladas */
.reserva-card.bg-light {
    opacity: 0.8;
}
</style>
