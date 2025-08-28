// Test directo en consola del navegador
// Copia y pega esto en la consola del navegador (F12)

console.log('🧪 Probando búsqueda directamente...');

// Verificar si existe el objeto
if (window.livewirePatient) {
    console.log('✅ window.livewirePatient disponible');
    
    // Probar búsqueda directamente
    window.livewirePatient.validateField('search_nombre', 'visconte')
        .then(() => console.log('✅ Búsqueda directa funcionó'))
        .catch(err => console.error('❌ Error en búsqueda directa:', err));
} else {
    console.log('❌ window.livewirePatient NO disponible');
}

// Verificar campo de entrada
const campo = document.getElementById('paciente');
if (campo) {
    console.log('✅ Campo #paciente encontrado');
    console.log('Valor actual:', campo.value);
    
    // Simular escritura
    campo.value = 'visconte';
    campo.dispatchEvent(new Event('input', { bubbles: true }));
    console.log('📝 Texto simulado enviado');
} else {
    console.log('❌ Campo #paciente NO encontrado');
}