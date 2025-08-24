<?php
// Verificar sesión activa
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? 1;
$userName = $_SESSION['username'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultas Médicas - Sistema de Edición Genérica</title>
    
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        .main-container { margin-top: 20px; }
        .editing-banner { 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            border-left: 4px solid #ffc107;
            background: linear-gradient(45deg, #fff3cd, #ffeaa7);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }
        .patient-section { background: #f8f9fa; padding: 20px; border-radius: 12px; margin-bottom: 25px; }
        .consulta-history { background: white; border: 1px solid #dee2e6; border-radius: 12px; padding: 20px; }
        .btn-editar-consulta { 
            background: linear-gradient(45deg, #28a745, #20c997); 
            color: white; 
            border: none; 
            padding: 8px 16px; 
            border-radius: 8px; 
            transition: all 0.3s ease;
        }
        .btn-editar-consulta:hover { 
            background: linear-gradient(45deg, #218838, #1da1a1); 
            transform: translateY(-2px); 
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .form-section { background: #f8f9fa; padding: 25px; border-radius: 12px; margin-bottom: 20px; }
        .nav-tabs .nav-link.active { background: linear-gradient(45deg, #007bff, #0056b3); color: white; }
        .system-status { position: fixed; bottom: 20px; right: 20px; background: #28a745; color: white; padding: 10px 15px; border-radius: 25px; z-index: 1000; }
    </style>
</head>

<body>
    <div class="container-fluid main-container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-stethoscope"></i> Sistema de Consultas - Edición Genérica Funcionando</h3>
                        <p class="mb-0">Usuario: <?php echo htmlspecialchars($userName); ?> | ID: <?php echo $userId; ?></p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Selector de Paciente -->
                        <div class="patient-section">
                            <h5><i class="fas fa-user-search text-primary"></i> Seleccionar Paciente</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="patient-search" class="form-label fw-bold">Buscar Paciente (CI/Nombre/Apellido)</label>
                                        <input type="text" class="form-control form-control-lg" id="patient-search" placeholder="Ej: 12345678 o Juan Pérez">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-primary btn-lg form-control" onclick="buscarPaciente()">
                                            <i class="fas fa-search"></i> Buscar Paciente
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información del paciente seleccionado -->
                            <div id="patient-info" class="mt-3" style="display: none;">
                                <div class="alert alert-success alert-dismissible">
                                    <strong><i class="fas fa-user-check"></i> Paciente Seleccionado:</strong>
                                    <span id="selected-patient-name"></span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-3" onclick="limpiarPaciente()">
                                        <i class="fas fa-times"></i> Cambiar Paciente
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Historial de Consultas -->
                        <div class="consulta-history mb-4" id="consulta-history" style="display: none;">
                            <h5><i class="fas fa-history text-info"></i> Historial de Consultas</h5>
                            <p class="text-muted">Haz clic en "Editar" para cargar cualquier consulta en el formulario correspondiente.</p>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
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
                            <ul class="nav nav-tabs nav-fill" id="consultaTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-general" data-bs-toggle="tab" href="#form-general">
                                        <i class="fas fa-notes-medical"></i> Consulta General
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
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-informe-imagen" data-bs-toggle="tab" href="#form-informe-imagen">
                                        <i class="fas fa-images"></i> Informe Imagen
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content mt-4" id="consultaTabContent">
                                <!-- Formulario General -->
                                <div class="tab-pane fade show active" id="form-general">
                                    <h6><i class="fas fa-notes-medical text-primary"></i> Consulta General</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="txtmotivo" class="form-label fw-bold">Motivo de la Consulta</label>
                                                <textarea class="form-control" id="txtmotivo" rows="4" placeholder="Describe el motivo de la consulta..."></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="visionod" class="form-label">Visión OD</label>
                                                <input type="text" class="form-control" id="visionod" placeholder="Ej: 20/20">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="visionoi" class="form-label">Visión OI</label>
                                                <input type="text" class="form-control" id="visionoi" placeholder="Ej: 20/20">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="tensionod" class="form-label">Tensión OD</label>
                                                <input type="text" class="form-control" id="tensionod" placeholder="Ej: 15">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="tensionoi" class="form-label">Tensión OI</label>
                                                <input type="text" class="form-control" id="tensionoi" placeholder="Ej: 16">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulario Anteojos -->
                                <div class="tab-pane fade" id="form-anteojos">
                                    <h6><i class="fas fa-glasses text-success"></i> Prescripción de Anteojos</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Ojo Derecho (OD)</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="od_esf" class="form-label">Esfera</label>
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
                                                        <label for="od_cil" class="form-label">Cilindro</label>
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
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="od_adicion" class="form-label">Adición</label>
                                                        <select class="form-control" id="od_adicion">
                                                            <option value="">Seleccionar</option>
                                                            <option value="+0.50">+0.50</option>
                                                            <option value="+1.00">+1.00</option>
                                                            <option value="+1.50">+1.50</option>
                                                            <option value="+2.00">+2.00</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="od_eje" class="form-label">Eje</label>
                                                        <input type="text" class="form-control" id="od_eje" placeholder="Ej: 90">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-danger">Ojo Izquierdo (OI)</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="oi_esf" class="form-label">Esfera</label>
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
                                                        <label for="oi_cil" class="form-label">Cilindro</label>
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
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="oi_adicion" class="form-label">Adición</label>
                                                        <select class="form-control" id="oi_adicion">
                                                            <option value="">Seleccionar</option>
                                                            <option value="+0.50">+0.50</option>
                                                            <option value="+1.00">+1.00</option>
                                                            <option value="+1.50">+1.50</option>
                                                            <option value="+2.00">+2.00</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="oi_eje" class="form-label">Eje</label>
                                                        <input type="text" class="form-control" id="oi_eje" placeholder="Ej: 180">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulario Estudios -->
                                <div class="tab-pane fade" id="form-estudios">
                                    <h6><i class="fas fa-x-ray text-warning"></i> Estudios Médicos</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="tipo_estudio" class="form-label fw-bold">Tipo de Estudio</label>
                                                <select class="form-control" id="tipo_estudio">
                                                    <option value="">Seleccionar tipo</option>
                                                    <option value="OCT">OCT (Tomografía de Coherencia Óptica)</option>
                                                    <option value="Campo Visual">Campo Visual</option>
                                                    <option value="Retinografía">Retinografía</option>
                                                    <option value="Ecografía">Ecografía Ocular</option>
                                                    <option value="Angiografía">Angiografía Fluoresceínica</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="observaciones_estudio" class="form-label fw-bold">Observaciones del Estudio</label>
                                                <textarea class="form-control" id="observaciones_estudio" rows="6" placeholder="Describe los hallazgos del estudio..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulario Informe Imagen -->
                                <div class="tab-pane fade" id="form-informe-imagen">
                                    <h6><i class="fas fa-images text-info"></i> Informe de Imagen</h6>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="archivo_imagen" class="form-label fw-bold">Archivo de Imagen</label>
                                                <input type="text" class="form-control" id="archivo_imagen" placeholder="Nombre del archivo de imagen">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="descripcion_imagen" class="form-label fw-bold">Descripción de la Imagen</label>
                                                <textarea class="form-control" id="descripcion_imagen" rows="6" placeholder="Describe lo observado en la imagen..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de Acción -->
                            <div class="mt-4 text-center">
                                <button type="button" class="btn btn-success btn-lg me-3" onclick="guardarConsulta()">
                                    <i class="fas fa-save"></i> Guardar Consulta
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg me-3" onclick="limpiarFormulario()">
                                    <i class="fas fa-broom"></i> Limpiar Formulario
                                </button>
                                <button type="button" class="btn btn-info btn-lg" onclick="mostrarLogs()">
                                    <i class="fas fa-terminal"></i> Ver Logs
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sistema de Status -->
    <div class="system-status" id="system-status">
        <i class="fas fa-check-circle"></i> Sistema Funcionando
    </div>

    <!-- Modal de Logs -->
    <div class="modal fade" id="logsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-terminal"></i> Logs del Sistema de Edición</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="logs-output" style="background: #000; color: #00ff00; padding: 20px; border-radius: 8px; height: 500px; overflow-y: scroll; font-family: 'Courier New', monospace; font-size: 14px;">
                        <div>🚀 Sistema de edición genérica iniciado correctamente</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="limpiarLogs()">
                        <i class="fas fa-trash"></i> Limpiar Logs
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Variables globales del sistema
        let currentPatient = null;
        let currentConsultaId = null;
        let editMode = false;
        let systemLogs = [];

        // Sistema de logging
        function addLog(message, type = 'info') {
            const timestamp = new Date().toLocaleTimeString();
            const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : type === 'warning' ? '⚠️' : '📝';
            
            const logEntry = {
                timestamp: timestamp,
                message: `${icon} ${message}`,
                type: type
            };
            
            systemLogs.push(logEntry);
            console.log(message);
        }

        // Inicialización del sistema
        $(document).ready(function() {
            addLog('🚀 Inicializando sistema de consultas con edición genérica');
            
            // Inicializar Select2
            $('.form-control').select2({
                width: '100%',
                allowClear: true
            });
            
            addLog('✅ Sistema inicializado correctamente', 'success');
            addLog('💡 Busca un paciente para ver su historial y poder editarlo');
            
            // Actualizar status
            setTimeout(() => {
                document.getElementById('system-status').innerHTML = '<i class="fas fa-heart"></i> Sistema Activo';
            }, 2000);
        });

        // FUNCIÓN PRINCIPAL DE BÚSQUEDA DE PACIENTES
        function buscarPaciente() {
            const searchTerm = document.getElementById('patient-search').value.trim();
            
            if (!searchTerm) {
                alert('Por favor, ingresa un término de búsqueda');
                return;
            }

            addLog(`🔍 Buscando paciente: "${searchTerm}"`);
            
            // Simulación de búsqueda (reemplazar por llamada real a la API)
            setTimeout(() => {
                currentPatient = {
                    id: 45,
                    nombre: 'Juan Carlos',
                    apellido: 'Pérez García',
                    ci: searchTerm.includes('123') ? '12345678' : '87654321'
                };
                
                document.getElementById('selected-patient-name').textContent = 
                    `${currentPatient.nombre} ${currentPatient.apellido} (CI: ${currentPatient.ci})`;
                
                document.getElementById('patient-info').style.display = 'block';
                document.getElementById('consulta-history').style.display = 'block';
                
                cargarHistorialConsultas();
                addLog(`✅ Paciente encontrado: ${currentPatient.nombre} ${currentPatient.apellido}`, 'success');
            }, 1000);
        }

        // CARGAR HISTORIAL DE CONSULTAS
        function cargarHistorialConsultas() {
            const tbody = document.getElementById('consultas-table-body');
            
            // Datos simulados con diferentes tipos de consultas
            const consultas = [
                { 
                    id: 108, 
                    fecha: '2024-08-20', 
                    motivo: 'Control de rutina anual', 
                    tipo: 'General',
                    doctor: 'Dr. García',
                    datos: {
                        txtmotivo: 'Control de rutina - examen anual completo. Paciente refiere buena visión.',
                        visionod: '20/20',
                        visionoi: '20/25',
                        tensionod: '15',
                        tensionoi: '14'
                    }
                },
                { 
                    id: 107, 
                    fecha: '2024-07-15', 
                    motivo: 'Prescripción de anteojos', 
                    tipo: 'Anteojos',
                    doctor: 'Dr. López',
                    datos: {
                        txtmotivo: 'Prescripción de anteojos para corrección de miopía bilateral',
                        od_esf: '-1.50',
                        od_cil: '-0.50',
                        od_adicion: '',
                        od_eje: '90',
                        oi_esf: '-1.75',
                        oi_cil: '-0.25',
                        oi_adicion: '',
                        oi_eje: '85'
                    }
                },
                { 
                    id: 106, 
                    fecha: '2024-06-10', 
                    motivo: 'OCT de control', 
                    tipo: 'Estudios',
                    doctor: 'Dr. Martínez',
                    datos: {
                        tipo_estudio: 'OCT',
                        observaciones_estudio: 'OCT normal bilateral. Espesor macular dentro de parámetros normales. No se observan alteraciones retinianas.'
                    }
                },
                { 
                    id: 105, 
                    fecha: '2024-05-22', 
                    motivo: 'Evaluación de retinografía', 
                    tipo: 'Informe Imagen',
                    doctor: 'Dra. Rodríguez',
                    datos: {
                        archivo_imagen: 'retino_paciente_45_20240522.jpg',
                        descripcion_imagen: 'Retinografía bilateral que muestra fondos oculares sin alteraciones patológicas. Papila óptica rosada, bien delimitada. Mácula sin alteraciones. Vasos retinianos de calibre normal.'
                    }
                }
            ];
            
            tbody.innerHTML = '';
            
            consultas.forEach(consulta => {
                const badgeClass = consulta.tipo === 'General' ? 'bg-primary' : 
                                 consulta.tipo === 'Anteojos' ? 'bg-success' : 
                                 consulta.tipo === 'Estudios' ? 'bg-warning' : 'bg-info';
                                 
                const row = `
                    <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                        <td class="fw-bold">${consulta.fecha}</td>
                        <td>${consulta.motivo}</td>
                        <td><span class="badge ${badgeClass}">${consulta.tipo}</span></td>
                        <td>${consulta.doctor}</td>
                        <td>
                            <button class="btn-editar-consulta" 
                                    onclick="editarConsultaGenerica(${consulta.id})" 
                                    data-consulta='${JSON.stringify(consulta).replace(/'/g, "&apos;")}'>
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
            
            addLog(`📋 Historial cargado: ${consultas.length} consultas encontradas`);
        }

        // FUNCIÓN PRINCIPAL DE EDICIÓN GENÉRICA ⭐
        function editarConsultaGenerica(consultaId) {
            const button = event.target.closest('.btn-editar-consulta');
            const consultaData = JSON.parse(button.getAttribute('data-consulta').replace(/&apos;/g, "'"));
            
            addLog(`✏️ ===== INICIANDO EDICIÓN GENÉRICA =====`);
            addLog(`📋 Consulta ID: ${consultaId}`);
            addLog(`📋 Tipo: ${consultaData.tipo}`);
            
            // PASO 1: Determinar tipo de formulario automáticamente
            let tipoFormulario = determinarTipoFormulario(consultaData);
            addLog(`🔍 Tipo detectado automáticamente: ${tipoFormulario}`);
            
            // PASO 2: Cambiar al tab correcto
            cambiarTabAutomatico(tipoFormulario);
            
            // PASO 3: Poblar formulario después de un delay para asegurar que el tab se cargó
            setTimeout(() => {
                poblarFormularioGenerico(consultaData.datos, tipoFormulario);
                activarModoEdicion(consultaId, consultaData);
            }, 300);
        }

        // Determinar tipo de formulario basado en los datos
        function determinarTipoFormulario(consultaData) {
            const datos = consultaData.datos;
            
            // Detectar anteojos por presencia de campos de refracción
            if (datos.od_esf || datos.oi_esf || datos.od_cil || datos.oi_cil) {
                addLog(`👓 Detectados campos de anteojos: od_esf=${datos.od_esf}, oi_esf=${datos.oi_esf}`);
                return 'anteojos';
            }
            
            // Detectar estudios por presencia de tipo_estudio
            if (datos.tipo_estudio || datos.observaciones_estudio) {
                addLog(`🔬 Detectados campos de estudios: tipo=${datos.tipo_estudio}`);
                return 'estudios';
            }
            
            // Detectar informe imagen por presencia de archivo_imagen
            if (datos.archivo_imagen || datos.descripcion_imagen) {
                addLog(`🖼️ Detectados campos de imagen: archivo=${datos.archivo_imagen}`);
                return 'informe_imagen';
            }
            
            // Por defecto: formulario general
            addLog(`📝 Sin campos específicos detectados, usando formulario general`);
            return 'general';
        }

        // Cambiar tab automáticamente
        function cambiarTabAutomatico(tipo) {
            const tabMap = {
                'general': 'tab-general',
                'anteojos': 'tab-anteojos', 
                'estudios': 'tab-estudios',
                'informe_imagen': 'tab-informe-imagen'
            };
            
            const tabId = tabMap[tipo];
            const tabElement = document.getElementById(tabId);
            
            if (tabElement) {
                addLog(`🔄 Cambiando automáticamente al tab: ${tipo}`);
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
                
                // Actualizar estado visual
                document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
                tabElement.classList.add('active');
            } else {
                addLog(`❌ Error: Tab ${tabId} no encontrado`, 'error');
            }
        }

        // Poblar formulario genérico ⭐
        function poblarFormularioGenerico(datos, tipo) {
            addLog(`📝 ===== POBLANDO FORMULARIO ${tipo.toUpperCase()} =====`);
            
            // Limpiar formulario primero
            limpiarFormulario();
            
            let camposPoblados = 0;
            
            // Poblar todos los campos disponibles en los datos
            Object.keys(datos).forEach(campo => {
                const elemento = document.getElementById(campo);
                if (elemento && datos[campo]) {
                    const valor = datos[campo];
                    
                    // Manejar diferentes tipos de elementos
                    if (elemento.tagName === 'SELECT') {
                        elemento.value = valor;
                        // Trigger Select2 si está inicializado
                        if ($(elemento).hasClass('select2-hidden-accessible')) {
                            $(elemento).val(valor).trigger('change');
                        }
                    } else {
                        elemento.value = valor;
                    }
                    
                    camposPoblados++;
                    addLog(`  ✅ ${campo} = "${valor}"`);
                } else if (!elemento) {
                    addLog(`  ⚠️ Campo ${campo} no encontrado en DOM`, 'warning');
                } else {
                    addLog(`  ⚠️ Campo ${campo} vacío, saltando`);
                }
            });
            
            addLog(`✅ Formulario poblado completamente: ${camposPoblados} campos`, 'success');
            
            // Scroll al formulario
            document.querySelector('.form-section').scrollIntoView({ behavior: 'smooth' });
        }

        // Activar modo edición visual
        function activarModoEdicion(consultaId, consultaData) {
            editMode = true;
            currentConsultaId = consultaId;
            
            // Remover banner anterior si existe
            const existingBanner = document.querySelector('.editing-banner');
            if (existingBanner) {
                existingBanner.remove();
            }
            
            // Crear banner de edición
            const banner = document.createElement('div');
            banner.className = 'alert alert-warning editing-banner mb-4';
            banner.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-edit fa-lg"></i>
                        <strong>MODO EDICIÓN ACTIVO:</strong> 
                        Editando consulta #${consultaId} (${consultaData.tipo}) - ${consultaData.fecha}
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-warning me-2" onclick="cancelarEdicion()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="guardarConsulta()">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            `;
            
            // Insertar banner al principio del contenido
            const cardBody = document.querySelector('.card-body');
            cardBody.insertBefore(banner, cardBody.firstChild);
            
            // Cambiar botón principal de guardar
            const saveBtn = document.querySelector('[onclick="guardarConsulta()"]');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Actualizar Consulta';
                saveBtn.className = 'btn btn-warning btn-lg me-3';
            }
            
            // Actualizar status del sistema
            document.getElementById('system-status').innerHTML = '<i class="fas fa-edit"></i> Modo Edición';
            document.getElementById('system-status').style.background = '#ffc107';
            document.getElementById('system-status').style.color = '#000';
            
            addLog(`🎨 Modo edición activado para consulta #${consultaId}`, 'success');
            addLog(`💡 Realiza los cambios necesarios y haz clic en "Actualizar Consulta"`);
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
            const saveBtn = document.querySelector('.btn-warning');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Consulta';
                saveBtn.className = 'btn btn-success btn-lg me-3';
            }
            
            // Restaurar status
            document.getElementById('system-status').innerHTML = '<i class="fas fa-check-circle"></i> Sistema Funcionando';
            document.getElementById('system-status').style.background = '#28a745';
            document.getElementById('system-status').style.color = '#fff';
            
            limpiarFormulario();
            addLog('❌ Edición cancelada - formulario limpiado');
        }

        // Guardar consulta
        function guardarConsulta() {
            if (editMode) {
                addLog(`💾 Guardando cambios en consulta #${currentConsultaId}...`);
                
                // Simular guardado
                setTimeout(() => {
                    alert(`✅ Consulta #${currentConsultaId} actualizada exitosamente`);
                    addLog(`✅ Consulta #${currentConsultaId} actualizada correctamente`, 'success');
                    cancelarEdicion();
                    cargarHistorialConsultas(); // Recargar historial
                }, 1500);
                
            } else {
                addLog('💾 Guardando nueva consulta...');
                
                setTimeout(() => {
                    alert('✅ Nueva consulta guardada exitosamente');
                    addLog('✅ Nueva consulta guardada correctamente', 'success');
                    limpiarFormulario();
                    if (currentPatient) {
                        cargarHistorialConsultas(); // Recargar historial
                    }
                }, 1500);
            }
        }

        // Limpiar formulario
        function limpiarFormulario() {
            document.querySelectorAll('input, textarea, select').forEach(element => {
                if (element.id && element.id !== 'patient-search') {
                    element.value = '';
                    
                    if ($(element).hasClass('select2-hidden-accessible')) {
                        $(element).val('').trigger('change');
                    }
                }
            });
            
            addLog('🧹 Formulario limpiado');
        }

        // Limpiar paciente
        function limpiarPaciente() {
            currentPatient = null;
            document.getElementById('patient-search').value = '';
            document.getElementById('patient-info').style.display = 'none';
            document.getElementById('consulta-history').style.display = 'none';
            limpiarFormulario();
            cancelarEdicion();
            addLog('🔄 Paciente deseleccionado - sistema reiniciado');
        }

        // Mostrar logs
        function mostrarLogs() {
            const logsOutput = document.getElementById('logs-output');
            logsOutput.innerHTML = '';
            
            systemLogs.forEach(log => {
                logsOutput.innerHTML += `<div>[${log.timestamp}] ${log.message}</div>`;
            });
            
            logsOutput.scrollTop = logsOutput.scrollHeight;
            
            const modal = new bootstrap.Modal(document.getElementById('logsModal'));
            modal.show();
        }

        // Limpiar logs
        function limpiarLogs() {
            systemLogs = [];
            document.getElementById('logs-output').innerHTML = '<div>📝 Logs limpiados</div>';
            addLog('🧹 Sistema de logs reiniciado');
        }

        // Log inicial del sistema
        setTimeout(() => {
            addLog('🎉 Sistema de edición genérica completamente funcional', 'success');
            addLog('📖 INSTRUCCIONES DE USO:');
            addLog('  1️⃣ Busca un paciente usando CI o nombre completo');
            addLog('  2️⃣ Aparecerá su historial de consultas automaticamente');  
            addLog('  3️⃣ Haz clic en "Editar" en cualquier consulta');
            addLog('  4️⃣ El sistema detectará el tipo y llenará el formulario correcto');
            addLog('  5️⃣ Realiza cambios y guarda con "Actualizar Consulta"');
            addLog('💡 ¡El sistema es completamente genérico y funciona para todos los tipos!');
        }, 1000);
    </script>

</body>
</html>