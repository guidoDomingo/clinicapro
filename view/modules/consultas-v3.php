<!-- Consultas v3.0 Livewire - Optimizado para AdminLTE -->

<?php
// Procesar parámetros de URL para carga automática de paciente
$paciente_id = isset($_GET['paciente_id']) ? (int)$_GET['paciente_id'] : null;
$reserva_id = isset($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : null;

// Datos del paciente para carga automática
$pacienteData = null;
if ($paciente_id) {
    // Aquí podrías cargar los datos del paciente desde la base de datos
    // Por ahora, pasamos solo el ID para que JavaScript haga la carga
    $pacienteData = array(
        'paciente_id' => $paciente_id,
        'reserva_id' => $reserva_id
    );
}
?>

<!-- CSS Libraries -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/alertifyjs/1.13.1/css/alertify.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/alertifyjs/1.13.1/css/themes/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    
    <style>
        /* Estilos optimizados para AdminLTE */
        .consultas-v3-container {
            padding: 20px;
            margin: 0;
            margin-left: 250px; /* Espacio para el sidebar */
            max-width: none;
            background-color: #f4f4f4;
            min-height: calc(100vh - 57px);
            transition: margin-left 0.3s ease;
        }
        
        /* Responsive para sidebar colapsado */
        .sidebar-collapse .consultas-v3-container {
            margin-left: 70px;
            margin-top: 38px;
        }
        
        /* Media queries para responsive */
        @media (max-width: 991px) {
            .consultas-v3-container {
                margin-left: 0 !important;
            }
        }
        
        /* Header compacto */
        .consultas-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,123,255,0.15);
        }
        
        .consultas-header h1 {
            font-size: 1.5rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Tabs de tipos de formulario compactas */
        .form-type-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .form-type-tab {
            flex: 1;
            min-width: 150px;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .form-type-tab.active.general {
            background: #28a745;
            color: white;
        }
        
        .form-type-tab.active.anteojos {
            background: #6f42c1;
            color: white;
        }
        
        .form-type-tab.active.estudios {
            background: #17a2b8;
            color: white;
        }
        
        .form-type-tab.active.informe_imagen {
            background: #dc3545;
            color: white;
        }
        
        .form-type-tab:not(.active) {
            background: #e9ecef;
            color: #6c757d;
        }
        
        .form-type-tab:not(.active):hover {
            background: #dee2e6;
        }
        
        /* Cards optimizadas para AdminLTE */
        .consultas-card {
            background: white;
            border-radius: 8px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #dee2e6;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px 25px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            border-radius: 8px 8px 0 0;
        }
        
        .card-body-custom {
            padding: 25px;
        }
        
        /* Sección de búsqueda de paciente optimizada */
        .patient-search-container {
            position: relative;
            margin-bottom: 15px;
        }
        
        .smart-search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            max-height: 250px;
            overflow-y: auto;
            display: none;
        }
        
        .search-result-item {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f3f4;
            transition: background 0.2s;
        }
        
        .search-result-item:hover {
            background: #f8f9fa;
        }
        
        .search-result-item.selected {
            background: #007bff;
            color: white;
        }
        
        /* Información del paciente seleccionado */
        .selected-patient-info {
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        
        .patient-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
            display: block;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Tabs del sistema optimizadas */
        .consultas-tabs {
            display: flex;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        
        .consultas-tab {
            flex: 1;
            padding: 12px 20px;
            border: none;
            background: #f8f9fa;
            color: #6c757d;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            border-right: 1px solid #dee2e6;
        }
        
        .consultas-tab:last-child {
            border-right: none;
        }
        
        .consultas-tab.active {
            background: #007bff;
            color: white;
        }
        
        .consultas-tab:hover:not(.active) {
            background: #e9ecef;
            color: #495057;
        }
        
        /* Formulario optimizado */
        .form-section {
            margin-bottom: 20px;
        }
        
        .form-section-title {
            background: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            margin: 0 0 15px 0;
            border-radius: 8px 8px 0 0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        /* Tabla optimizada */
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: #007bff;
            color: white;
            font-weight: 600;
            padding: 12px;
            border: none;
            font-size: 0.9rem;
        }
        
        .table tbody tr {
            transition: background 0.2s;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .table tbody td {
            padding: 10px 12px;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        
        /* Botones de acción */
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        
        .btn-action {
            padding: 4px 8px;
            font-size: 0.8rem;
            border-radius: 4px;
        }
        
        /* Loading mejorado */
        .loading-container {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }
        
        .spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(0, 123, 255, 0.3);
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Select2 y Summernote optimizados para AdminLTE */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 12px !important;
        }
        
        .note-editor {
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }
        
        /* Responsive mejorado */
        @media (max-width: 768px) {
            .consultas-v3-container {
                padding: 15px;
            }
            
            .form-type-tabs {
                flex-direction: column;
            }
            
            .form-type-tab {
                min-width: auto;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .patient-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .consultas-tabs {
                flex-direction: column;
            }
            
            .consultas-tab {
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
            
            .consultas-tab:last-child {
                border-bottom: none;
            }
        }
        
        /* Ocultar contenido inactivo - Reglas más específicas */
        .consultas-v3-container .tab-content {
            display: none !important;
        }
        
        .consultas-v3-container .tab-content.active {
            display: block !important;
        }
        
        /* Forzar visibilidad del tab activo */
        .consultas-v3-container .tab-content[style*="display: block"] {
            display: block !important;
        }
        
        /* Alertas compactas */
        .alert {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
        
        /* Espaciado general más compacto */
        .consultas-v3-container .container-fluid {
            padding: 0;
        }
        
        /* Mejoras para campos de texto grandes */
        .large-textarea-container {
            grid-column: 1 / -1;
        }
        
        .large-textarea-container label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        /* Vision y tension section optimizada */
        .vision-tension-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        /* Anteojos section optimizada */
        #anteojos-section .row {
            margin: 0;
        }
        
        #anteojos-section .col-md-6 {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        /* Paginación */
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .pagination .page-link {
            border-radius: 6px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="consultas-v3-container">
        
        <!-- Header optimizado -->
        <div class="consultas-header">
            <h1><i class="fas fa-stethoscope"></i> Consultas Médicas v3.0 Livewire</h1>
        </div>

        <!-- Tabs de tipos de formulario -->
        <div class="form-type-tabs">
            <button class="form-type-tab general active" onclick="setActiveFormType('general')">
                <i class="fas fa-notes-medical"></i> General
            </button>
            <button class="form-type-tab anteojos" onclick="setActiveFormType('anteojos')">
                <i class="fas fa-glasses"></i> Anteojos
            </button>
            <button class="form-type-tab estudios" onclick="setActiveFormType('estudios')">
                <i class="fas fa-microscope"></i> Estudios
            </button>
            <button class="form-type-tab informe_imagen" onclick="setActiveFormType('informe_imagen')">
                <i class="fas fa-file-medical-alt"></i> Informe + Imagen
            </button>
        </div>

        <!-- Sección de búsqueda de paciente -->
        <div class="consultas-card">
            <div class="card-header-custom">
                <i class="fas fa-search"></i> Búsqueda de Paciente
            </div>
            <div class="card-body-custom">
                <div class="patient-search-container">
                    <label for="smartPatientSearch" class="form-label">
                        <i class="fas fa-user-search"></i> Buscar Paciente (nombre, apellido o cédula)
                    </label>
                    <input 
                        type="text" 
                        id="smartPatientSearch" 
                        class="form-control"
                        placeholder="Escriba al menos 3 caracteres para buscar..." 
                        oninput="handleSmartSearch(event)"
                        onkeydown="handleSmartSearchKeydown(event)"
                        autocomplete="off"
                    >
                    <div id="smartSearchDropdown" class="smart-search-dropdown">
                        <!-- Los resultados se cargarán aquí dinámicamente -->
                    </div>
                </div>
                
                <div class="mt-3">
                    <button class="btn btn-outline-danger btn-sm" onclick="clearSmartPatientSearch()" title="Limpiar búsqueda y resetear toda la página">
                        <i class="fas fa-broom"></i> Limpiar Todo
                    </button>
                </div>

                <!-- Información del paciente seleccionado -->
                <div id="selectedPatientInfo" class="selected-patient-info" style="display: none;">
                    <div class="patient-details">
                        <h5 id="selectedPatientName">Paciente seleccionado</h5>
                        <p id="selectedPatientDetails" class="mb-0">Información del paciente</p>
                        <div class="patient-stats">
                            <div class="stat-item">
                                <span class="stat-number" id="totalConsultas">0</span>
                                <span class="stat-label">Total Consultas</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="ultimaConsulta">--</span>
                                <span class="stat-label">Última Consulta</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number" id="proximaConsulta">--</span>
                                <span class="stat-label">Próxima Consulta</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navegación de pestañas del sistema -->
        <div class="consultas-card">
            <div class="consultas-tabs">
                <button class="consultas-tab active" onclick="showTab('list')">
                    <i class="fas fa-list me-2"></i>Listar Consultas
                </button>
                <button class="consultas-tab" onclick="showTab('create')">
                    <i class="fas fa-plus me-2"></i>Nueva Consulta
                </button>
                <button class="consultas-tab" onclick="showTab('search')">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
                <button class="consultas-tab" onclick="showTab('patients')">
                    <i class="fas fa-users me-2"></i>Pacientes
                </button>
            </div>
        </div>
        
        <!-- Contenido de pestañas -->
        
        <!-- Tab: Listar Consultas -->
        <div id="tab-list" class="tab-content consultas-card active">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Lista de Consultas</span>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="loadConsultas()">
                        <i class="fas fa-sync me-1"></i>Actualizar
                    </button>
                    <select class="form-select form-select-sm" style="width: 150px;" onchange="changePageSize(this.value)">
                        <option value="10">10 por página</option>
                        <option value="20" selected>20 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                </div>
            </div>
            <div class="card-body-custom">
                <div id="consultas-loading" class="loading-container">
                    <div class="spinner"></div>
                    <p>Cargando consultas...</p>
                </div>
                <div id="consultas-list"></div>
                <div id="consultas-pagination"></div>
            </div>
        </div>
        
        <!-- Tab: Nueva Consulta -->
        <div id="tab-create" class="tab-content consultas-card" style="display: none;">
            <div class="card-header-custom">
                <i class="fas fa-plus me-2"></i>Nueva Consulta
            </div>
            <div class="card-body-custom">
                <form id="create-form">
                    <div class="consultas-card">
                        <div class="form-section-title">
                            <i class="fas fa-user"></i> Datos del Paciente
                        </div>
                        <div class="card-body-custom">
                            <div class="form-row">
                                <div class="form-floating search-box d-none">
                                    <input type="text" class="form-control" id="patient-search" placeholder="🔍 AUTOCOMPLETADO - Escribe aquí para sugerencias..." 
                                           onkeyup="handlePatientSearchKeyup(event)" 
                                           onkeydown="handlePatientSearchKeydown(event)" 
                                           autocomplete="off"
                                           style="border: 3px solid #28a745; background-color: #f8fff9;">
                                    <div id="patient-results" class="search-results" style="display: none;"></div>
                                </div>
                                <input type="hidden" id="selected-patient-id" name="id_persona" required>
                            </div>
                            <div id="selected-patient-info" style="display: none;" class="alert alert-info">
                                <strong>Paciente seleccionado:</strong> <span id="patient-info-text"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="consultas-card">
                        <div class="form-section-title">
                            <i class="fas fa-notes-medical"></i> Datos de la Consulta
                        </div>
                        <div class="card-body-custom">
                            <div class="form-row">
                                <div class="form-floating">
                                    <select class="form-select select2bs4" id="motivos_comunes" name="motivoscomunes">
                                        <option value="">Seleccionar motivo común...</option>
                                    </select>
                                    <label>Motivos Comunes</label>
                                </div>
                                <div class="large-textarea-container">
                                    <label for="motivo">Motivo de Consulta *</label>
                                    <textarea class="form-control summernote" id="motivo" name="txtmotivo"></textarea>
                                </div>
                            </div>
                            
                            <!-- Sección de Visión y Tensión (solo para formularios General) -->
                            <div class="vision-tension-section">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="vision_od" name="visionod">
                                    <label>Visión OD</label>
                                </div>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="vision_oi" name="visionoi">
                                    <label>Visión OI</label>
                                </div>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="tension_od" name="tensionod">
                                    <label>Tensión OD</label>
                                </div>
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="tension_oi" name="tensionoi">
                                    <label>Tensión OI</label>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-floating">
                                    <select class="form-select select2bs4" id="preformatos_consulta">
                                        <option value="">Seleccionar preformato para consulta...</option>
                                    </select>
                                    <label>Preformatos - Consulta</label>
                                </div>
                                <div class="large-textarea-container">
                                    <label for="consulta">Consulta</label>
                                    <textarea class="form-control summernote" id="consulta" name="consulta_textarea"></textarea>
                                </div>
                                <div class="form-floating">
                                    <select class="form-select select2bs4" id="preformatos_receta">
                                        <option value="">Seleccionar preformato para receta...</option>
                                    </select>
                                    <label>Preformatos - Receta</label>
                                </div>
                                <div class="large-textarea-container">
                                    <label for="receta">Receta</label>
                                    <textarea class="form-control summernote" id="receta" name="receta_textarea"></textarea>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="large-textarea-container">
                                    <label for="nota">Notas</label>
                                    <textarea class="form-control summernote" id="nota" name="txtnota"></textarea>
                                </div>
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="proxima_consulta" name="proximaconsulta">
                                    <label>Próxima Consulta</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección de Anteojos (se muestra condicionalmente) -->
                    <div id="anteojos-section" class="consultas-card" style="display: none;">
                        <div class="form-section-title">
                            <i class="fas fa-glasses"></i> Datos de Anteojos
                        </div>
                        <div class="card-body-custom">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary">Ojo Derecho (OD)</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="od_esf" class="form-label">Esfera (ESF) <span class="text-danger">*</span></label>
                                            <select class="form-select select2bs4" id="od_esf" name="od_esf">
                                                <option value="">Seleccionar esfera...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="od_cil" class="form-label">Cilindro (CIL)</label>
                                            <select class="form-select select2bs4" id="od_cil" name="od_cil">
                                                <option value="">Seleccionar cilindro...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="od_eje" class="form-label">Eje</label>
                                            <input type="number" class="form-control" id="od_eje" name="od_eje" min="0" max="180" placeholder="0-180">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="od_dnp" class="form-label">DNP</label>
                                            <input type="number" class="form-control" id="od_dnp" name="od_dnp" placeholder="DNP">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="od_add" class="form-label">Adición</label>
                                            <select class="form-select select2bs4" id="od_add" name="od_add">
                                                <option value="">Seleccionar adición...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="od_altura" class="form-label">Altura</label>
                                            <input type="number" class="form-control" id="od_altura" name="od_altura" placeholder="Altura">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="od_nota" class="form-label">Notas OD</label>
                                        <textarea class="form-control" id="od_nota" name="od_nota" rows="3" placeholder="Notas para ojo derecho"></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="text-success">Ojo Izquierdo (OI)</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="oi_esf" class="form-label">Esfera (ESF) <span class="text-danger">*</span></label>
                                            <select class="form-select select2bs4" id="oi_esf" name="oi_esf">
                                                <option value="">Seleccionar esfera...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="oi_cil" class="form-label">Cilindro (CIL)</label>
                                            <select class="form-select select2bs4" id="oi_cil" name="oi_cil">
                                                <option value="">Seleccionar cilindro...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="oi_eje" class="form-label">Eje</label>
                                            <input type="number" class="form-control" id="oi_eje" name="oi_eje" min="0" max="180" placeholder="0-180">
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="oi_dnp" class="form-label">DNP</label>
                                            <input type="number" class="form-control" id="oi_dnp" name="oi_dnp" placeholder="DNP">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="oi_add" class="form-label">Adición</label>
                                            <select class="form-select select2bs4" id="oi_add" name="oi_add">
                                                <option value="">Seleccionar adición...</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="oi_altura" class="form-label">Altura</label>
                                            <input type="number" class="form-control" id="oi_altura" name="oi_altura" placeholder="Altura">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="oi_nota" class="form-label">Notas OI</label>
                                        <textarea class="form-control" id="oi_nota" name="oi_nota" rows="3" placeholder="Notas para ojo izquierdo"></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="dist_interpupilar" class="form-label">Distancia Interpupilar</label>
                                    <input type="number" class="form-control" id="dist_interpupilar" name="dist_interpupilar" placeholder="Distancia interpupilar">
                                </div>
                                <div class="col-md-6">
                                    <label for="anteojos_notas" class="form-label">Notas Generales Anteojos</label>
                                    <textarea class="form-control" id="anteojos_notas" name="anteojos_notas" rows="3" placeholder="Notas generales sobre los anteojos"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección de Archivos y Contacto -->
                    <div class="consultas-card">
                        <div class="form-section-title">
                            <i class="fas fa-paperclip"></i> Archivos Adjuntos y Contacto
                        </div>
                        <div class="card-body-custom">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="archivos_consulta" class="form-label">Archivos Adjuntos</label>
                                    <input type="file" class="form-control" id="archivos_consulta" name="archivos_consulta[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.rtf,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,.svg">
                                    <div class="form-text">Máximo 10 archivos. Formatos permitidos: PDF, Word, Excel, Imágenes (JPG, PNG)</div>
                                    <div id="archivos-preview" class="mt-2"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" name="whatsapptxt" placeholder="Número de WhatsApp">
                                                <label>Mensaje WhatsApp</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating">
                                                <input type="email" class="form-control" name="email" placeholder="Correo electrónico">
                                                <label>Email</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-3">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </button>
                        <button type="submit" class="btn btn-primary" id="create-btn">
                            <i class="fas fa-save me-2"></i>Crear Consulta
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tab: Buscar -->
        <div id="tab-search" class="tab-content consultas-card" style="display: none;">
            <div class="card-header-custom">
                <i class="fas fa-search me-2"></i>Buscar Consultas
            </div>
            <div class="card-body-custom">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="search-input" placeholder="Buscar...">
                            <label>Buscar por paciente, motivo, consulta...</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary w-100 h-100" onclick="performSearch()">
                            <i class="fas fa-search me-2"></i>Buscar
                        </button>
                    </div>
                </div>
                <div id="search-results"></div>
            </div>
        </div>
        
        <!-- Tab: Pacientes -->
        <div id="tab-patients" class="tab-content consultas-card" style="display: none;">
            <div class="card-header-custom">
                <i class="fas fa-users me-2"></i>Lista de Pacientes
            </div>
            <div class="card-body-custom">
                <div id="patients-list"></div>
            </div>
        </div>
        
    </div>
    
    <!-- Modal para Editar Consulta -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Editar Consulta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="edit-form-container">
                        <!-- El formulario de edición se carga aquí dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveEdit()">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Modal para Editar Consulta -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Editar Consulta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="edit-form-container">
                        <!-- El formulario de edición se carga aquí dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="saveEdit()">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alertifyjs/1.13.1/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    
    <!-- Asegurar que jQuery esté disponible -->
    <script>
        if (typeof jQuery === 'undefined') {
            console.warn('jQuery no está disponible, cargando desde CDN...');
            document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
        }
    </script>

    <script>
        // Estado global de la aplicación
        const appState = {
            currentTab: 'list',
            currentPage: 1,
            pageSize: 20,
            currentSearch: '',
            editingRecord: null,
            selectedPatient: null,
            currentFormType: 'general',
            loadingPatientConsultas: false,  // 🆕 Indicador para evitar carga automática
            isResetting: false  // 🛡️ Indicador para prevenir envío durante reset
        };

        // Configuración de la API
        const API_BASE = 'modules/consultas/api/livewire-system.php';

        // Helper functions para notificaciones
        function showSuccess(message) {
            if (typeof alertify !== 'undefined') {
                alertify.success(message);
            } else {
                console.log('SUCCESS: ' + message);
                // Fallback visual
                const alert = document.createElement('div');
                alert.className = 'alert alert-success position-fixed';
                alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                alert.innerHTML = message;
                document.body.appendChild(alert);
                setTimeout(() => alert.remove(), 3000);
            }
        }

        function showError(message) {
            if (typeof alertify !== 'undefined') {
                alertify.error(message);
            } else {
                console.error('ERROR: ' + message);
                // Fallback visual
                const alert = document.createElement('div');
                alert.className = 'alert alert-danger position-fixed';
                alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                alert.innerHTML = message;
                document.body.appendChild(alert);
                setTimeout(() => alert.remove(), 5000);
            }
        }

        // ✅ NUEVA: Función auxiliar para formatear fechas de forma consistente
        function formatDate(dateValue, defaultValue = '--') {
            if (!dateValue) return defaultValue;
            
            try {
                let fecha;
                
                // Si es una fecha tipo "YYYY-MM-DD" (típica de PostgreSQL)
                if (typeof dateValue === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
                    // Crear fecha local sin problemas de zona horaria
                    const [year, month, day] = dateValue.split('-').map(Number);
                    fecha = new Date(year, month - 1, day); // month - 1 porque Date usa 0-based months
                } else {
                    // Para otros formatos de fecha
                    fecha = new Date(dateValue);
                }
                
                if (!isNaN(fecha.getTime())) {
                    // Usar formateo manual para evitar problemas de zona horaria
                    const day = fecha.getDate().toString().padStart(2, '0');
                    const month = (fecha.getMonth() + 1).toString().padStart(2, '0');
                    const year = fecha.getFullYear();
                    return `${day}/${month}/${year}`;
                }
            } catch (e) {
                console.warn('Error parseando fecha:', dateValue, e);
            }
            
            return defaultValue;
        }

        /**
         * Funciones de navegación de pestañas
         */
        function showTab(tabName) {
            console.log('🔵 showTab called with:', tabName);
            
            // Actualizar pestañas visuales - remover active de todas
            document.querySelectorAll('.consultas-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Agregar active solo al tab clickeado
            const activeTabButton = document.querySelector(`button[onclick="showTab('${tabName}')"]`);
            if (activeTabButton) {
                activeTabButton.classList.add('active');
            }
            
            // Actualizar contenido
            console.log('🔍 Ocultando todos los tab-content...');
            document.querySelectorAll('.tab-content').forEach((content, index) => {
                content.style.display = 'none';
                content.classList.remove('active');
                console.log(`  - ${content.id}: ocultado`);
            });
            const targetTab = document.getElementById(`tab-${tabName}`);
            if (targetTab) {
                targetTab.style.display = 'block';
                targetTab.classList.add('active');
                console.log(`✅ Tab ${tabName} mostrado`);
                
                // 🔍 Debug visual del elemento
                const computedStyle = window.getComputedStyle(targetTab);
                console.log(`🔍 Elemento tab-${tabName}:`);
                console.log('  - display:', computedStyle.display);
                console.log('  - visibility:', computedStyle.visibility);
                console.log('  - opacity:', computedStyle.opacity);
                console.log('  - height:', computedStyle.height);
                console.log('  - width:', computedStyle.width);
                console.log('  - position:', computedStyle.position);
                console.log('  - top:', computedStyle.top);
                console.log('  - left:', computedStyle.left);
                console.log('  - z-index:', computedStyle.zIndex);
                
                // 🔍 Debug específico para tab-create
                if (tabName === 'create') {
                    console.log('🔍 Debugging formulario create...');
                    const form = document.getElementById('create-form');
                    if (form) {
                        const formStyle = window.getComputedStyle(form);
                        console.log('  - Form display:', formStyle.display);
                        console.log('  - Form height:', formStyle.height);
                        console.log('  - Form visibility:', formStyle.visibility);
                        
                        // Verificar elementos hijos del formulario
                        const formChildren = form.children;
                        console.log('  - Form children count:', formChildren.length);
                        Array.from(formChildren).forEach((child, index) => {
                            const childStyle = window.getComputedStyle(child);
                            console.log(`    [${index}] ${child.className}: display=${childStyle.display}, height=${childStyle.height}`);
                        });
                    } else {
                        console.error('❌ Form create-form not found!');
                    }
                }
            } else {
                console.error(`❌ No se encontró el elemento tab-${tabName}`);
            }
            
            appState.currentTab = tabName;
            console.log('🔵 appState.currentTab updated to:', appState.currentTab);
            
            // Cargar contenido específico de la pestaña
            switch(tabName) {
                case 'list':
                    // Solo cargar todas las consultas si NO estamos cargando consultas específicas de un paciente
                    if (!appState.loadingPatientConsultas) {
                        loadConsultas();
                    }
                    break;
                case 'create':
                    console.log('🔵 Ejecutando resetForm() para tab create');
                    resetForm();
                    
                    // 🔧 FIX: Asegurar que todas las form-section estén visibles
                    console.log('🔧 Activando todas las form-section...');
                    const createTab = document.getElementById('tab-create');
                    if (createTab) {
                        const formSections = createTab.querySelectorAll('.form-section');
                        formSections.forEach((section, index) => {
                            section.classList.add('active');
                            console.log(`  - Section ${index} activada`);
                        });
                    }
                    
                    // 🔧 NUEVO: Heredar paciente ya seleccionado si existe
                    if (appState.selectedPatient && appState.selectedPatient.id) {
                        console.log('🔄 Heredando paciente ya seleccionado:', appState.selectedPatient);
                        setTimeout(() => {
                            const hiddenField = document.getElementById('selected-patient-id');
                            const patientSearchField = document.getElementById('patient-search');
                            const patientInfoDiv = document.getElementById('selected-patient-info');
                            const patientInfoText = document.getElementById('patient-info-text');
                            
                            if (hiddenField && patientSearchField) {
                                hiddenField.value = appState.selectedPatient.id;
                                patientSearchField.value = `${appState.selectedPatient.firstName} ${appState.selectedPatient.lastName}`;
                                
                                if (patientInfoDiv && patientInfoText) {
                                    patientInfoText.textContent = `${appState.selectedPatient.firstName} ${appState.selectedPatient.lastName} - ${appState.selectedPatient.documentNumber}`;
                                    patientInfoDiv.style.display = 'block';
                                }
                                
                                console.log('✅ Paciente heredado correctamente en el formulario');
                            }
                        }, 200); // Pequeño delay para asegurar que el DOM esté listo
                    }
                    break;
                case 'patients':
                    loadPatients();
                    break;
            }
        }

        /**
         * Funciones para manejar tipos de formulario
         */
        function setActiveFormType(formType) {
            // Actualizar estado
            appState.currentFormType = formType;
            
            // Actualizar tabs visuales
            document.querySelectorAll('.form-type-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.form-type-tab.${formType}`).classList.add('active');
            
            // Actualizar formularios si estamos en create/edit
            if (appState.currentTab === 'create' || appState.editingRecord) {
                updateFormForType(formType);
                loadMotivosComunes(formType);
                loadPreformatos(formType);
            }
            
            if (window.debugMode) console.log('Form type changed to:', formType);
        }

        /**
         * Actualizar formulario según tipo
         */
        function updateFormForType(formType) {
            console.log('🔄 updateFormForType called with:', formType);
            
            // Mantener las secciones básicas activas, solo ocultar secciones específicas
            const anteojosSection = document.getElementById('anteojos-section');
            const visionTensionSection = document.querySelector('.vision-tension-section');
            
            // Ocultar todas las secciones específicas primero
            if (anteojosSection) {
                anteojosSection.style.display = 'none';
                anteojosSection.classList.remove('active');
            }
            
            // Controlar sección de Visión y Tensión
            if (visionTensionSection) {
                if (formType === 'general') {
                    visionTensionSection.style.display = 'flex';
                    console.log('✅ Sección de visión y tensión activada para formulario General');
                } else {
                    visionTensionSection.style.display = 'none';
                    console.log('🔒 Sección de visión y tensión oculta para formulario:', formType);
                }
            }
            
            // Mostrar la sección correspondiente al tipo
            if (formType === 'anteojos' && anteojosSection) {
                anteojosSection.style.display = 'block';
                anteojosSection.classList.add('active');
                console.log('✅ Sección de anteojos activada');
                
                // Cargar valores de referencia para anteojos
                loadReferenciales();
            }
            
            // Actualizar select de tipo de formulario
            const tipoSelect = document.getElementById('tipo_formulario');
            if (tipoSelect) {
                tipoSelect.value = formType;
                console.log('✅ Select actualizado a:', formType);
            }
        }

        /**
         * Función para búsqueda de paciente
         */
        async function searchPatient() {
            const nombres = document.getElementById('searchNombres').value.trim();
            const apellidos = document.getElementById('searchApellidos').value.trim();
            const ci = document.getElementById('searchCI').value.trim();
            
            if (!nombres && !apellidos && !ci) {
                showError('Por favor ingrese al menos un criterio de búsqueda');
                return;
            }
            
            try {
                // Construir string de búsqueda combinando todos los criterios
                let searchQuery = '';
                if (nombres) searchQuery += nombres + ' ';
                if (apellidos) searchQuery += apellidos + ' ';
                if (ci) searchQuery += ci + ' ';
                searchQuery = searchQuery.trim();
                
                if (window.debugMode) console.log('Searching for patient with query:', searchQuery);
                
                const result = await callAPI('search', {
                    table: 'rh_person',
                    search: searchQuery,
                    limit: 20
                });
                
                if (window.debugMode) console.log('Patient search result:', result);
                
                if (result.data && result.data.length > 0) {
                    if (result.data.length === 1) {
                        // Solo un resultado, seleccionarlo directamente
                        const patient = result.data[0];
                        showPatientInfo(patient);
                        await loadPatientStats(patient.person_id);
                    } else {
                        // Múltiples resultados, mostrar selector
                        showPatientSelector(result.data);
                    }
                } else {
                    showError('No se encontró ningún paciente con esos datos');
                    clearPatientInfo();
                }
            } catch (error) {
                console.error('Patient search error:', error);
                showError('Error en la búsqueda: ' + error.message);
            }
        }

        /**
         * Mostrar selector de múltiples pacientes
         */
        function showPatientSelector(patients) {
            const selectorHTML = `
                <div class="patient-selector" style="margin-top: 15px;">
                    <h5>Se encontraron ${patients.length} pacientes:</h5>
                    <div class="list-group">
                        ${patients.map(patient => `
                            <button type="button" class="list-group-item list-group-item-action" 
                                    onclick="selectPatient(${patient.person_id}, '${patient.first_name}', '${patient.last_name}', '${patient.document_number || ''}')">
                                <strong>${patient.first_name} ${patient.last_name}</strong>
                                <br><small>CI: ${patient.document_number || 'No disponible'} | Tel: ${patient.phone_number || 'No disponible'}</small>
                            </button>
                        `).join('')}
                    </div>
                </div>
            `;
            
            // Agregar el selector después de la búsqueda
            const searchArea = document.querySelector('.patient-search-area');
            
            // Remover selector anterior si existe
            const existingSelector = searchArea.querySelector('.patient-selector');
            if (existingSelector) {
                existingSelector.remove();
            }
            
            searchArea.insertAdjacentHTML('afterend', selectorHTML);
        }

        /**
         * Mostrar información del paciente
         */
        function showPatientInfo(patient) {
            const infoDiv = document.getElementById('selectedPatientInfo');
            const nameElement = document.getElementById('selectedPatientName');
            const detailsElement = document.getElementById('selectedPatientDetails');
            
            nameElement.textContent = `${patient.first_name} ${patient.last_name}`;
            detailsElement.textContent = `CI: ${patient.document_number} | Tel: ${patient.phone_number || 'No disponible'}`;
            
            infoDiv.style.display = 'block';
            
            // Guardar paciente seleccionado
            appState.selectedPatient = {
                id: patient.person_id,
                firstName: patient.first_name,
                lastName: patient.last_name,
                documentNumber: patient.document_number
            };
        }

        /**
         * 🆕 Actualizar estadísticas del paciente seleccionado después de operaciones CRUD
         */
        async function updatePatientStats() {
            if (!appState.selectedPatient || !appState.selectedPatient.id) {
                console.log('⚠️ No hay paciente seleccionado para actualizar estadísticas');
                return;
            }
            
            const patientId = appState.selectedPatient.id;
            console.log('🔄 Actualizando estadísticas del paciente ID:', patientId);
            
            try {
                // Cargar consultas actualizadas del paciente
                const result = await callAPI('search', {
                    table: 'consultas',
                    search: `id_persona:${patientId}`,
                    limit: 100,
                    _t: Date.now() // Anti-cache
                });
                
                const consultas = result.data || [];
                const totalConsultas = consultas.length;
                
                // Valores por defecto
                let ultimaConsulta = '--';
                let proximaConsulta = '--';
                
                if (consultas.length > 0) {
                    // Ordenar por fecha de registro más reciente
                    consultas.sort((a, b) => new Date(b.fecha_registro) - new Date(a.fecha_registro));
                    
                    // Última consulta
                    if (consultas[0].fecha_registro) {
                        ultimaConsulta = formatDate(consultas[0].fecha_registro);
                    }
                    
                    // Próxima consulta programada
                    const proximaConsultaData = consultas.find(c => {
                        if (!c.proximaconsulta) return false;
                        const fecha = new Date(c.proximaconsulta);
                        return !isNaN(fecha.getTime()) && fecha > new Date();
                    });
                    
                    if (proximaConsultaData) {
                        proximaConsulta = formatDate(proximaConsultaData.proximaconsulta);
                    }
                }
                
                // Actualizar estadísticas en la UI
                document.getElementById('totalConsultas').textContent = totalConsultas;
                document.getElementById('ultimaConsulta').textContent = ultimaConsulta;
                document.getElementById('proximaConsulta').textContent = proximaConsulta;
                
                console.log('✅ Estadísticas actualizadas:', {
                    total: totalConsultas,
                    ultima: ultimaConsulta,
                    proxima: proximaConsulta
                });
                
            } catch (error) {
                console.error('❌ Error actualizando estadísticas del paciente:', error);
            }
        }

        /**
         * Cargar estadísticas del paciente
         */
        async function loadPatientStats(patientId) {
            try {
                // Buscar consultas solo de este paciente usando su ID
                const result = await callAPI('search', {
                    table: 'consultas',
                    search: `id_persona:${patientId}`,
                    limit: 100  // Cargar más consultas para historial completo
                });
                
                if (window.debugMode) console.log(`Loading patient stats for ID ${patientId}:`, result);
                
                const consultas = result.data || [];
                const totalConsultas = consultas.length;
                
                // ✅ CORREGIR: Valores por defecto más apropiados
                let ultimaConsulta = '--';
                let proximaConsulta = '--';
                
                if (consultas.length > 0) {
                    // Ordenar por fecha de registro más reciente
                    consultas.sort((a, b) => new Date(b.fecha_registro) - new Date(a.fecha_registro));
                    
                    // ✅ MEJORAR: Manejo robusto de fecha_registro usando función auxiliar
                    if (consultas[0].fecha_registro) {
                        ultimaConsulta = formatDate(consultas[0].fecha_registro);
                    }
                    
                    // ✅ MEJORAR: Buscar próxima consulta programada usando función auxiliar
                    const proximaConsultaData = consultas.find(c => {
                        if (!c.proximaconsulta) return false;
                        const fecha = new Date(c.proximaconsulta);
                        return !isNaN(fecha.getTime()) && fecha > new Date();
                    });
                    
                    if (proximaConsultaData) {
                        proximaConsulta = formatDate(proximaConsultaData.proximaconsulta);
                    }
                } else {
                    // ✅ Si no hay consultas, mostrar valores por defecto
                    ultimaConsulta = '--';
                    proximaConsulta = '--';
                }
                
                // Actualizar estadísticas en la UI
                document.getElementById('totalConsultas').textContent = totalConsultas;
                document.getElementById('ultimaConsulta').textContent = ultimaConsulta;
                document.getElementById('proximaConsulta').textContent = proximaConsulta;
                
                // 🆕 NUEVO: Cargar automáticamente todas las consultas en "Listar Consultas"
                await loadAllPatientConsultasInList(patientId, consultas);
                
            } catch (error) {
                console.error('Error loading patient stats:', error);
            }
        }

        /**
         * 🆕 Cargar automáticamente todas las consultas del paciente en "Listar Consultas"
         */
        async function loadAllPatientConsultasInList(patientId, consultas) {
            try {
                if (!consultas || consultas.length === 0) {
                    console.log(`No hay consultas para cargar en lista para paciente ID ${patientId}`);
                    return;
                }

                console.log(`📋 Cargando automáticamente ${consultas.length} consultas en "Listar Consultas" para paciente ID ${patientId}`);

                // Activar el indicador para evitar la carga automática de todas las consultas
                appState.loadingPatientConsultas = true;

                // Cambiar automáticamente a la pestaña "Listar Consultas"
                showTab('list');

                // Dar un pequeño delay para asegurar que la pestaña se carga
                setTimeout(() => {
                    // Esperar hasta que el elemento consultas-list esté disponible
                    const waitForElement = (selector, callback, maxAttempts = 10, currentAttempt = 1) => {
                        const element = document.getElementById(selector);
                        
                        if (element) {
                            callback(element);
                        } else if (currentAttempt < maxAttempts) {
                            console.log(`⏳ Esperando elemento ${selector}... intento ${currentAttempt}/${maxAttempts}`);
                            setTimeout(() => {
                                waitForElement(selector, callback, maxAttempts, currentAttempt + 1);
                            }, 200);
                        } else {
                            console.error(`❌ No se pudo encontrar el elemento ${selector} después de ${maxAttempts} intentos`);
                            
                            // Como último recurso, intentar cargar las consultas normalmente
                            console.log('🔄 Intentando cargar consultas con loadConsultas() como respaldo...');
                            appState.loadingPatientConsultas = false; // Desactivar antes del fallback
                            if (typeof loadConsultas === 'function') {
                                loadConsultas();
                            }
                        }
                    };
                    
                    // Esperar a que aparezca consultas-list y luego cargar los datos
                    waitForElement('consultas-list', (listEl) => {
                        const paginationEl = document.getElementById('consultas-pagination');
                        
                        // Ordenar consultas por fecha más reciente primero
                        consultas.sort((a, b) => new Date(b.fecha_registro) - new Date(a.fecha_registro));
                        
                        // Mostrar todas las consultas del paciente en la tabla
                        listEl.innerHTML = createModernConsultasTable(consultas);
                        console.log(`✅ ${consultas.length} consultas del paciente cargadas correctamente en "Listar Consultas"`);
                        
                        // Actualizar paginación
                        if (paginationEl) {
                            paginationEl.innerHTML = createPagination({
                                page: 1,
                                limit: consultas.length,
                                total: consultas.length,
                                pages: 1
                            });
                        }
                        
                        // Agregar indicador visual de que está filtrado por paciente
                        const tableContainer = listEl.querySelector('.table-container');
                        if (tableContainer && consultas.length > 0) {
                            const patientName = `${consultas[0].first_name} ${consultas[0].last_name}`;
                            const filterIndicator = document.createElement('div');
                            filterIndicator.className = 'alert alert-info';
                            filterIndicator.style.marginBottom = '15px';
                            filterIndicator.innerHTML = `
                                <strong>📋 Consultando historial completo de:</strong> ${patientName} 
                                <span class="badge bg-primary ms-2">${consultas.length} consultas</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-3" onclick="showAllConsultas()">
                                    🔄 Mostrar todas las consultas
                                </button>
                            `;
                            tableContainer.insertBefore(filterIndicator, tableContainer.firstChild);
                        }
                        
                        // Scroll suave hasta la tabla
                        listEl.scrollIntoView({ behavior: 'smooth' });
                        
                        // Desactivar el indicador después de cargar exitosamente
                        appState.loadingPatientConsultas = false;
                    });
                    
                }, 300); // Incrementar el delay inicial

            } catch (error) {
                console.error('Error loading patient consultas automatically:', error);
                // Desactivar el indicador en caso de error
                appState.loadingPatientConsultas = false;
            }
        }

        /**
         * Ver detalles de una consulta específica
         */
        function viewConsultaDetails(consultaId) {
            console.log(`🔍 Mostrando detalles de consulta ID: ${consultaId}`);
            
            // Cambiar a la pestaña "Listar Consultas" (no "Buscar")
            showTab('list');
            
            // Buscar la consulta específica después de cambiar de pestaña
            setTimeout(async () => {
                try {
                    console.log(`🚀 Buscando consulta ID ${consultaId} directamente con API...`);
                    
                    const result = await callAPI('search', {
                        table: 'consultas',
                        search: `id:${consultaId}`,
                        limit: 1
                    });
                    
                    console.log('📋 Resultado de búsqueda:', result);
                    
                    if (result.data && result.data.length > 0) {
                        // Mostrar los resultados en la tabla de "Listar Consultas"
                        const listEl = document.getElementById('list-content');
                        const paginationEl = document.getElementById('consultas-pagination');
                        
                        if (listEl) {
                            listEl.innerHTML = createModernConsultasTable(result.data);
                            console.log('✅ Consulta mostrada correctamente en Listar Consultas');
                            
                            if (paginationEl) {
                                paginationEl.innerHTML = createPagination(result.meta || {
                                    page: 1,
                                    limit: 1,
                                    total: 1,
                                    pages: 1
                                });
                            }
                            
                            // Scroll hasta la tabla para que sea visible
                            listEl.scrollIntoView({ behavior: 'smooth' });
                            
                        } else {
                            console.error('❌ No se encontró el elemento list-content');
                            console.log('� Elementos disponibles:', {
                                'list-content': document.getElementById('list-content'),
                                'tab-list': document.getElementById('tab-list'),
                                'search-results': document.getElementById('search-results')
                            });
                        }
                    } else {
                        console.warn('⚠️ No se encontró la consulta');
                        showError(`No se encontró la consulta con ID ${consultaId}`);
                    }
                    
                } catch (error) {
                    console.error('❌ Error al mostrar consulta:', error);
                    showError('Error al cargar la consulta');
                }
            }, 500); // Dar más tiempo para que se cargue la pestaña completamente
        }

        // 🆕 NUEVO: Estado para el autocompletado inteligente
        let smartSearchState = {
            isSearching: false,
            lastQuery: '',
            selectedIndex: -1,
            results: []
        };

        /**
         * 🆕 NUEVO: Manejar entrada en el campo de búsqueda inteligente
         */
        function handleSmartSearch(event) {
            const query = event.target.value.trim();
            
            if (query.length < 3) {
                hideSmartDropdown();
                return;
            }
            
            // Evitar búsquedas repetidas
            if (query === smartSearchState.lastQuery) {
                return;
            }
            
            smartSearchState.lastQuery = query;
            smartSearchState.selectedIndex = -1;
            
            // Debounce: esperar 300ms después de que el usuario deje de escribir
            clearTimeout(smartSearchState.searchTimeout);
            smartSearchState.searchTimeout = setTimeout(() => {
                performSmartSearch(query);
            }, 300);
        }

        /**
         * 🆕 NUEVO: Manejar teclas en el autocompletado
         */
        function handleSmartSearchKeydown(event) {
            const dropdown = document.getElementById('smartSearchDropdown');
            const items = dropdown.querySelectorAll('.search-result-item');
            
            switch(event.key) {
                case 'Enter':
                    event.preventDefault();
                    if (smartSearchState.selectedIndex >= 0 && smartSearchState.selectedIndex < items.length) {
                        // Seleccionar el item destacado
                        selectSmartSearchResult(smartSearchState.results[smartSearchState.selectedIndex]);
                    } else if (event.target.value.trim().length >= 3) {
                        // Buscar si no hay selección pero hay texto suficiente
                        performSmartSearch(event.target.value.trim());
                    }
                    break;
                    
                case 'ArrowDown':
                    event.preventDefault();
                    if (items.length > 0) {
                        smartSearchState.selectedIndex = Math.min(smartSearchState.selectedIndex + 1, items.length - 1);
                        updateSmartSelection(items);
                    }
                    break;
                    
                case 'ArrowUp':
                    event.preventDefault();
                    if (items.length > 0) {
                        smartSearchState.selectedIndex = Math.max(smartSearchState.selectedIndex - 1, -1);
                        updateSmartSelection(items);
                    }
                    break;
                    
                case 'Escape':
                    hideSmartDropdown();
                    event.target.blur();
                    break;
            }
        }

        /**
         * 🆕 NUEVO: Realizar búsqueda inteligente
         */
        async function performSmartSearch(query) {
            if (smartSearchState.isSearching) return;
            
            smartSearchState.isSearching = true;
            showSmartDropdown('🔍 Buscando...');
            
            try {
                const response = await fetch('modules/consultas/api/livewire-system.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=search&table=rh_person&nombres=${encodeURIComponent(query)}&apellidos=${encodeURIComponent(query)}&ci=${encodeURIComponent(query)}`
                });
                
                const data = await response.json();
                
                console.log('🔍 Respuesta de búsqueda:', data); // Debug
                
                if (data.success && data.data && data.data.length > 0) {
                    smartSearchState.results = data.data;
                    displaySmartSearchResults(data.data);
                } else {
                    showSmartDropdown('<div class="no-results">❌ No se encontraron pacientes</div>');
                }
                
            } catch (error) {
                console.error('❌ Error en búsqueda inteligente:', error);
                showSmartDropdown('<div class="error-results">⚠️ Error en la búsqueda</div>');
            } finally {
                smartSearchState.isSearching = false;
            }
        }

        /**
         * 🆕 NUEVO: Mostrar dropdown de búsqueda
         */
        function showSmartDropdown(content) {
            const dropdown = document.getElementById('smartSearchDropdown');
            dropdown.innerHTML = content;
            dropdown.style.display = 'block';
        }

        /**
         * 🆕 NUEVO: Ocultar dropdown
         */
        function hideSmartDropdown() {
            const dropdown = document.getElementById('smartSearchDropdown');
            dropdown.style.display = 'none';
            smartSearchState.selectedIndex = -1;
        }

        /**
         * 🆕 NUEVO: Mostrar resultados en el dropdown
         */
        function displaySmartSearchResults(patients) {
            let html = '';
            
            patients.forEach((patient, index) => {
                // ✅ USAR CAMPOS CORRECTOS DE LA BD
                const firstName = patient.first_name || 'N/A';
                const lastName = patient.last_name || 'N/A';
                const fullName = `${firstName} ${lastName}`;
                const ci = patient.document_number || 'Sin CI';
                const telefono = patient.phone_number || null;
                
                html += `
                    <div class="search-result-item" 
                         data-index="${index}"
                         onclick="selectSmartSearchResult(${JSON.stringify(patient).replace(/"/g, '&quot;')})"
                         onmouseover="smartSearchState.selectedIndex = ${index}; updateSmartSelection(document.querySelectorAll('.search-result-item'))"
                         onmouseleave="smartSearchState.selectedIndex = -1; updateSmartSelection(document.querySelectorAll('.search-result-item'))"
                         style="
                            padding: 12px 15px;
                            cursor: pointer;
                            border-bottom: 1px solid #eee;
                            transition: background 0.2s;
                         ">
                        <div style="font-weight: bold; color: #333;">${fullName}</div>
                        <div style="color: #666; font-size: 0.9em;">CI: ${ci}</div>
                        ${telefono ? `<div style="color: #888; font-size: 0.8em;">📞 ${telefono}</div>` : ''}
                    </div>
                `;
            });
            
            showSmartDropdown(html);
            
            // 🚫 NO seleccionar automáticamente el primer resultado
            smartSearchState.selectedIndex = -1;
        }

        /**
         * 🆕 NUEVO: Actualizar selección visual
         */
        function updateSmartSelection(items) {
            items.forEach((item, index) => {
                if (index === smartSearchState.selectedIndex) {
                    item.style.background = '#007bff';
                    item.style.color = 'white';
                    item.classList.add('selected');
                } else {
                    item.style.background = '';
                    item.style.color = '';
                    item.classList.remove('selected');
                }
            });
        }

        /**
         * 🆕 NUEVO: Seleccionar resultado de búsqueda
         */
        function selectSmartSearchResult(patient) {
            console.log('🎯 Paciente seleccionado:', patient);
            
            // ✅ USAR CAMPOS CORRECTOS DE LA BD
            const firstName = patient.first_name || 'N/A';
            const lastName = patient.last_name || 'N/A';
            const fullName = `${firstName} ${lastName}`;
            
            // Actualizar campo de búsqueda
            document.getElementById('smartPatientSearch').value = fullName;
            
            // Ocultar dropdown
            hideSmartDropdown();
            
            // Cargar información del paciente
            loadSelectedPatient(patient);
            
            // Cargar consultas automáticamente usando person_id
            const patientId = patient.person_id || patient.id_persona;
            if (patientId) {
                loadPatientConsultas(patientId);
            } else {
                console.error('❌ No se encontró ID del paciente');
            }
        }
        
        /**
         * 🆕 NUEVO: Alias para cargar consultas del paciente (compatibilidad)
         */
        function loadPatientConsultas(patientId) {
            console.log('🔄 Cargando consultas del paciente ID:', patientId);
            loadPatientStats(patientId);
        }

        /**
         * 🆕 NUEVO: Limpiar búsqueda inteligente y TODA LA PÁGINA
         */
        function clearSmartPatientSearch() {
            console.log('🧹 Limpiando toda la página...');
            
            // 1. Limpiar campo de búsqueda y dropdown
            document.getElementById('smartPatientSearch').value = '';
            hideSmartDropdown();
            
            // 2. Limpiar información del paciente seleccionado
            clearPatientInfo();
            
            // 3. Limpiar estado de búsqueda
            smartSearchState.lastQuery = '';
            smartSearchState.results = [];
            smartSearchState.selectedIndex = -1;
            
            // 4. 🆕 LIMPIAR TABLA DE CONSULTAS - volver al estado inicial
            const listContent = document.getElementById('list-content');
            if (listContent) {
                // Mostrar mensaje de carga mientras limpiamos
                listContent.innerHTML = `
                    <div class="text-center p-4" style="color: #666;">
                        <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                        Limpiando y cargando consultas generales...
                    </div>
                `;
            }
            
            // 🆕 TAMBIÉN LIMPIAR LA TABLA VISIBLE (tbody)
            const tableBody = document.querySelector('#list-content tbody');
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center p-4" style="color: #666;">
                            <div class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></div>
                            Cargando consultas generales...
                        </td>
                    </tr>
                `;
            }
            
            // 5. 🆕 LIMPIAR ESTADÍSTICAS DEL PACIENTE
            const statsElements = {
                'totalConsultas': '0',
                'ultimaConsulta': '--',
                'proximaConsulta': '--'
            };
            
            Object.entries(statsElements).forEach(([id, defaultValue]) => {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = defaultValue;
                }
            });
            
            // 6. 🆕 RESET DEL ESTADO GLOBAL DE LA APLICACIÓN - COMPLETO
            appState.selectedPatient = null;
            appState.currentTab = 'list';
            appState.loadingPatientConsultas = false;
            appState.isResetting = false;
            
            // 🆕 LIMPIAR FILTROS Y BÚSQUEDAS ACTIVAS
            appState.currentFilters = {};
            appState.currentSearch = ''; // ✅ Esto es clave para loadConsultas
            appState.pageSize = 10; // Resetear tamaño de página
            
            // 🆕 LIMPIAR CUALQUIER FILTRO DE PACIENTE ESPECÍFICO  
            if (window.currentPatientFilter) {
                delete window.currentPatientFilter;
            }
            
            // 7. 🆕 CARGAR CONSULTAS APROPIADAS (respetando paciente seleccionado)
            console.log('🔄 Recargando consultas apropiadas...');
            
            // Usar la función loadConsultas mejorada que respeta el paciente seleccionado
            setTimeout(async () => {
                try {
                    if (appState.selectedPatient && appState.selectedPatient.id) {
                        console.log('👤 Paciente seleccionado detectado - cargando solo sus consultas:', appState.selectedPatient.firstName);
                        loadConsultas(1);
                    } else {
                        console.log('🌐 No hay paciente seleccionado - cargando consultas generales');
                        
                        // Llamar directamente a la API sin filtros para consultas generales
                        const result = await callAPI('list', {
                            table: 'consultas',
                            page: 1,
                            limit: 10,
                            search: '', // Sin búsqueda
                            filters: {} // Sin filtros
                        });
                        
                        if (result && result.data) {
                            // Si existe renderConsultasList, usarla, sino usar loadConsultas
                            if (typeof renderConsultasList === 'function') {
                                renderConsultasList(result.data, result.meta);
                            } else {
                                // Fallback: forzar actualización de la tabla
                                const listContent = document.getElementById('list-content');
                                if (listContent && result.data.length > 0) {
                                    // Simular la estructura que espera la tabla
                                    loadConsultas(1);
                                }
                            }
                            console.log('✅ Consultas generales cargadas:', result.data.length);
                        } else {
                            // Fallback si no hay datos
                            loadConsultas(1);
                        }
                    }
                    
                } catch (error) {
                    console.error('❌ Error cargando consultas:', error);
                    loadConsultas(1); // Fallback a la función normal
                }
            }, 150); // Pausa para que se vea la limpieza
            
            // 8. 🆕 LIMPIAR CUALQUIER FILTRO O BÚSQUEDA ACTIVA EN LA UI
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = '';
            }
            
            // 🆕 LIMPIAR SELECTOR DE PÁGINA SI EXISTE
            const pageSizeSelect = document.querySelector('select[onchange*="pageSize"]');
            if (pageSizeSelect) {
                pageSizeSelect.value = '10'; // Valor por defecto
            }
            
            // 🆕 RESETEAR CONTADORES DE PAGINACIÓN
            appState.currentPage = 1;
            
            // 9. 🆕 VOLVER A LA PESTAÑA PRINCIPAL
            showTab('list');
            
            console.log('✅ Página completamente limpiada y resetada');
        }

        /**
         * Limpiar búsqueda de paciente (mantenida para compatibilidad)
         */
        function clearPatientSearch() {
            clearSmartPatientSearch();
        }

        /**
         * 🆕 NUEVO: Cargar información del paciente seleccionado desde autocompletado
         */
        function loadSelectedPatient(patient) {
            console.log('🎯 Cargando información del paciente:', patient);
            
            // Mostrar información del paciente
            const infoDiv = document.getElementById('selectedPatientInfo');
            const nameElement = document.getElementById('selectedPatientName');
            const detailsElement = document.getElementById('selectedPatientDetails');
            
            if (infoDiv && nameElement && detailsElement) {
                // ✅ USAR CAMPOS CORRECTOS DE LA BD
                const firstName = patient.first_name || 'N/A';
                const lastName = patient.last_name || 'N/A';
                const ci = patient.document_number || 'Sin CI';
                const telefono = patient.phone_number || 'No disponible';
                
                nameElement.textContent = `${firstName} ${lastName}`;
                detailsElement.textContent = `CI: ${ci} | Tel: ${telefono}`;
                
                infoDiv.style.display = 'block';
                
                // Guardar paciente seleccionado con el formato correcto
                appState.selectedPatient = {
                    id: patient.person_id || patient.id_persona, // ✅ person_id es el campo correcto
                    firstName: firstName,
                    lastName: lastName,
                    documentNumber: ci
                };
                
                // Cargar estadísticas del paciente
                const patientId = patient.person_id || patient.id_persona;
                if (patientId) {
                    loadPatientStats(patientId);
                }
                
                console.log('✅ Paciente cargado en appState:', appState.selectedPatient);
            } else {
                console.error('❌ No se encontraron elementos para mostrar información del paciente');
            }
        }

        /**
         * Limpiar información del paciente
         */
        function clearPatientInfo() {
            document.getElementById('selectedPatientInfo').style.display = 'none';
            document.getElementById('totalConsultas').textContent = '0';
            document.getElementById('ultimaConsulta').textContent = '--';
            document.getElementById('proximaConsulta').textContent = '--';
            appState.selectedPatient = null;
        }

        /**
         * 🆕 Mostrar todas las consultas (quitar filtro de paciente específico)
         */
        function showAllConsultas() {
            console.log('🔄 Cargando todas las consultas...');
            
            // Desactivar el indicador para permitir carga normal
            appState.loadingPatientConsultas = false;
            
            // Recargar todas las consultas sin filtro
            loadConsultas();
        }

        /**
         * Funciones de API
         */
        async function callAPI(action, data = {}) {
            const requestData = {
                action: action,
                ...data
            };
            
            // Solo logear requests importantes o cuando hay debug activo
            if (action === 'create' || action === 'update' || action === 'delete' || action === 'search' || window.debugMode) {
                console.log('🚀 API Request:', requestData);
            }
            
            try {
                const response = await fetch(API_BASE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                });
                
                const responseText = await response.text();
                
                // Solo mostrar raw response en caso de error o debug mode
                if (action === 'search' || window.debugMode) {
                    console.log('📨 Raw API Response:', responseText);
                }
                
                // Intentar parsear como JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON Parse Error:', parseError);
                    console.error('Response text was:', responseText);
                    
                    // Si la respuesta contiene HTML/PHP errors, extraer el error
                    if (responseText.includes('<br />') || responseText.includes('Warning:') || responseText.includes('Fatal error:')) {
                        throw new Error('Error del servidor PHP - revisa la consola del navegador para más detalles');
                    } else {
                        throw new Error('Respuesta inválida del servidor: ' + responseText.substring(0, 100));
                    }
                }
                
                // Solo logear responses importantes o en debug mode
                if (action === 'create' || action === 'update' || action === 'delete' || window.debugMode) {
                    console.log('API Response:', result);
                }
                
                if (!result.success) {
                    throw new Error(result.message || 'Error en la operación');
                }
                
                // Log para search results
                if (action === 'search' || window.debugMode) {
                    console.log('✅ API Response:', result);
                }
                
                return result;
                
            } catch (error) {
                console.error('API Error:', error);
                showError('Error: ' + error.message);
                throw error;
            }
        }

        /**
         * Cargar lista de consultas
         */
        async function loadConsultas(page = 1) {
            const loadingEl = document.getElementById('consultas-loading');
            const listEl = document.getElementById('consultas-list');
            const paginationEl = document.getElementById('consultas-pagination');
            
            loadingEl.style.display = 'block';
            listEl.innerHTML = '';
            paginationEl.innerHTML = '';
            
            try {
                // MEJORA: Si hay un paciente seleccionado, cargar solo sus consultas
                let searchParam = appState.currentSearch;
                if (appState.selectedPatient && appState.selectedPatient.id) {
                    searchParam = `id_persona:${appState.selectedPatient.id}`;
                    console.log('📋 Cargando consultas del paciente seleccionado:', appState.selectedPatient.firstName, appState.selectedPatient.lastName);
                } else {
                    console.log('📋 Cargando consultas generales');
                }
                
                // CORRECCIÓN: Agregar timestamp para evitar cache en lista
                const result = await callAPI('list', {
                    table: 'consultas',
                    page: page,
                    limit: appState.pageSize,
                    search: searchParam,
                    _t: Date.now() // Anti-cache timestamp
                });
                
                loadingEl.style.display = 'none';
                
                if (result.data && result.data.length > 0) {
                    listEl.innerHTML = createModernConsultasTable(result.data);
                    paginationEl.innerHTML = createPagination(result.meta);
                    
                    // 📊 Actualizar estadísticas del paciente si hay uno seleccionado
                    if (appState.selectedPatient && appState.selectedPatient.id) {
                        actualizarEstadisticasPaciente(result.data);
                    }
                } else {
                    const message = appState.selectedPatient 
                        ? `📋 No hay consultas para ${appState.selectedPatient.firstName} ${appState.selectedPatient.lastName}`
                        : '📋 No hay consultas registradas';
                    listEl.innerHTML = `<div class="table-container"><div style="text-align: center; padding: 60px; color: #666;"><h4>${message}</h4><p>Comience creando una nueva consulta médica</p></div></div>`;
                    
                    // 📊 Si no hay consultas, limpiar estadísticas
                    if (appState.selectedPatient && appState.selectedPatient.id) {
                        actualizarEstadisticasPaciente([]);
                    }
                }
                
            } catch (error) {
                loadingEl.style.display = 'none';
                listEl.innerHTML = '<div class="table-container"><div style="text-align: center; padding: 60px; color: #dc3545;"><h4>⚠️ Error al cargar consultas</h4><p>Por favor, inténtelo de nuevo</p></div></div>';
            }
        }

        /**
         * Crear tabla moderna de consultas
         */
        function createModernConsultasTable(consultas) {
            const tableHeader = `
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            const tableRows = consultas.map(consulta => {
                // ✅ CORREGIR: Usar función auxiliar para formateo de fechas
                let fechaFormatted = formatDate(consulta.fecha_consulta);
                
                // Fallback a fecha_registro si no hay fecha_consulta válida
                if (fechaFormatted === '--' && consulta.fecha_registro) {
                    fechaFormatted = formatDate(consulta.fecha_registro);
                }
                
                const pacienteInfo = `${consulta.first_name || ''} ${consulta.last_name || ''}`.trim();
                const tipoColor = getTipoColor(consulta.tipo_formulario);
                
                return `
                    <tr>
                        <td><strong>#${consulta.id_consulta}</strong></td>
                        <td>${fechaFormatted}</td>
                        <td>
                            <div>
                                <strong>${pacienteInfo || 'Sin nombre'}</strong>
                                ${consulta.document_number ? `<br><small style="color: #666;">CI: ${consulta.document_number}</small>` : ''}
                            </div>
                        </td>
                        <td><span class="badge" style="background: ${tipoColor}; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">${consulta.tipo_formulario || 'general'}</span></td>
                        <td style="max-width: 200px;">
                            <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${consulta.motivo || 'No especificado'}">
                                ${consulta.motivo || 'No especificado'}
                            </div>
                        </td>
                        <td><span class="badge" style="background: #4CAF50; color: white; padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">Completada</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-warning btn-sm" onclick="editConsulta(${consulta.id_consulta})" title="Editar">
                                    ✏️
                                </button>
                                <button class="btn-primary btn-sm" onclick="viewConsulta(${consulta.id_consulta})" title="Ver detalles">
                                    👁️
                                </button>
                                <button class="btn-danger btn-sm" onclick="deleteConsulta(${consulta.id_consulta})" title="Eliminar">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            const tableFooter = `
                        </tbody>
                    </table>
                </div>
            `;
            
            return tableHeader + tableRows + tableFooter;
        }

        /**
         * Obtener color según tipo de formulario
         */
        function getTipoColor(tipo) {
            switch(tipo) {
                case 'general': return '#4CAF50';
                case 'anteojos': return '#7B68EE';
                case 'estudios': return '#5B9BD5';
                case 'informe_imagen': return '#9966CC';
                default: return '#666';
            }
        }

        /**
         * Crear card de consulta
         */
        function createConsultaCard(consulta) {
            const fechaFormatted = new Date(consulta.fecha_consulta).toLocaleString('es-ES');
            const pacienteInfo = `${consulta.first_name || ''} ${consulta.last_name || ''}`.trim();
            
            return `
                <div class="record-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex gap-3 mb-2">
                                <span class="badge bg-primary">#${consulta.id_consulta}</span>
                                <span class="badge bg-info">${consulta.tipo_formulario || 'general'}</span>
                                <small class="text-muted">${fechaFormatted}</small>
                            </div>
                            
                            <h6 class="mb-2">
                                <i class="fas fa-user me-1"></i>
                                ${pacienteInfo || 'Sin nombre'}
                                ${consulta.document_number ? `(${consulta.document_number})` : ''}
                            </h6>
                            
                            <p class="mb-2">
                                <strong>Motivo:</strong> ${consulta.motivo || 'No especificado'}
                            </p>
                            
                            ${consulta.consulta ? `
                                <p class="mb-2">
                                    <strong>Consulta:</strong> ${consulta.consulta.substring(0, 100)}...
                                </p>
                            ` : ''}
                            
                            ${consulta.vision_od || consulta.vision_oi ? `
                                <div class="row">
                                    <div class="col-6">
                                        <small><strong>Visión OD:</strong> ${consulta.vision_od || '-'}</small>
                                    </div>
                                    <div class="col-6">
                                        <small><strong>Visión OI:</strong> ${consulta.vision_oi || '-'}</small>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                        
                        <div class="record-actions">
                            <button class="btn btn-sm btn-outline-primary btn-action" onclick="editConsulta(${consulta.id_consulta})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info btn-action" onclick="viewConsulta(${consulta.id_consulta})" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-action" onclick="deleteConsulta(${consulta.id_consulta})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Crear paginación
         */
        function createPagination(meta) {
            if (meta.pages <= 1) return '';
            
            let html = '<nav><ul class="pagination justify-content-center">';
            
            // Botón anterior
            html += `
                <li class="page-item ${meta.page <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="loadConsultas(${meta.page - 1})">Anterior</a>
                </li>
            `;
            
            // Páginas
            const maxVisible = 5;
            let start = Math.max(1, meta.page - Math.floor(maxVisible / 2));
            let end = Math.min(meta.pages, start + maxVisible - 1);
            
            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }
            
            for (let i = start; i <= end; i++) {
                html += `
                    <li class="page-item ${i === meta.page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadConsultas(${i})">${i}</a>
                    </li>
                `;
            }
            
            // Botón siguiente
            html += `
                <li class="page-item ${meta.page >= meta.pages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="loadConsultas(${meta.page + 1})">Siguiente</a>
                </li>
            `;
            
            html += '</ul></nav>';
            html += `<div class="text-center text-muted mt-2">
                Mostrando ${meta.page * meta.limit - meta.limit + 1} - ${Math.min(meta.page * meta.limit, meta.total)} de ${meta.total} registros
            </div>`;
            
            return html;
        }

        /**
         * Manejo de navegación con teclado en búsqueda de pacientes
         */
        let selectedResultIndex = -1;
        let searchResults = [];
        
        function handlePatientSearchKeydown(event) {
            const resultsEl = document.getElementById('patient-results');
            const items = resultsEl.querySelectorAll('.search-result-item:not(.text-muted)');
            
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                selectedResultIndex = Math.min(selectedResultIndex + 1, items.length - 1);
                updateSelectedResult(items);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                selectedResultIndex = Math.max(selectedResultIndex - 1, -1);
                updateSelectedResult(items);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (selectedResultIndex >= 0 && items[selectedResultIndex]) {
                    items[selectedResultIndex].click();
                }
            } else if (event.key === 'Escape') {
                resultsEl.style.display = 'none';
                selectedResultIndex = -1;
            }
        }
        
        function handlePatientSearchKeyup(event) {
            // No procesar teclas de navegación
            if (['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(event.key)) {
                return;
            }
            
            selectedResultIndex = -1;
            searchPatients(event.target.value);
        }
        
        function updateSelectedResult(items) {
            // Remover selección previa
            items.forEach(item => item.classList.remove('selected'));
            
            // Agregar selección actual
            if (selectedResultIndex >= 0 && items[selectedResultIndex]) {
                items[selectedResultIndex].classList.add('selected');
                items[selectedResultIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        /**
         * Buscar pacientes para autocompletar
         */
        let searchTimeout;
        async function searchPatients(query) {
            if (window.debugMode) console.log('searchPatients called with:', query);
            const resultsEl = document.getElementById('patient-results');
            if (window.debugMode) console.log('Results element found:', resultsEl);
            
            // Solo buscar si tiene 3 o más caracteres
            if (query.length < 3) {
                resultsEl.style.display = 'none';
                selectedResultIndex = -1;
                return;
            }
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                selectedResultIndex = -1; // Reset selection
                if (window.debugMode) console.log('Making API call for search...');
                try {
                    console.log('🔍 SEARCH DEBUG - Enviando:', { table: 'rh_person', search: query, limit: 10 }); // DEBUG
                    const result = await callAPI('search', {
                        table: 'rh_person',
                        search: query,
                        limit: 10
                    });
                    
                    console.log('📡 SEARCH DEBUG - Respuesta:', result); // DEBUG
                    
                    if (result.data && result.data.length > 0) {
                        if (window.debugMode) console.log('Processing', result.data.length, 'results');
                        resultsEl.innerHTML = result.data.map(patient => {
                            // Sanitizar datos para evitar errores
                            const firstName = patient.first_name || '';
                            const lastName = patient.last_name || '';
                            const documentNumber = patient.document_number || '';
                            const phoneNumber = patient.phone_number || 'N/A';
                            const personId = patient.person_id || '';
                            
                            if (window.debugMode) console.log('Processing patient:', {personId, firstName, lastName, documentNumber});
                            
                            return `
                                <div class="search-result-item" onclick="selectPatient(${personId}, '${firstName.replace(/'/g, "\\'")}', '${lastName.replace(/'/g, "\\'")}', '${documentNumber}')">
                                    <strong>${firstName} ${lastName}</strong><br>
                                    <small class="text-muted">Documento: ${documentNumber} | Tel: ${phoneNumber}</small>
                                </div>
                            `;
                        }).join('');
                        resultsEl.style.display = 'block';
                        if (window.debugMode) console.log('Results displayed successfully');
                        if (window.debugMode) console.log('Results displayed successfully');
                    } else {
                        if (window.debugMode) console.log('No results found');
                        resultsEl.innerHTML = '<div class="search-result-item text-muted">No se encontraron pacientes con ese nombre</div>';
                        resultsEl.style.display = 'block';
                    }
                    
                } catch (error) {
                    console.error('Error en búsqueda de pacientes:', error);
                    resultsEl.innerHTML = '<div class="search-result-item text-muted text-danger">Error en la búsqueda</div>';
                    resultsEl.style.display = 'block';
                }
            }, 300); // Restaurar timeout normal
        }

        /**
         * Limpiar selección de paciente
         */
        function clearPatientSelection() {
            if (window.debugMode) console.log('Clearing patient selection');
            document.getElementById('selected-patient-id').value = '';
            document.getElementById('patient-search').value = '';
            document.getElementById('selected-patient-info').style.display = 'none';
            document.getElementById('patient-results').style.display = 'none';
            appState.selectedPatient = null;
        }

        /**
         * Seleccionar paciente
         */
        function selectPatient(id, firstName, lastName, documentNumber) {
            if (window.debugMode) console.log('selectPatient called with:', {id, firstName, lastName, documentNumber});
            
            // Validar y llenar campo oculto
            const hiddenField = document.getElementById('selected-patient-id');
            if (hiddenField) {
                hiddenField.value = id;
                if (window.debugMode) console.log('Hidden field value set to:', hiddenField.value);
            } else {
                console.warn('⚠️ Hidden field selected-patient-id not found');
            }
            
            // Validar y llenar campo de búsqueda
            const patientSearchField = document.getElementById('patient-search');
            if (patientSearchField) {
                patientSearchField.value = `${firstName} ${lastName}`;
                if (window.debugMode) console.log('Patient search field updated');
            } else {
                console.warn('⚠️ Patient search field not found');
            }
            
            // Validar y actualizar texto de información
            const patientInfoText = document.getElementById('patient-info-text');
            if (patientInfoText) {
                patientInfoText.textContent = `${firstName} ${lastName} - ${documentNumber}`;
                if (window.debugMode) console.log('Patient info text updated');
            } else {
                console.warn('⚠️ Patient info text element not found');
            }
            
            // Mostrar información del paciente seleccionado
            const selectedPatientInfo = document.getElementById('selected-patient-info');
            if (selectedPatientInfo) {
                selectedPatientInfo.style.display = 'block';
                if (window.debugMode) console.log('Selected patient info shown');
            } else {
                console.warn('⚠️ Selected patient info element not found');
            }
            
            // Ocultar resultados de búsqueda
            const patientResults = document.getElementById('patient-results');
            if (patientResults) {
                patientResults.style.display = 'none';
                if (window.debugMode) console.log('Patient results hidden');
            }
            
            // También llenar campo search-input si existe
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = `${firstName} ${lastName}`;
                if (window.debugMode) console.log('Search input field updated');
            }
            
            // Actualizar estado de la aplicación
            appState.selectedPatient = { id, firstName, lastName, documentNumber };
            if (window.debugMode) console.log('appState.selectedPatient updated:', appState.selectedPatient);
            
            // 🆕 NUEVO: Limpiar archivos cuando se selecciona un paciente para nueva consulta
            clearSelectedFiles();
            
            // 🆕 IMPORTANTE: Recargar consultas para mostrar solo las del paciente seleccionado
            if (typeof loadConsultas === 'function') {
                setTimeout(function() {
                    console.log('🔄 Recargando consultas del paciente seleccionado...');
                    loadConsultas(1);
                }, 200);
            }
            
            // 🆕 NUEVO: También actualizar elementos del estilo anterior si existen
            const selectedPatientInfoLegacy = document.getElementById('selectedPatientInfo');
            const selectedPatientName = document.getElementById('selectedPatientName');
            const selectedPatientDetails = document.getElementById('selectedPatientDetails');
            
            if (selectedPatientInfoLegacy && selectedPatientName && selectedPatientDetails) {
                selectedPatientName.textContent = `${firstName} ${lastName}`;
                selectedPatientDetails.textContent = `CI: ${documentNumber}`;
                selectedPatientInfoLegacy.style.display = 'block';
                if (window.debugMode) console.log('Legacy patient info elements updated');
            }
        }

        /**
         * Manejar cambio de tipo de formulario
         */
        /**
         * Manejar cambio de tipo de formulario
         */
        function handleFormTypeChange() {
            // Usar el tipo de formulario actual del estado de la aplicación
            const tipoFormulario = appState.currentFormType;
            const anteojosSection = document.getElementById('anteojos-section');
            
            console.log('🔄 Cambio de tipo de formulario:', tipoFormulario);
            
            // Mostrar/ocultar secciones específicas
            if (tipoFormulario === 'anteojos') {
                anteojosSection.style.display = 'block';
                anteojosSection.classList.add('active'); // 🔧 Agregar clase active
                console.log('✅ Sección de anteojos activada');
            } else {
                anteojosSection.style.display = 'none';
                anteojosSection.classList.remove('active'); // 🔧 Remover clase active
                console.log('❌ Sección de anteojos desactivada');
            }
            
            // Cargar motivos comunes y preformatos para el nuevo tipo
            loadMotivosComunes(tipoFormulario);
            loadPreformatos(tipoFormulario);
            
            // Reinicializar componentes después del cambio
            setTimeout(function() {
                if (typeof reinitializeComponents === 'function') {
                    reinitializeComponents();
                }
            }, 100);
        }

        /**
         * Cargar motivos comunes por tipo de formulario
         */
        async function loadMotivosComunes(tipoFormulario = 'general') {
            try {
                const result = await callAPI('get_motivos_comunes', {
                    tipo_formulario: tipoFormulario
                });
                
                const select = document.getElementById('motivos_comunes');
                select.innerHTML = '<option value="">Seleccionar motivo común...</option>';
                
                if (result.data && result.data.length > 0) {
                    result.data.forEach(motivo => {
                        const option = document.createElement('option');
                        option.value = motivo.id_motivo;
                        option.textContent = motivo.nombre;
                        option.dataset.descripcion = motivo.descripcion || '';
                        select.appendChild(option);
                    });
                }
                
                // Solo logear en debug mode
                if (window.debugMode) {
                    console.log(`Motivos comunes cargados para ${tipoFormulario}:`, result.data.length);
                }
            } catch (error) {
                console.error('Error cargando motivos comunes:', error);
            }
        }

        /**
         * Cargar preformatos por tipo de formulario
         */
        async function loadPreformatos(tipoFormulario = 'general') {
            try {
                // Cargar preformatos de consulta
                const consultaResult = await callAPI('get_preformatos', {
                    tipo_formulario: tipoFormulario,
                    tipo: 'consulta'
                });
                
                const consultaSelect = document.getElementById('preformatos_consulta');
                consultaSelect.innerHTML = '<option value="">Seleccionar preformato para consulta...</option>';
                
                if (consultaResult.data && consultaResult.data.length > 0) {
                    consultaResult.data.forEach(preformato => {
                        const option = document.createElement('option');
                        option.value = preformato.id_preformato;
                        option.textContent = preformato.nombre;
                        option.dataset.contenido = preformato.contenido || '';
                        consultaSelect.appendChild(option);
                    });
                }
                
                // Cargar preformatos de receta
                const recetaResult = await callAPI('get_preformatos', {
                    tipo_formulario: tipoFormulario,
                    tipo: 'receta'
                });
                
                const recetaSelect = document.getElementById('preformatos_receta');
                recetaSelect.innerHTML = '<option value="">Seleccionar preformato para receta...</option>';
                
                if (recetaResult.data && recetaResult.data.length > 0) {
                    recetaResult.data.forEach(preformato => {
                        const option = document.createElement('option');
                        option.value = preformato.id_preformato;
                        option.textContent = preformato.nombre;
                        option.dataset.contenido = preformato.contenido || '';
                        recetaSelect.appendChild(option);
                    });
                }
                
                // Solo logear en debug mode
                if (window.debugMode) {
                    console.log(`Preformatos cargados para ${tipoFormulario}: Consulta=${consultaResult.data.length}, Receta=${recetaResult.data.length}`);
                }
            } catch (error) {
                console.error('Error cargando preformatos:', error);
            }
        }

        /**
         * Cargar valores de referencia para selects de anteojos
         */
        async function loadReferenciales() {
            try {
                // Cargar valores para esfera (tanto por ID como por clase)
                await loadReferencialValores('esfera', ['od_esf', 'oi_esf']);
                await loadReferencialSelectsByClass('esfera');
                
                // Cargar valores para cilindro  
                await loadReferencialValores('cilindro', ['od_cil', 'oi_cil']);
                await loadReferencialSelectsByClass('cilindro');
                
                // Cargar valores para adición
                await loadReferencialValores('adicion', ['od_add', 'oi_add']);
                await loadReferencialSelectsByClass('adicion');
                
                console.log('✅ Todos los referenciales de anteojos cargados exitosamente');
            } catch (error) {
                console.error('❌ Error cargando referenciales:', error);
            }
        }

        /**
         * Cargar valores específicos de un tipo de referencial por clase (para modales de edición)
         */
        async function loadReferencialSelectsByClass(tipo) {
            try {
                const result = await callAPI('get_referenciales', { tipo: tipo });
                
                if (result.data && result.data.length > 0) {
                    // Buscar todos los selects con clase referencial-select y data-referencial del tipo especificado
                    const selects = document.querySelectorAll(`.referencial-select[data-referencial="${tipo}"]`);
                    
                    selects.forEach(selectElement => {
                        // Obtener el valor actual si existe
                        const currentValue = selectElement.getAttribute('data-current-value') || '';
                        
                        // Limpiar opciones existentes
                        selectElement.innerHTML = `<option value="">Seleccionar ${tipo}...</option>`;
                        
                        // Agregar nuevas opciones
                        result.data.forEach(valor => {
                            const option = document.createElement('option');
                            option.value = valor.valor;
                            option.textContent = valor.etiqueta;
                            if (valor.valor_numerico !== null) {
                                option.dataset.numerico = valor.valor_numerico;
                            }
                            
                            // Seleccionar si coincide con el valor actual
                            if (valor.valor === currentValue) {
                                option.selected = true;
                            }
                            
                            selectElement.appendChild(option);
                        });
                        
                        console.log(`✅ ${tipo} cargado en select por clase: ${result.data.length} valores, valor actual: "${currentValue}"`);
                    });
                } else {
                    console.warn(`⚠️ No hay valores disponibles para ${tipo}`);
                }
            } catch (error) {
                console.error(`❌ Error cargando ${tipo} por clase:`, error);
                throw error;
            }
        }

        /**
         * Cargar valores específicos de un tipo de referencial
         */
        async function loadReferencialValores(tipo, selectIds) {
            try {
                const result = await callAPI('get_referenciales', { tipo: tipo });
                
                if (result.data && result.data.length > 0) {
                    selectIds.forEach(selectId => {
                        const selectElement = document.getElementById(selectId);
                        if (selectElement) {
                            // Limpiar opciones existentes
                            selectElement.innerHTML = `<option value="">Seleccionar ${tipo}...</option>`;
                            
                            // Agregar nuevas opciones
                            result.data.forEach(valor => {
                                const option = document.createElement('option');
                                option.value = valor.valor;
                                option.textContent = valor.etiqueta;
                                if (valor.valor_numerico !== null) {
                                    option.dataset.numerico = valor.valor_numerico;
                                }
                                selectElement.appendChild(option);
                            });
                            
                            console.log(`✅ ${tipo} cargado en select ${selectId}: ${result.data.length} valores`);
                        } else {
                            console.warn(`⚠️ Select ${selectId} no encontrado`);
                        }
                    });
                } else {
                    console.warn(`⚠️ No hay valores disponibles para ${tipo}`);
                }
            } catch (error) {
                console.error(`❌ Error cargando ${tipo}:`, error);
                throw error;
            }
        }

        /**
         * Actualizar estadísticas del paciente
         */
        function actualizarEstadisticasPaciente(consultasData) {
            if (window.debugMode) console.log('📊 Actualizando estadísticas del paciente...', consultasData);
            
            const totalElement = document.getElementById('totalConsultas');
            const ultimaElement = document.getElementById('ultimaConsulta');
            const proximaElement = document.getElementById('proximaConsulta');
            
            if (!consultasData || !Array.isArray(consultasData)) {
                if (window.debugMode) console.log('❌ Datos de consultas inválidos');
                if (totalElement) totalElement.textContent = '0';
                if (ultimaElement) ultimaElement.textContent = '--';
                if (proximaElement) proximaElement.textContent = '--';
                return;
            }
            
            // Total de consultas
            const total = consultasData.length;
            if (totalElement) totalElement.textContent = total;
            
            // Última consulta (fecha más reciente)
            let ultimaFecha = '--';
            if (consultasData.length > 0) {
                // Ordenar por fecha_registro descendente para obtener la más reciente
                const consultasOrdenadas = consultasData.sort((a, b) => 
                    new Date(b.fecha_registro) - new Date(a.fecha_registro)
                );
                
                const fechaMasReciente = consultasOrdenadas[0].fecha_registro;
                if (fechaMasReciente) {
                    try {
                        const fecha = new Date(fechaMasReciente);
                        ultimaFecha = fecha.toLocaleDateString('es-ES');
                    } catch (error) {
                        console.error('Error formateando fecha:', error);
                        ultimaFecha = fechaMasReciente.split(' ')[0]; // Fallback
                    }
                }
            }
            if (ultimaElement) ultimaElement.textContent = ultimaFecha;
            
            // Próxima consulta (fecha más cercana en el futuro)
            let proximaFecha = '--';
            const fechaActual = new Date();
            const consultasFuturas = consultasData.filter(consulta => {
                if (!consulta.proximaconsulta) return false;
                try {
                    const fechaProxima = new Date(consulta.proximaconsulta);
                    return fechaProxima > fechaActual;
                } catch (error) {
                    return false;
                }
            });
            
            if (consultasFuturas.length > 0) {
                // Ordenar por fecha ascendente para obtener la más próxima
                const consultasOrdenadas = consultasFuturas.sort((a, b) => 
                    new Date(a.proximaconsulta) - new Date(b.proximaconsulta)
                );
                
                const fechaProximaConsulta = consultasOrdenadas[0].proximaconsulta;
                try {
                    const fecha = new Date(fechaProximaConsulta);
                    proximaFecha = fecha.toLocaleDateString('es-ES');
                } catch (error) {
                    console.error('Error formateando próxima fecha:', error);
                    proximaFecha = fechaProximaConsulta;
                }
            }
            if (proximaElement) proximaElement.textContent = proximaFecha;
            
            if (window.debugMode) {
                console.log('📊 Estadísticas actualizadas:', {
                    total: total,
                    ultima: ultimaFecha,
                    proxima: proximaFecha
                });
            }
        }

        /**
         * Aplicar motivo común seleccionado
         */
        function aplicarMotivoComun() {
            const select = document.getElementById('motivos_comunes');
            const textarea = document.getElementById('motivo');
            
            if (window.debugMode) console.log('aplicarMotivoComun called');
            if (window.debugMode) console.log('Select value:', select.value);
            console.log('Textarea element:', textarea);
            
            if (select.value && textarea) {
                const selectedOption = select.options[select.selectedIndex];
                const motivoTexto = selectedOption.textContent;
                const descripcion = selectedOption.dataset.descripcion;
                
                if (window.debugMode) {
                    console.log('Selected option:', selectedOption);
                    console.log('Descripcion:', descripcion);
                    console.log('MotivoTexto:', motivoTexto);
                }
                
                // Verificar si el textarea tiene Summernote inicializado
                const isSummernote = $(textarea).hasClass('summernote') && $(textarea).next('.note-editor').length > 0;
                console.log('Is Summernote:', isSummernote);
                console.log('Has summernote class:', $(textarea).hasClass('summernote'));
                console.log('Has note-editor sibling:', $(textarea).next('.note-editor').length > 0);
                
                const content = descripcion || motivoTexto;
                
                if (isSummernote) {
                    try {
                        // Obtener contenido actual de Summernote
                        const currentContent = $(textarea).summernote('code');
                        console.log('Current Summernote content:', currentContent);
                        
                        // Si está vacío o solo tiene <p><br></p>, usar el nuevo contenido
                        if (!currentContent || currentContent === '<p><br></p>' || currentContent.trim() === '') {
                            $(textarea).summernote('code', '<p>' + content + '</p>');
                            console.log('Set new content in Summernote');
                        } else {
                            // Agregar al contenido existente
                            $(textarea).summernote('code', currentContent + '<p>' + content + '</p>');
                            console.log('Added to existing Summernote content');
                        }
                    } catch (error) {
                        console.error('Error working with Summernote:', error);
                        // Fallback to regular textarea
                        if (!textarea.value.trim()) {
                            textarea.value = content;
                        } else {
                            textarea.value += (textarea.value.trim() ? '\n' : '') + content;
                        }
                    }
                } else {
                    // Comportamiento normal para textarea sin Summernote
                    console.log('Using regular textarea');
                    if (!textarea.value.trim()) {
                        textarea.value = content;
                    } else {
                        textarea.value += (textarea.value.trim() ? '\n' : '') + content;
                    }
                }
                
                // Limpiar selección
                select.value = '';
                if ($(select).hasClass('select2bs4')) {
                    $(select).val(null).trigger('change');
                }
                if (window.debugMode) console.log('Selection cleared');
            } else {
                if (window.debugMode) console.log('No value selected or textarea not found');
            }
        }

        /**
         * Aplicar preformato seleccionado
         */
        function aplicarPreformato(tipo) {
            const selectId = `preformatos_${tipo}`;
            const textareaId = tipo === 'consulta' ? 'consulta' : 'receta';
            
            const select = document.getElementById(selectId);
            const textarea = document.getElementById(textareaId);
            
            console.log('aplicarPreformato called for tipo:', tipo);
            console.log('Select ID:', selectId, 'Select element:', select);
            console.log('Textarea ID:', textareaId, 'Textarea element:', textarea);
            
            if (select && select.value && textarea) {
                const selectedOption = select.options[select.selectedIndex];
                const contenido = selectedOption.dataset.contenido;
                
                if (window.debugMode) {
                    console.log('Selected option:', selectedOption);
                    console.log('Contenido:', contenido);
                }
                
                if (contenido) {
                    // Verificar si el textarea tiene Summernote inicializado
                    const isSummernote = $(textarea).hasClass('summernote') && $(textarea).next('.note-editor').length > 0;
                    console.log('Is Summernote:', isSummernote);
                    
                    if (isSummernote) {
                        try {
                            // Obtener contenido actual de Summernote
                            const currentContent = $(textarea).summernote('code');
                            console.log('Current Summernote content:', currentContent);
                            
                            // Si está vacío o solo tiene <p><br></p>, usar el nuevo contenido
                            if (!currentContent || currentContent === '<p><br></p>' || currentContent.trim() === '') {
                                $(textarea).summernote('code', '<p>' + contenido.replace(/\n/g, '</p><p>') + '</p>');
                                console.log('Set new preformato content in Summernote');
                            } else {
                                // Agregar al contenido existente
                                $(textarea).summernote('code', currentContent + '<p><br></p><p>' + contenido.replace(/\n/g, '</p><p>') + '</p>');
                                console.log('Added preformato to existing Summernote content');
                            }
                        } catch (error) {
                            console.error('Error working with Summernote preformato:', error);
                            // Fallback to regular textarea
                            if (!textarea.value.trim()) {
                                textarea.value = contenido;
                            } else {
                                textarea.value += (textarea.value.trim() ? '\n\n' : '') + contenido;
                            }
                        }
                    } else {
                        // Comportamiento normal para textarea sin Summernote
                        console.log('Using regular textarea for preformato');
                        if (!textarea.value.trim()) {
                            textarea.value = contenido;
                        } else {
                            textarea.value += (textarea.value.trim() ? '\n\n' : '') + contenido;
                        }
                    }
                }
                
                // Limpiar selección
                select.value = '';
                if ($(select).hasClass('select2bs4')) {
                    $(select).val(null).trigger('change');
                }
                if (window.debugMode) console.log('Preformato selection cleared');
            } else {
                if (window.debugMode) {
                    console.log('No value selected, or elements not found');
                    console.log('Select exists:', !!select, 'has value:', select?.value);
                    console.log('Textarea exists:', !!textarea);
                }
            }
        }

        /**
         * Crear nueva consulta
         */
        async function createConsulta(event) {
            console.log('🔥 createConsulta called, event:', event);
            console.log('🔥 Event type:', event.type);
            console.log('🔥 Event target:', event.target);
            console.log('🔥 Current tab:', appState.currentTab);
            console.log('🔥 Loading patient consultas:', appState.loadingPatientConsultas);
            
            event.preventDefault();
            
            // 🛡️ PROTECCIÓN: No procesar si no estamos en el tab correcto o estamos cargando datos
            if (appState.currentTab !== 'create') {
                console.log('⚠️ Formulario enviado fuera del tab create - ignorando');
                return;
            }
            
            if (appState.loadingPatientConsultas) {
                console.log('⚠️ Formulario enviado mientras se cargan consultas de paciente - ignorando');
                return;
            }
            
            if (appState.isResetting) {
                console.log('⚠️ Formulario enviado durante reset - ignorando');
                return;
            }
            
            const form = document.getElementById('create-form');
            const formData = new FormData(form);
            let data = Object.fromEntries(formData.entries());
            
            // Extraer contenido de editores Summernote si están inicializados
            $('textarea.summernote', form).each(function() {
                const textarea = $(this);
                if (textarea.next('.note-editor').length > 0) {
                    const fieldName = textarea.attr('name');
                    data[fieldName] = textarea.summernote('code');
                }
            });
            
            console.log('Form data before processing:', data);
            console.log('🔍 Valor de id_persona:', data.id_persona);
            console.log('🔍 appState.selectedPatient:', appState.selectedPatient);
            
            // Validación personalizada para campos Summernote required
            const motivoContent = data.txtmotivo || '';
            const cleanMotivo = motivoContent.replace(/<[^>]*>/g, '').trim(); // Remover HTML tags y espacios
            
            // Verificar si el paciente está seleccionado primero (si no hay paciente, el formulario no debería enviarse)
            if (!data.id_persona || data.id_persona === '' || data.id_persona === '0') {
                console.log('❌ No hay paciente seleccionado - formulario no debería enviarse');
                console.log('💡 INSTRUCCIONES: Busca "visconte" en el campo rojo y selecciona el paciente de la lista');
                showError('Debe seleccionar un paciente del autocompletado. Busque "visconte" y seleccione de la lista.');
                return;
            }
            
            if (!cleanMotivo || cleanMotivo === '' || motivoContent === '<p><br></p>' || motivoContent === '<p></p>') {
                showError('El campo motivo de consulta es requerido');
                return;
            }
            
            console.log('Motivo validation passed:', cleanMotivo);
            
            // CORRECCIÓN: Convertir fechas vacías a null para PostgreSQL
            if (data.proximaconsulta === '') {
                data.proximaconsulta = null;
            }
            
            // CORRECCIÓN: Asegurar que campos importantes existan aunque estén vacíos
            if (!('proximaconsulta' in data)) {
                data.proximaconsulta = '';
            }
            if (!('txtnota' in data)) {
                data.txtnota = '';
            }
            
            // CORRECCIÓN CRÍTICA: Agregar tipo_formulario del estado actual
            data.tipo_formulario = appState.currentFormType || 'general';
            console.log('🔧 Tipo de formulario establecido:', data.tipo_formulario);
            
            // CORRECCIÓN: Procesar campos booleanos
            data = processBooleanFields(data);
            
            console.log('Form data after processing:', data);
            console.log('Patient ID value:', data.id_persona);
            
            const btn = document.getElementById('create-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando...';
            btn.disabled = true;
            
            try {
                // Preparar datos relacionados según el tipo de formulario
                let related = null;
                if (data.tipo_formulario === 'anteojos') {
                    related = {
                        anteojos: {
                            esfera_od: data.od_esf,
                            cilindro_od: data.od_cil,
                            eje_od: data.od_eje,
                            dnp_od: data.od_dnp,
                            add_od: data.od_add,
                            altura_od: data.od_altura,
                            nota_od: data.od_nota,
                            esfera_oi: data.oi_esf,
                            cilindro_oi: data.oi_cil,
                            eje_oi: data.oi_eje,
                            dnp_oi: data.oi_dnp,
                            add_oi: data.oi_add,
                            altura_oi: data.oi_altura,
                            nota_oi: data.oi_nota,
                            dist_interpupilar: data.dist_interpupilar,
                            notas: data.anteojos_notas
                        }
                    };
                } else if (data.tipo_formulario === 'informe_imagen') {
                    related = {
                        informe_imagen: {
                            equipo_medico: data.equipo_medico,
                            descripcion_od: data.descripcion_od,
                            descripcion_oi: data.descripcion_oi,
                            emails_compartir: data.emails_compartir,
                            compartir_activo: data.compartir_activo
                        }
                    };
                } else if (data.tipo_formulario === 'estudios') {
                    related = {
                        estudios: {
                            equipo_medico: data.equipo_medico,
                            otro_equipo: data.otro_equipo,
                            descripcion_estudio: data.descripcion_estudio,
                            observaciones: data.observaciones,
                            resultados: data.resultados,
                            fecha_estudio: data.fecha_estudio || null
                        }
                    };
                }
                
                const result = await callAPI('create', {
                    table: 'consultas',
                    data: data,
                    related: related
                });
                
                showSuccess('Consulta creada exitosamente');
                
                // 🆕 NUEVO: Subir archivos si existen
                const archivosInput = document.getElementById('archivos_consulta');
                if (archivosInput && archivosInput.files && archivosInput.files.length > 0) {
                    console.log('📁 Subiendo archivos para consulta ID:', result.data.id_consulta);
                    try {
                        const uploadedFiles = await uploadArchivos(result.data.id_consulta, Array.from(archivosInput.files));
                        if (uploadedFiles.length > 0) {
                            console.log('✅ Archivos subidos exitosamente:', uploadedFiles.length);
                        }
                    } catch (uploadError) {
                        console.error('❌ Error subiendo archivos:', uploadError);
                        showError('Consulta creada, pero hubo problemas subiendo algunos archivos');
                    }
                }
                
                resetForm();
                showTab('list');
                
                // 🆕 NUEVO: Actualizar estadísticas del paciente después de crear
                await updatePatientStats();
                
            } catch (error) {
                // Error ya manejado en callAPI
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        /**
         * Editar consulta
         */
        async function editConsulta(id) {
            console.log('🔧 editConsulta called with ID:', id);
            try {
                // CORRECCIÓN: Agregar timestamp para evitar cache
                const result = await callAPI('read', {
                    table: 'consultas',
                    id: id,
                    with: ['anteojos', 'informe_imagen', 'estudios'],
                    _t: Date.now() // Anti-cache timestamp
                });
                
                console.log('📋 Datos recibidos para edición:', result.data);
                appState.editingRecord = result.data;
                
                // Generar formulario de edición dinámicamente
                const formHtml = createEditForm(result.data);
                const container = document.getElementById('edit-form-container');
                if (container) {
                    container.innerHTML = formHtml;
                    console.log('✅ Formulario HTML generado e insertado');
                } else {
                    console.error('❌ No se encontró edit-form-container');
                    return;
                }
                
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
                console.log('✅ Modal mostrado');
                
                // 🔧 NUEVO: Activar todas las form-section del modal de edición
                setTimeout(function() {
                    console.log('🔧 Activando secciones del formulario de edición...');
                    const editFormSections = container.querySelectorAll('.form-section');
                    editFormSections.forEach((section, index) => {
                        section.classList.add('active');
                        console.log(`  - Edit Section ${index} activada`);
                    });
                    
                    // Reinicializar componentes
                    if (typeof reinitializeComponents === 'function') {
                        console.log('🔄 Reinicializando componentes...');
                        reinitializeComponents();
                    }
                    
                    // NUEVO: Cargar referencias si hay selects de referenciales en el modal de edición
                    const editReferencialSelects = container.querySelectorAll('.referencial-select');
                    if (editReferencialSelects.length > 0) {
                        console.log('🔧 Cargando referencias para modal de edición...');
                        loadReferenciales();
                    }
                    
                    // 🆕 NUEVO: Cargar archivos existentes de la consulta
                    console.log('📁 Cargando archivos existentes para consulta ID:', result.data.id_consulta);
                    loadArchivosExistentes(result.data.id_consulta);
                    
                    // Configurar event listener para nuevos archivos en modal de edición
                    const editArchivosInput = document.getElementById('edit_archivos_consulta');
                    if (editArchivosInput) {
                        editArchivosInput.addEventListener('change', function(e) {
                            previewEditArchivos(e.target);
                        });
                    }
                    
                    console.log('✅ Formulario de edición completamente cargado');
                }, 200);
                
            } catch (error) {
                console.error('❌ Error en editConsulta:', error);
                // Error ya manejado en callAPI
            }
        }

        /**
         * Crear formulario de edición dinámico
         */
        function createEditForm(data) {
            return `
                <form id="edit-form">
                    <input type="hidden" name="id_consulta" value="${data.id_consulta}">
                    <input type="hidden" name="id_persona" value="${data.id_persona || ''}">
                    
                    <div class="form-section">
                        <h6 class="form-section-title">Información del Paciente</h6>
                        <div class="form-section-body">
                            <div class="alert alert-info">
                                <strong>${data.first_name || ''} ${data.last_name || ''}</strong><br>
                                Documento: ${data.document_number || 'N/A'}
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h6 class="form-section-title">Datos de la Consulta</h6>
                        <div class="form-section-body">
                            <div class="field-group">
                                <div class="large-textarea-container">
                                    <label>Motivo de Consulta *</label>
                                    <textarea class="form-control summernote" name="txtmotivo">${data.txtmotivo || ''}</textarea>
                                </div>
                                <div class="form-floating">
                                    <select class="form-select select2bs4" name="tipo_formulario" onchange="handleEditFormTypeChange()">
                                        <option value="general" ${data.tipo_formulario === 'general' ? 'selected' : ''}>General</option>
                                        <option value="anteojos" ${data.tipo_formulario === 'anteojos' ? 'selected' : ''}>Anteojos</option>
                                        <option value="informe_imagen" ${data.tipo_formulario === 'informe_imagen' ? 'selected' : ''}>Informe con Imagen</option>
                                        <option value="estudios" ${data.tipo_formulario === 'estudios' ? 'selected' : ''}>Estudios</option>
                                    </select>
                                    <label>Tipo de Formulario</label>
                                </div>
                            </div>
                            
                            <!-- Sección Visión y Tensión - Solo para formularios generales -->
                            ${data.tipo_formulario === 'general' ? `
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="visionod" value="${data.visionod || ''}">
                                        <label>Visión OD</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="visionoi" value="${data.visionoi || ''}">
                                        <label>Visión OI</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="tensionod" value="${data.tensionod || ''}">
                                        <label>Tensión OD</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="tensionoi" value="${data.tensionoi || ''}">
                                        <label>Tensión OI</label>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            
                            <div class="field-group">
                                <div class="large-textarea-container">
                                    <label>Consulta</label>
                                    <textarea class="form-control summernote" name="consulta_textarea">${data.consulta_textarea || ''}</textarea>
                                </div>
                                <div class="large-textarea-container">
                                    <label>Receta</label>
                                    <textarea class="form-control summernote" name="receta_textarea">${data.receta_textarea || ''}</textarea>
                                </div>
                            </div>
                            
                            <div class="field-group">
                                <div class="large-textarea-container">
                                    <label>Notas</label>
                                    <textarea class="form-control summernote" name="txtnota">${data.txtnota || ''}</textarea>
                                </div>
                                <div class="form-floating">
                                    <input type="date" class="form-control" name="proximaconsulta" value="${data.proximaconsulta || ''}">
                                    <label>Próxima Consulta</label>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" name="whatsapptxt" value="${data.whatsapptxt || ''}">
                                        <label>Mensaje WhatsApp</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" name="email" value="${data.email || ''}">
                                        <label>Email</label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sección de Archivos Adjuntos en Edición -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6>Archivos Adjuntos</h6>
                                    <div id="edit-archivos-existentes" class="mb-3">
                                        <!-- Archivos existentes se cargarán aquí -->
                                    </div>
                                    <label for="edit_archivos_consulta" class="form-label">Agregar nuevos archivos</label>
                                    <input type="file" class="form-control" id="edit_archivos_consulta" name="archivos_consulta[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.rtf,.jpg,.jpeg,.png,.gif,.bmp,.tiff,.webp,.svg">
                                    <div class="form-text">Máximo 10 archivos. Formatos permitidos: PDF, Word, Excel, Imágenes (JPG, PNG)</div>
                                    <div id="edit-archivos-preview" class="mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${data.tipo_formulario === 'anteojos' && data.anteojos ? createAnteojosEditSection(data.anteojos) : ''}
                    ${data.tipo_formulario === 'informe_imagen' && data.informe_imagen ? createInformeImagenEditSection(data.informe_imagen) : ''}
                    ${data.tipo_formulario === 'estudios' && data.estudios ? createEstudiosEditSection(data.estudios) : ''}
                </form>
            `;
        }

        /**
         * Crear sección de edición de anteojos
         */
        function createAnteojosEditSection(anteojos) {
            return `
                <div id="edit-anteojos-section" class="form-section">
                    <h6 class="form-section-title">Datos de Anteojos</h6>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Ojo Derecho (OD)</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="od_esf" class="form-label">Esfera</label>
                                        <select class="form-select referencial-select" 
                                                name="od_esf" 
                                                data-referencial="esfera"
                                                data-current-value="${anteojos.esfera_od || ''}">
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="od_cil" class="form-label">Cilindro</label>
                                        <select class="form-select referencial-select" 
                                                name="od_cil" 
                                                data-referencial="cilindro"
                                                data-current-value="${anteojos.cilindro_od || ''}">
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" name="od_eje" min="0" max="180" value="${anteojos.eje_od || ''}">
                                            <label>Eje</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" name="od_dnp" value="${anteojos.dnp_od || ''}">
                                            <label>DNP</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="od_add" class="form-label">Adición</label>
                                        <select class="form-select referencial-select" 
                                                name="od_add" 
                                                data-referencial="adicion"
                                                data-current-value="${anteojos.add_od || ''}">
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" name="od_altura" value="${anteojos.altura_od || ''}">
                                            <label>Altura</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating">
                                    <textarea class="form-control" name="od_nota">${anteojos.nota_od || ''}</textarea>
                                    <label>Notas OD</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="text-success">Ojo Izquierdo (OI)</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="oi_esf" class="form-label">Esfera</label>
                                        <select class="form-select referencial-select" 
                                                name="oi_esf" 
                                                data-referencial="esfera"
                                                data-current-value="${anteojos.esfera_oi || ''}">
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="oi_cil" class="form-label">Cilindro</label>
                                        <select class="form-select referencial-select" 
                                                name="oi_cil" 
                                                data-referencial="cilindro"
                                                data-current-value="${anteojos.cilindro_oi || ''}">
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" name="oi_eje" min="0" max="180" value="${anteojos.eje_oi || ''}">
                                            <label>Eje</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" name="oi_dnp" value="${anteojos.dnp_oi || ''}">
                                            <label>DNP</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="oi_add" class="form-label">Adición</label>
                                        <select class="form-select referencial-select" 
                                                name="oi_add" 
                                                data-referencial="adicion"
                                                data-current-value="${anteojos.add_oi || ''}">
                                            <option value="">Seleccionar...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <input type="number" class="form-control" name="oi_altura" value="${anteojos.altura_oi || ''}">
                                            <label>Altura</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-floating">
                                    <textarea class="form-control" name="oi_nota">${anteojos.nota_oi || ''}</textarea>
                                    <label>Notas OI</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="dist_interpupilar" value="${anteojos.dist_interpupilar || ''}">
                                    <label>Distancia Interpupilar</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <textarea class="form-control" name="anteojos_notas">${anteojos.notas || ''}</textarea>
                                    <label>Notas Generales Anteojos</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Crear sección de edición de informe con imagen
         */
        function createInformeImagenEditSection(informe_imagen) {
            return `
                <div id="edit-informe-imagen-section" class="form-section">
                    <h6 class="form-section-title">Datos de Informe con Imagen</h6>
                    <div class="form-section-body">
                        <div class="form-floating mb-3">
                            <select class="form-select select2bs4" name="equipo_medico">
                                <option value="">Seleccionar equipo</option>
                                <option value="Cirrus 700" ${informe_imagen.equipo_medico === 'Cirrus 700' ? 'selected' : ''}>Cirrus 700</option>
                                <option value="Stratus" ${informe_imagen.equipo_medico === 'Stratus' ? 'selected' : ''}>Stratus</option>
                                <option value="Pentacam" ${informe_imagen.equipo_medico === 'Pentacam' ? 'selected' : ''}>Pentacam</option>
                                <option value="Campo Visual" ${informe_imagen.equipo_medico === 'Campo Visual' ? 'selected' : ''}>Campo Visual</option>
                                <option value="Otro" ${informe_imagen.equipo_medico === 'Otro' ? 'selected' : ''}>Otro</option>
                            </select>
                            <label>Equipo Médico</label>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <textarea class="form-control" name="descripcion_od" style="height: 150px;">${informe_imagen.descripcion_od || ''}</textarea>
                                    <label>Descripción Ojo Derecho (OD)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <textarea class="form-control" name="descripcion_oi" style="height: 150px;">${informe_imagen.descripcion_oi || ''}</textarea>
                                    <label>Descripción Ojo Izquierdo (OI)</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="emails_compartir" value="${informe_imagen.emails_compartir || ''}">
                                    <label>Emails para Compartir</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="compartir_activo" ${informe_imagen.compartir_activo ? 'checked' : ''}>
                                    <label class="form-check-label">Compartir Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Crear sección de edición de estudios
         */
        function createEstudiosEditSection(estudios) {
            return `
                <div id="edit-estudios-section" class="form-section">
                    <h6 class="form-section-title">Datos de Estudios Médicos</h6>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select select2bs4" name="equipo_medico">
                                        <option value="">Seleccionar equipo</option>
                                        <option value="Cirrus 700" ${estudios.equipo_medico === 'Cirrus 700' ? 'selected' : ''}>Cirrus 700</option>
                                        <option value="Stratus" ${estudios.equipo_medico === 'Stratus' ? 'selected' : ''}>Stratus</option>
                                        <option value="Pentacam" ${estudios.equipo_medico === 'Pentacam' ? 'selected' : ''}>Pentacam</option>
                                        <option value="Campo Visual" ${estudios.equipo_medico === 'Campo Visual' ? 'selected' : ''}>Campo Visual</option>
                                        <option value="Otro" ${estudios.equipo_medico === 'Otro' ? 'selected' : ''}>Otro</option>
                                    </select>
                                    <label>Equipo Médico</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="otro_equipo" value="${estudios.otro_equipo || ''}">
                                    <label>Otro Equipo</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="descripcion_estudio" style="height: 120px;">${estudios.descripcion_estudio || ''}</textarea>
                            <label>Descripción del Estudio</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="observaciones" style="height: 100px;">${estudios.observaciones || ''}</textarea>
                            <label>Observaciones</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="resultados" style="height: 120px;">${estudios.resultados || ''}</textarea>
                            <label>Resultados</label>
                        </div>
                        
                        <div class="form-floating">
                            <input type="date" class="form-control" name="fecha_estudio" value="${estudios.fecha_estudio || ''}">
                            <label>Fecha del Estudio</label>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Procesar datos booleanos para evitar problemas con PostgreSQL
         */
        function processBooleanFields(data) {
            // Convertir campos booleanos que pueden estar undefined/empty string
            const booleanFields = ['compartir_activo'];
            
            booleanFields.forEach(field => {
                if (data[field] === undefined || data[field] === '' || data[field] === null) {
                    data[field] = false;
                } else {
                    data[field] = !!data[field];
                }
            });
            
            return data;
        }

        /**
         * Guardar edición
         */
        async function saveEdit() {
            const form = document.getElementById('edit-form');
            const formData = new FormData(form);
            let data = Object.fromEntries(formData.entries());
            
            // Extraer contenido de editores Summernote si están inicializados
            $('textarea.summernote', form).each(function() {
                const textarea = $(this);
                if (textarea.next('.note-editor').length > 0) {
                    const fieldName = textarea.attr('name');
                    data[fieldName] = textarea.summernote('code');
                }
            });
            
            // CORRECCIÓN: Convertir fechas vacías a null para PostgreSQL
            if (data.proximaconsulta === '') {
                data.proximaconsulta = null;
            }
            
            // CORRECCIÓN: Procesar campos booleanos
            data = processBooleanFields(data);
            
            try {
                // Preparar datos relacionados según el tipo de formulario
                let related = null;
                if (data.tipo_formulario === 'anteojos') {
                    related = {
                        anteojos: {
                            esfera_od: data.od_esf,
                            cilindro_od: data.od_cil,
                            eje_od: data.od_eje,
                            dnp_od: data.od_dnp,
                            add_od: data.od_add,
                            altura_od: data.od_altura,
                            nota_od: data.od_nota,
                            esfera_oi: data.oi_esf,
                            cilindro_oi: data.oi_cil,
                            eje_oi: data.oi_eje,
                            dnp_oi: data.oi_dnp,
                            add_oi: data.oi_add,
                            altura_oi: data.oi_altura,
                            nota_oi: data.oi_nota,
                            dist_interpupilar: data.dist_interpupilar,
                            notas: data.anteojos_notas
                        }
                    };
                } else if (data.tipo_formulario === 'informe_imagen') {
                    related = {
                        informe_imagen: {
                            equipo_medico: data.equipo_medico,
                            descripcion_od: data.descripcion_od,
                            descripcion_oi: data.descripcion_oi,
                            emails_compartir: data.emails_compartir,
                            compartir_activo: data.compartir_activo
                        }
                    };
                } else if (data.tipo_formulario === 'estudios') {
                    related = {
                        estudios: {
                            equipo_medico: data.equipo_medico,
                            otro_equipo: data.otro_equipo,
                            descripcion_estudio: data.descripcion_estudio,
                            observaciones: data.observaciones,
                            resultados: data.resultados,
                            fecha_estudio: data.fecha_estudio || null
                        }
                    };
                }
                
                const result = await callAPI('update', {
                    table: 'consultas',
                    id: data.id_consulta,
                    data: data,
                    related: related
                });
                
                // Subir archivos nuevos si existen
                const archivosInput = document.getElementById('edit_archivos_consulta');
                if (archivosInput && archivosInput.files.length > 0) {
                    console.log('📎 Subiendo', archivosInput.files.length, 'archivos nuevos para consulta editada');
                    const uploadedFiles = await uploadArchivos(data.id_consulta, Array.from(archivosInput.files));
                    
                    if (uploadedFiles.length > 0) {
                        // Recargar archivos existentes para mostrar los nuevos
                        await loadArchivosExistentes(data.id_consulta);
                        // Limpiar el input de archivos
                        archivosInput.value = '';
                        const previewDiv = document.getElementById('edit-archivos-preview');
                        if (previewDiv) previewDiv.innerHTML = '';
                    }
                }
                
                showSuccess('Consulta actualizada exitosamente');
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                modal.hide();
                
                // CORRECCIÓN: Recargar datos actualizados en la lista
                loadConsultas(appState.currentPage);
                
                // 🆕 NUEVO: Actualizar estadísticas del paciente después de actualizar
                await updatePatientStats();
                
                // CORRECCIÓN: Limpiar cualquier cache del registro editado
                if (window.currentEditingRecord) {
                    delete window.currentEditingRecord;
                }
                
            } catch (error) {
                // Error ya manejado en callAPI
            }
        }

        /**
         * Eliminar consulta
         */
        async function deleteConsulta(id) {
            if (!confirm('¿Está seguro de que desea eliminar esta consulta?')) {
                return;
            }
            
            try {
                const result = await callAPI('delete', {
                    table: 'consultas',
                    id: id
                });
                
                showSuccess('Consulta eliminada exitosamente');
                loadConsultas(appState.currentPage);
                
                // 🆕 NUEVO: Actualizar estadísticas del paciente después de eliminar
                await updatePatientStats();
                
            } catch (error) {
                // Error ya manejado en callAPI
            }
        }

        /**
         * Ver detalles de consulta
         */
        function viewConsulta(id) {
            // Por ahora, redirigir a editar
            editConsulta(id);
        }

        /**
         * Realizar búsqueda
         */
        async function performSearch() {
            const searchInput = document.getElementById('search-input');
            const query = searchInput.value.trim();
            const resultsEl = document.getElementById('search-results');
            
            if (!query) {
                showError('Ingrese un término de búsqueda');
                return;
            }
            
            resultsEl.innerHTML = '<div class="loading"><div class="spinner"></div>Buscando...</div>';
            
            try {
                const result = await callAPI('search', {
                    table: 'consultas',
                    search: query,
                    limit: 50
                });
                
                if (result.data && result.data.length > 0) {
                    resultsEl.innerHTML = result.data.map(consulta => createConsultaCard(consulta)).join('');
                } else {
                    resultsEl.innerHTML = '<div class="text-center text-muted py-4">No se encontraron resultados</div>';
                }
                
            } catch (error) {
                resultsEl.innerHTML = '<div class="text-center text-danger py-4">Error en la búsqueda</div>';
            }
        }

        /**
         * Cargar pacientes
         */
        async function loadPatients() {
            const listEl = document.getElementById('patients-list');
            listEl.innerHTML = '<div class="loading"><div class="spinner"></div>Cargando pacientes...</div>';
            
            try {
                const result = await callAPI('list', {
                    table: 'rh_person',
                    limit: 50
                });
                
                if (result.data && result.data.length > 0) {
                    listEl.innerHTML = result.data.map(patient => `
                        <div class="record-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${patient.first_name} ${patient.last_name}</h6>
                                    <p class="mb-1"><strong>Documento:</strong> ${patient.document_number}</p>
                                    <p class="mb-0 text-muted">
                                        ${patient.phone_number ? `Tel: ${patient.phone_number}` : ''} 
                                        ${patient.email ? `| Email: ${patient.email}` : ''}
                                    </p>
                                </div>
                                <div class="record-actions">
                                    <button class="btn btn-sm btn-outline-primary btn-action" onclick="createConsultaForPatient(${patient.person_id}, '${patient.first_name}', '${patient.last_name}', '${patient.document_number}')" title="Nueva consulta">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    listEl.innerHTML = '<div class="text-center text-muted py-4">No se encontraron pacientes</div>';
                }
                
            } catch (error) {
                listEl.innerHTML = '<div class="text-center text-danger py-4">Error al cargar pacientes</div>';
            }
        }

        /**
         * Crear consulta para paciente específico
         */
        function createConsultaForPatient(id, firstName, lastName, documentNumber) {
            showTab('create');
            selectPatient(id, firstName, lastName, documentNumber);
        }

        /**
         * Cambiar tamaño de página
         */
        function changePageSize(size) {
            appState.pageSize = parseInt(size);
            appState.currentPage = 1;
            loadConsultas();
        }

        /**
         * Limpiar formulario
         */
        function resetForm() {
            console.log('🔄 Resetting form...');
            console.log('🔄 appState before reset:', JSON.stringify(appState));
            
            // 🛡️ Agregar flag temporal para prevenir envío durante reset
            appState.isResetting = true;
            console.log('🔄 isResetting set to true');
            
            // Primero resetear los campos Summernote
            $('.summernote').each(function() {
                if ($(this).next('.note-editor').length > 0) {
                    $(this).summernote('code', '');
                }
            });
            
            // Luego resetear el formulario
            const form = document.getElementById('create-form');
            if (form) {
                form.reset();
                console.log('✅ Form reset ejecutado');
            } else {
                console.error('❌ No se encontró create-form');
            }
            
            // 🆕 NUEVO: Limpiar archivos seleccionados y preview
            const archivosInput = document.getElementById('archivos_consulta');
            const archivosPreview = document.getElementById('archivos-preview');
            
            if (archivosInput) {
                archivosInput.value = '';  // Limpiar input de archivos
                console.log('🗂️ Input de archivos limpiado');
            }
            
            if (archivosPreview) {
                archivosPreview.innerHTML = '';  // Limpiar preview de archivos
                console.log('🗂️ Preview de archivos limpiado');
            }
            
            // 🔧 MODIFICADO: No limpiar paciente si ya está seleccionado
            const shouldPreservePatient = appState.selectedPatient && appState.selectedPatient.id;
            console.log('🔄 Preservar paciente seleccionado:', shouldPreservePatient);
            
            if (!shouldPreservePatient) {
                // Verificar que los elementos existan antes de acceder a ellos
                const selectedPatientId = document.getElementById('selected-patient-id');
                const selectedPatientInfo = document.getElementById('selected-patient-info');
                const patientSearch = document.getElementById('patient-search');
                
                if (selectedPatientId) selectedPatientId.value = '';
                if (selectedPatientInfo) selectedPatientInfo.style.display = 'none';
                if (patientSearch) patientSearch.value = '';
                
                appState.selectedPatient = null;
                console.log('🧹 Datos de paciente limpiados');
            } else {
                console.log('🔒 Paciente preservado para el formulario');
            }
            
            document.getElementById('anteojos-section').style.display = 'none';
            document.getElementById('patient-results').style.display = 'none';
            
            // CORRECCIÓN: Mantener el tipo de formulario actual en lugar de forzar 'general'
            const currentType = appState.currentFormType || 'general';
            console.log('🔄 Manteniendo tipo de formulario actual:', currentType);
            
            // Mostrar sección de visión y tensión solo para formulario General
            const visionTensionSection = document.querySelector('.vision-tension-section');
            if (visionTensionSection) {
                if (currentType === 'general') {
                    visionTensionSection.style.display = 'flex';
                    console.log('✅ Sección de visión y tensión activada para formulario General');
                } else {
                    visionTensionSection.style.display = 'none';
                    console.log('🔒 Sección de visión y tensión oculta para formulario:', currentType);
                }
            }
            
            // Cargar motivos comunes y preformatos para el tipo actual
            console.log('🔄 Cargando motivos comunes y preformatos...');
            loadMotivosComunes(currentType);
            loadPreformatos(currentType);
            
            // Activar secciones específicas según el tipo de formulario
            if (currentType === 'anteojos') {
                const anteojosSection = document.getElementById('anteojos-section');
                if (anteojosSection) {
                    anteojosSection.style.display = 'block';
                    anteojosSection.classList.add('active');
                    console.log('✅ Sección de anteojos reactivada después del reset');
                    
                    // Recargar referencias después del reset
                    setTimeout(() => {
                        loadReferenciales();
                    }, 200);
                }
            }
            
            // 🛡️ Quitar flag después de un pequeño delay
            setTimeout(() => {
                appState.isResetting = false;
                console.log('🔄 isResetting set to false');
            }, 100);
            
            console.log('✅ Form reset completed');
        }

        /**
         * 🆕 Limpiar archivos seleccionados en formularios
         */
        function clearSelectedFiles() {
            console.log('🗂️ Limpiando archivos seleccionados...');
            
            // Limpiar formulario de creación
            const archivosInput = document.getElementById('archivos_consulta');
            const archivosPreview = document.getElementById('archivos-preview');
            
            if (archivosInput) {
                archivosInput.value = '';
                console.log('🗂️ Input de archivos de creación limpiado');
            }
            
            if (archivosPreview) {
                archivosPreview.innerHTML = '';
                console.log('🗂️ Preview de archivos de creación limpiado');
            }
            
            // Limpiar formulario de edición si existe
            const editArchivosInput = document.getElementById('edit-archivos');
            const editArchivosPreview = document.getElementById('edit-archivos-preview');
            
            if (editArchivosInput) {
                editArchivosInput.value = '';
                console.log('🗂️ Input de archivos de edición limpiado');
            }
            
            if (editArchivosPreview) {
                editArchivosPreview.innerHTML = '';
                console.log('🗂️ Preview de archivos de edición limpiado');
            }
            
            console.log('✅ Archivos limpiados completamente');
        }

        /**
         * Manejar cambio de tipo en formulario de edición
         */
        function handleEditFormTypeChange() {
            // Esta función se llamará dinámicamente desde el formulario de edición
            // La implementación dependerá del contexto específico
        }

        /**
         * 🆕 Funciones para manejo de archivos
         */
        function previewArchivos(input) {
            const preview = document.getElementById('archivos-preview');
            preview.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                const container = document.createElement('div');
                container.className = 'archivos-selected';
                
                Array.from(input.files).forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item d-flex align-items-center mb-2 p-2 border rounded';
                    
                    const icon = getFileIcon(file.type, file.name);
                    const size = formatFileSize(file.size);
                    
                    fileItem.innerHTML = `
                        <i class="${icon} me-2"></i>
                        <span class="file-name me-auto">${file.name}</span>
                        <span class="file-size text-muted me-2">${size}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    
                    container.appendChild(fileItem);
                });
                
                preview.appendChild(container);
            }
        }
        
        function getFileIcon(mimeType, fileName = '') {
            // Obtener extensión del archivo como respaldo
            const extension = fileName.split('.').pop().toLowerCase();
            
            // Imágenes
            if (mimeType.startsWith('image/') || ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp', 'svg'].includes(extension)) {
                return 'fas fa-image text-success';
            }
            
            // PDFs
            if (mimeType.includes('pdf') || extension === 'pdf') {
                return 'fas fa-file-pdf text-danger';
            }
            
            // Documentos Word
            if (mimeType.includes('word') || mimeType.includes('msword') || mimeType.includes('wordprocessingml') || ['doc', 'docx'].includes(extension)) {
                return 'fas fa-file-word text-primary';
            }
            
            // Excel
            if (mimeType.includes('excel') || mimeType.includes('spreadsheet') || mimeType.includes('ms-excel') || ['xls', 'xlsx'].includes(extension)) {
                return 'fas fa-file-excel text-success';
            }
            
            // PowerPoint
            if (mimeType.includes('powerpoint') || mimeType.includes('presentation') || ['ppt', 'pptx'].includes(extension)) {
                return 'fas fa-file-powerpoint text-warning';
            }
            
            // Archivos de texto
            if (mimeType.includes('text') || ['txt', 'rtf'].includes(extension)) {
                return 'fas fa-file-alt text-info';
            }
            
            // Archivo genérico
            return 'fas fa-file text-secondary';
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function removeFile(index) {
            const input = document.getElementById('archivos_consulta');
            const dt = new DataTransfer();
            
            for (let i = 0; i < input.files.length; i++) {
                if (i !== index) {
                    dt.items.add(input.files[i]);
                }
            }
            
            input.files = dt.files;
            previewArchivos(input);
        }
        
        async function uploadArchivos(consulta_id, archivos) {
            if (!archivos || archivos.length === 0) return [];
            
            const uploadedFiles = [];
            
            for (let archivo of archivos) {
                const formData = new FormData();
                formData.append('archivos', archivo);
                formData.append('id_consulta', consulta_id);
                
                // Corregir el envío de id_persona - usar el ID correcto
                const personaId = appState.selectedPatient?.id || appState.selectedPatient?.person_id;
                if (personaId) {
                    formData.append('id_persona', personaId);
                }
                
                formData.append('action', 'upload_archivo');
                
                console.log('📤 Enviando archivo:', {
                    nombre: archivo.name,
                    tamaño: archivo.size,
                    tipo: archivo.type,
                    consulta_id: consulta_id
                });
                
                try {
                    const response = await fetch('modules/consultas/api/livewire-system.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    console.log('📎 Upload response:', result);
                    
                    if (result.success) {
                        // Manejar diferentes formatos de respuesta
                        const archivosSubidos = result.subidos || result.data || [];
                        uploadedFiles.push(...archivosSubidos);
                        console.log('✅ Archivo subido exitosamente:', archivo.name);
                        console.log('🔢 Archivos procesados en esta respuesta:', archivosSubidos.length);
                    } else {
                        console.error('❌ Error subiendo archivo:', archivo.name, result.message || result.errores);
                        showError(`Error subiendo ${archivo.name}: ${result.message || 'Error desconocido'}`);
                    }
                } catch (error) {
                    console.error('❌ Error en upload:', archivo.name, error);
                    showError(`Error de conexión subiendo ${archivo.name}`);
                }
            }
            
            console.log('📎 Total archivos subidos:', uploadedFiles.length);
            return uploadedFiles;
        }
        
        async function loadArchivosExistentes(consultaId) {
            console.log('📁 loadArchivosExistentes llamada con consultaId:', consultaId);
            
            if (!consultaId) {
                console.log('❌ No consultaId provided');
                return;
            }
            
            try {
                console.log('📞 Llamando API get_archivos_consulta...');
                const result = await callAPI('get_archivos_consulta', { id_consulta: consultaId });
                console.log('📊 Resultado de get_archivos_consulta:', result);
                
                const container = document.getElementById('edit-archivos-existentes');
                console.log('🎯 Container encontrado:', !!container);
                
                if (!container) return;
                
                const archivos = result.archivos || result.data || [];
                console.log('📁 Archivos a mostrar:', archivos.length, archivos);
                
                if (archivos && archivos.length > 0) {
                    const html = '<div class="archivos-existentes">' +
                        archivos.map(archivo => `
                            <div class="file-item d-flex align-items-center mb-2 p-2 border rounded bg-light" data-archivo-id="${archivo.id_archivo}">
                                <i class="${getFileIcon(archivo.tipo_archivo, archivo.nombre_archivo)} me-2"></i>
                                <span class="file-name me-auto">${archivo.nombre_archivo}</span>
                                <span class="file-size text-muted me-2">${formatFileSize(archivo.tamano_archivo || 0)}</span>
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="downloadArchivo(${archivo.id_archivo})" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExistingArchivo(${archivo.id_archivo})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `).join('') +
                    '</div>';
                    
                    container.innerHTML = html;
                    console.log('✅ HTML de archivos insertado en container');
                } else {
                    container.innerHTML = '<p class="text-muted">No hay archivos adjuntos</p>';
                    console.log('ℹ️ No hay archivos, mostrando mensaje vacío');
                }
            } catch (error) {
                console.error('❌ Error cargando archivos existentes:', error);
            }
        }
        
        async function removeExistingArchivo(archivoId) {
            if (!confirm('¿Está seguro de que desea eliminar este archivo?')) return;
            
            try {
                const result = await callAPI('delete_archivo', { id_archivo: archivoId });
                if (result.success) {
                    // Remover del DOM
                    const fileItem = document.querySelector(`[data-archivo-id="${archivoId}"]`);
                    if (fileItem) {
                        fileItem.remove();
                    }
                    showSuccess('Archivo eliminado exitosamente');
                } else {
                    showError('Error eliminando archivo');
                }
            } catch (error) {
                console.error('Error eliminando archivo:', error);
                showError('Error eliminando archivo');
            }
        }
        
        function downloadArchivo(archivoId) {
            console.log('📥 Descargando archivo ID:', archivoId);
            const url = `modules/consultas/api/livewire-system.php?action=download_archivo&id_archivo=${archivoId}`;
            window.open(url, '_blank');
        }
        
        function previewEditArchivos(input) {
            const preview = document.getElementById('edit-archivos-preview');
            if (!preview) return;
            
            preview.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                const container = document.createElement('div');
                container.className = 'archivos-selected';
                
                Array.from(input.files).forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item d-flex align-items-center mb-2 p-2 border rounded';
                    
                    const icon = getFileIcon(file.type, file.name);
                    const size = formatFileSize(file.size);
                    
                    fileItem.innerHTML = `
                        <i class="${icon} me-2"></i>
                        <span class="file-name me-auto">${file.name}</span>
                        <span class="file-size text-muted me-2">${size}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEditFile(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    
                    container.appendChild(fileItem);
                });
                
                preview.appendChild(container);
            }
        }
        
        function removeEditFile(index) {
            const input = document.getElementById('edit_archivos_consulta');
            if (!input) return;
            
            const dt = new DataTransfer();
            
            for (let i = 0; i < input.files.length; i++) {
                if (i !== index) {
                    dt.items.add(input.files[i]);
                }
            }
            
            input.files = dt.files;
            previewEditArchivos(input);
        }

        /**
         * Event listeners y inicialización
         */
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Iniciando sistema Livewire CRUD...');
            
            // Verificar dependencias
            console.log('jQuery loaded:', typeof $ !== 'undefined');
            console.log('Summernote available:', typeof $.fn.summernote !== 'undefined');
            console.log('Select2 available:', typeof $.fn.select2 !== 'undefined');
            
            // Esperar un momento para que todas las librerías se carguen
            setTimeout(function() {
                // Inicializar componentes
                try {
                    initializeSummernote();
                    initializeSelect2();
                    console.log('✅ Componentes inicializados correctamente');
                } catch (error) {
                    console.error('❌ Error inicializando componentes:', error);
                }
                
                // Configurar formulario de creación
                const createForm = document.getElementById('create-form');
                if (createForm) {
                    createForm.addEventListener('submit', createConsulta);
                    console.log('✅ Formulario de creación configurado');
                } else {
                    console.error('❌ No se encontró el formulario de creación');
                }
                
                // Configurar búsqueda con Enter
                const searchInput = document.getElementById('search-input');
                if (searchInput) {
                    searchInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            performSearch();
                        }
                    });
                }
                
                // Cargar datos iniciales
                loadConsultas();
                
                // 🆕 Cargar automáticamente paciente si viene desde citas
                <?php if ($pacienteData): ?>
                    console.log('🔄 Cargando paciente automáticamente desde citas...');
                    console.log('Paciente ID: <?php echo $pacienteData['paciente_id']; ?>');
                    console.log('Reserva ID: <?php echo $pacienteData['reserva_id']; ?>');
                    
                    // Habilitar debug mode temporalmente para la carga automática
                    window.debugMode = true;
                    
                    // Cargar paciente después de que todo esté inicializado
                    setTimeout(function() {
                        cargarPacienteAutomatico(<?php echo $pacienteData['paciente_id']; ?>, <?php echo $pacienteData['reserva_id'] ?: 'null'; ?>);
                    }, 1000);
                <?php endif; ?>
                
                // Cargar motivos comunes y preformatos iniciales para tipo general
                loadMotivosComunes('general');
                loadPreformatos('general');
                
                console.log('✅ Sistema Livewire CRUD iniciado exitosamente');
                
            }, 500); // Delay de 500ms para asegurar que todo esté cargado
            
            // Cerrar resultados de búsqueda al hacer clic fuera
            document.addEventListener('click', function(e) {
                const searchBox = document.querySelector('.search-box');
                if (searchBox && !searchBox.contains(e.target)) {
                    const patientResults = document.getElementById('patient-results');
                    if (patientResults) {
                        patientResults.style.display = 'none';
                    }
                }
                
                // Cerrar dropdown de búsqueda inteligente al hacer clic fuera
                const smartContainer = document.querySelector('.smart-search-container');
                const smartDropdown = document.getElementById('smartSearchDropdown');
                
                if (smartContainer && smartDropdown && !smartContainer.contains(e.target)) {
                    hideSmartDropdown();
                }
            });
            
        });
    </script>
    
    <!-- jQuery (required for Select2 and Summernote) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/alertifyjs/1.13.1/alertify.min.js"></script>
    
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Summernote -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    
    <script>
        // Inicializar Select2 y Summernote después de que se cargue la página
        $(document).ready(function() {
            console.log('jQuery loaded:', typeof $ !== 'undefined');
            console.log('Summernote available:', typeof $.fn.summernote !== 'undefined');
            console.log('Select2 available:', typeof $.fn.select2 !== 'undefined');
            
            // Esperar un poco para asegurar que todas las librerías estén cargadas
            setTimeout(function() {
                // Inicializar Select2
                initializeSelect2();
                
                // Inicializar Summernote
                initializeSummernote();
                
                // Configurar event listeners después de que todo esté inicializado
                setTimeout(function() {
                    setupEventListeners();
                }, 100);
            }, 100);
        });
        
        function initializeSelect2() {
            if (typeof $.fn.select2 === 'undefined') {
                console.error('Select2 not loaded');
                return;
            }
            
            try {
                $('.select2bs4').select2({
                    theme: 'bootstrap4',
                    placeholder: 'Seleccionar...',
                    allowClear: true,
                    width: '100%'
                });
                if (window.debugMode) {
                    console.log('Select2 initialized successfully');
                }
            } catch (error) {
                console.error('Error initializing Select2:', error);
            }
        }
        
        function initializeSummernote() {
            if (typeof $.fn.summernote === 'undefined') {
                console.error('Summernote not loaded');
                return;
            }
            
            try {
                $('.summernote').summernote({
                    height: 200,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    placeholder: 'Escriba aquí...',
                    dialogsInBody: true,
                    dialogsFade: true
                });
                if (window.debugMode) {
                    console.log('Summernote initialized successfully');
                }
            } catch (error) {
                console.error('Error initializing Summernote:', error);
            }
        }
        
        // Función para reinicializar componentes después de cambios dinámicos
        function reinitializeComponents() {
            try {
                // Destruir instancias existentes de Select2
                $('.select2bs4').each(function() {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                });
                
                // Destruir instancias existentes de Summernote
                $('.summernote').each(function() {
                    if ($(this).next('.note-editor').length > 0) {
                        $(this).summernote('destroy');
                    }
                });
                
                // Reinicializar después de un pequeño delay
                setTimeout(function() {
                    initializeSelect2();
                    initializeSummernote();
                    
                    // Reconfigurar event listeners después de que Select2 esté listo
                    setTimeout(function() {
                        setupEventListeners();
                    }, 100);
                }, 50);
                
            } catch (error) {
                console.error('Error reinitializing components:', error);
            }
        }
        
        // Función para configurar event listeners
        function setupEventListeners() {
            console.log('Setting up event listeners...');
            
            // Configurar motivos comunes
            const motivosSelect = document.getElementById('motivos_comunes');
            if (motivosSelect) {
                // Usar eventos de Select2 si está inicializado, sino usar evento normal
                if ($(motivosSelect).hasClass('select2-hidden-accessible')) {
                    // Select2 está inicializado
                    $(motivosSelect).off('select2:select').on('select2:select', function(e) {
                        if (window.debugMode) console.log('Select2 motivo comun selected:', e.params.data);
                        aplicarMotivoComun();
                    });
                    if (window.debugMode) console.log('Motivos comunes Select2 listener configured');
                } else {
                    // Fallback a evento normal
                    motivosSelect.removeEventListener('change', aplicarMotivoComun);
                    motivosSelect.addEventListener('change', aplicarMotivoComun);
                    if (window.debugMode) console.log('Motivos comunes normal listener configured');
                }
            } else {
                if (window.debugMode) console.log('Motivos comunes select not found');
            }
            
            // Configurar preformatos consulta
            const preformatosConsulta = document.getElementById('preformatos_consulta');
            if (preformatosConsulta) {
                if ($(preformatosConsulta).hasClass('select2-hidden-accessible')) {
                    $(preformatosConsulta).off('select2:select').on('select2:select', function(e) {
                        if (window.debugMode) console.log('Select2 preformato consulta selected:', e.params.data);
                        aplicarPreformato('consulta');
                    });
                    if (window.debugMode) console.log('Preformatos consulta Select2 listener configured');
                } else {
                    preformatosConsulta.addEventListener('change', function() {
                        aplicarPreformato('consulta');
                    });
                    if (window.debugMode) console.log('Preformatos consulta normal listener configured');
                }
            }
            
            // Configurar preformatos receta
            const preformatosReceta = document.getElementById('preformatos_receta');
            if (preformatosReceta) {
                if ($(preformatosReceta).hasClass('select2-hidden-accessible')) {
                    $(preformatosReceta).off('select2:select').on('select2:select', function(e) {
                        if (window.debugMode) console.log('Select2 preformato receta selected:', e.params.data);
                        aplicarPreformato('receta');
                    });
                    if (window.debugMode) console.log('Preformatos receta Select2 listener configured');
                } else {
                    preformatosReceta.addEventListener('change', function() {
                        aplicarPreformato('receta');
                    });
                    if (window.debugMode) console.log('Preformatos receta normal listener configured');
                }
            }
            
            // 🆕 Configurar event listener para archivos
            const archivosInput = document.getElementById('archivos_consulta');
            if (archivosInput) {
                archivosInput.removeEventListener('change', handleArchivosChange);
                archivosInput.addEventListener('change', handleArchivosChange);
                if (window.debugMode) console.log('Archivos input listener configured');
            }
        }
        
        function handleArchivosChange(event) {
            previewArchivos(event.target);
        }
        
        // Ocultar resultados de búsqueda cuando se hace click fuera
        document.addEventListener('click', function(event) {
            const searchBox = document.querySelector('.search-box');
            const resultsEl = document.getElementById('patient-results');
            
            if (searchBox && !searchBox.contains(event.target)) {
                resultsEl.style.display = 'none';
                selectedResultIndex = -1;
            }
        });
        
        /*
         * Debug Mode:
         * Para habilitar los logs de depuración, ejecuta en la consola:
         * window.debugMode = true;
         * 
         * Para deshabilitar:
         * window.debugMode = false;
         */
        
        // 🆕 Función para cargar paciente automáticamente desde citas
        async function cargarPacienteAutomatico(pacienteId, reservaId) {
            try {
                console.log(`🔍 Buscando paciente con ID: ${pacienteId}`);
                
                // Hacer petición para obtener datos del paciente
                const response = await fetch(`api/persons/show?id=${pacienteId}`);
                const data = await response.json();
                
                if (data.status === 'success' && data.data) {
                    const paciente = data.data;
                    console.log('✅ Paciente encontrado:', paciente);
                    
                    // Llenar el campo de búsqueda con el nombre del paciente
                    const searchInput = document.getElementById('search-input');
                    if (searchInput) {
                        searchInput.value = `${paciente.first_name} ${paciente.last_name}`;
                        console.log('📝 Campo search-input llenado');
                    }
                    
                    // También llenar el campo patient-search si existe
                    const patientSearchInput = document.getElementById('patient-search');
                    if (patientSearchInput) {
                        patientSearchInput.value = `${paciente.first_name} ${paciente.last_name}`;
                        console.log('📝 Campo patient-search llenado');
                    }
                    
                    // Verificar que todos los elementos necesarios existen
                    const hiddenField = document.getElementById('selected-patient-id');
                    const patientInfoText = document.getElementById('patient-info-text');
                    const selectedPatientInfo = document.getElementById('selected-patient-info');
                    
                    console.log('🔍 Elementos encontrados:', {
                        hiddenField: !!hiddenField,
                        patientInfoText: !!patientInfoText,
                        selectedPatientInfo: !!selectedPatientInfo
                    });
                    
                    // Simular selección del paciente usando los parámetros correctos
                    selectPatient(
                        paciente.person_id,
                        paciente.first_name,
                        paciente.last_name,
                        paciente.document_number
                    );
                    
                    console.log('✅ selectPatient llamado correctamente');
                    
                    // 🆕 IMPORTANTE: Recargar consultas para mostrar solo las del paciente seleccionado
                    setTimeout(function() {
                        console.log('🔄 Recargando consultas del paciente seleccionado...');
                        loadConsultas(1);
                    }, 500);
                    
                    // Mostrar mensaje de éxito
                    if (typeof alertify !== 'undefined') {
                        alertify.success(`Paciente ${paciente.first_name} ${paciente.last_name} cargado automáticamente desde citas`);
                    }
                    
                    // Si hay reserva ID, podrías cargar información adicional de la reserva
                    if (reservaId) {
                        console.log(`📋 Información de reserva ID: ${reservaId} disponible`);
                        // Aquí podrías cargar datos adicionales de la reserva si es necesario
                    }
                    
                } else {
                    console.error('❌ No se pudo cargar el paciente:', data.message || 'Paciente no encontrado');
                    if (typeof alertify !== 'undefined') {
                        alertify.error('No se pudo cargar automáticamente los datos del paciente');
                    }
                }
                
            } catch (error) {
                console.error('❌ Error cargando paciente automáticamente:', error);
                if (typeof alertify !== 'undefined') {
                    alertify.error('Error de conexión al cargar datos del paciente');
                }
            }
        }
        
    </script>
</body>
</html>