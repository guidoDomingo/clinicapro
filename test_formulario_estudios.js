/**
 * SCRIPT DE VERIFICACIÓN ESPECÍFICO PARA FORMULARIO DE ESTUDIOS
 * ============================================================
 * Ejecutar este código en la consola del navegador para verificar
 * que el formulario de estudios esté funcionando correctamente
 */

console.log('🔬 INICIANDO VERIFICACIÓN DEL FORMULARIO DE ESTUDIOS');

function verificarFormularioEstudios() {
    console.log('═══════════════════════════════════════════════════');
    console.log('🧪 VERIFICANDO FORMULARIO DE ESTUDIOS');
    console.log('═══════════════════════════════════════════════════');
    
    // 1. Verificar que el formulario de estudios esté visible
    const formulario = document.getElementById('formulario-estudios');
    console.log('📋 Formulario estudios encontrado:', !!formulario);
    
    if (formulario) {
        const esVisible = formulario.style.display !== 'none' && 
                         !formulario.classList.contains('d-none');
        console.log('👁️ Formulario estudios visible:', esVisible);
    }
    
    // 2. Verificar campos específicos del formulario de estudios
    const camposEsperados = [
        'txtmotivo-estudios',           // Campo motivo
        'equipo_medico-estudios',       // Campo equipo médico (tipo_estudio)
        'consulta-textarea-estudios',   // Campo observaciones
        'txtnota-estudios'              // Campo nota
    ];
    
    console.log('🔍 Verificando campos específicos:');
    console.log('──────────────────────────────────');
    
    let camposEncontrados = 0;
    let camposFaltantes = [];
    
    camposEsperados.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        
        if (field) {
            camposEncontrados++;
            console.log(`✅ Campo '${fieldId}' encontrado`, {
                tipo: field.tagName.toLowerCase(),
                valor: field.value || '(vacío)',
                name: field.name || '(sin name)'
            });
        } else {
            camposFaltantes.push(fieldId);
            console.error(`❌ Campo '${fieldId}' NO encontrado`);
        }
    });
    
    console.log('──────────────────────────────────');
    console.log(`📊 RESUMEN: ${camposEncontrados}/${camposEsperados.length} campos encontrados`);
    
    if (camposFaltantes.length > 0) {
        console.warn('⚠️ Campos faltantes:', camposFaltantes);
    }
    
    // 3. Probar el EstudiosFormComponent si está disponible
    if (typeof EstudiosFormComponent !== 'undefined') {
        console.log('──────────────────────────────────');
        console.log('🔧 Probando EstudiosFormComponent...');
        
        try {
            const tempComponent = new EstudiosFormComponent();
            const basicFields = tempComponent.getBasicFields();
            
            console.log('📝 Campos básicos definidos:', basicFields);
            
            // Verificar si todos los campos básicos existen
            let camposBasicosEncontrados = 0;
            basicFields.forEach(fieldId => {
                if (document.getElementById(fieldId)) {
                    camposBasicosEncontrados++;
                    console.log(`✅ Campo básico '${fieldId}' encontrado`);
                } else {
                    console.error(`❌ Campo básico '${fieldId}' NO encontrado`);
                }
            });
            
            console.log(`📊 Campos básicos: ${camposBasicosEncontrados}/${basicFields.length} encontrados`);
            
        } catch (error) {
            console.error('💥 Error probando EstudiosFormComponent:', error);
        }
    } else {
        console.warn('⚠️ EstudiosFormComponent no disponible en este contexto');
    }
    
    // 4. Probar simulación de datos
    console.log('──────────────────────────────────');
    console.log('🧪 Simulando recolección de datos...');
    
    const datosSimulados = {};
    
    // Simular lo que haría getFormData()
    camposEsperados.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            datosSimulados[fieldId] = field.value || `TEST_${fieldId}`;
        }
    });
    
    console.log('📊 Datos que se recolectarían:', datosSimulados);
    
    // 5. Verificar mapeo específico
    console.log('──────────────────────────────────');
    console.log('🗺️ Verificando mapeo de campos específicos...');
    
    const equipoMedico = document.getElementById('equipo_medico-estudios');
    if (equipoMedico) {
        console.log('✅ equipo_medico-estudios → tipo_estudio:', equipoMedico.value || '(vacío)');
    } else {
        console.error('❌ equipo_medico-estudios NO encontrado para mapeo a tipo_estudio');
    }
    
    const observaciones = document.getElementById('consulta-textarea-estudios');
    if (observaciones) {
        console.log('✅ consulta-textarea-estudios → observaciones:', observaciones.value || '(vacío)');
    } else {
        console.error('❌ consulta-textarea-estudios NO encontrado para mapeo a observaciones');
    }
    
    console.log('═══════════════════════════════════════════════════');
    
    // Resultado final
    const todosFuncionan = camposFaltantes.length === 0;
    
    if (todosFuncionan) {
        console.log('🎉 ¡ÉXITO! El formulario de estudios está completamente funcional');
        return true;
    } else {
        console.log('❌ PROBLEMAS: El formulario de estudios tiene campos faltantes');
        return false;
    }
}

// Función para llenar datos de prueba
function llenarDatosPrueba() {
    console.log('🧪 Llenando datos de prueba para formulario de estudios...');
    
    const txtmotivo = document.getElementById('txtmotivo-estudios');
    if (txtmotivo) {
        txtmotivo.value = 'TEST: Estudio OCT de retina completo';
        console.log('✅ Motivo llenado');
    }
    
    const equipoMedico = document.getElementById('equipo_medico-estudios');
    if (equipoMedico && equipoMedico.options.length > 1) {
        equipoMedico.selectedIndex = 1; // Seleccionar primera opción real
        console.log('✅ Equipo médico seleccionado:', equipoMedico.value);
        
        // Disparar evento change para activar validaciones
        equipoMedico.dispatchEvent(new Event('change', { bubbles: true }));
    } else {
        console.warn('⚠️ Campo equipo_medico-estudios no disponible o sin opciones');
    }
    
    const observaciones = document.getElementById('consulta-textarea-estudios');
    if (observaciones) {
        observaciones.value = 'Estudio completo de la retina usando OCT. Resultados dentro de parámetros normales.';
        console.log('✅ Observaciones llenadas');
    }
    
    const nota = document.getElementById('txtnota-estudios');
    if (nota) {
        nota.value = 'Paciente colaborativo durante el procedimiento';
        console.log('✅ Nota llenada');
    }
}

// Ejecutar verificación automáticamente
verificarFormularioEstudios();

// Exponer funciones globalmente
window.verificarFormularioEstudios = verificarFormularioEstudios;
window.llenarDatosPrueba = llenarDatosPrueba;

console.log('📋 Funciones disponibles:');
console.log('- verificarFormularioEstudios() - Verifica el estado del formulario');
console.log('- llenarDatosPrueba() - Llena datos de prueba en el formulario');