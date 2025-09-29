/**
 * Script temporal para interceptar y debuggear la generación de botones
 * Añadir al final de servicios.js temporalmente
 */

// Interceptar la función renderizarTablaReservas para debug
const renderizarOriginal = window.renderizarTablaReservas;

window.renderizarTablaReservas = function(respuesta) {
    console.log("🔍 INTERCEPTANDO renderizarTablaReservas");
    console.log("Datos recibidos:", respuesta);
    
    if (respuesta.status === "success" && respuesta.data && respuesta.data.length > 0) {
        respuesta.data.forEach(function(reserva, index) {
            const estadoOriginal = reserva.reserva_estado;
            const esCancelada = (estadoOriginal === 'CANCELADA');
            
            console.log(`\n📋 Reserva ${index + 1} (ID: ${reserva.reserva_id}):`);
            console.log(`   Estado: ${estadoOriginal}`);
            console.log(`   esCancelada: ${esCancelada}`);
            console.log(`   Activo: ${reserva.activo}`);
            
            if (esCancelada) {
                console.log("   🔒 Esta reserva debería tener botones DESHABILITADOS");
                
                // Generar HTML de muestra para verificar
                const htmlMuestra = `<button class="btn btn-secondary btn-sm" disabled title="Reserva cancelada">
                    <i class="fas fa-edit"></i>
                </button>`;
                console.log("   HTML esperado:", htmlMuestra);
            }
        });
    }
    
    // Llamar a la función original
    if (renderizarOriginal) {
        return renderizarOriginal(respuesta);
    }
};

console.log("🚀 Interceptor de renderizarTablaReservas activado");