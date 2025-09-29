// Test para verificar si la cancelación funciona y si las consultas filtran correctamente

// 1. Cancelar una reserva específica
function testCancelarReserva(reservaId) {
    console.log('🧪 Iniciando test de cancelación para reserva:', reservaId);
    
    // Primero verificar estado actual
    $.ajax({
        url: 'ajax/servicios.ajax.php',
        method: 'POST',
        data: {
            action: 'buscarReservas',
            fecha: '2025-09-22'
        },
        dataType: 'json',
        success: function(respuesta) {
            console.log('📊 Reservas ANTES de cancelar:', respuesta.data);
            
            // Buscar la reserva específica
            const reservaAntes = respuesta.data.find(r => r.reserva_id == reservaId);
            if (reservaAntes) {
                console.log('✅ Reserva encontrada antes de cancelar:', reservaAntes);
            } else {
                console.log('❌ Reserva NO encontrada antes de cancelar');
                return;
            }
            
            // Proceder con la cancelación
            procesarCancelacionReserva(reservaId, 'Test de cancelación')
                .then(resultado => {
                    console.log('🎯 Resultado de cancelación:', resultado);
                    
                    if (resultado.success) {
                        // Verificar que ya no aparece en la lista
                        setTimeout(() => {
                            $.ajax({
                                url: 'ajax/servicios.ajax.php',
                                method: 'POST',
                                data: {
                                    action: 'buscarReservas',
                                    fecha: '2025-09-22'
                                },
                                dataType: 'json',
                                success: function(respuestaDespues) {
                                    console.log('📊 Reservas DESPUÉS de cancelar:', respuestaDespues.data);
                                    
                                    const reservaDespues = respuestaDespues.data.find(r => r.reserva_id == reservaId);
                                    if (reservaDespues) {
                                        console.log('❌ ERROR: La reserva TODAVÍA aparece en la lista');
                                        console.log('🔍 Datos de la reserva que no debería aparecer:', reservaDespues);
                                    } else {
                                        console.log('✅ ÉXITO: La reserva ya NO aparece en la lista');
                                    }
                                },
                                error: function(error) {
                                    console.error('Error al verificar estado después:', error);
                                }
                            });
                        }, 1000); // Esperar 1 segundo para que la BD se actualice
                    }
                })
                .catch(error => {
                    console.error('❌ Error en cancelación:', error);
                });
        },
        error: function(error) {
            console.error('Error al obtener reservas iniciales:', error);
        }
    });
}

// Ejecutar test automáticamente
console.log('🚀 Ejecutando test de cancelación...');
// testCancelarReserva(114); // Descomentar para ejecutar

// También agregar función para verificar estado en BD directamente
function verificarEstadoBD(reservaId) {
    console.log('🔍 Verificando estado en BD para reserva:', reservaId);
    
    $.ajax({
        url: 'diagnostico_reserva.php',
        method: 'GET',
        success: function(html) {
            console.log('📋 Diagnóstico de BD obtenido');
            // Crear un div temporal para mostrar el resultado
            const $temp = $('<div>').html(html);
            console.log('📄 Contenido del diagnóstico:', $temp.text());
        },
        error: function(error) {
            console.error('Error al obtener diagnóstico:', error);
        }
    });
}

// Instrucciones de uso:
console.log(`
🎮 INSTRUCCIONES DE TEST:
1. testCancelarReserva(114) - Para probar cancelación
2. verificarEstadoBD(114) - Para ver estado en BD
3. Verificar logs en consola para ver resultados
`);