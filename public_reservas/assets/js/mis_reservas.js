/**
 * Script para la visualización de reservas del paciente
 * Carga y muestra las reservas del paciente logueado
 */

// Cargar todas las reservas del paciente cuando se carga el documento
document.addEventListener('DOMContentLoaded', function() {
    // Asegurar que jQuery está cargado
    if (typeof jQuery !== 'undefined') {
        cargarReservasPaciente();
    } else {
        console.error('jQuery no está disponible');
        document.getElementById('cargando').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Error: No se pudo cargar jQuery. 
                Por favor recargue la página o contacte al administrador.
            </div>
        `;
    }
});

/**
 * Carga todas las reservas del paciente actual desde el servidor
 */
function cargarReservasPaciente() {
    jQuery('#cargando').removeClass('d-none');
    jQuery('#reservasContainer').addClass('d-none');
    jQuery('#sinReservas').addClass('d-none');
    
    jQuery.ajax({
        url: 'ajax/reservas.ajax.php',
        type: 'POST',
        data: {
            action: 'obtenerReservasPaciente'
        },
        dataType: 'json',
        success: function(response) {
            jQuery('#cargando').addClass('d-none');
            
            if (response.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.mensaje
                });
                return;
            }
            
            if (response.reservas && response.reservas.length > 0) {
                jQuery('#reservasContainer').removeClass('d-none');
                
                // Vaciar los contenedores
                jQuery('#pendientes-container').empty();
                jQuery('#completadas-container').empty();
                jQuery('#canceladas-container').empty();
                jQuery('#todas-container').empty();
                
                // Organizar reservas por estado
                const reservasPendientes = [];
                const reservasCompletadas = [];
                const reservasCanceladas = [];
                
                // Procesar cada reserva
                response.reservas.forEach(function(reserva) {
                    // Crear la card para esta reserva
                    const reservaCard = crearReservaCard(reserva);
                    
                    // Añadir a la lista correspondiente según estado
                    if (reserva.reserva_estado === 'PENDIENTE') {
                        reservasPendientes.push(reservaCard);
                    } else if (reserva.reserva_estado === 'COMPLETADA' || reserva.reserva_estado === 'ATENDIDA') {
                        reservasCompletadas.push(reservaCard);
                    } else if (reserva.reserva_estado === 'CANCELADA') {
                        reservasCanceladas.push(reservaCard);
                    }
                    
                    // Añadir a la lista de todas las reservas
                    jQuery('#todas-container').append(reservaCard.clone());
                });
                
                // Actualizar contadores y mostrar las reservas en cada tab
                if (reservasPendientes.length > 0) {
                    jQuery('#pendientes-tab').html(`<i class="fas fa-clock mr-1"></i> Pendientes (${reservasPendientes.length})`);
                    reservasPendientes.forEach(card => jQuery('#pendientes-container').append(card));
                } else {
                    jQuery('#pendientes-container').html('<div class="alert alert-info">No tienes reservas pendientes</div>');
                }
                
                if (reservasCompletadas.length > 0) {
                    jQuery('#completadas-tab').html(`<i class="fas fa-check-circle mr-1"></i> Completadas (${reservasCompletadas.length})`);
                    reservasCompletadas.forEach(card => jQuery('#completadas-container').append(card));
                } else {
                    jQuery('#completadas-container').html('<div class="alert alert-info">No tienes reservas completadas</div>');
                }
                
                if (reservasCanceladas.length > 0) {
                    jQuery('#canceladas-tab').html(`<i class="fas fa-times-circle mr-1"></i> Canceladas (${reservasCanceladas.length})`);
                    reservasCanceladas.forEach(card => jQuery('#canceladas-container').append(card));
                } else {
                    jQuery('#canceladas-container').html('<div class="alert alert-info">No tienes reservas canceladas</div>');
                }
                
                jQuery('#todas-tab').html(`<i class="fas fa-list mr-1"></i> Todas (${response.reservas.length})`);
                
            } else {
                jQuery('#sinReservas').removeClass('d-none');
            }
        },
        error: function(xhr, status, error) {
            jQuery('#cargando').addClass('d-none');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al cargar las reservas: ' + error
            });
        }
    });
}

/**
 * Crea una tarjeta HTML para mostrar los datos de una reserva
 * @param {Object} reserva Datos de la reserva
 * @returns {jQuery} Elemento jQuery con la tarjeta de reserva
 */
function crearReservaCard(reserva) {
    // Clonar la plantilla
    const template = document.querySelector('#reserva-card-template');
    const card = document.importNode(template.content, true).querySelector('.reserva-card');
    
    // Formatear la fecha de YYYY-MM-DD a DD/MM/YYYY
    const fechaOriginal = reserva.fecha_reserva;
    const partesFecha = fechaOriginal.split('-');
    const fechaFormateada = `${partesFecha[2]}/${partesFecha[1]}/${partesFecha[0]}`;
    
    // Llenar los datos básicos
    card.querySelector('.fecha-reserva').innerHTML += fechaFormateada;
    card.querySelector('.servicio-nombre').textContent = reserva.nombre_servicio;
    card.querySelector('.doctor-nombre').textContent = reserva.doctor;
    card.querySelector('.horario-reserva').textContent = reserva.horario;
    card.querySelector('.sala-nombre').textContent = reserva.sala_nombre || 'No especificada';
    card.querySelector('.servicio-monto').textContent = Number(reserva.monto).toLocaleString('es-PY');
    
    // Mostrar código de seguimiento
    card.querySelector('.codigo-seguimiento .badge').textContent = reserva.codigo_seguimiento;
    
    // Configurar el estado y el badge
    const estadoBadge = card.querySelector('.estado-badge');
    estadoBadge.textContent = reserva.reserva_estado;
    
    if (reserva.reserva_estado === 'PENDIENTE') {
        estadoBadge.classList.add('badge', 'badge-warning');
        
        // Añadir botón para cancelar cita
        const btnCancelar = document.createElement('button');
        btnCancelar.className = 'btn btn-sm btn-danger';
        btnCancelar.innerHTML = '<i class="fas fa-times-circle mr-1"></i>Cancelar';
        btnCancelar.onclick = function() { 
            cancelarReserva(reserva.codigo_seguimiento);
        };
        card.querySelector('.acciones-reserva').appendChild(btnCancelar);
        
    } else if (reserva.reserva_estado === 'COMPLETADA' || reserva.reserva_estado === 'ATENDIDA') {
        estadoBadge.classList.add('badge', 'badge-success');
    } else if (reserva.reserva_estado === 'CANCELADA') {
        estadoBadge.classList.add('badge', 'badge-danger');
        card.classList.add('bg-light');
    } else {
        estadoBadge.classList.add('badge', 'badge-secondary');
    }
    
    return jQuery(card);
}

/**
 * Cancela una reserva mediante AJAX
 * @param {string} codigo Código de seguimiento de la reserva
 */
function cancelarReserva(codigo) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta acción no se puede deshacer. Se cancelará tu reserva.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            jQuery.ajax({
                url: 'ajax/reservas.ajax.php',
                type: 'POST',
                data: {
                    action: 'cancelarReserva',
                    codigo: codigo
                },
                dataType: 'json',
                success: function(response) {
                    if (response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.mensaje
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Reserva cancelada',
                            text: 'Tu reserva ha sido cancelada correctamente.'
                        }).then(() => {
                            // Recargar las reservas
                            cargarReservasPaciente();
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al cancelar la reserva: ' + error
                    });
                }
            });
        }
    });
}
