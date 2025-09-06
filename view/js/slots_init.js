/**
 * Script para inicialización de slots en la interfaz compacta
 */

$(document).ready(function() {
    console.log('slots_init.js: Modo slots paginados cargado - no sobrescribiendo función principal');
    
    // No sobrescribir la función de cargar horarios - solo proveer funcionalidad auxiliar
    if (typeof inicializarSlotsPaginados === 'function') {
        console.log('slots_init.js: Función inicializarSlotsPaginados disponible como auxiliar');
        
        // Definir función auxiliar para usar paginación cuando sea necesaria
        window.cargarHorariosPaginados = function(servicioId, doctorId, fecha) {
            if (!doctorId || !fecha) {
                console.log('slots_init.js: Parámetros faltantes para paginación');
                return;
            }
            
            $.ajax({
                url: "ajax/servicios.ajax.php",
                method: "POST",
                data: { 
                    action: "obtenerHorariosDisponibles",
                    doctor_id: doctorId,
                    fecha: fecha
                },
                dataType: "json",
                success: function(respuesta) {
                    console.log("Respuesta de slots (paginados):", respuesta);
                    if (respuesta.data && respuesta.data.length > 0) {
                        inicializarSlotsPaginados(respuesta.data);
                    }
                },
                error: function(xhr) {
                    console.error("Error al cargar horarios paginados:", xhr);
                }
            });
        };
    }
});
