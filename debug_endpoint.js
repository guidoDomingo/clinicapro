// Script de debug para endpoint
console.log('🔧 DEBUG - Verificación de Endpoint');

// Verificar URL actual
console.log('📍 URL actual:', window.location.href);
console.log('📍 Origin:', window.location.origin);
console.log('📍 Pathname:', window.location.pathname);

// Calcular endpoint esperado
const baseURL = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '');
const expectedEndpoint = baseURL + '/modules/consultas/api/livwire-crud.php';

console.log('🎯 Endpoint esperado:', expectedEndpoint);

// Verificar instancias de Livwire
if (window.livwireAPI) {
    console.log('✅ livwireAPI disponible:', window.livwireAPI);
    
    if (window.livwireAPI.crud && window.livwireAPI.crud.config) {
        console.log('📡 Endpoint actual del CRUD:', window.livwireAPI.crud.config.endpoint);
    }
    
    if (window.livwireAPI.crud && window.livwireAPI.crud._instance) {
        console.log('⚙️ Config de instancia:', window.livwireAPI.crud._instance.config);
    }
} else {
    console.log('❌ livwireAPI no disponible');
}

// Probar endpoint manualmente
async function testEndpoint() {
    console.log('🧪 Probando endpoint manualmente...');
    
    try {
        const response = await fetch(expectedEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'test',
                data: {}
            })
        });
        
        console.log('📡 Respuesta:', response.status, response.statusText);
        
        const text = await response.text();
        console.log('📄 Contenido:', text.substring(0, 200));
        
    } catch (error) {
        console.error('❌ Error probando endpoint:', error);
    }
}

// Probar automáticamente
setTimeout(testEndpoint, 1000);

console.log('🔧 Debug de endpoint cargado - ejecutar testEndpoint() para probar manualmente');