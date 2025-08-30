/**
 * icd11-integration.js
 * Funciones para la integración de ICD-11 con el formulario de consulta
 */

// Función global para encontrar el textarea de la consulta usando diferentes estrategias
function findConsultaTextarea() {
    console.log('🔍 Buscando el textarea de consulta en consultas-v3...');

    // **CAMPO #CONSULTA EXCLUIDO INTENCIONALMENTE**
    console.log('🚫 Campo #consulta ignorado por configuración - buscando alternativas...');

    // **ESTRATEGIA PRINCIPAL: BUSCAR CAMPOS ALTERNATIVOS**
    const alternativeIds = ['txtmotivo', 'motivo', 'receta_textarea', 'receta', 'nota', 'descripcion', 'consulta_textarea'];
    
    console.log('🔍 Buscando campos alternativos:', alternativeIds);
    
    for (const id of alternativeIds) {
        const element = document.getElementById(id);
        console.log(`🔍 Verificando campo #${id}:`, {
            exists: !!element,
            visible: element ? element.offsetParent !== null : false,
            display: element ? getComputedStyle(element).display : 'N/A',
            classList: element ? Array.from(element.classList) : []
        });
        
        if (element && element.offsetParent !== null) {
            console.log(`📝 Campo alternativo encontrado y visible: #${id}`);
            
            // Verificar si tiene Summernote
            if (typeof $ !== 'undefined' && $(element).hasClass('summernote')) {
                const hasSummernote = $(element).next('.note-editor').length > 0;
                console.log(`✏️  Summernote en #${id}: ${hasSummernote}`);
                
                if (hasSummernote) {
                    return {
                        element: element,
                        isSummernote: true,
                        setValue: function(text) {
                            const currentContent = $(element).summernote('code');
                            let newContent = text;
                            
                            if (currentContent && currentContent.trim() !== '' && !currentContent.includes('[Diagnóstico ICD-11:')) {
                                newContent = currentContent + '<br>' + text;
                            }
                            
                            $(element).summernote('code', newContent);
                            console.log(`✅ Texto insertado en Summernote #${id}`);
                        },
                        getValue: function() {
                            return $(element).summernote('code');
                        },
                        focus: function() {
                            $(element).summernote('focus');
                        }
                    };
                }
            }
            
            // Si no tiene Summernote, usar como textarea normal
            console.log(`📝 Usando campo #${id} como textarea normal`);
            return {
                element: element,
                isSummernote: false,
                setValue: function(text) {
                    const currentContent = element.value || '';
                    let newContent = text;
                    
                    if (currentContent.trim() !== '' && !currentContent.includes('[Diagnóstico ICD-11:')) {
                        newContent = currentContent + '\n' + text;
                    }
                    
                    element.value = newContent;
                    console.log(`✅ Texto insertado en textarea #${id}`);
                },
                getValue: function() {
                    return element.value;
                },
                focus: function() {
                    element.focus();
                }
            };
        }
    }

    // **ESTRATEGIA SECUNDARIA: BUSCAR CUALQUIER SUMMERNOTE VISIBLE**
    const summernoteElements = document.querySelectorAll('.summernote');
    for (const element of summernoteElements) {
        // Excluir el campo #consulta específicamente
        if (element.id === 'consulta') {
            console.log('🚫 Saltando campo #consulta (excluido)');
            continue;
        }
        
        if (element.offsetParent !== null) {
            console.log(`📝 Summernote genérico encontrado: #${element.id || 'sin-id'}`);
            
            const hasSummernote = $(element).next('.note-editor').length > 0;
            if (hasSummernote) {
                return {
                    element: element,
                    isSummernote: true,
                    setValue: function(text) {
                        const currentContent = $(element).summernote('code');
                        let newContent = text;
                        
                        if (currentContent && currentContent.trim() !== '' && !currentContent.includes('[Diagnóstico ICD-11:')) {
                            newContent = currentContent + '<br>' + text;
                        }
                        
                        $(element).summernote('code', newContent);
                        console.log(`✅ Texto insertado en Summernote genérico`);
                    },
                    getValue: function() {
                        return $(element).summernote('code');
                    },
                    focus: function() {
                        $(element).summernote('focus');
                    }
                };
            }
        }
    }

    // **ESTRATEGIA TERCIARIA: BUSCAR CUALQUIER TEXTAREA VISIBLE**
    const textareas = document.querySelectorAll('textarea');
    for (const element of textareas) {
        // Excluir el campo #consulta específicamente
        if (element.id === 'consulta') {
            console.log('🚫 Saltando textarea #consulta (excluido)');
            continue;
        }
        
        if (element.offsetParent !== null && !element.disabled) {
            console.log(`📝 Textarea genérico encontrado: #${element.id || 'sin-id'}`);
            
            return {
                element: element,
                isSummernote: false,
                setValue: function(text) {
                    const currentContent = element.value;
                    let newContent = text;
                    
                    if (currentContent && currentContent.trim() !== '' && !currentContent.includes('[Diagnóstico ICD-11:')) {
                        newContent = currentContent + '\n' + text;
                    }
                    
                    element.value = newContent;
                    console.log(`✅ Texto insertado en textarea genérico`);
                },
                getValue: function() {
                    return element.value;
                },
                focus: function() {
                    element.focus();
                }
            };
        }
    }

    // **ESTRATEGIA FINAL: CAMBIO DE TAB**
    console.log('🔄 No se encontraron campos visibles, intentando cambiar de tab...');
    
    try {
        window.insertingIcdContent = true;
        
        const createTabButton = document.querySelector("button[onclick=\"showTab('create')\"]");
        if (createTabButton) {
            console.log('🔄 Activando tab create para inserción...');
            
            return {
                pending: true,
                resolve: function(callback) {
                    createTabButton.click();
                    
                    setTimeout(() => {
                        console.log('🔍 Reintentando búsqueda después del cambio de tab...');
                        
                        // FORZAR ACTIVACIÓN DE CAMPOS OCULTOS
                        const fieldsToActivate = ['txtmotivo', 'motivo', 'receta_textarea', 'receta', 'nota', 'descripcion', 'consulta_textarea'];
                        let activatedField = null;
                        
                        console.log('🔧 Intentando activar campos ocultos...');
                        
                        for (const fieldId of fieldsToActivate) {
                            const field = document.getElementById(fieldId);
                            if (field && field.id !== 'consulta') {
                                console.log(`🔧 Campo #${fieldId} encontrado:`, {
                                    display: getComputedStyle(field).display,
                                    visibility: getComputedStyle(field).visibility,
                                    offsetParent: field.offsetParent
                                });
                                
                                // Forzar visibilidad
                                if (getComputedStyle(field).display === 'none') {
                                    field.style.display = 'block';
                                    console.log(`🔧 Forzando display: block en #${fieldId}`);
                                }
                                
                                if (getComputedStyle(field).visibility === 'hidden') {
                                    field.style.visibility = 'visible';
                                    console.log(`🔧 Forzando visibility: visible en #${fieldId}`);
                                }
                                
                                // Verificar si ahora es visible
                                if (field.offsetParent !== null) {
                                    console.log(`🎯 Campo #${fieldId} ahora es visible después de forzar`);
                                    
                                    activatedField = {
                                        element: field,
                                        isSummernote: $(field).hasClass('summernote') && $(field).next('.note-editor').length > 0,
                                        setValue: function(text) {
                                            if (this.isSummernote) {
                                                const currentContent = $(field).summernote('code');
                                                let newContent = text;
                                                if (currentContent && currentContent.trim() !== '' && !currentContent.includes('[Diagnóstico ICD-11:')) {
                                                    newContent = currentContent + '<br>' + text;
                                                }
                                                $(field).summernote('code', newContent);
                                                console.log(`✅ Texto insertado en Summernote #${fieldId} (forzado)`);
                                            } else {
                                                const currentContent = field.value || '';
                                                let newContent = text;
                                                if (currentContent.trim() !== '' && !currentContent.includes('[Diagnóstico ICD-11:')) {
                                                    newContent = currentContent + '\n' + text;
                                                }
                                                field.value = newContent;
                                                console.log(`✅ Texto insertado en textarea #${fieldId} (forzado)`);
                                            }
                                        },
                                        getValue: function() {
                                            return this.isSummernote ? $(field).summernote('code') : field.value;
                                        },
                                        focus: function() {
                                            if (this.isSummernote) {
                                                $(field).summernote('focus');
                                            } else {
                                                field.focus();
                                            }
                                        }
                                    };
                                    break;
                                }
                            }
                        }
                        
                        // Si no se pudo activar ningún campo específico, buscar cualquier textarea
                        if (!activatedField) {
                            console.log('🔧 Buscando cualquier textarea para activar...');
                            const allTextareas = document.querySelectorAll('textarea');
                            for (const textarea of allTextareas) {
                                if (textarea.id !== 'consulta' && !textarea.disabled) {
                                    console.log(`🔧 Intentando activar textarea #${textarea.id || 'sin-id'}`);
                                    
                                    // Forzar visibilidad
                                    if (getComputedStyle(textarea).display === 'none') {
                                        textarea.style.display = 'block';
                                        console.log(`🔧 Forzando display en textarea #${textarea.id}`);
                                    }
                                    
                                    if (getComputedStyle(textarea).visibility === 'hidden') {
                                        textarea.style.visibility = 'visible';
                                        console.log(`🔧 Forzando visibility en textarea #${textarea.id}`);
                                    }
                                    
                                    if (textarea.offsetParent !== null) {
                                        console.log(`🎯 Textarea #${textarea.id} activado exitosamente`);
                                        activatedField = {
                                            element: textarea,
                                            isSummernote: false,
                                            setValue: function(text) {
                                                const currentContent = textarea.value || '';
                                                let newContent = text;
                                                if (currentContent.trim() !== '' && !currentContent.includes('[Diagnóstico ICD-11:')) {
                                                    newContent = currentContent + '\n' + text;
                                                }
                                                textarea.value = newContent;
                                                console.log(`✅ Texto insertado en textarea genérico activado`);
                                            },
                                            getValue: function() {
                                                return textarea.value;
                                            },
                                            focus: function() {
                                                textarea.focus();
                                            }
                                        };
                                        break;
                                    }
                                }
                            }
                        }
                        
                        if (activatedField) {
                            console.log('✅ Campo activado exitosamente para inserción');
                            callback(activatedField);
                        } else {
                            console.log('❌ No se pudo encontrar campo después de cambio de tab');
                            callback(null);
                        }
                        window.insertingIcdContent = false;
                    }, 500);
                }
            };
        }
    } catch (error) {
        console.error('❌ Error en cambio de tab:', error);
        window.insertingIcdContent = false;
    }

    console.log('❌ No se pudo encontrar ningún campo de texto válido para inserción');
    return null;
}

// Función para insertar texto ICD-11
function insertIcdText(text) {
    console.log('📝 Intentando insertar texto ICD-11:', text);
    
    const field = findConsultaTextarea();
    
    if (!field) {
        console.log('❌ No se encontró campo válido para inserción');
        return false;
    }
    
    if (field.pending) {
        console.log('⏳ Campo pendiente, resolviendo...');
        field.resolve((resolvedField) => {
            if (resolvedField) {
                resolvedField.setValue(text);
                resolvedField.focus();
                console.log('✅ Texto insertado después de resolver campo pendiente');
            }
        });
        return true;
    }
    
    field.setValue(text);
    field.focus();
    console.log('✅ Texto insertado exitosamente');
    return true;
}

// ============================================================
// CONFIGURACIÓN DE NAVEGACIÓN DE TABS PARA CONSULTAS-V3
// ============================================================

function setupTabNavigation() {
    console.log('🚀 Configurando navegación de tabs para consultas-v3...');
    
    if (typeof window.showTab !== 'function') {
        console.log('⚠️  Función showTab no encontrada, creando versión básica...');
        
        window.showTab = function(tabName) {
            console.log(`🔄 Mostrando tab: ${tabName}`);
            
            // Ocultar todos los tabs
            const allTabs = document.querySelectorAll('[id^="tab-"]');
            allTabs.forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Mostrar tab específico
            const targetTab = document.getElementById(`tab-${tabName}`);
            if (targetTab) {
                targetTab.style.display = 'block';
                console.log(`✅ Tab ${tabName} mostrado`);
                return true;
            } else {
                console.log(`❌ Tab ${tabName} no encontrado`);
                return false;
            }
        };
    }
    
    // Contar tabs disponibles
    const availableTabs = document.querySelectorAll('[id^="tab-"]');
    console.log(`✅ Configurada navegación entre pestañas (${availableTabs.length} tabs encontrados)`);
}

// ============================================================
// INICIALIZACIÓN AUTOMÁTICA DEL SISTEMA
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 ICD-11 Integration para Consultas-V3: Inicializando...');
    
    // Configurar navegación de tabs
    setupTabNavigation();
    
    // Verificar campo consulta al cargar
    const consultaField = document.getElementById('consulta');
    if (consultaField) {
        console.log('✅ Campo #consulta detectado al cargar (será EXCLUIDO de inserción automática)');
    }
    
    // Configurar escuchadores de eventos personalizados
    document.addEventListener('icd11:codeSelected', function(event) {
        console.log('🎯 Evento icd11:codeSelected recibido:', event.detail);
        
        const { code, title, description } = event.detail;
        const icdText = `[Diagnóstico ICD-11: ${code}]\n${title}\n\n${description}\n`;
        
        const success = insertIcdText(icdText);
        
        if (success) {
            console.log('✅ Código ICD-11 insertado exitosamente');
        } else {
            console.log('❌ Error insertando código ICD-11');
        }
    });
    
    console.log('✅ ICD-11 Integration inicializado correctamente para consultas-v3');
});

// ============================================================
// DETECTAR CUANDO SUMMERNOTE SE INICIALIZA
// ============================================================

// Detectar inicialización de Summernote en #consulta (solo para logging)
$(document).ready(function() {
    const consultaField = $('#consulta');
    if (consultaField.length && consultaField.hasClass('summernote')) {
        
        const checkSummernoteInit = function() {
            const hasEditor = consultaField.next('.note-editor').length > 0;
            if (hasEditor) {
                console.log('✅ Summernote inicializado en #consulta (EXCLUIDO de inserción automática)');
            } else {
                setTimeout(checkSummernoteInit, 100);
            }
        };
        
        checkSummernoteInit();
    }
});