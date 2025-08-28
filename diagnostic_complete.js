// Script de diagnóstico directo
console.log('🔍 Diagnóstico del sistema de búsqueda...');

// 1. Verificar si el objeto principal existe
console.log('1. Verificando window.livewirePatient:', !!window.livewirePatient);

// 2. Verificar si el campo existe
const campo = document.getElementById('paciente');
console.log('2. Campo #paciente existe:', !!campo);
if (campo) {
    console.log('   - Placeholder:', campo.placeholder);
    console.log('   - Eventos registrados:', getEventListeners ? getEventListeners(campo) : 'No disponible');
}

// 3. Verificar otros campos relacionados
const campoDoc = document.getElementById('txtdocumento');
const campoFicha = document.getElementById('txtficha');
console.log('3. Campo #txtdocumento existe:', !!campoDoc);
console.log('4. Campo #txtficha existe:', !!campoFicha);

// 4. Probar manualmente la función de búsqueda
if (window.livewirePatient && window.livewirePatient.validateField) {
    console.log('5. Probando validateField directamente...');
    window.livewirePatient.validateField('search_nombre', 'visconte')
        .then(result => console.log('✅ validateField funcionó:', result))
        .catch(error => console.error('❌ validateField falló:', error));
} else {
    console.log('5. ❌ validateField no está disponible');
}

// 5. Probar el evento manualmente
if (campo) {
    console.log('6. Agregando evento manual...');
    campo.addEventListener('input', function(e) {
        console.log('🎯 Evento input detectado:', e.target.value);
    });
    
    // Simular evento
    campo.value = 'test';
    const evento = new Event('input', { bubbles: true });
    campo.dispatchEvent(evento);
}

console.log('✅ Diagnóstico completado');