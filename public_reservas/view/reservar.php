<?php
/**
 * Vista de reservas
 * Permite a los usuarios hacer reservas de servicios médicos
 */

// Verificar que el usuario esté autenticado
if (!AuthController::isAuthenticated()) {
    // El usuario no está autenticado, esto no debería ocurrir ya que template.php verifica la autenticación
    // pero lo manejamos por seguridad
    echo "<div class='alert alert-danger'>
            <strong>Error:</strong> Debe iniciar sesión para acceder a esta página.
          </div>";
    echo "<script>setTimeout(function() { window.location.href = 'index.php?view=login'; }, 2000);</script>";
    return;
}

// Obtener datos del usuario
$userData = AuthController::ctrGetUserData();

// Debug: Mostrar información de sesión en log
error_log("Reservar.php - Usuario: " . json_encode($userData) . ", Sesión ID: " . session_id(), 3, "c:/laragon/www/clinica/logs/session_debug.log");
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h3><i class="fas fa-calendar-plus mr-2"></i> Nueva Reserva</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <p class="lead">¡Bienvenido/a, <?php echo $userData['nombre']; ?>!</p>
                <p>Aquí puede reservar su próxima cita médica.</p>
            </div>
        </div>
        
        <!-- Formulario de reserva -->
        <form id="formReserva" method="post">
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="servicio">Servicio</label>
                        <select class="form-control" id="servicio" name="servicio_id" required>
                            <option value="">-- Seleccione un servicio --</option>
                            <!-- Los servicios se cargarán con AJAX -->
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="text" class="form-control datepicker" id="fecha" name="fecha_reserva" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="doctor">Doctor</label>
                        <select class="form-control" id="doctor" name="doctor_id" disabled required>
                            <option value="">-- Primero seleccione fecha --</option>
                            <!-- Los doctores se cargarán con AJAX -->
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="horario">Horario</label>
                        <select class="form-control" id="horario" name="horario" disabled required>
                            <option value="">-- Primero seleccione doctor --</option>
                            <!-- Los horarios se cargarán con AJAX -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="observaciones">Observaciones (opcional)</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnReservar" name="guardarReserva">
                        <i class="fas fa-calendar-check mr-2"></i> Reservar Cita
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sección de instrucciones -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h4><i class="fas fa-info-circle mr-2"></i> Instrucciones</h4>
    </div>
    <div class="card-body">
        <ol>
            <li>Seleccione el servicio médico que desea reservar</li>
            <li>Elija una fecha disponible en el calendario</li>
            <li>Seleccione un doctor disponible para esa fecha</li>
            <li>Elija un horario disponible para su consulta</li>
            <li>Opcionalmente, añada observaciones sobre su consulta</li>
            <li>Haga clic en "Reservar Cita" para confirmar</li>
        </ol>
        <p class="text-muted">Una vez confirmada la reserva, recibirá un correo electrónico con los detalles de su cita.</p>
    </div>
</div>

<script>
$(document).ready(function() {
    console.log('Inicializando formulario de reservas');
    
    // Inicializar datepicker
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        startDate: 'today',
        language: 'es',
        autoclose: true
    });
    
    // Cargar servicios al inicio
    cargarServicios();
    
    // Manejar cambio de fecha
    $('#fecha').change(function() {
        var fecha = $(this).val();
        if (fecha) {
            cargarDoctores(fecha);
        } else {
            $('#doctor').html('<option value="">-- Primero seleccione fecha --</option>').prop('disabled', true);
            $('#horario').html('<option value="">-- Primero seleccione doctor --</option>').prop('disabled', true);
        }
    });
    
    // Manejar cambio de doctor
    $('#doctor').change(function() {
        var doctorId = $(this).val();
        var fecha = $('#fecha').val();
        if (doctorId && fecha) {
            cargarHorarios(fecha, doctorId);
        } else {
            $('#horario').html('<option value="">-- Primero seleccione doctor --</option>').prop('disabled', true);
        }
    });
    
    // Funciones para cargar datos con AJAX
    function cargarServicios() {
        console.log('Cargando servicios...');
        $.ajax({
            url: 'ajax/reservas_public.ajax.php',
            method: 'POST',
            data: {accion: 'obtenerServicios', fecha: new Date().toISOString().split('T')[0]},
            dataType: 'json',
            success: function(response) {
                console.log('Servicios recibidos:', response);
                var options = '<option value="">-- Seleccione un servicio --</option>';
                if (response && response.length > 0) {
                    $.each(response, function(index, servicio) {
                        options += '<option value="' + servicio.serv_id + '">' + servicio.serv_descripcion + '</option>';
                    });
                }
                $('#servicio').html(options);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar servicios:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los servicios. Por favor, intente más tarde.'
                });
            }
        });
    }
    
    function cargarDoctores(fecha) {
        console.log('Cargando doctores para fecha:', fecha);
        $('#doctor').html('<option value="">Cargando doctores...</option>').prop('disabled', true);
        
        // Obtener el servicio seleccionado
        var servicioId = $('#servicio').val();
        if (!servicioId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor seleccione un servicio primero'
            });
            $('#doctor').html('<option value="">-- Seleccione un servicio primero --</option>').prop('disabled', true);
            return;
        }
        
        $.ajax({
            url: 'ajax/reservas_public.ajax.php',
            method: 'POST',
            data: {accion: 'obtenerMedicos', fecha: fecha, servicio_id: servicioId},
            dataType: 'json',
            success: function(response) {
                console.log('Doctores recibidos:', response);
                var options = '<option value="">-- Seleccione un doctor --</option>';
                if (response && response.length > 0) {
                    $.each(response, function(index, doctor) {
                        options += '<option value="' + doctor.doctor_id + '">' + doctor.nombre + '</option>';
                    });
                    $('#doctor').html(options).prop('disabled', false);
                } else {
                    $('#doctor').html('<option value="">No hay doctores disponibles para esta fecha</option>').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar doctores:', error);
                $('#doctor').html('<option value="">Error al cargar doctores</option>').prop('disabled', true);
            }
        });
    }
    
    function cargarHorarios(fecha, doctorId) {
        console.log('Cargando horarios para fecha:', fecha, 'y doctor:', doctorId);
        $('#horario').html('<option value="">Cargando horarios...</option>').prop('disabled', true);
        
        var servicioId = $('#servicio').val();
        
        $.ajax({
            url: 'ajax/reservas_public.ajax.php',
            method: 'POST',
            data: {
                accion: 'obtenerHorarios', 
                fecha: fecha, 
                doctor_id: doctorId,
                servicio_id: servicioId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Horarios recibidos:', response);
                var options = '<option value="">-- Seleccione un horario --</option>';
                if (response && response.length > 0) {
                    $.each(response, function(index, horario) {
                        options += '<option value="' + horario.hora + ' - ' + horario.hora_fin + '">' + 
                                   horario.hora_formateada + ' (' + horario.duracion + ' min)</option>';
                    });
                    $('#horario').html(options).prop('disabled', false);
                } else {
                    $('#horario').html('<option value="">No hay horarios disponibles</option>').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar horarios:', error);
                $('#horario').html('<option value="">Error al cargar horarios</option>').prop('disabled', true);
            }
        });
    }
    
    // Manejar envío del formulario
    $('#formReserva').submit(function(e) {
        e.preventDefault();
        
        // Validar que todos los campos requeridos estén completos
        var formData = $(this).serialize();
        var isValid = true;
        var errorMessage = '';
        
        if (!$('#servicio').val()) {
            isValid = false;
            errorMessage = 'Por favor seleccione un servicio';
        } else if (!$('#fecha').val()) {
            isValid = false;
            errorMessage = 'Por favor seleccione una fecha';
        } else if (!$('#doctor').val()) {
            isValid = false;
            errorMessage = 'Por favor seleccione un doctor';
        } else if (!$('#horario').val()) {
            isValid = false;
            errorMessage = 'Por favor seleccione un horario';
        }
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
            return;
        }
        
        Swal.fire({
            title: '¿Confirmar reserva?',
            text: "¿Está seguro que desea reservar esta cita?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, reservar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Enviando formulario de reserva...');
                
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Procesando reserva...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar formulario por AJAX para procesamiento
                $.ajax({
                    url: 'ajax/reservas_public.ajax.php',
                    method: 'POST',
                    data: formData + '&accion=guardarReserva&guardarReserva=1',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Respuesta de reserva:', response);
                        
                        if (response.error === false) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Reserva creada!',
                                text: response.mensaje + '. Su código de seguimiento es: ' + response.codigo,
                                confirmButtonText: 'Continuar'
                            }).then(() => {
                                // Redirigir a la página de consulta
                                window.location.href = 'http://clinica.test/public_reservas/index.php?accion=consultar';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.mensaje || 'No se pudo procesar la reserva'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud AJAX:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo procesar la solicitud. Por favor intente nuevamente.'
                        });
                    }
                });
            }
        });
    });
});
</script>
