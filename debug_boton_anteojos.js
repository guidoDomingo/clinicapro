console.log('🚀 Testing botón actualizar consulta...');

// Verificar que el botón existe
const btnAnteojos = document.getElementById('btnGuardarConsulta-anteojos');
console.log('📋 Botón anteojos encontrado:', btnAnteojos ? '✅ SÍ' : '❌ NO');

if (btnAnteojos) {
    console.log('📋 Onclick del botón:', btnAnteojos.getAttribute('onclick'));
    console.log('📋 Wire:click del botón:', btnAnteojos.getAttribute('wire:click'));
}

// Verificar funciones disponibles
console.log('🔧 Funciones disponibles:');
console.log('- guardarConsultaConLivewire:', typeof window.guardarConsultaConLivewire);
console.log('- Livewire global:', typeof window.Livewire);
console.log('- livewireCRUD:', typeof window.livewireCRUD);

// Verificar campos con wire:model
const camposImportantes = [
    'od_esf', 'od_cil', 'od_eje', 'od_dnp', 'od_add', 'od_altura', 'od_nota',
    'oi_esf', 'oi_cil', 'oi_eje', 'oi_dnp', 'oi_add', 'oi_altura', 'oi_nota',
    'dist_interpupilar', 'consulta_textarea', 'receta_textarea', 
    'txtnota', 'proximaconsulta', 'whatsapptxt', 'email'
];

console.log('🔍 Verificando campos wire:model:');
camposImportantes.forEach(campo => {
    const elemento = document.getElementById(campo) || 
                    document.getElementById(`${campo}-anteojos`) ||
                    document.getElementById(`consulta-textarea-anteojos`) ||
                    document.getElementById(`receta-textarea-anteojos`);
    if (elemento) {
        const wireModel = elemento.getAttribute('wire:model');
        console.log(`  ${campo}: ${wireModel ? '✅ ' + wireModel : '❌ Sin wire:model'}`);
    } else {
        console.log(`  ${campo}: ❌ Elemento no encontrado`);
    }
});

// Simular click en el botón (para testing)
window.testButtonClick = function() {
    console.log('🧪 Simulando click en botón anteojos...');
    if (btnAnteojos) {
        btnAnteojos.click();
    } else {
        console.error('❌ Botón no encontrado para testing');
    }
};

console.log('✅ Debug script loaded. Para probar: testButtonClick()');