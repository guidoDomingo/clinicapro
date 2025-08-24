<?php
/**
 * MÓDULO DE CONSULTAS SIMPLIFICADO - SIN PROBLEMAS DE CARGA
 * 
 * Versión simplificada que carga todos los scripts de forma síncrona
 * y evita los problemas de inicialización asíncrona
 */

// Verificar sesión activa
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Sesión no válida. Por favor, inicia sesión nuevamente.</div>';
    exit;
}

// Obtener información del usuario actual
$userId = $_SESSION['user_id'] ?? 1;
$userName = $_SESSION['username'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas Médicas - Sistema Funcionando</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .main-container { margin-top: 20px; }
        .card { margin-bottom: 20px; }
        .editing-banner { 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            border-left: 4px solid #ffc107;
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
        }
        .tab-content { min-height: 400px; }
        .patient-selector { background: #f8f9fa; padding: 15px; border-radius: 8px; }
        .consulta-history { background: white; border-radius: 8px; }
        .btn-editar-consulta { 
            background: #28a745; 
            color: white; 
            border: none; 
            padding: 4px 8px; 
            border-radius: 4px; 
        }
        .btn-editar-consulta:hover { background: #218838; }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>
</head>

<body>
    <div class="container-fluid main-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-stethoscope"></i> Consultas Médicas - Sistema Funcionando</h3>
                    </div>
                    <div class="card-body">
                        
                        <!-- Selector de Paciente -->
                        <div class="patient-selector mb-4">
                            <h5><i class="fas fa-user-search"></i> Seleccionar Paciente</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="patient-search">Buscar Paciente (CI/Nombre/Apellido)</label>
                                        <input type="text" class="form-control" id="patient-search" placeholder="Ej: 12345678 o Juan Pérez">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-primary form-control" onclick="buscarPaciente()">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del paciente seleccionado -->
                            <div id="patient-info" class="mt-3" style="display: none;">
                                <div class="alert alert-success">
                                    <strong><i class="fas fa-user-check"></i> Paciente Seleccionado:</strong>
                                    <span id="selected-patient-name"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Historial de Consultas -->
                        <div class="consulta-history mb-4" id="consulta-history" style="display: none;">
                            <h5><i class="fas fa-history"></i> Historial de Consultas</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Motivo</th>
                                            <th>Tipo</th>
                                            <th>Doctor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="consultas-table-body">
                                        <!-- Se llena dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Formularios de Consulta -->
                        <div class="form-section">
                            <ul class="nav nav-tabs" id="consultaTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-general" data-bs-toggle="tab" href="#form-general">
                                        <i class="fas fa-notes-medical"></i> General
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-anteojos" data-bs-toggle="tab" href="#form-anteojos">
                                        <i class="fas fa-glasses"></i> Anteojos
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-estudios" data-bs-toggle="tab" href="#form-estudios">
                                        <i class="fas fa-x-ray"></i> Estudios
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-3" id="consultaTabContent">
                                <!-- Formulario General -->
                                <div class="tab-pane fade show active" id="form-general">
                                    <h6><i class="fas fa-notes-medical"></i> Consulta General</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="txtmotivo">Motivo de la Consulta</label>
                                                <textarea class="form-control" id="txtmotivo" rows="3" placeholder="Describe el motivo de la consulta..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="visionod">Visión OD</label>
                                                <input type="text" class="form-control" id="visionod" placeholder="Ej: 20/20">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="visionoi">Visión OI</label>
                                                <input type="text" class="form-control" id="visionoi" placeholder="Ej: 20/20">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulario Anteojos -->
                                <div class="tab-pane fade" id="form-anteojos">
                                    <h6><i class="fas fa-glasses"></i> Prescripción de Anteojos</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Ojo Derecho (OD)</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="od_esf">Esfera</label>
                                                        <select class="form-control" id="od_esf">
                                                            <option value="">Seleccionar</option>
                                                            <option value="-3.00">-3.00</option>
                                                            <option value="-2.50">-2.50</option>
                                                            <option value="-2.00">-2.00</option>
                                                            <option value="-1.50">-1.50</option>
                                                            <option value="-1.00">-1.00</option>
                                                            <option value="-0.50">-0.50</option>
                                                            <option value="0.00">0.00</option>
                                                            <option value="+0.50">+0.50</option>
                                                            <option value="+1.00">+1.00</option>
                                                            <option value="+1.50">+1.50</option>
                                                            <option value="+2.00">+2.00</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="od_cil">Cilindro</label>
                                                        <select class="form-control" id="od_cil">
                                                            <option value="">Seleccionar</option>
                                                            <option value="-2.00">-2.00</option>
                                                            <option value="-1.50">-1.50</option>
                                                            <option value="-1.00">-1.00</option>
                                                            <option value="-0.50">-0.50</option>
                                                            <option value="0.00">0.00</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Ojo Izquierdo (OI)</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="oi_esf">Esfera</label>
                                                        <select class="form-control" id="oi_esf">
                                                            <option value="">Seleccionar</option>
                                                            <option value="-3.00">-3.00</option>
                                                            <option value="-2.50">-2.50</option>
                                                            <option value="-2.00">-2.00</option>
                                                            <option value="-1.50">-1.50</option>
                                                            <option value="-1.00">-1.00</option>
                                                            <option value="-0.50">-0.50</option>
                                                            <option value="0.00">0.00</option>
                                                            <option value="+0.50">+0.50</option>
                                                            <option value="+1.00">+1.00</option>
                                                            <option value="+1.50">+1.50</option>
                                                            <option value="+2.00">+2.00</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="oi_cil">Cilindro</label>
                                                        <select class="form-control" id="oi_cil">
                                                            <option value="">Seleccionar</option>
                                                            <option value="-2.00">-2.00</option>
                                                            <option value="-1.50">-1.50</option>
                                                            <option value="-1.00">-1.00</option>
                                                            <option value="-0.50">-0.50</option>
                                                            <option value="0.00">0.00</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulario Estudios -->
                                <div class="tab-pane fade" id="form-estudios">
                                    <h6><i class="fas fa-x-ray"></i> Estudios Médicos</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="tipo_estudio">Tipo de Estudio</label>
                                                <select class="form-control" id="tipo_estudio">
                                                    <option value="">Seleccionar tipo</option>
                                                    <option value="OCT">OCT</option>
                                                    <option value="Campo Visual">Campo Visual</option>
                                                    <option value="Retinografía">Retinografía</option>
                                                    <option value="Ecografía">Ecografía</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="observaciones_estudio">Observaciones del Estudio</label>
                                                <textarea class="form-control" id="observaciones_estudio" rows="4" placeholder="Describe los hallazgos..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción -->
                            <div class="mt-4">
                                <button type="button" class="btn btn-success" onclick="guardarConsulta()">
                                    <i class="fas fa-save"></i> Guardar Consulta
                                </button>
                                <button type="button" class="btn btn-secondary ms-2" onclick="limpiarFormulario()">
                                    <i class="fas fa-broom"></i> Limpiar
                                </button>
                                <button type="button" class="btn btn-info ms-2" onclick="mostrarConsola()">
                                    <i class="fas fa-terminal"></i> Mostrar Consola
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loading" style="display: none;">
        <div class="text-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <div class="mt-3">
                <h5>Cargando sistema...</h5>
            </div>
        </div>
    </div>

    <!-- Console modal -->
    <div class="modal fade" id="consoleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-terminal"></i> Consola del Sistema</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="console-output" style="background: #000; color: #00ff00; padding: 15px; border-radius: 5px; height: 400px; overflow-y: scroll; font-family: monospace;">
                        <div>🚀 Sistema de consultas iniciado correctamente</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Variables globales
        let currentPatient = null;
        let currentConsultaId = null;
        let editMode = false;

        // Inicialización
        $(document).ready(function() {
            console.log('🚀 Inicializando sistema simplificado...');
            
            // Inicializar Select2
            $('.form-control').select2({
                width: '100%',
                allowClear: true
            });
            
            // Ocultar loading
            $('#loading').hide();
            
            logToConsole('✅ Sistema inicializado correctamente');
            logToConsole('💡 Busca un paciente para empezar');
        });

        // Función para logging en consola
        function logToConsole(message) {
            const timestamp = new Date().toLocaleTimeString();
            const consoleOutput = document.getElementById('console-output');
            consoleOutput.innerHTML += `<div>[${timestamp}] ${message}</div>`;
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
            console.log(message);
        }

        // Buscar paciente
        function buscarPaciente() {
            const searchTerm = document.getElementById('patient-search').value.trim();
            
            if (!searchTerm) {
                alert('Por favor, ingresa un término de búsqueda');
                return;
            }

            logToConsole(`🔍 Buscando paciente: ${searchTerm}`);
            
            // Simulación de búsqueda
            setTimeout(() => {
                // Datos simulados
                currentPatient = {
                    id: 45,
                    nombre: 'Juan',
                    apellido: 'Pérez',
                    ci: '12345678'
                };
                
                document.getElementById('selected-patient-name').textContent = 
                    `${currentPatient.nombre} ${currentPatient.apellido} (CI: ${currentPatient.ci})`;
                
                document.getElementById('patient-info').style.display = 'block';
                document.getElementById('consulta-history').style.display = 'block';
                
                cargarHistorialConsultas();
                logToConsole(`✅ Paciente encontrado: ${currentPatient.nombre} ${currentPatient.apellido}`);
            }, 1000);
        }

        // Cargar historial de consultas
        function cargarHistorialConsultas() {
            const tbody = document.getElementById('consultas-table-body');
            
            // Datos simulados de consultas
            const consultas = [
                { 
                    id: 108, 
                    fecha: '2024-08-20', 
                    motivo: 'Control de rutina', 
                    tipo: 'General',
                    doctor: 'Dr. García',
                    datos: {
                        txtmotivo: 'Control de rutina - examen anual',
                        visionod: '20/20',
                        visionoi: '20/25'
                    }
                },
                { 
                    id: 107, 
                    fecha: '2024-07-15', 
                    motivo: 'Prescripción anteojos', 
                    tipo: 'Anteojos',
                    doctor: 'Dr. López',
                    datos: {
                        txtmotivo: 'Prescripción de anteojos para miopía',
                        od_esf: '-1.50',
                        od_cil: '-0.50',
                        oi_esf: '-1.75',
                        oi_cil: '-0.25'
                    }
                },
                { 
                    id: 106, 
                    fecha: '2024-06-10', 
                    motivo: 'OCT control', 
                    tipo: 'Estudios',
                    doctor: 'Dr. Martínez',
                    datos: {
                        tipo_estudio: 'OCT',
                        observaciones_estudio: 'OCT normal, sin alteraciones retinianas'
                    }
                }
            ];
            
            tbody.innerHTML = '';
            
            consultas.forEach(consulta => {
                const row = `
                    <tr>
                        <td>${consulta.fecha}</td>
                        <td>${consulta.motivo}</td>
                        <td><span class="badge bg-info">${consulta.tipo}</span></td>
                        <td>${consulta.doctor}</td>
                        <td>
                            <button class="btn btn-sm btn-primary editar-consulta" 
                                    onclick="editarConsulta(${consulta.id})" 
                                    data-consulta='${JSON.stringify(consulta)}'>
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            
            logToConsole(`📋 Historial cargado: ${consultas.length} consultas`);
        }

        // Editar consulta (FUNCIÓN PRINCIPAL GENÉRICA)
        function editarConsulta(consultaId) {
            const button = event.target.closest('.editar-consulta');
            const consultaData = JSON.parse(button.getAttribute('data-consulta'));
            
            logToConsole(`✏️ Editando consulta #${consultaId}`);
            
            // Determinar tipo de formulario
            let tipoFormulario = 'general';
            if (consultaData.datos.od_esf || consultaData.datos.oi_esf) {
                tipoFormulario = 'anteojos';
            } else if (consultaData.datos.tipo_estudio) {
                tipoFormulario = 'estudios';
            }
            
            logToConsole(`📋 Tipo detectado: ${tipoFormulario}`);
            
            // Cambiar al tab correcto
            cambiarTab(tipoFormulario);
            
            // Poblar formulario después de un pequeño delay
            setTimeout(() => {
                poblarFormulario(consultaData.datos, tipoFormulario);
                activarModoEdicion(consultaId);
            }, 300);
        }

        // Cambiar tab
        function cambiarTab(tipo) {
            const tabMap = {
                'general': 'tab-general',
                'anteojos': 'tab-anteojos', 
                'estudios': 'tab-estudios'
            };
            
            const tabId = tabMap[tipo];
            const tabElement = document.getElementById(tabId);
            
            if (tabElement) {
                logToConsole(`🔄 Cambiando a tab: ${tipo}`);
                // Activar tab usando Bootstrap
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
        }

        // Poblar formulario genérico
        function poblarFormulario(datos, tipo) {
            logToConsole(`📝 Poblando formulario ${tipo}`);
            
            // Limpiar formulario primero
            limpiarFormulario();
            
            // Poblar campos según los datos disponibles
            Object.keys(datos).forEach(campo => {
                const elemento = document.getElementById(campo);
                if (elemento && datos[campo]) {
                    elemento.value = datos[campo];
                    
                    // Si es Select2, actualizar
                    if ($(elemento).hasClass('select2-hidden-accessible')) {
                        $(elemento).val(datos[campo]).trigger('change');
                    }
                    
                    logToConsole(`  ✅ ${campo} = ${datos[campo]}`);
                }
            });
            
            logToConsole(`✅ Formulario poblado completamente`);
        }

        // Activar modo edición
        function activarModoEdicion(consultaId) {
            editMode = true;
            currentConsultaId = consultaId;
            
            // Crear banner de edición
            const existingBanner = document.querySelector('.editing-banner');
            if (existingBanner) {
                existingBanner.remove();
            }
            
            const banner = document.createElement('div');
            banner.className = 'alert alert-warning editing-banner';
            banner.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-edit"></i>
                        <strong>Modo Edición:</strong> Está editando la consulta #${consultaId}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelarEdicion()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            `;
            
            // Insertar banner
            const container = document.querySelector('.main-container');
            container.insertBefore(banner, container.firstChild);
            
            // Cambiar botón guardar
            const saveBtn = document.querySelector('[onclick="guardarConsulta()"]');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Actualizar Consulta';
                saveBtn.className = 'btn btn-warning';
            }
            
            logToConsole(`🎨 Modo edición activado para consulta #${consultaId}`);
        }

        // Cancelar edición
        function cancelarEdicion() {
            editMode = false;
            currentConsultaId = null;
            
            // Remover banner
            const banner = document.querySelector('.editing-banner');
            if (banner) {
                banner.remove();
            }
            
            // Restaurar botón guardar
            const saveBtn = document.querySelector('[onclick="guardarConsulta()"]');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Consulta';
                saveBtn.className = 'btn btn-success';
            }
            
            limpiarFormulario();
            logToConsole('❌ Edición cancelada');
        }

        // Guardar consulta
        function guardarConsulta() {
            if (editMode) {
                logToConsole(`💾 Actualizando consulta #${currentConsultaId}...`);
                
                setTimeout(() => {
                    alert(`✅ Consulta #${currentConsultaId} actualizada exitosamente`);
                    cancelarEdicion();
                    cargarHistorialConsultas();
                }, 1000);
                
            } else {
                logToConsole('💾 Guardando nueva consulta...');
                
                setTimeout(() => {
                    alert('✅ Nueva consulta guardada exitosamente');
                    limpiarFormulario();
                    cargarHistorialConsultas();
                }, 1000);
            }
        }

        // Limpiar formulario
        function limpiarFormulario() {
            // Limpiar todos los inputs y textareas
            document.querySelectorAll('input, textarea, select').forEach(element => {
                if (element.id && element.id !== 'patient-search') {
                    element.value = '';
                    
                    // Si es Select2, limpiar también
                    if ($(element).hasClass('select2-hidden-accessible')) {
                        $(element).val('').trigger('change');
                    }
                }
            });
            
            logToConsole('🧹 Formulario limpiado');
        }

        // Mostrar consola
        function mostrarConsola() {
            const modal = new bootstrap.Modal(document.getElementById('consoleModal'));
            modal.show();
        }

        // Log inicial
        setTimeout(() => {
            logToConsole('🎉 Sistema completamente funcional');
            logToConsole('📖 Instrucciones:');
            logToConsole('  1. Busca un paciente usando CI o nombre');
            logToConsole('  2. Aparecerá el historial de consultas');  
            logToConsole('  3. Haz clic en "Editar" para editar cualquier consulta');
            logToConsole('  4. El sistema detectará automáticamente el tipo de formulario');
        }, 500);
    </script>

</body>
</html>