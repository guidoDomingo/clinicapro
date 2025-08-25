/**
 * SCRIPT DE VERIFICACIÓN DE CAMPO TXTMOTIVO
 * ==========================================
 * Ejecutar este código en la consola del navegador para verificar 
 * que el campo txtmotivo-anteojos sea encontrado correctamente
 */

console.log('🔍 INICIANDO VERIFICACIÓN DE CAMPO TXTMOTIVO EN FORMULARIO ANTEOJOS');

// Función de prueba
function verificarCampoTxtMotivoAnteojos() {
    console.log('──────────────────────────────────');
    console.log('🧪 Verificando campo txtmotivo-anteojos...');
    
    // 1. Buscar el campo específico
    const campoTxtMotivo = document.getElementById('txtmotivo-anteojos');
    
    if (campoTxtMotivo) {
        console.log('✅ Campo txtmotivo-anteojos ENCONTRADO:', campoTxtMotivo);
        console.log('📋 Valor actual:', campoTxtMotivo.value);
        console.log('🏷️ Name attribute:', campoTxtMotivo.name);
        console.log('🎯 ID:', campoTxtMotivo.id);
        console.log('📍 Ubicación en DOM:', campoTxtMotivo.parentElement);
        
        // Probar asignar un valor de test
        campoTxtMotivo.value = 'TEST: Campo encontrado correctamente';
        console.log('🧪 Valor de prueba asignado');
        
        // Verificar que FormComponents lo puede encontrar
        if (typeof AnteojosFormComponent !== 'undefined') {
            console.log('🔧 AnteojosFormComponent disponible, probando getBasicFields...');
            
            // Crear instancia temporal para probar
            const tempComponent = new AnteojosFormComponent();
            const basicFields = tempComponent.getBasicFields();
            
            console.log('📝 Campos básicos definidos:', basicFields);
            
            if (basicFields.includes('txtmotivo-anteojos')) {
                console.log('🎉 ¡ÉXITO! El campo txtmotivo-anteojos está incluido en getBasicFields()');
            } else {
                console.error('❌ PROBLEMA: El campo txtmotivo-anteojos NO está en getBasicFields()');
            }
        } else {
            console.warn('⚠️ AnteojosFormComponent no está disponible en este contexto');
        }
        
    } else {
        console.error('❌ Campo txtmotivo-anteojos NO ENCONTRADO');
        
        // Buscar campos similares para diagnóstico
        console.log('🔍 Buscando campos similares...');
        
        const todosCamposTxtMotivo = document.querySelectorAll('input[id*="txtmotivo"], input[name*="txtmotivo"]');
        console.log('📋 Todos los campos con "txtmotivo":', todosCamposTxtMotivo);
        
        todosCamposTxtMotivo.forEach((campo, index) => {
            console.log(`${index + 1}. ID: ${campo.id}, Name: ${campo.name}, Value: "${campo.value}"`);
        });
    }
    
    console.log('──────────────────────────────────');
    return campoTxtMotivo;
}

// Función para verificar el formulario activo
function verificarFormularioActivo() {
    console.log('🎯 Verificando qué formulario está activo...');
    
    const formularios = ['general', 'anteojos', 'estudios', 'informe_imagen'];
    
    formularios.forEach(tipo => {
        const formulario = document.getElementById(`formulario-${tipo}`);
        if (formulario) {
            const esVisible = formulario.style.display !== 'none' && 
                             !formulario.classList.contains('d-none') && 
                             !formulario.classList.contains('hidden');
            
            console.log(`📋 Formulario ${tipo}: ${esVisible ? '👁️ VISIBLE' : '🙈 OCULTO'}`);
        }
    });
}

// Función completa de diagnóstico
function diagnosticoCompleto() {
    console.log('🚀 INICIANDO DIAGNÓSTICO COMPLETO');
    console.log('═══════════════════════════════════');
    
    verificarFormularioActivo();
    const campo = verificarCampoTxtMotivoAnteojos();
    
    console.log('═══════════════════════════════════');
    
    if (campo) {
        console.log('🎉 DIAGNÓSTICO: ¡CAMPO TXTMOTIVO-ANTEOJOS FUNCIONAL!');
        return true;
    } else {
        console.log('❌ DIAGNÓSTICO: CAMPO TXTMOTIVO-ANTEOJOS NO FUNCIONAL');
        return false;
    }
}

// Ejecutar diagnóstico automáticamente
diagnosticoCompleto();

// Exponer funciones globalmente para uso manual
window.verificarCampoTxtMotivoAnteojos = verificarCampoTxtMotivoAnteojos;
window.verificarFormularioActivo = verificarFormularioActivo;
window.diagnosticoCompleto = diagnosticoCompleto;