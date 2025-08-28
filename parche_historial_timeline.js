/**
 * PARCHE PARA HISTORIAL Y TIMELINE EN CONSULTAS REALES
 * 
 * Este script asegura que el historial y timeline funcionen
 * correctamente en la página real de consultas
 */

console.log('🔧 === PARCHE PARA HISTORIAL Y TIMELINE ===');

// Función para verificar y reparar elementos
function verificarYRepararElementos() {
    console.log('🔍 Verificando elementos de historial y timeline...');
    
    // Verificar tabla de consultas
    const tabla = document.querySelector('#tabla-consultas');
    if (tabla) {
        let tbody = tabla.querySelector('tbody');
        if (!tbody) {
            console.log('🔧 Creando tbody faltante...');
            tbody = document.createElement('tbody');
            tabla.appendChild(tbody);
        }
        console.log('✅ Tabla de consultas verificada');
    } else {
        console.log('❌ Tabla #tabla-consultas no encontrada');
    }
    
    // Verificar timeline
    const timeline = document.querySelector('#timeline');
    if (timeline) {
        console.log('✅ Timeline container verificado');
    } else {
        console.log('❌ Timeline #timeline no encontrado');
    }
    
    // Verificar archivos
    const archivos = document.querySelector('#archivos-container');
    if (archivos) {
        console.log('✅ Archivos container verificado');
    } else {
        console.log('❌ Archivos container no encontrado');
    }
}

// Interceptar la función original de actualizar historial
function interceptarFuncionesOriginales() {
    console.log('🔄 Interceptando funciones originales...');
    
    // Guardar referencia a la función original si existe
    if (typeof window.actualizarHistorial === 'function') {
        window.actualizarHistorialOriginal = window.actualizarHistorial;
    }
    
    // Nueva función mejorada
    window.actualizarHistorial = function(historial) {
        console.log('🔄 === HISTORIAL INTERCEPTADO ===');
        console.log('📊 Datos recibidos:', historial);
        
        // Guardar datos globalmente para debugging
        window.ultimosDescargados = window.ultimosDescargados || {};
        window.ultimosDescargados.historial = historial;
        
        if (!historial || historial.length === 0) {
            console.log('⚠️ No hay datos de historial');
            return;
        }
        
        // Función para intentar actualizar con reintentos
        function actualizarConReintentos(reintentos = 0) {
            const tablaBody = document.querySelector('#tabla-consultas tbody');
            
            if (!tablaBody && reintentos < 10) {
                console.log(`⏱️ Tbody no encontrado, reintentando... (${reintentos + 1}/10)`);
                setTimeout(() => actualizarConReintentos(reintentos + 1), 100);
                return;
            }
            
            if (!tablaBody) {
                console.log('❌ No se pudo encontrar tbody después de 10 intentos');
                return;
            }
            
            console.log('✅ Tbody encontrado, actualizando historial...');
            
            // Generar HTML de las consultas
            const historialHTML = historial.map((consulta, index) => {
                const fecha = consulta.fecha_registro || consulta.fecha || new Date().toISOString();
                const motivo = consulta.motivo || 'Sin motivo especificado';
                const tipo = consulta.tipo_formulario || 'Consulta';
                const id = consulta.id_consulta || index + 1;
                
                return `
                    <tr class="consulta-row" data-consulta-id="${id}">
                        <td class="fecha-col">
                            <div class="fecha-display">
                                ${formatearFecha(fecha)}
                            </div>
                        </td>
                        <td class="paciente-col">
                            <div class="paciente-info">
                                <strong>Consulta #${id}</strong>
                                <small class="d-block text-muted">${motivo}</small>
                            </div>
                        </td>
                        <td class="tipo-col">
                            <span class="badge badge-primary tipo-badge">
                                <i class="fas fa-stethoscope"></i>
                                ${tipo}
                            </span>
                        </td>
                        <td class="acciones-col">
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-info btn-sm" onclick="verConsulta(${id})">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="editarConsulta(${id})">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="imprimirConsulta(${id})">
                                    <i class="fas fa-print"></i> Imprimir
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            tablaBody.innerHTML = historialHTML;
            
            // Actualizar badge de la pestaña
            const historialTab = document.querySelector('a[href="#historial-panel"]');
            if (historialTab) {
                let badge = historialTab.querySelector('.badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge badge-primary ml-1';
                    historialTab.appendChild(badge);
                }
                badge.textContent = historial.length;
                console.log(`✅ Badge historial actualizado: ${historial.length}`);
            }
            
            console.log(`✅ Historial actualizado exitosamente con ${historial.length} consultas`);
        }
        
        actualizarConReintentos();
    };
    
    // Interceptar timeline también
    if (typeof window.actualizarTimeline === 'function') {
        window.actualizarTimelineOriginal = window.actualizarTimeline;
    }
    
    window.actualizarTimeline = function(timeline) {
        console.log('🔄 === TIMELINE INTERCEPTADO ===');
        console.log('📊 Datos timeline:', timeline);
        
        if (!timeline || timeline.length === 0) {
            console.log('⚠️ No hay datos de timeline');
            return;
        }
        
        function actualizarTimelineConReintentos(reintentos = 0) {
            const timelineContainer = document.querySelector('#timeline');
            
            if (!timelineContainer && reintentos < 10) {
                console.log(`⏱️ Timeline no encontrado, reintentando... (${reintentos + 1}/10)`);
                setTimeout(() => actualizarTimelineConReintentos(reintentos + 1), 100);
                return;
            }
            
            if (!timelineContainer) {
                console.log('❌ No se pudo encontrar timeline después de 10 intentos');
                return;
            }
            
            console.log('✅ Timeline encontrado, actualizando...');
            
            const timelineHTML = `
                <div class="timeline-header-main">
                    <h5><i class="fas fa-clock"></i> Timeline de Actividades</h5>
                    <div class="timeline-stats">
                        <span class="badge badge-info">${timeline.length} actividades</span>
                    </div>
                </div>
                <div class="timeline-content-wrapper">
                    ${timeline.map((actividad, index) => {
                        const fecha = actividad.fecha || new Date().toISOString();
                        const tipo = actividad.tipo || 'Actividad';
                        const descripcion = actividad.descripcion || actividad.motivo || 'Sin descripción';
                        const id = actividad.id || index + 1;
                        
                        return `
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-fecha">${formatearFecha(fecha)}</span>
                                        <span class="badge badge-secondary tipo-timeline">${tipo}</span>
                                    </div>
                                    <div class="timeline-descripcion">
                                        ${descripcion}
                                    </div>
                                    <div class="timeline-footer">
                                        <span class="timeline-id">ID: ${id}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
            
            timelineContainer.innerHTML = timelineHTML;
            
            // Actualizar badge de timeline
            const timelineTab = document.querySelector('a[href="#timeline-panel"]');
            if (timelineTab) {
                let badge = timelineTab.querySelector('.badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge badge-info ml-1';
                    timelineTab.appendChild(badge);
                }
                badge.textContent = timeline.length;
                console.log(`✅ Badge timeline actualizado: ${timeline.length}`);
            }
            
            console.log(`✅ Timeline actualizado exitosamente con ${timeline.length} actividades`);
        }
        
        actualizarTimelineConReintentos();
    };
}

// Función auxiliar para formatear fechas
function formatearFecha(fecha) {
    try {
        const d = new Date(fecha);
        if (isNaN(d.getTime())) {
            return fecha; // Devolver original si no se puede parsear
        }
        return d.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    } catch (e) {
        return fecha;
    }
}

// Funciones placeholder para evitar errores
function verConsulta(id) {
    console.log('Ver consulta:', id);
    alert(`Ver consulta #${id}`);
}

function editarConsulta(id) {
    console.log('Editar consulta:', id);
    
    // Verificar si existe la función real de edición en el contexto global
    if (typeof window.editarConsultaReal === 'function') {
        window.editarConsultaReal(id);
    } else {
        // Función de edición integrada
        console.log('✏️ Editando consulta:', id);
        
        fetch(`ajax/obtener-consulta.php?id=${id}`)
        .then(response => {
            console.log('📡 Respuesta HTTP status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📨 Datos recibidos:', data);
            
            if (data.success && data.consulta) {
                // Primero cambiar al formulario correcto según el tipo
                const tipoFormulario = data.consulta.tipo_formulario || 'general';
                console.log('📋 Tipo de formulario:', tipoFormulario);
                
                // Verificar si existe la función de cambio de formulario
                if (typeof window.cambiarFormulario === 'function') {
                    console.log('🔄 Cambiando a formulario:', tipoFormulario);
                    
                    // Usar función debug si está disponible para mayor información
                    if (typeof window.cambiarFormularioDebug === 'function') {
                        console.log('🔧 Usando función debug para cambio desde historial...');
                        window.cambiarFormularioDebug(tipoFormulario);
                    } else {
                        console.log('🔄 Usando función normal para cambio desde historial...');
                        window.cambiarFormulario(tipoFormulario);
                    }
                    
                    // Verificar que el cambio fue exitoso
                    setTimeout(() => {
                        const formularioActivo = document.querySelector('.formulario-especifico.active');
                        console.log('✅ Formulario activo después del cambio:', formularioActivo ? formularioActivo.id : 'NINGUNO');
                        
                        if (tipoFormulario === 'anteojos') {
                            const formularioAnteojos = document.getElementById('formulario-anteojos');
                            const btnGuardar = document.getElementById('btnGuardarConsulta-anteojos');
                            
                            console.log('👓 Estado formulario anteojos:', {
                                existe: !!formularioAnteojos,
                                activo: formularioAnteojos ? formularioAnteojos.classList.contains('active') : false,
                                visible: formularioAnteojos ? formularioAnteojos.offsetWidth > 0 && formularioAnteojos.offsetHeight > 0 : false,
                                display: formularioAnteojos ? window.getComputedStyle(formularioAnteojos).display : 'N/A'
                            });
                            
                            console.log('👓 Estado botón guardar:', {
                                existe: !!btnGuardar,
                                visible: btnGuardar ? btnGuardar.offsetWidth > 0 && btnGuardar.offsetHeight > 0 : false,
                                display: btnGuardar ? window.getComputedStyle(btnGuardar).display : 'N/A',
                                opacity: btnGuardar ? window.getComputedStyle(btnGuardar).opacity : 'N/A'
                            });
                        }
                    }, 300);
                } else {
                    console.error('❌ Función cambiarFormulario no disponible');
                }
                
                // Cargar los datos en el formulario después de un pequeño delay
                setTimeout(() => {
                    cargarDatosEnFormularioHistorial(data.consulta);
                    
                    // Cambiar a la pestaña de Nueva Consulta
                    if (typeof window.activateTab === 'function') {
                        window.activateTab('consulta-panel');
                    }
                    
                    if (typeof alertify !== 'undefined') {
                        alertify.success(`Consulta ${tipoFormulario} cargada para edición`);
                    }
                }, 500);
                
            } else {
                console.error('❌ Error en respuesta:', data);
                
                if (data.message === 'Usuario no autenticado') {
                    console.warn('🔐 Problema de autenticación detectado');
                    if (data.debug) {
                        console.log('🔍 Debug info:', data.debug);
                    }
                    
                    if (typeof alertify !== 'undefined') {
                        alertify.error('Sesión expirada. Por favor, recarga la página e inicia sesión nuevamente.');
                    } else {
                        alert('Sesión expirada. Por favor, recarga la página.');
                    }
                } else {
                    if (typeof alertify !== 'undefined') {
                        alertify.error(data.message || 'Error al cargar la consulta');
                    } else {
                        alert('Error al cargar la consulta: ' + (data.message || 'Error desconocido'));
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof alertify !== 'undefined') {
                alertify.error('Error de conexión al cargar la consulta');
            } else {
                alert('Error de conexión');
            }
        });
    }
}

// Función para cargar datos en el formulario desde el historial usando Livewire
function cargarDatosEnFormularioHistorial(consulta) {
    console.log('📝 Cargando datos en formulario desde historial:', consulta);
    
    const tipoFormulario = consulta.tipo_formulario || 'general';
    
    // Verificar si hay instancia de LivewireCRUD disponible
    if (typeof window.livewireCRUD !== 'undefined' && window.livewireCRUD) {
        console.log('✅ LivewireCRUD encontrado - usando método Livewire');
        cargarDatosConLivewire(consulta, tipoFormulario);
    } else if (typeof window.livewire !== 'undefined' && window.livewire) {
        console.log('✅ Livewire global encontrado - usando método Livewire');
        cargarDatosConLivewireGlobal(consulta, tipoFormulario);
    } else {
        console.log('⚠️ Livewire no disponible, usando método DOM tradicional');
        cargarDatosEnFormularioTradicional(consulta);
    }
    
    // Marcar como edición y cambiar el botón
    setTimeout(() => {
        const btnGuardar = document.getElementById('btnGuardarConsulta');
        if (btnGuardar) {
            btnGuardar.innerHTML = '<i class="fas fa-edit"></i> Actualizar Consulta';
            btnGuardar.classList.add('btn-warning');
            btnGuardar.classList.remove('btn-success');
        }
        
        // Guardar ID para actualización
        let consultaIdInput = document.getElementById('consultaId');
        if (!consultaIdInput) {
            consultaIdInput = document.createElement('input');
            consultaIdInput.type = 'hidden';
            consultaIdInput.id = 'consultaId';
            document.body.appendChild(consultaIdInput);
        }
        consultaIdInput.value = consulta.id || '';
    }, 200);
}

// Función para cargar datos usando LivewireCRUD específico del proyecto
function cargarDatosConLivewire(consulta, tipoFormulario) {
    try {
        console.log('📡 Cargando datos con LivewireCRUD:', consulta);
        
        // Preparar datos según el tipo de formulario
        const datosParaLivewire = prepararDatosParaLivewire(consulta, tipoFormulario);
        
        // Establecer datos usando el método setState de LivewireCRUD
        Object.keys(datosParaLivewire).forEach(key => {
            if (datosParaLivewire[key] !== null && datosParaLivewire[key] !== undefined) {
                window.livewireCRUD.setState(`data.${key}`, datosParaLivewire[key]);
                console.log(`✅ LivewireCRUD ${key}:`, datosParaLivewire[key]);
            }
        });
        
        // Establecer el ID de consulta para edición
        window.livewireCRUD.setState('data.id_consulta', consulta.id);
        window.livewireCRUD.setState('data.id_persona', consulta.id_persona);
        
        // Forzar actualización de elementos
        setTimeout(() => {
            actualizarElementosDOM(datosParaLivewire);
        }, 100);
        
        console.log('🎉 Datos cargados exitosamente con LivewireCRUD');
        
    } catch (error) {
        console.error('❌ Error cargando datos con LivewireCRUD:', error);
        cargarDatosEnFormularioTradicional(consulta);
    }
}

// Función para cargar datos usando Livewire global
function cargarDatosConLivewireGlobal(consulta, tipoFormulario) {
    try {
        console.log('📡 Cargando datos con Livewire global:', consulta);
        
        // Preparar datos según el tipo de formulario
        const datosParaLivewire = prepararDatosParaLivewire(consulta, tipoFormulario);
        
        // Establecer datos usando el método setState de Livewire global
        Object.keys(datosParaLivewire).forEach(key => {
            if (datosParaLivewire[key] !== null && datosParaLivewire[key] !== undefined) {
                window.livewire.setState(`data.${key}`, datosParaLivewire[key]);
                console.log(`✅ Livewire global ${key}:`, datosParaLivewire[key]);
            }
        });
        
        // Forzar actualización de elementos
        setTimeout(() => {
            actualizarElementosDOM(datosParaLivewire);
        }, 100);
        
        console.log('🎉 Datos cargados exitosamente con Livewire global');
        
    } catch (error) {
        console.error('❌ Error cargando datos con Livewire global:', error);
        cargarDatosEnFormularioTradicional(consulta);
    }
}

// Función para preparar datos según el tipo de formulario
function prepararDatosParaLivewire(consulta, tipoFormulario) {
    const datosComunes = {
        txtmotivo: consulta.txtmotivo || '',
        motivoscomunes: consulta.motivoscomunes || 'Seleccionar',
        txtnota: consulta.observaciones || '',
        consulta_textarea: consulta.diagnostico || '',
        receta_textarea: consulta.receta_textarea || '',
        proximaconsulta: consulta.proximaconsulta || '',
        whatsapptxt: consulta.whatsapptxt || '',
        email: consulta.email || ''
    };
    
    if (tipoFormulario === 'general') {
        Object.assign(datosComunes, {
            visionod: consulta.visionod || '',
            visionoi: consulta.visionoi || '',
            tensionod: consulta.tensionod || '',
            tensionoi: consulta.tensionoi || ''
        });
        
    } else if (tipoFormulario === 'anteojos') {
        Object.assign(datosComunes, {
            od_esf: consulta.esfera_od || '',
            od_cil: consulta.cilindro_od || '',
            od_eje: consulta.eje_od || '',
            od_dnp: consulta.dnp_od || '',
            od_add: consulta.add_od || '',
            od_altura: consulta.altura_od || '',
            od_nota: consulta.nota_od || '',
            oi_esf: consulta.esfera_oi || '',
            oi_cil: consulta.cilindro_oi || '',
            oi_eje: consulta.eje_oi || '',
            oi_dnp: consulta.dnp_oi || '',
            oi_add: consulta.add_oi || '',
            oi_altura: consulta.altura_oi || '',
            oi_nota: consulta.nota_oi || '',
            dist_interpupilar: consulta.dist_interpupilar || ''
        });
        
    } else if (tipoFormulario === 'estudios') {
        Object.assign(datosComunes, {
            equipo_medico: consulta.equipo_medico || '',
            otro_equipo: consulta.otro_equipo || '',
            resultados: consulta.resultados || '',
            emails_compartir: consulta.emails_compartir || '',
            compartir_activo: consulta.compartir_activo || false
        });
        
    } else if (tipoFormulario === 'informe_imagen') {
        Object.assign(datosComunes, {
            equipoMedico: consulta.equipoMedico || '',
            descripcion_od: consulta.descripcion_od || '',
            descripcion_oi: consulta.descripcion_oi || '',
            emails_compartir: consulta.emails_compartir || '',
            compartir_activo: consulta.compartir_activo || false
        });
    }
    
    return datosComunes;
}

// Función para actualizar elementos DOM directamente (respaldo)
function actualizarElementosDOM(datos) {
    console.log('🔄 Actualizando elementos DOM como respaldo:', datos);
    
    // Mapeo de nombres de campos a IDs reales de HTML
    const fieldIdMapping = {
        // Campos comunes (sin sufijo para general, con sufijo para anteojos)
        'txtmotivo': 'txtmotivo-anteojos', // Para formulario de anteojos
        'motivoscomunes': 'motivoscomunes-anteojos', // Para formulario de anteojos
        'consulta_textarea': 'consulta-textarea-anteojos', // Para formulario de anteojos
        'receta_textarea': 'receta-textarea-anteojos', // Para formulario de anteojos
        'txtnota': 'txtnota', // Campo global
        'proximaconsulta': 'proximaconsulta',
        'whatsapptxt': 'whatsapptxt',
        'email': 'email',
        
        // Campos específicos de anteojos
        'od_esf': 'od_esf',
        'od_cil': 'od_cil', 
        'od_eje': 'ejeod',
        'od_dnp': 'dnpod',
        'od_add': 'od_adicion',
        'od_altura': 'altura_od',
        'od_nota': 'notaod',
        'oi_esf': 'oi_esf',
        'oi_cil': 'oi_cil',
        'oi_eje': 'ejeoi',
        'oi_dnp': 'dnpoi', 
        'oi_add': 'oi_adicion',
        'oi_altura': 'altura_oi',
        'oi_nota': 'notaoi',
        'dist_interpupilar': 'dist_interpupilar',
        
        // Campos adicionales que podrían existir
        'notas': 'notas', // Campo de notas generales si existe
        'observaciones': 'observaciones' // Campo de observaciones si existe
    };
    
    Object.keys(datos).forEach(key => {
        if (datos[key] !== null && datos[key] !== undefined && datos[key] !== '') {
            // Usar el mapeo si existe, sino usar el key original
            const elementId = fieldIdMapping[key] || key;
            const element = document.getElementById(elementId);
            
            if (element) {
                if (element.tagName === 'SELECT') {
                    // Para elementos select, intentar seleccionar la opción correcta
                    const option = Array.from(element.options).find(opt => 
                        opt.value === datos[key] || opt.textContent.trim() === datos[key]
                    );
                    if (option) {
                        element.selectedIndex = option.index;
                        console.log(`🎯 Select ${key} (${elementId}) seleccionado:`, datos[key]);
                    } else {
                        // Si no encuentra la opción, intentar añadirla temporalmente
                        const newOption = document.createElement('option');
                        newOption.value = datos[key];
                        newOption.textContent = datos[key];
                        newOption.selected = true;
                        element.appendChild(newOption);
                        console.log(`➕ Opción añadida y seleccionada en ${key} (${elementId}):`, datos[key]);
                    }
                } else {
                    // Para otros elementos, establecer el valor directamente
                    element.value = datos[key];
                }
                
                // Disparar eventos para notificar el cambio
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change', { bubbles: true }));
                
                console.log(`🔄 DOM actualizado ${key} → ${elementId}:`, datos[key]);
            } else {
                console.warn(`⚠️ Elemento no encontrado: ${key} → ${elementId}`);
            }
        }
    });
}

// Función de respaldo para cargar datos usando el método tradicional DOM
function cargarDatosEnFormularioTradicional(consulta) {
    console.log('📝 Cargando datos usando método DOM tradicional:', consulta);
    
    const tipoFormulario = consulta.tipo_formulario || 'general';
    
    // Cargar datos básicos comunes a todos los formularios
    const camposComunes = [
        {id: 'txtmotivo', valor: consulta.txtmotivo},
        {id: 'consulta-textarea', valor: consulta.diagnostico},
        {id: 'receta-textarea', valor: consulta.receta_textarea},
        {id: 'txtnota', valor: consulta.observaciones}
    ];
    
    // También buscar campos con sufijo del tipo de formulario
    const camposSufijo = [
        {id: `txtmotivo-${tipoFormulario}`, valor: consulta.txtmotivo},
        {id: `consulta-textarea-${tipoFormulario}`, valor: consulta.diagnostico},
        {id: `receta-textarea-${tipoFormulario}`, valor: consulta.receta_textarea}
    ];
    
    [...camposComunes, ...camposSufijo].forEach(({id, valor}) => {
        const elemento = document.getElementById(id);
        if (elemento && valor) {
            elemento.value = valor;
            console.log(`✅ Campo ${id} cargado:`, valor);
        }
    });
    
    // Cargar datos específicos según el tipo de formulario
    if (tipoFormulario === 'general') {
        const camposGenerales = [
            {id: 'visionod', valor: consulta.visionod},
            {id: 'visionoi', valor: consulta.visionoi},
            {id: 'tensionod', valor: consulta.tensionod},
            {id: 'tensionoi', valor: consulta.tensionoi}
        ];
        
        camposGenerales.forEach(({id, valor}) => {
            const elemento = document.getElementById(id);
            if (elemento && valor) {
                elemento.value = valor;
                console.log(`✅ Campo general ${id}:`, valor);
            }
        });
        
    } else if (tipoFormulario === 'anteojos') {
        const camposAnteojos = [
            {id: 'od_esf', valor: consulta.esfera_od},
            {id: 'od_cil', valor: consulta.cilindro_od},
            {id: 'ejeod', valor: consulta.eje_od},
            {id: 'dnpod', valor: consulta.dnp_od},
            {id: 'od_adicion', valor: consulta.add_od},
            {id: 'altura_od', valor: consulta.altura_od},
            {id: 'notaod', valor: consulta.nota_od},
            {id: 'oi_esf', valor: consulta.esfera_oi},
            {id: 'oi_cil', valor: consulta.cilindro_oi},
            {id: 'ejeoi', valor: consulta.eje_oi},
            {id: 'dnpoi', valor: consulta.dnp_oi},
            {id: 'oi_adicion', valor: consulta.add_oi},
            {id: 'altura_oi', valor: consulta.altura_oi},
            {id: 'notaoi', valor: consulta.nota_oi},
            {id: 'dist_interpupilar', valor: consulta.dist_interpupilar}
        ];
        
        camposAnteojos.forEach(({id, valor}) => {
            const elemento = document.getElementById(id);
            if (elemento && valor) {
                elemento.value = valor;
                // Para los selects, también intentar seleccionar la opción
                if (elemento.tagName === 'SELECT') {
                    const opcion = Array.from(elemento.options).find(opt => opt.value === valor);
                    if (opcion) {
                        elemento.selectedIndex = opcion.index;
                    }
                }
                console.log(`✅ Campo anteojos ${id}:`, valor);
            }
        });
    }
    
    console.log('✅ Datos cargados completamente en formulario', tipoFormulario);
}

function imprimirConsulta(id) {
    console.log('Imprimir consulta:', id);
    alert(`Imprimir consulta #${id}`);
}

// Ejecutar el parche cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Parche de historial y timeline cargado');
    
    // Esperar un momento para que todo se cargue
    setTimeout(() => {
        verificarYRepararElementos();
        interceptarFuncionesOriginales();
        console.log('✅ Parche aplicado exitosamente');
    }, 500);
});

// También ejecutar después de que jQuery esté listo
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function() {
        setTimeout(() => {
            verificarYRepararElementos();
            interceptarFuncionesOriginales();
            console.log('✅ Parche aplicado con jQuery');
        }, 1000);
    });
}

console.log('✅ Parche de historial y timeline cargado y listo');