/**
 * DEBUGGING TOOLS PARA SISTEMA DE EDICIÓN
 */

// Función de debug global
window.debugEditSystem = function() {
    console.group('🔧 DEBUG - Sistema de Edición');
    
    // 1. Verificar si ConsultasManager está disponible
    console.log('1. ConsultasManager disponible:', !!window.consultasManager);
    if (window.consultasManager) {
        console.log('   - Estado actual:', window.consultasManager.state);
        console.log('   - Método editConsulta:', typeof window.consultasManager.editConsulta);
    }
    
    // 2. Verificar funciones globales
    console.log('2. editarConsultaGenerico:', typeof window.editarConsultaGenerico);
    console.log('3. editarConsulta:', typeof window.editarConsulta);
    
    // 4. Buscar botones de editar
    const editButtons = document.querySelectorAll('.editar-consulta, .btn-editar, [data-action="edit"]');
    console.log('4. Botones de editar encontrados:', editButtons.length);
    editButtons.forEach((btn, i) => {
        console.log(`   Botón ${i+1}:`, {
            element: btn,
            id: btn.dataset.id,
            idpersona: btn.dataset.idpersona,
            classes: btn.className
        });
    });
    
    // 5. Verificar event listeners
    console.log('5. Event listeners configurados en document');
    
    // 6. Probar función directamente
    console.log('6. Probando función directa...');
    if (window.editarConsultaGenerico) {
        console.log('   Función existe, probando con datos de ejemplo...');
        // No ejecutamos realmente, solo reportamos
    }
    
    console.groupEnd();
    
    return {
        consultasManager: !!window.consultasManager,
        editFunction: typeof window.editarConsultaGenerico,
        buttonCount: editButtons.length
    };
};

// Función para probar edición con datos reales
window.testEditConsulta = function(idConsulta, idPersona) {
    console.log('🧪 TESTING - Edición de consulta:', { idConsulta, idPersona });
    
    if (!window.editarConsultaGenerico) {
        console.error('❌ Función editarConsultaGenerico no disponible');
        return false;
    }
    
    try {
        window.editarConsultaGenerico(idConsulta, idPersona);
        return true;
    } catch (error) {
        console.error('❌ Error ejecutando edición:', error);
        return false;
    }
};

// Función para simular clic en botón
window.simulateEditClick = function(buttonSelector) {
    const button = document.querySelector(buttonSelector);
    if (button) {
        console.log('🖱️ Simulando clic en:', button);
        button.click();
        return true;
    } else {
        console.error('❌ Botón no encontrado:', buttonSelector);
        return false;
    }
};

// Auto-ejecutar debug cuando se cargue
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        console.log('🔍 Auto-debug del sistema de edición...');
        window.debugEditSystem();
    }, 2000);
});

console.log('✅ Herramientas de debug cargadas. Usa: debugEditSystem(), testEditConsulta(id, persona), simulateEditClick(selector)');