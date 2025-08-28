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
    alert(`Editar consulta #${id}`);
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