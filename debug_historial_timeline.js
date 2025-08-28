/**
 * DEBUG Y CORRECCIÓN DE HISTORIAL Y TIMELINE
 * 
 * Script mejorado que incluye debug detallado y retry logic
 * para asegurar que el historial y timeline se muestren correctamente
 */

// Debug: Verificar elementos
function debugElementos() {
    console.log('🔍 === DEBUG DE ELEMENTOS ===');
    
    const elementos = [
        '#tabla-consultas',
        '#tabla-consultas tbody', 
        '#timeline',
        '#timeline-container',
        '#archivos-container'
    ];
    
    elementos.forEach(selector => {
        const elemento = document.querySelector(selector);
        console.log(`🎯 ${selector}:`, elemento ? '✅ ENCONTRADO' : '❌ NO ENCONTRADO', elemento);
    });
    
    // Verificar pestañas
    const pestanas = document.querySelectorAll('.nav-pills .nav-link');
    console.log('🏷️ Pestañas encontradas:', pestanas.length);
    pestanas.forEach((tab, i) => {
        console.log(`   Pestaña ${i}:`, tab.textContent.trim(), tab.getAttribute('href'));
    });
}

// Función de actualización de historial con retry y debug
function actualizarHistorialDebug(historial, reintentos = 0) {
    console.log('📋 === ACTUALIZANDO HISTORIAL (intento ' + (reintentos + 1) + ') ===');
    console.log('📊 Datos recibidos:', historial);
    
    if (!historial || historial.length === 0) {
        console.log('⚠️ No hay datos de historial para mostrar');
        return;
    }
    
    // Buscar tabla con múltiples selectores
    let tablaBody = document.querySelector('#tabla-consultas tbody');
    if (!tablaBody) {
        tablaBody = document.querySelector('#tabla-consultas');
        console.log('⚠️ tbody no encontrado, usando tabla completa');
    }
    
    if (!tablaBody && reintentos < 3) {
        console.log('⏱️ Tabla no disponible, reintentando en 500ms...');
        setTimeout(() => actualizarHistorialDebug(historial, reintentos + 1), 500);
        return;
    }
    
    if (!tablaBody) {
        console.log('❌ No se pudo encontrar la tabla de consultas después de varios intentos');
        return;
    }
    
    console.log('✅ Tabla encontrada:', tablaBody);
    
    // Generar HTML del historial
    const historialHTML = historial.map((consulta, index) => {
        return `
            <tr class="consulta-row" data-consulta-id="${consulta.id_consulta || index}">
                <td class="fecha-col">
                    <div class="fecha-display">
                        ${consulta.fecha_registro || 'Sin fecha'}
                    </div>
                </td>
                <td class="paciente-col">
                    <div class="paciente-info">
                        <strong>Consulta #${consulta.id_consulta || index + 1}</strong>
                        <small class="d-block text-muted">${consulta.motivo || 'Sin motivo especificado'}</small>
                    </div>
                </td>
                <td class="tipo-col">
                    <span class="badge badge-primary tipo-badge">
                        <i class="fas fa-stethoscope"></i>
                        ${consulta.tipo_formulario || 'Consulta'}
                    </span>
                </td>
                <td class="acciones-col">
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-info btn-sm" onclick="verConsulta(${consulta.id_consulta || index})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="editarConsulta(${consulta.id_consulta || index})">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="imprimirConsulta(${consulta.id_consulta || index})">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
    
    console.log('📝 HTML generado:', historialHTML.substring(0, 200) + '...');
    
    // Insertar en la tabla
    if (tablaBody.tagName === 'TABLE') {
        // Si es la tabla completa, necesitamos crear/encontrar tbody
        let tbody = tablaBody.querySelector('tbody');
        if (!tbody) {
            tbody = document.createElement('tbody');
            tablaBody.appendChild(tbody);
        }
        tbody.innerHTML = historialHTML;
    } else {
        // Es el tbody directamente
        tablaBody.innerHTML = historialHTML;
    }
    
    console.log('✅ Historial insertado en la tabla');
    
    // Verificar que se insertó correctamente
    const filas = tablaBody.querySelectorAll('tr');
    console.log('📊 Filas generadas:', filas.length);
    
    // Hacer visible la pestaña de historial si hay datos
    actualizarBadgeHistorial(historial.length);
}

// Actualizar timeline con debug
function actualizarTimelineDebug(timeline, reintentos = 0) {
    console.log('📅 === ACTUALIZANDO TIMELINE (intento ' + (reintentos + 1) + ') ===');
    console.log('📊 Datos timeline:', timeline);
    
    if (!timeline || timeline.length === 0) {
        console.log('⚠️ No hay datos de timeline para mostrar');
        return;
    }
    
    // Buscar contenedor timeline
    let timelineContainer = document.querySelector('#timeline');
    if (!timelineContainer) {
        timelineContainer = document.querySelector('#timeline-container');
        console.log('⚠️ #timeline no encontrado, usando #timeline-container');
    }
    
    if (!timelineContainer && reintentos < 3) {
        console.log('⏱️ Timeline container no disponible, reintentando en 500ms...');
        setTimeout(() => actualizarTimelineDebug(timeline, reintentos + 1), 500);
        return;
    }
    
    if (!timelineContainer) {
        console.log('❌ No se pudo encontrar el contenedor timeline');
        return;
    }
    
    console.log('✅ Timeline container encontrado:', timelineContainer);
    
    // Generar HTML del timeline
    const timelineHTML = `
        <div class="timeline-header-main">
            <h5><i class="fas fa-clock"></i> Timeline de Actividades</h5>
            <div class="timeline-stats">
                <span class="badge badge-info">${timeline.length} actividades</span>
            </div>
        </div>
        <div class="timeline-content-wrapper">
            ${timeline.map((actividad, index) => `
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <span class="timeline-fecha">${actividad.fecha || 'Sin fecha'}</span>
                            <span class="badge badge-secondary tipo-timeline">${actividad.tipo || 'Actividad'}</span>
                        </div>
                        <div class="timeline-descripcion">
                            ${actividad.descripcion || actividad.motivo || 'Sin descripción disponible'}
                        </div>
                        <div class="timeline-footer">
                            <span class="timeline-id">ID: ${actividad.id || index + 1}</span>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    console.log('📝 Timeline HTML generado:', timelineHTML.substring(0, 200) + '...');
    
    timelineContainer.innerHTML = timelineHTML;
    
    console.log('✅ Timeline insertado en el contenedor');
    
    // Actualizar badge de timeline
    actualizarBadgeTimeline(timeline.length);
}

// Actualizar badges de las pestañas
function actualizarBadgeHistorial(cantidad) {
    const historialTab = document.querySelector('a[href="#historial-panel"]');
    if (historialTab) {
        let badge = historialTab.querySelector('.badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge badge-primary';
            historialTab.appendChild(badge);
        }
        badge.textContent = cantidad;
        console.log('✅ Badge historial actualizado:', cantidad);
    }
}

function actualizarBadgeTimeline(cantidad) {
    const timelineTab = document.querySelector('a[href="#timeline-panel"]');
    if (timelineTab) {
        let badge = timelineTab.querySelector('.badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge badge-info';
            timelineTab.appendChild(badge);
        }
        badge.textContent = cantidad;
        console.log('✅ Badge timeline actualizado:', cantidad);
    }
}

// Función para probar manualmente
function probarHistorialManual() {
    console.log('🧪 === PRUEBA MANUAL DEL HISTORIAL ===');
    
    // Datos de prueba
    const historialPrueba = [
        {
            id_consulta: 1,
            fecha_registro: '2024-08-27',
            motivo: 'Consulta de prueba 1',
            tipo_formulario: 'Consulta General'
        },
        {
            id_consulta: 2,
            fecha_registro: '2024-08-26',
            motivo: 'Consulta de prueba 2',
            tipo_formulario: 'Control'
        },
        {
            id_consulta: 3,
            fecha_registro: '2024-08-25',
            motivo: 'Consulta de prueba 3',
            tipo_formulario: 'Emergencia'
        }
    ];
    
    const timelinePrueba = [
        {
            fecha: '2024-08-27 10:00',
            tipo: 'Consulta',
            descripcion: 'Consulta médica realizada',
            id: 1
        },
        {
            fecha: '2024-08-26 15:30',
            tipo: 'Control',
            descripcion: 'Control de seguimiento',
            id: 2
        }
    ];
    
    debugElementos();
    actualizarHistorialDebug(historialPrueba);
    actualizarTimelineDebug(timelinePrueba);
}

// Auto-ejecutar debug al cargar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Debug de historial y timeline cargado');
    
    // Esperar un poco y hacer debug automático
    setTimeout(debugElementos, 1000);
    
    // Exponer función global para pruebas
    window.probarHistorialManual = probarHistorialManual;
    window.debugElementos = debugElementos;
    
    console.log('✅ Funciones de debug disponibles globalmente');
    console.log('   - probarHistorialManual()');
    console.log('   - debugElementos()');
});

// Interceptar las llamadas originales y agregar debug
if (typeof actualizarHistorial === 'function') {
    console.log('🔄 Interceptando función actualizarHistorial original');
    const originalActualizarHistorial = actualizarHistorial;
    
    window.actualizarHistorial = function(historial) {
        console.log('🔄 Llamada interceptada a actualizarHistorial');
        actualizarHistorialDebug(historial);
        // También llamar la original por si acaso
        try {
            originalActualizarHistorial(historial);
        } catch (e) {
            console.log('⚠️ Error en función original:', e);
        }
    };
}

if (typeof actualizarTimeline === 'function') {
    console.log('🔄 Interceptando función actualizarTimeline original');
    const originalActualizarTimeline = actualizarTimeline;
    
    window.actualizarTimeline = function(timeline) {
        console.log('🔄 Llamada interceptada a actualizarTimeline');
        actualizarTimelineDebug(timeline);
        // También llamar la original por si acaso
        try {
            originalActualizarTimeline(timeline);
        } catch (e) {
            console.log('⚠️ Error en función original:', e);
        }
    };
}