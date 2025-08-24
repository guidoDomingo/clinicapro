/**
 * SISTEMA UNIFICADO PARA MANEJO DE MOTIVOS COMUNES
 * 
 * Este script maneja la funcionalidad de agregar motivos comunes
 * al campo de texto correspondiente, detectando automáticamente
 * el tipo de formulario activo por los IDs únicos.
 */

/**
 * Función para detectar el ID correcto del campo motivo según el formulario activo
 */
function detectarCampoMotivoActivo() {
    // Lista de posibles IDs de campos de motivo según el tipo de formulario
    const posiblesIds = [
        'txtmotivo-anteojos',    // Formulario de anteojos
        'txtmotivo-estudios',    // Formulario de estudios  
        'txtmotivo-informe-imagen', // Formulario de informe imagen
        'txtmotivo'              // Formulario general (fallback)
    ];
    
    for (const id of posiblesIds) {
        const elemento = document.getElementById(id);
        // Verificar que el elemento exista Y que esté visible (no en formulario oculto)
        if (elemento && elemento.closest('.formulario-especifico') && 
            elemento.closest('.formulario-especifico').style.display !== 'none') {
            console.log(`✅ Campo motivo activo detectado: ${id}`);
            return elemento;
        }
    }
    
    console.warn('⚠️ No se pudo detectar campo motivo activo');
    return null;
}

/**
 * Función para detectar el ID correcto del select motivos comunes según el formulario activo
 */
function detectarSelectMotivosActivo() {
    // Lista de posibles IDs de selects de motivos comunes según el tipo de formulario
    const posiblesIds = [
        'motivoscomunes-anteojos',    // Formulario de anteojos
        'motivoscomunes-estudios',    // Formulario de estudios
        'motivoscomunes-informe-imagen', // Formulario de informe imagen
        'motivoscomunes'              // Formulario general (fallback)
    ];
    
    for (const id of posiblesIds) {
        const elemento = document.getElementById(id);
        // Verificar que el elemento exista Y que esté visible (no en formulario oculto)
        if (elemento && elemento.closest('.formulario-especifico') && 
            elemento.closest('.formulario-especifico').style.display !== 'none') {
            console.log(`✅ Select motivos comunes activo detectado: ${id}`);
            return elemento;
        }
    }
    
    console.warn('⚠️ No se pudo detectar select motivos comunes activo');
    return null;
}

/**
 * Función para agregar un motivo común al campo de texto
 * @param {string} motivoTexto - El texto del motivo a agregar
 */
function agregarMotivoAlCampo(motivoTexto) {
    const campoMotivoActivo = detectarCampoMotivoActivo();
    
    if (!campoMotivoActivo) {
        console.error('❌ No se encontró campo motivo activo para agregar:', motivoTexto);
        return false;
    }
    
    if (!motivoTexto || motivoTexto.trim() === '') {
        console.warn('⚠️ Motivo vacío, no se agregará');
        return false;
    }
    
    const valorActual = campoMotivoActivo.value.trim();
    
    if (valorActual !== '') {
        // Si ya hay contenido, agregar con coma
        campoMotivoActivo.value = valorActual + ', ' + motivoTexto.trim();
    } else {
        // Si está vacío, agregar directamente
        campoMotivoActivo.value = motivoTexto.trim();
    }
    
    console.log(`✅ Motivo agregado: "${motivoTexto}" al campo ${campoMotivoActivo.id}`);
    
    // Disparar evento change para que otros sistemas lo detecten
    campoMotivoActivo.dispatchEvent(new Event('change'));
    
    return true;
}

/**
 * Configurar events listeners para todos los selects de motivos comunes
 */
function configurarEventListenersMotivosComunes() {
    console.log('🔧 Configurando event listeners para motivos comunes...');
    
    const todosLosIds = [
        'motivoscomunes',
        'motivoscomunes-anteojos',
        'motivoscomunes-estudios', 
        'motivoscomunes-informe-imagen'
    ];
    
    let configurados = 0;
    
    todosLosIds.forEach(selectorId => {
        const selector = document.getElementById(selectorId);
        if (!selector) {
            console.log(`⚠️ Selector ${selectorId} no encontrado, saltando...`);
            return;
        }
        
        console.log(`🎯 Configurando eventos para: ${selectorId}`);
        
        // Verificar si es Select2
        const isSelect2 = selector.classList.contains('select2-hidden-accessible') || 
                         (typeof $ !== 'undefined' && $(selector).hasClass('select2-hidden-accessible'));
        
        if (isSelect2) {
            // Limpiar eventos existentes para evitar duplicados
            if (typeof $ !== 'undefined') {
                $(selector).off('select2:select.motivosComunes');
                
                // Configurar evento Select2
                $(selector).on('select2:select.motivosComunes', function(e) {
                    console.log(`🔄 Select2 evento disparado en ${selectorId}:`, e.params.data);
                    if (e.params.data.id !== 'Seleccionar' && e.params.data.id !== '' && e.params.data.text !== 'Seleccionar') {
                        console.log(`🔄 Select2 - Motivo seleccionado: "${e.params.data.text}" desde ${selectorId}`);
                        const resultado = agregarMotivoAlCampo(e.params.data.text);
                        console.log(`Resultado de agregar motivo: ${resultado}`);
                        
                        // Resetear select después de agregar
                        setTimeout(() => {
                            $(selector).val('').trigger('change');
                        }, 100);
                    }
                });
                
                configurados++;
                console.log(`✅ Event listener Select2 configurado para ${selectorId}`);
            }
        } else {
            // Limpiar event listener nativo existente
            if (selector._motivoChangeHandler) {
                selector.removeEventListener('change', selector._motivoChangeHandler);
            }
            
            // Configurar evento nativo
            selector._motivoChangeHandler = function(e) {
                console.log(`🔄 Evento nativo disparado en ${selectorId}:`, this.value, this.selectedIndex);
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value !== 'Seleccionar' && selectedOption.value !== '' && selectedOption.text !== 'Seleccionar') {
                    console.log(`🔄 Nativo - Motivo seleccionado: "${selectedOption.text}" desde ${selectorId}`);
                    const resultado = agregarMotivoAlCampo(selectedOption.text);
                    console.log(`Resultado de agregar motivo: ${resultado}`);
                    
                    // Resetear select después de agregar
                    setTimeout(() => {
                        this.selectedIndex = 0;
                    }, 100);
                }
            };
            
            selector.addEventListener('change', selector._motivoChangeHandler);
            configurados++;
            console.log(`✅ Event listener nativo configurado para ${selectorId}`);
        }
    });
    
    console.log(`🎉 Configuración de motivos comunes completada. ${configurados} selectors configurados.`);
    return configurados;
}

/**
 * Inicializar sistema de motivos comunes
 */
function inicializarSistemaMotivosComunes() {
    console.log('🚀 Inicializando sistema unificado de motivos comunes...');
    
    // Función para intentar configurar
    const intentarConfigurar = () => {
        const configurados = configurarEventListenersMotivosComunes();
        if (configurados > 0) {
            console.log(`✅ Sistema inicializado correctamente con ${configurados} elementos`);
        } else {
            console.warn('⚠️ No se encontraron elementos para configurar');
        }
        return configurados;
    };
    
    // Estrategia 1: Si el DOM ya está cargado
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        console.log('📋 DOM ya cargado, configurando inmediatamente...');
        setTimeout(intentarConfigurar, 100);
        
        // Intentar de nuevo después de un delay para elementos que se cargan dinámicamente
        setTimeout(intentarConfigurar, 1000);
        setTimeout(intentarConfigurar, 3000);
    }
    
    // Estrategia 2: Esperar DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('📋 DOMContentLoaded disparado, configurando...');
            setTimeout(intentarConfigurar, 100);
            setTimeout(intentarConfigurar, 1000);
        });
    }
    
    // Estrategia 3: Cuando se cambie de formulario
    document.addEventListener('formTypeChanged', function(e) {
        console.log('🔄 Formulario cambiado, reconfigurando motivos comunes...', e.detail);
        setTimeout(intentarConfigurar, 500);
        setTimeout(intentarConfigurar, 1500);
    });
    
    // Estrategia 4: Cuando jQuery esté disponible
    if (typeof $ !== 'undefined') {
        $(document).ready(function() {
            console.log('📋 jQuery document ready, configurando...');
            setTimeout(intentarConfigurar, 100);
            setTimeout(intentarConfigurar, 1000);
        });
    }
    
    // Estrategia 5: Observador de mutaciones para elementos agregados dinámicamente
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver((mutations) => {
            let shouldReconfigure = false;
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Verificar si se agregó un select de motivos comunes
                            if (node.id && node.id.includes('motivoscomunes')) {
                                console.log(`🔄 Nuevo select detectado: ${node.id}`);
                                shouldReconfigure = true;
                            }
                            // O si se agregó un contenedor que podría contener selects
                            if (node.querySelector && node.querySelector('[id*="motivoscomunes"]')) {
                                console.log(`🔄 Contenedor con selects detectado`);
                                shouldReconfigure = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldReconfigure) {
                setTimeout(intentarConfigurar, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('👀 MutationObserver configurado para detectar nuevos elementos');
    }
}

// Auto-inicializar cuando se carga el script
inicializarSistemaMotivosComunes();

/**
 * Función de diagnóstico manual para debugging
 */
window.diagnosticarMotivosComunes = function() {
    console.group('🔧 DIAGNÓSTICO MOTIVOS COMUNES');
    
    const formas = detectarFormasActivas();
    const select = detectarSelectMotivosActivo();
    const textarea = detectarTextareaActivco();
    
    console.log('📋 Formas detectadas:', formas);
    console.log('📝 Select activo:', select ? select.id : 'NO ENCONTRADO');
    console.log('📄 Textarea activo:', textarea ? textarea.id : 'NO ENCONTRADO');
    
    if (select) {
        console.log('🎯 Opciones disponibles en select:', Array.from(select.options).map(o => `${o.value}: ${o.text}`));
        console.log('🔗 Event listeners del select:', getEventListeners ? getEventListeners(select) : 'getEventListeners no disponible');
        
        // Verificar si es Select2
        const isSelect2 = select.classList.contains('select2-hidden-accessible') || (typeof $ !== 'undefined' && $(select).hasClass('select2-hidden-accessible'));
        console.log('🎨 Es Select2:', isSelect2);
        
        if (isSelect2 && typeof $ !== 'undefined') {
            console.log('📊 Select2 data:', $(select).select2('data'));
        }
    }
    
    if (textarea) {
        console.log('📝 Valor actual del textarea:', textarea.value);
        console.log('🔗 Event listeners del textarea:', getEventListeners ? getEventListeners(textarea) : 'getEventListeners no disponible');
    }
    
    // Intentar reconfigurar
    console.log('🔄 Intentando reconfigurar...');
    const configurados = configurarEventListenersMotivosComunes();
    console.log(`✅ Elementos configurados: ${configurados}`);
    
    console.groupEnd();
    
    return {
        formas,
        select,
        textarea,
        configurados
    };
};

/**
 * Función para probar manualmente la adición de un motivo
 */
window.probarMotivo = function(texto = 'Motivo de prueba') {
    const textarea = detectarTextareaActivco();
    if (textarea) {
        console.log(`🧪 Probando adición de motivo: "${texto}"`);
        agregarMotivoATexto(textarea, texto);
    } else {
        console.error('❌ No se pudo encontrar textarea activo');
    }
};

/**
 * Función para verificar el estado de los equipos médicos
 */
window.verificarEquiposMedicos = function() {
    console.group('🏥 VERIFICACIÓN DE EQUIPOS MÉDICOS');
    
    const select = document.getElementById('equipo_medico-estudios');
    if (select) {
        const opciones = Array.from(select.options);
        console.log('📋 Select equipo_medico-estudios:');
        console.log('   - Total opciones:', opciones.length);
        console.log('   - Opciones disponibles:', opciones.map(o => `"${o.value}": "${o.text}"`));
        console.log('   - Es Select2:', select.classList.contains('select2-hidden-accessible') || (typeof $ !== 'undefined' && $(select).hasClass('select2-hidden-accessible')));
        console.log('   - Visible:', select.offsetParent !== null);
        
        // Verificar si tiene datos o solo la opción por defecto
        if (opciones.length <= 1) {
            console.warn('⚠️ El select parece estar vacío. Intentando cargar equipos...');
            if (window.appInitializer && window.appInitializer.getComponent) {
                const consultasManager = window.appInitializer.getComponent('consultas');
                if (consultasManager && consultasManager.loadEquiposMedicos) {
                    consultasManager.loadEquiposMedicos();
                }
            }
        } else {
            console.log('✅ Equipos médicos cargados correctamente desde la base de datos');
        }
    } else {
        console.error('❌ Select equipo_medico-estudios NO ENCONTRADO');
    }
    
    // Probar también el endpoint directamente
    fetch('modules/consultas/api/consultas-api.php?action=get_equipos_medicos')
        .then(response => response.json())
        .then(data => {
            console.log('🌐 Respuesta del endpoint get_equipos_medicos:', data);
            if (data.success) {
                console.log(`✅ API funcionando - ${data.count} equipos disponibles`);
                if (data.referencial_id) {
                    console.log(`📋 Referencial ID: ${data.referencial_id} (${data.referencial_codigo})`);
                }
            } else {
                console.warn('⚠️ Error en API:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error llamando al endpoint:', error);
        });
    
    console.groupEnd();
};

/**
 * Función para verificar el estado de los motivos comunes en todos los selects
 */
window.verificarMotivosEnSelects = function() {
    console.group('🔍 VERIFICACIÓN DE MOTIVOS EN SELECTS');
    
    const selectIds = [
        'motivoscomunes',
        'motivoscomunes-anteojos',
        'motivoscomunes-estudios',
        'motivoscomunes-informe-imagen'
    ];
    
    selectIds.forEach(selectId => {
        const select = document.getElementById(selectId);
        if (select) {
            const opciones = Array.from(select.options);
            console.log(`📋 ${selectId}:`);
            console.log(`   - Total opciones: ${opciones.length}`);
            console.log(`   - Opciones disponibles:`, opciones.map(o => `"${o.value}": "${o.text}"`));
            console.log(`   - Es Select2:`, select.classList.contains('select2-hidden-accessible') || (typeof $ !== 'undefined' && $(select).hasClass('select2-hidden-accessible')));
            console.log(`   - Visible:`, select.offsetParent !== null);
        } else {
            console.warn(`❌ ${selectId}: NO ENCONTRADO`);
        }
    });
    
    // Verificar también el estado del ConsultasManager
    if (window.appInitializer && window.appInitializer.getComponent) {
        const consultasManager = window.appInitializer.getComponent('consultas');
        if (consultasManager && consultasManager.state) {
            console.log('📊 Estado ConsultasManager:');
            console.log('   - motivosComunes:', consultasManager.state.motivosComunes?.length || 0);
            console.log('   - motivosPorTipo:', consultasManager.state.motivosPorTipo);
        }
    }
    
    console.groupEnd();
};

// Exponer funciones globalmente para debugging y uso manual
window.detectarCampoMotivoActivo = detectarCampoMotivoActivo;
window.agregarMotivoAlCampo = agregarMotivoAlCampo;
window.configurarEventListenersMotivosComunes = configurarEventListenersMotivosComunes;
window.configurarMotivosComunes = configurarEventListenersMotivosComunes;
window.detectarSelectMotivos = detectarSelectMotivosActivo;

console.log('✅ Sistema unificado de motivos comunes cargado');