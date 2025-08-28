// Versión ultra-simple del buscador de pacientes
console.log('🚀 Iniciando buscador ultra-simple...');

// Configuración
const API_URL = './modules/consultas/api/livwire-crud.php';
let searchTimeout;

// Función principal de búsqueda
async function buscarPacienteSimple(termino) {
    if (termino.length < 2) {
        ocultarSugerencias();
        return;
    }
    
    console.log('🔍 Buscando:', termino);
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'validateField',
                data: {
                    property: 'search_nombre',
                    value: termino,
                    formType: 'general'
                }
            })
        });
        
        const result = await response.json();
        console.log('✅ Respuesta:', result);
        
        if (result.success && result.data.patients) {
            mostrarSugerencias(result.data.patients);
        } else {
            ocultarSugerencias();
        }
        
    } catch (error) {
        console.error('❌ Error:', error);
        ocultarSugerencias();
    }
}

// Mostrar sugerencias
function mostrarSugerencias(pacientes) {
    let dropdown = document.getElementById('suggestions-simple');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'suggestions-simple';
        dropdown.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 9999;
            max-height: 200px;
            overflow-y: auto;
        `;
        
        const campo = document.getElementById('paciente');
        if (campo && campo.parentNode) {
            campo.parentNode.style.position = 'relative';
            campo.parentNode.appendChild(dropdown);
        }
    }
    
    if (pacientes.length > 0) {
        dropdown.innerHTML = pacientes.map(p => 
            `<div onclick="seleccionarPacienteSimple(${p.id}, '${p.nombre}', '${p.dni}', '${p.ficha}')" 
                  style="padding: 10px; cursor: pointer; border-bottom: 1px solid #eee;"
                  onmouseover="this.style.background='#f5f5f5'"
                  onmouseout="this.style.background='white'">
                <strong>${p.nombre}</strong><br>
                <small>DNI: ${p.dni} - Ficha: ${p.ficha}</small>
            </div>`
        ).join('');
        dropdown.style.display = 'block';
        console.log('👥 Sugerencias mostradas:', pacientes.length);
    } else {
        ocultarSugerencias();
    }
}

// Ocultar sugerencias
function ocultarSugerencias() {
    const dropdown = document.getElementById('suggestions-simple');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
}

// Seleccionar paciente
async function seleccionarPacienteSimple(id, nombre, dni, ficha) {
    console.log('✅ Seleccionando:', nombre);
    
    // Llenar campos principales
    const campo = document.getElementById('paciente');
    if (campo) campo.value = nombre;
    
    const campoDoc = document.getElementById('txtdocumento');
    if (campoDoc) campoDoc.value = dni;
    
    const campoFicha = document.getElementById('txtficha');
    if (campoFicha) campoFicha.value = ficha;
    
    // Ocultar sugerencias
    ocultarSugerencias();
    
    // Cargar datos completos
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'loadPatient',
                data: { id: id }
            })
        });
        
        const result = await response.json();
        console.log('📋 Datos completos:', result);
        
        if (result.success && result.data) {
            const data = result.data;
            
            // Actualizar profile display
            const profileName = document.getElementById('profile-username');
            const profileCi = document.getElementById('profile-ci');
            const patientInfo = document.getElementById('patient-info-display');
            
            if (profileName) profileName.textContent = data.nombre;
            if (profileCi) profileCi.textContent = `Doc: ${data.documento} - Ficha: ${data.ficha}`;
            if (patientInfo) patientInfo.style.display = 'block';
            
            // Llenar todos los campos disponibles
            const campos = {
                'paciente': data.nombre,
                'txtdocumento': data.documento,
                'txtficha': data.ficha,
                'email': data.email,
                'whatsapp': data.whatsapp,
                'direccion': data.direccion,
                'fecha_nacimiento': data.fecha_nacimiento,
                'genero': data.genero
            };
            
            // Actualizar campos si existen
            Object.keys(campos).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && campos[fieldId]) {
                    field.value = campos[fieldId];
                    console.log(`📝 Campo ${fieldId} actualizado:`, campos[fieldId]);
                }
            });
            
            // Llenar campos ocultos
            const hiddenInputs = document.querySelectorAll('#idPersona, #id_persona_file');
            hiddenInputs.forEach(input => input.value = id);
            
            // Disparar eventos change para notificar a otros sistemas
            Object.keys(campos).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            
            console.log('✅ Paciente cargado completamente con todos los datos');
            
            // Cargar datos históricos del paciente
            await cargarDatosHistoricos(id);
        }
    } catch (error) {
        console.error('❌ Error cargando datos completos:', error);
    }
}

// Cargar datos históricos del paciente
async function cargarDatosHistoricos(patientId) {
    console.log('📚 Cargando datos históricos del paciente:', patientId);
    
    try {
        // Usar API directo que sabemos que funciona
        const response = await fetch('./patient_history_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'loadPatientHistory',
                data: { id: patientId }
            })
        });
        
        const result = await response.json();
        console.log('📊 Datos históricos recibidos:', result);
        
        if (result.success && result.data) {
            const data = result.data;
            
            // Actualizar contador de consultas
            actualizarContadores(data.consultas, data.cuota_mb);
            
            // Actualizar historial
            if (data.historial && data.historial.length > 0) {
                actualizarHistorial(data.historial);
                
                // Mostrar badge con cantidad en la pestaña
                actualizarBadgePestana('historial-tab', data.historial.length);
            } else {
                mostrarMensajeVacio('historial');
            }
            
            // Actualizar timeline
            if (data.timeline && data.timeline.length > 0) {
                actualizarTimeline(data.timeline);
                
                // Mostrar badge con cantidad en la pestaña
                actualizarBadgePestana('timeline-tab', data.timeline.length);
            } else {
                mostrarMensajeVacio('timeline');
            }
            
            // Actualizar archivos
            if (data.archivos && data.archivos.length > 0) {
                actualizarArchivos(data.archivos);
                
                // Mostrar badge con cantidad en la pestaña
                actualizarBadgePestana('archivos-tab', data.archivos.length);
            } else {
                mostrarMensajeVacio('archivos');
            }
            
            // Activar automáticamente la pestaña de historial si tiene datos
            if (data.historial && data.historial.length > 0) {
                activarPestana('historial-panel');
            }
            
            console.log('✅ Datos históricos cargados exitosamente');
        }
        
    } catch (error) {
        console.error('❌ Error cargando datos históricos:', error);
    }
}

// Actualizar contadores en la interfaz
function actualizarContadores(consultas, cuotaMB) {
    // Buscar elementos de contador de consultas (usar los IDs específicos)
    const consultasElement = document.getElementById('txtCantConsulta');
    if (consultasElement) {
        consultasElement.textContent = consultas;
        console.log(`📊 Contador consultas actualizado: ${consultas}`);
    } else {
        console.log('❌ No se encontró elemento #txtCantConsulta');
    }
    
    // Buscar elementos de cuota MB (usar el ID específico)
    const cuotaElement = document.getElementById('cuota-valor');
    if (cuotaElement) {
        cuotaElement.textContent = cuotaMB;
        console.log(`💾 Contador cuota MB actualizado: ${cuotaMB}`);
    } else {
        console.log('❌ No se encontró elemento #cuota-valor');
    }
    
    // También buscar otros elementos por si acaso
    const consultasElements = document.querySelectorAll('.consultas-count, #consultas-count, [data-consultas]');
    consultasElements.forEach(el => {
        el.textContent = consultas;
    });
    
    const cuotaElements = document.querySelectorAll('.cuota-mb, #cuota-mb, [data-cuota-mb]');
    cuotaElements.forEach(el => {
        el.textContent = cuotaMB + ' MB';
    });
    
    console.log(`📊 Contadores actualizados: ${consultas} consultas, ${cuotaMB} MB`);
}

// Actualizar historial de consultas
function actualizarHistorial(historial) {
    console.log('📋 Actualizando historial con', historial.length, 'consultas');
    
    // Buscar la tabla de consultas específica
    const historialTable = document.querySelector('#tabla-consultas tbody');
    
    if (historialTable) {
        if (historial.length > 0) {
            const historialHTML = historial.map((consulta, index) => `
                <tr class="consulta-row" data-consulta-id="${consulta.id_consulta}">
                    <td class="fecha-col">
                        <div class="fecha-display">
                            ${formatearFechaCompleta(consulta.fecha_registro)}
                        </div>
                    </td>
                    <td class="paciente-col">
                        <div class="paciente-info">
                            <strong>Paciente Actual</strong>
                        </div>
                    </td>
                    <td class="tipo-col">
                        <span class="badge badge-${getTipoBadge(consulta.tipo_formulario)} tipo-badge">
                            <i class="fas fa-${getTipoIcon(consulta.tipo_formulario)}"></i>
                            ${formatearTipoConsulta(consulta.tipo_formulario)}
                        </span>
                    </td>
                    <td class="acciones-col">
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-info btn-ver-consulta" 
                                    onclick="verDetalleConsulta(${consulta.id_consulta})"
                                    title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-primary btn-editar-consulta" 
                                    onclick="editarConsulta(${consulta.id_consulta})"
                                    title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${consulta.tipo_formulario ? `
                                <button class="btn btn-sm btn-success btn-imprimir" 
                                        onclick="imprimirConsulta(${consulta.id_consulta})"
                                        title="Imprimir">
                                    <i class="fas fa-print"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
                ${consulta.diagnostico || consulta.motivo_consulta ? `
                    <tr class="consulta-detalle" data-consulta-id="${consulta.id_consulta}">
                        <td colspan="4" class="detalle-consulta">
                            ${consulta.motivo_consulta ? `
                                <div class="motivo-consulta">
                                    <strong>Motivo:</strong> ${consulta.motivo_consulta}
                                </div>
                            ` : ''}
                            ${consulta.diagnostico ? `
                                <div class="diagnostico-consulta">
                                    <strong>Diagnóstico/Observaciones:</strong> ${consulta.diagnostico}
                                </div>
                            ` : ''}
                        </td>
                    </tr>
                ` : ''}
            `).join('');
            
            historialTable.innerHTML = historialHTML;
            
            // Agregar eventos de expansión/colapso
            configurarEventosHistorial();
            
            console.log(`📋 Historial actualizado con ${historial.length} consultas en tabla`);
        } else {
            historialTable.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <div>No hay consultas registradas para este paciente</div>
                    </td>
                </tr>
            `;
        }
    } else {
        console.log('⚠️ No se encontró tabla #tabla-consultas tbody');
    }
}

// Actualizar timeline de actividades
function actualizarTimeline(timeline) {
    console.log('📅 Actualizando timeline con', timeline.length, 'actividades');
    
    // Buscar el timeline específico
    const timelineContainer = document.getElementById('timeline');
    
    if (timelineContainer) {
        if (timeline.length > 0) {
            const timelineHTML = timeline.map((actividad, index) => `
                <div class="timeline-item timeline-${actividad.tipo}" data-timeline-id="${actividad.referencia_id}">
                    <div class="timeline-marker">
                        <i class="fas fa-${getTimelineIcon(actividad.tipo_formulario || actividad.tipo)}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <div class="timeline-fecha">
                                <i class="fas fa-calendar-alt"></i>
                                ${formatearFechaCompleta(actividad.fecha)}
                            </div>
                            <div class="timeline-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="verDetalleConsulta(${actividad.referencia_id})">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                            </div>
                        </div>
                        <div class="timeline-descripcion">
                            ${actividad.descripcion}
                        </div>
                        <div class="timeline-footer">
                            <span class="badge badge-${getTipoBadge(actividad.tipo_formulario)} tipo-timeline">
                                <i class="fas fa-${getTipoIcon(actividad.tipo_formulario)}"></i>
                                ${formatearTipoConsulta(actividad.tipo_formulario || actividad.tipo)}
                            </span>
                            <span class="timeline-id text-muted">
                                ID: ${actividad.referencia_id}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
            
            timelineContainer.innerHTML = `
                <div class="timeline-header-main">
                    <h5><i class="fas fa-clock"></i> Timeline de Actividades</h5>
                    <div class="timeline-stats">
                        <span class="badge badge-info">${timeline.length} actividades</span>
                    </div>
                </div>
                <div class="timeline-content-wrapper">
                    ${timelineHTML}
                </div>
            `;
            console.log(`📅 Timeline actualizado con ${timeline.length} actividades`);
        } else {
            timelineContainer.innerHTML = `
                <div class="timeline-empty">
                    <div class="text-center py-5">
                        <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay actividades registradas</h5>
                        <p class="text-muted">Las actividades aparecerán aquí cuando el paciente tenga consultas.</p>
                    </div>
                </div>
            `;
        }
    } else {
        console.log('⚠️ No se encontró contenedor #timeline');
    }
}

// Actualizar lista de archivos
function actualizarArchivos(archivos) {
    console.log('📎 Actualizando archivos con', archivos.length, 'elementos');
    
    // Buscar el contenedor de archivos
    const archivosContainer = document.getElementById('archivos-container') || 
                              document.querySelector('.archivos-container') ||
                              document.querySelector('[data-archivos]') ||
                              document.querySelector('#archivos-panel .form-section');
    
    if (archivosContainer) {
        if (archivos.length > 0) {
            const archivosHTML = `
                <div class="archivos-header">
                    <h5><i class="fas fa-folder-open"></i> Archivos del Paciente</h5>
                    <div class="archivos-stats">
                        <span class="badge badge-info">${archivos.length} archivo(s)</span>
                        <span class="badge badge-secondary">${calcularTamañoTotal(archivos)}</span>
                    </div>
                </div>
                <div class="archivos-list">
                    ${archivos.map(archivo => `
                        <div class="archivo-item" data-archivo-id="${archivo.id}">
                            <div class="archivo-icon">
                                <i class="fas fa-${getArchivoIcon(archivo.tipo_archivo)}"></i>
                            </div>
                            <div class="archivo-info">
                                <div class="archivo-nombre">
                                    <strong>${archivo.nombre_archivo}</strong>
                                </div>
                                <div class="archivo-detalles">
                                    <span class="archivo-tipo">${archivo.tipo_archivo}</span>
                                    <span class="archivo-tamaño">${formatearTamaño(archivo.archivo_tamaño)}</span>
                                    <span class="archivo-fecha">${formatearFecha(archivo.fecha_subida)}</span>
                                </div>
                                ${archivo.descripcion ? `
                                    <div class="archivo-descripcion">
                                        <small class="text-muted">${archivo.descripcion}</small>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="archivo-acciones">
                                <button class="btn btn-sm btn-primary" onclick="descargarArchivo(${archivo.id})">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="verArchivo(${archivo.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="eliminarArchivo(${archivo.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="archivos-actions mt-3">
                    <button class="btn btn-success" onclick="subirNuevoArchivo()">
                        <i class="fas fa-plus"></i> Subir Archivo
                    </button>
                </div>
            `;
            
            archivosContainer.innerHTML = archivosHTML;
            console.log(`📎 Archivos actualizados con ${archivos.length} elementos`);
        } else {
            archivosContainer.innerHTML = `
                <div class="archivos-empty">
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No hay archivos</h5>
                        <p class="text-muted">Los archivos del paciente aparecerán aquí.</p>
                        <button class="btn btn-primary" onclick="subirNuevoArchivo()">
                            <i class="fas fa-plus"></i> Subir primer archivo
                        </button>
                    </div>
                </div>
            `;
        }
    } else {
        console.log('⚠️ No se encontró contenedor de archivos');
    }
}

// Funciones utilitarias para formato
function formatearFecha(fecha) {
    if (!fecha) return 'Sin fecha';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatearTamaño(bytes) {
    if (!bytes) return '0 B';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
}

// Función para obtener el color del badge según el tipo
function getTipoBadge(tipo) {
    const tipos = {
        'general': 'primary',
        'anteojos': 'success', 
        'estudios': 'info',
        'informe_imagen': 'warning',
        'consulta': 'secondary'
    };
    return tipos[tipo] || 'secondary';
}

// Función para obtener el icono del timeline según el tipo
function getTimelineIcon(tipo) {
    const iconos = {
        'consulta': 'stethoscope',
        'anteojos': 'glasses',
        'estudios': 'x-ray',
        'informe_imagen': 'image',
        'archivo': 'file'
    };
    return iconos[tipo] || 'clock';
}

// Función para ver detalle de consulta (placeholder)
function verDetalleConsulta(consultaId) {
    console.log('Ver detalle de consulta:', consultaId);
    // Aquí se puede implementar la lógica para mostrar el detalle
    alert(`Ver detalle de consulta ID: ${consultaId}`);
}

// Función para editar consulta
function editarConsulta(consultaId) {
    console.log('Editar consulta:', consultaId);
    // Redirigir a la edición de consulta
    window.location.href = `?ruta=consultas-new&id_consulta=${consultaId}`;
}

// Función para imprimir consulta
function imprimirConsulta(consultaId) {
    console.log('Imprimir consulta:', consultaId);
    // Abrir PDF de la consulta
    window.open(`generar_pdf_consulta.php?id=${consultaId}`, '_blank');
}

// Funciones para archivos
function descargarArchivo(archivoId) {
    console.log('Descargar archivo:', archivoId);
    window.location.href = `download_file.php?id=${archivoId}`;
}

function verArchivo(archivoId) {
    console.log('Ver archivo:', archivoId);
    window.open(`view_file.php?id=${archivoId}`, '_blank');
}

function eliminarArchivo(archivoId) {
    console.log('Eliminar archivo:', archivoId);
    if (confirm('¿Está seguro de que desea eliminar este archivo?')) {
        // Implementar lógica de eliminación
        alert('Funcionalidad de eliminar archivo por implementar');
    }
}

function subirNuevoArchivo() {
    console.log('Subir nuevo archivo');
    // Mostrar modal de subida de archivos
    alert('Funcionalidad de subir archivo por implementar');
}

// Función para formatear fecha completa
function formatearFechaCompleta(fecha) {
    if (!fecha) return 'Sin fecha';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Función para formatear tipo de consulta
function formatearTipoConsulta(tipo) {
    const tipos = {
        'general': 'General',
        'anteojos': 'Anteojos',
        'estudios': 'Estudios',
        'informe_imagen': 'Informe + Imagen',
        'consulta': 'Consulta'
    };
    return tipos[tipo] || 'General';
}

// Función para obtener icono de tipo
function getTipoIcon(tipo) {
    const iconos = {
        'general': 'stethoscope',
        'anteojos': 'glasses',
        'estudios': 'x-ray',
        'informe_imagen': 'image',
        'consulta': 'notes-medical'
    };
    return iconos[tipo] || 'stethoscope';
}

// Función para obtener icono de archivo
function getArchivoIcon(tipoArchivo) {
    const iconos = {
        'pdf': 'file-pdf',
        'jpg': 'file-image',
        'jpeg': 'file-image',
        'png': 'file-image',
        'gif': 'file-image',
        'doc': 'file-word',
        'docx': 'file-word',
        'xls': 'file-excel',
        'xlsx': 'file-excel',
        'txt': 'file-alt',
        'zip': 'file-archive',
        'rar': 'file-archive'
    };
    return iconos[tipoArchivo?.toLowerCase()] || 'file';
}

// Función para calcular tamaño total de archivos
function calcularTamañoTotal(archivos) {
    const total = archivos.reduce((sum, archivo) => sum + (archivo.archivo_tamaño || 0), 0);
    return formatearTamaño(total);
}

// Función para actualizar badge de pestañas
function actualizarBadgePestana(tabId, cantidad) {
    const tab = document.querySelector(`[href="#${tabId.replace('-tab', '-panel')}"]`);
    if (tab) {
        // Remover badge anterior
        const existingBadge = tab.querySelector('.badge');
        if (existingBadge) existingBadge.remove();
        
        // Agregar nuevo badge
        if (cantidad > 0) {
            const badge = document.createElement('span');
            badge.className = 'badge badge-primary ml-1';
            badge.textContent = cantidad;
            tab.appendChild(badge);
        }
    }
}

// Función para mostrar mensaje vacío
function mostrarMensajeVacio(tipo) {
    const mensajes = {
        'historial': 'No hay consultas registradas',
        'timeline': 'No hay actividades registradas', 
        'archivos': 'No hay archivos subidos'
    };
    
    console.log(`ℹ️ ${mensajes[tipo]} para este paciente`);
}

// Función para activar una pestaña
function activarPestana(panelId) {
    // Buscar y activar la pestaña correspondiente
    const panel = document.getElementById(panelId);
    if (panel) {
        const tabLink = document.querySelector(`[href="#${panelId}"]`);
        if (tabLink) {
            // Simular click en la pestaña
            setTimeout(() => {
                tabLink.click();
            }, 500);
        }
    }
}

// Función para configurar eventos del historial
function configurarEventosHistorial() {
    // Agregar eventos de click para expandir/colapsar detalles
    document.querySelectorAll('.consulta-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Solo si no se hizo click en un botón
            if (!e.target.closest('button')) {
                const consultaId = this.dataset.consultaId;
                const detalleRow = document.querySelector(`.consulta-detalle[data-consulta-id="${consultaId}"]`);
                if (detalleRow) {
                    detalleRow.style.display = detalleRow.style.display === 'none' ? 'table-row' : 'none';
                }
            }
        });
    });
}

// Configurar eventos al cargar
document.addEventListener('DOMContentLoaded', function() {
    const campo = document.getElementById('paciente');
    if (campo) {
        console.log('🔗 Conectando eventos al campo paciente');
        
        campo.addEventListener('input', function(e) {
            const valor = e.target.value.trim();
            console.log('📝 Input detectado:', valor);
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                buscarPacienteSimple(valor);
            }, 500);
        });
        
        // Ocultar al perder foco (con delay para permitir clicks)
        campo.addEventListener('blur', function() {
            setTimeout(ocultarSugerencias, 200);
        });
        
        console.log('✅ Eventos configurados correctamente');
    } else {
        console.log('❌ Campo #paciente no encontrado');
    }
});

// Exponer funciones globalmente
window.buscarPacienteSimple = buscarPacienteSimple;
window.seleccionarPacienteSimple = seleccionarPacienteSimple;
window.cargarDatosHistoricos = cargarDatosHistoricos;

console.log('✅ Buscador ultra-simple cargado');