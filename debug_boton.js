// Script de debug para el botón actualizar
console.log('🔧 === DEBUG BOTÓN ACTUALIZAR ===');

// 1. Verificar si el botón existe
const btnAnteojos = document.getElementById('btnGuardarConsulta-anteojos');
console.log('1️⃣ Botón encontrado:', btnAnteojos ? '✅ SÍ' : '❌ NO');

if (btnAnteojos) {
    console.log('📍 Botón HTML:', btnAnteojos.outerHTML);
    console.log('📍 Botón visible:', btnAnteojos.offsetWidth > 0 && btnAnteojos.offsetHeight > 0);
    console.log('📍 Botón habilitado:', !btnAnteojos.disabled);
} else {
    // Buscar todos los botones relacionados
    console.log('🔍 Buscando botones similares...');
    const allButtons = document.querySelectorAll('button[id*="Guardar"], button[id*="anteojos"]');
    console.log('🔍 Botones encontrados:', allButtons.length);
    allButtons.forEach((btn, i) => {
        console.log(`  ${i+1}. ID: ${btn.id}, Texto: ${btn.textContent.trim()}`);
    });
}

// 2. Verificar formulario activo
const formAnteojos = document.getElementById('formulario-anteojos');
console.log('2️⃣ Formulario anteojos:', formAnteojos ? '✅ Encontrado' : '❌ NO encontrado');
if (formAnteojos) {
    console.log('📍 Formulario visible:', formAnteojos.style.display !== 'none');
    console.log('📍 Clases del formulario:', formAnteojos.className);
}

// 3. Verificar si estamos en modo edición
const consultaId = document.getElementById('id_consulta_actual')?.value;
console.log('3️⃣ ID consulta actual:', consultaId || 'NO DEFINIDO');
console.log('3️⃣ Modo edición:', consultaId && consultaId !== '' && consultaId !== '0' ? '✅ SÍ' : '❌ NO');

// 4. Verificar campos importantes
console.log('4️⃣ Campos del formulario:');
const campos = [
    'idPersona', 'txtmotivo-anteojos', 'od_esf', 'od_cil', 'oi_esf', 'oi_cil',
    'consulta-textarea-anteojos', 'txtnota', 'proximaconsulta'
];

campos.forEach(campo => {
    const elemento = document.getElementById(campo);
    const valor = elemento?.value || 'VACÍO';
    console.log(`   ${campo}: ${elemento ? '✅' : '❌'} → ${valor}`);
});

// 5. Verificar event listeners
console.log('5️⃣ Event listeners:');
if (btnAnteojos) {
    // Intentar agregar un event listener de prueba
    const testListener = function(e) {
        console.log('🎯 CLICK DETECTADO EN BOTÓN ANTEOJOS!');
        console.log('🎯 Event:', e);
        console.log('🎯 Target:', e.target);
        console.log('🎯 CurrentTarget:', e.currentTarget);
    };
    
    btnAnteojos.addEventListener('click', testListener);
    console.log('✅ Test listener agregado');
    
    // Test manual
    window.testButtonClick = function() {
        console.log('🧪 Simulando click manual...');
        btnAnteojos.click();
    };
    
    console.log('🧪 Para probar manualmente: testButtonClick()');
}

// 6. Verificar formularios array
console.log('6️⃣ Verificando configuración de formularios...');
setTimeout(() => {
    // Buscar en el contexto global si hay información sobre formularios
    if (window.formulariosConfig) {
        console.log('📋 Config formularios:', window.formulariosConfig);
    }
    
    // Buscar todos los event listeners registrados
    console.log('📋 Todos los botones con event listeners:');
    document.querySelectorAll('button').forEach((btn, i) => {
        if (btn.onclick || btn._listeners) {
            console.log(`  ${i+1}. ${btn.id}: ${btn.textContent.trim()}`);
        }
    });
}, 1000);

console.log('🏁 Debug completado. Revisa la consola para identificar problemas.');