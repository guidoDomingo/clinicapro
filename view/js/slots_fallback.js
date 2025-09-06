/**
 * Fallback para slots en caso de que la paginación no esté disponible
 */

// Verificar si existe la función de paginación de slots
if (typeof inicializarSlotsPaginados !== 'function') {
    console.log('Utilizando visualización simple de slots (sin paginación)');
    
    /**
     * Función fallback para mostrar slots sin paginación
     * @param {Array} slots Lista de slots de horario
     */
    function mostrarSlotsSimples(slots) {
        // Construir HTML para mostrar todos los slots
        let html = '<div class="row">';
        
        slots.forEach(function(slot) {                    // Determinar si el slot está disponible
                    const disponible = slot.disponible !== false; // Por defecto, asumimos disponible
                    const claseDisponibilidad = disponible ? '' : 'no-disponible';
                    
                    // Formatear las horas para mostrar (HH:MM)
                    const horaInicio = slot.hora_inicio ? slot.hora_inicio.substring(0, 5) : '??:??';
                    const horaFin = slot.hora_fin ? slot.hora_fin.substring(0, 5) : '??:??';
                    
                    // Nombre de la sala
                    const nombreSala = slot.sala_nombre || 'Sin sala asignada';
                    
                    html += `
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="slot-horario ${claseDisponibilidad}" 
                                 data-id="${slot.horario_id || slot.id || ''}"
                                 data-inicio="${slot.hora_inicio || ''}"
                                 data-fin="${slot.hora_fin || ''}"
                                 data-texto="${horaInicio} - ${horaFin}"
                                 data-sala="${nombreSala}">
                                <p class="mb-1 text-center"><strong>${horaInicio} - ${horaFin}</strong></p>
                                <p class="mb-0 text-center"><small>${nombreSala}</small></p>
                            </div>
                        </div>
                    `;
        });
        
        html += '</div>';
        
        // Insertar en el contenedor
        $('#slotsPaginados').html(html);
    }
    
    // NO sobrescribir la función de cargar horarios - solo proveer fallback cuando sea necesario
    $(document).ready(function() {
        console.log('slots_fallback.js: Modo fallback cargado - no sobrescribiendo función principal');
        
        // Solo definir funciones auxiliares para el fallback
        window.mostrarSlotsSimplesFallback = function(slots) {
            // Construir HTML para mostrar todos los slots
            let html = '<div class="row">';
            
            slots.forEach(function(slot) {
                // Determinar si el slot está disponible
                const disponible = slot.disponible !== false; // Por defecto, asumimos disponible
                const claseDisponibilidad = disponible ? '' : 'no-disponible';
                
                // Formatear las horas para mostrar (HH:MM)
                const horaInicio = slot.hora_inicio ? slot.hora_inicio.substring(0, 5) : '??:??';
                const horaFin = slot.hora_fin ? slot.hora_fin.substring(0, 5) : '??:??';
                
                // Nombre de la sala
                const nombreSala = slot.sala_nombre || 'Sin sala asignada';
                
                html += `
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="slot-horario ${claseDisponibilidad}" 
                             data-id="${slot.horario_id || slot.id || ''}"
                             data-inicio="${slot.hora_inicio || ''}"
                             data-fin="${slot.hora_fin || ''}"
                             data-texto="${horaInicio} - ${horaFin}"
                             data-sala="${nombreSala}">
                            <p class="mb-1 text-center"><strong>${horaInicio} - ${horaFin}</strong></p>
                            <p class="mb-0 text-center"><small>${nombreSala}</small></p>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            
            // Insertar en el contenedor
            $('#slotsPaginados').html(html);
        };
    });
}
