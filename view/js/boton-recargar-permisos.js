/**
 * Función para agregar botón de recarga de permisos a la interfaz
 */
function agregarBotonRecargarPermisos() {
    // Verificar si ya existe el botón
    if ($('#btnRecargarPermisos').length > 0) {
        return;
    }
    
    // Buscar un lugar apropiado para agregar el botón (cerca de los filtros)
    const $contenedorFiltros = $('.card-tools, .btn-group').first();
    
    if ($contenedorFiltros.length > 0) {
        const botonHTML = `
            <button id="btnRecargarPermisos" class="btn btn-outline-secondary btn-sm ml-2" 
                    title="Recargar permisos de usuario">
                <i class="fas fa-sync-alt"></i> Permisos
            </button>
        `;
        
        $contenedorFiltros.append(botonHTML);
        
        // Agregar evento al botón
        $('#btnRecargarPermisos').on('click', function() {
            const $boton = $(this);
            const iconoOriginal = $boton.find('i');
            
            // Mostrar loading
            iconoOriginal.removeClass('fa-sync-alt').addClass('fa-spinner fa-spin');
            $boton.prop('disabled', true);
            
            recargarPermisosYActualizarInterfaz()
                .then(function() {
                    if (typeof mostrarAlerta === 'function') {
                        mostrarAlerta('success', 'Permisos actualizados correctamente');
                    } else {
                        console.log('✅ Permisos actualizados correctamente');
                    }
                })
                .catch(function(error) {
                    if (typeof mostrarAlerta === 'function') {
                        mostrarAlerta('error', 'Error al actualizar permisos: ' + error);
                    } else {
                        console.error('❌ Error al actualizar permisos:', error);
                    }
                })
                .finally(function() {
                    // Restaurar botón
                    iconoOriginal.removeClass('fa-spinner fa-spin').addClass('fa-sync-alt');
                    $boton.prop('disabled', false);
                });
        });
        
        console.log('✅ Botón de recarga de permisos agregado');
    }
}

// Agregar el botón cuando se carga la página
$(document).ready(function() {
    setTimeout(agregarBotonRecargarPermisos, 1000); // Esperar un poco para que se cargue la interfaz
});