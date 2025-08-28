// Test para cargar datos históricos
console.log('🧪 Probando carga de datos históricos...');

// Función para probar los datos históricos de un paciente
async function probarDatosHistoricos(patientId = 60) { // ID de alejandro visconte
    console.log(`🔍 Probando con paciente ID: ${patientId}`);
    
    try {
        const response = await fetch('./modules/consultas/api/livwire-crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'loadPatientHistory',
                data: { id: patientId }
            })
        });
        
        const result = await response.json();
        console.log('📊 Resultado completo:', result);
        
        if (result.success) {
            const data = result.data;
            console.log(`📈 Consultas: ${data.consultas}`);
            console.log(`💾 Cuota MB: ${data.cuota_mb}`);
            console.log(`📋 Historial (${data.historial.length} items):`, data.historial);
            console.log(`📅 Timeline (${data.timeline.length} items):`, data.timeline);
            console.log(`📎 Archivos (${data.archivos.length} items):`, data.archivos);
        } else {
            console.log('❌ Error:', result.message);
        }
        
    } catch (error) {
        console.error('❌ Error en prueba:', error);
    }
}

// Ejecutar prueba automáticamente
probarDatosHistoricos();

// También exponer para uso manual
window.probarDatosHistoricos = probarDatosHistoricos;