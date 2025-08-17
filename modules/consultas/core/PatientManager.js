/**
 * ============================================
 * SISTEMA DE GESTIÓN DE PACIENTES
 * ============================================
 */

class PatientManager {
    constructor(consultasManager) {
        this.consultasManager = consultasManager;
        this.searchTimeout = null;
        this.currentResults = [];
    }
    
    /**
     * Inicializar funcionalidad de búsqueda de pacientes
     */
    init() {
        this.setupSearchHandlers();
        // this.setupPatientModal(); // Comentado temporalmente - implementar más tarde
        // this.setupKeyboardShortcuts(); // Comentado temporalmente - implementar más tarde
    }
    
    /**
     * Configurar handlers de búsqueda
     */
    setupSearchHandlers() {
        // Campo de búsqueda por documento
        const docInput = document.getElementById('txtdocumento');
        if (docInput) {
            docInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value, 'documento');
            });
        }
        
        // Campo de búsqueda por ficha
        const fichaInput = document.getElementById('txtficha');
        if (fichaInput) {
            fichaInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value, 'ficha');
            });
        }
        
        // Campo de búsqueda por nombre
        const nombreInput = document.getElementById('paciente');
        if (nombreInput) {
            nombreInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value, 'nombre');
            });
            
            // Manejar tecla Enter
            nombreInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.performSearch();
                }
            });
        }
        
        // Botón de búsqueda
        const btnBuscar = document.getElementById('btnBuscarPersona');
        if (btnBuscar) {
            btnBuscar.addEventListener('click', () => {
                this.performSearch();
            });
        }
        
        // Botón de limpiar
        const btnLimpiar = document.getElementById('btnLimpiarPersona');
        if (btnLimpiar) {
            btnLimpiar.addEventListener('click', () => {
                this.clearSearch();
            });
        }
        
        // Botón nueva persona
        const btnNueva = document.getElementById('btnNuevaPersona');
        if (btnNueva) {
            btnNueva.addEventListener('click', () => {
                this.openNewPatientModal();
            });
        }
        
        // Click fuera del dropdown para ocultarlo
        document.addEventListener('click', (e) => {
            const isClickInsideSearchField = e.target.closest('#paciente') || 
                                           e.target.closest('#txtdocumento') || 
                                           e.target.closest('#txtficha');
            const isClickInsideDropdown = e.target.closest('#patient-dropdown') || 
                                        e.target.closest('#document-dropdown') || 
                                        e.target.closest('#ficha-dropdown');
            
            if (!isClickInsideSearchField && !isClickInsideDropdown) {
                this.hideSearchResults();
            }
        });
    }
    
    /**
     * Manejar input de búsqueda con debounce
     */
    handleSearchInput(value, type) {
        // Limpiar timeout anterior
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        // Si el valor está vacío, limpiar resultados
        if (!value.trim()) {
            this.clearSearchResults();
            return;
        }
        
        // Activar autocompletado para todos los tipos de búsqueda
        const minChars = type === 'nombre' ? 3 : (type === 'documento' ? 4 : 2); // Nombre: 3, Documento: 4, Ficha: 2
        
        if (value.length >= minChars) {
            this.searchTimeout = setTimeout(() => {
                this.showAutocompleteResults(value, type);
            }, 300);
        } else {
            this.clearSearchResults();
        }
    }
    
    /**
     * Mostrar resultados de autocompletado como dropdown
     */
    async showAutocompleteResults(searchTerm, searchType = 'nombre') {
        try {
            let body;
            
            // Configurar búsqueda según el tipo
            if (searchType === 'nombre') {
                body = `accion=buscar_por_nombre&termino=${encodeURIComponent(searchTerm)}`;
            } else {
                // Para documento y ficha usar el endpoint principal
                const params = new URLSearchParams();
                params.append('operacion', 'buscarparam');
                
                if (searchType === 'documento') {
                    params.append('documento', searchTerm);
                    params.append('nro_ficha', '');
                    params.append('nombre', '');
                } else if (searchType === 'ficha') {
                    params.append('documento', '');
                    params.append('nro_ficha', searchTerm);
                    params.append('nombre', '');
                }
                
                body = params.toString();
            }
            
            const response = await fetch('../../../ajax/persona.ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: body
            });
            
            const result = await response.json();
            let results = [];
            
            // Procesar respuesta según el tipo de endpoint
            if (searchType === 'nombre') {
                results = Array.isArray(result) ? result : [];
            } else {
                // Para documento y ficha
                if (result.status === 'success') {
                    results = result.multiple ? result.data : [result.data];
                }
            }
            
            if (results && results.length > 0) {
                this.displayAutocompleteDropdown(results, searchType);
            } else {
                this.hideSearchResults();
            }
            
        } catch (error) {
            console.error('Error en autocompletado:', error);
            this.hideSearchResults();
        }
    }
    
    /**
     * Mostrar dropdown de autocompletado
     */
    displayAutocompleteDropdown(results, searchType = 'nombre', targetInput = null) {
        // Determinar el campo correcto basado en el searchType
        let inputElement;
        let dropdownId;
        
        if (targetInput) {
            inputElement = targetInput;
        } else {
            switch (searchType) {
                case 'documento':
                    inputElement = document.getElementById('txtdocumento');
                    dropdownId = 'document-dropdown';
                    break;
                case 'ficha':
                    inputElement = document.getElementById('txtficha');
                    dropdownId = 'ficha-dropdown';
                    break;
                default: // 'nombre'
                    inputElement = document.getElementById('paciente');
                    dropdownId = 'patient-dropdown';
            }
        }
        
        if (!inputElement) return;
        
        // Crear o obtener contenedor de resultados
        let dropdown = document.getElementById(dropdownId || 'patient-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.id = dropdownId || 'patient-dropdown';
            dropdown.className = 'patient-search-dropdown';
            inputElement.parentNode.appendChild(dropdown);
        }
        
        // Crear HTML de resultados
        let html = '<ul class="dropdown-menu show" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; max-height: 300px; overflow-y: auto;">';
        
        results.forEach(patient => {
            html += `
                <li class="dropdown-item patient-item" style="cursor: pointer; padding: 8px 12px; border-bottom: 1px solid #eee;" 
                    data-patient='${JSON.stringify(patient)}'>
                    <div>
                        <strong>${patient.nombres} ${patient.apellidos}</strong>
                        <div style="font-size: 0.85em; color: #666;">
                            ${patient.documento ? 'CI: ' + patient.documento : ''} 
                            ${patient.nro_ficha ? '| Ficha: ' + patient.nro_ficha : ''}
                        </div>
                    </div>
                </li>
            `;
        });
        
        html += '</ul>';
        dropdown.innerHTML = html;
        
        // Configurar eventos de click
        dropdown.querySelectorAll('.patient-item').forEach(item => {
            item.addEventListener('click', async (e) => {
                const patientData = JSON.parse(e.target.closest('.patient-item').dataset.patient);
                await this.selectPatient(patientData);
                this.hideSearchResults();
            });
            
            // Hover effects
            item.addEventListener('mouseenter', (e) => {
                e.target.style.backgroundColor = '#f8f9fa';
            });
            
            item.addEventListener('mouseleave', (e) => {
                e.target.style.backgroundColor = '';
            });
        });
        
        // Posicionar dropdown
        dropdown.style.position = 'absolute';
        dropdown.style.top = '100%';
        dropdown.style.left = '0';
        dropdown.style.right = '0';
        dropdown.style.zIndex = '1000';
        
        // Configurar posición relativa del contenedor padre si no la tiene
        if (!inputElement.parentNode.style.position) {
            inputElement.parentNode.style.position = 'relative';
        }
    }
    
    /**
     * Ocultar resultados de búsqueda
     */
    hideSearchResults() {
        // Ocultar todos los dropdowns de búsqueda
        const dropdowns = ['patient-dropdown', 'document-dropdown', 'ficha-dropdown'];
        dropdowns.forEach(dropdownId => {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.remove();
            }
        });
    }
    
    /**
     * Limpiar resultados de búsqueda
     */
    clearSearchResults() {
        this.hideSearchResults();
    }
    
    /**
     * Realizar búsqueda automática
     */
    async performAutoSearch(value, type) {
        if (value.length < 2) return; // Mínimo 2 caracteres
        
        try {
            const results = await this.searchPatients({ [type]: value });
            this.displaySearchResults(results);
        } catch (error) {
            console.error('Error en búsqueda automática:', error);
        }
    }
    
    /**
     * Realizar búsqueda completa
     */
    async performSearch() {
        const documento = document.getElementById('txtdocumento')?.value?.trim();
        const ficha = document.getElementById('txtficha')?.value?.trim();
        const nombre = document.getElementById('paciente')?.value?.trim();
        
        if (!documento && !ficha && !nombre) {
            this.consultasManager.notifications.warning('Debe ingresar al menos un criterio de búsqueda');
            return;
        }
        
        try {
            this.consultasManager.setLoading(true, 'Buscando paciente...');
            
            const results = await this.searchPatients({
                documento,
                nro_ficha: ficha,
                nombre
            });
            
            if (results.length === 0) {
                this.consultasManager.notifications.info('No se encontraron pacientes con esos criterios');
                return;
            }
            
            if (results.length === 1) {
                // Un solo resultado, cargarlo directamente
                await this.selectPatient(results[0]);
            } else {
                // Múltiples resultados, mostrar selector
                this.showPatientSelector(results);
            }
            
        } catch (error) {
            console.error('Error en búsqueda:', error);
            this.consultasManager.notifications.error('Error al buscar paciente');
        } finally {
            this.consultasManager.setLoading(false);
        }
    }
    
    /**
     * Buscar pacientes en la base de datos
     */
    async searchPatients(criteria) {
        const response = await this.consultasManager.apiCall('../../../ajax/persona.ajax.php', {
            operacion: 'buscarparam',
            ...criteria
        });
        
        if (response.status === 'success') {
            return response.multiple ? response.data : [response.data];
        }
        
        return [];
    }
    
    /**
     * Mostrar selector de múltiples pacientes
     */
    showPatientSelector(patients) {
        const html = `
            <div class="patient-selector">
                <h5>Se encontraron ${patients.length} pacientes:</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Ficha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${patients.map(patient => `
                                <tr>
                                    <td>${patient.documento || 'Sin documento'}</td>
                                    <td>${patient.nombres} ${patient.apellidos}</td>
                                    <td>${patient.nro_ficha || 'Sin ficha'}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm select-patient" 
                                                data-patient='${JSON.stringify(patient)}'>
                                            <i class="fas fa-check"></i> Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        
        // Mostrar modal con opciones
        this.showModal('Seleccionar Paciente', html, () => {
            // Configurar eventos de selección
            document.querySelectorAll('.select-patient').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const patientData = JSON.parse(e.target.closest('button').dataset.patient);
                    await this.selectPatient(patientData);
                    this.closeModal();
                });
            });
        });
    }
    
    /**
     * Seleccionar un paciente
     */
    async selectPatient(patient) {
        console.log('👤 Seleccionando paciente:', patient);
        
        try {
            // Actualizar estado
            this.consultasManager.state.currentPatient = patient;
            
            // Actualizar formulario
            this.updatePatientFields(patient);
            
            // Actualizar panel de información
            this.updatePatientPanel(patient);
            
            // Cargar información adicional
            await this.loadPatientAdditionalInfo(patient.id_persona);
            
            // Cargar resumen de consultas
            await this.loadPatientSummary(patient.id_persona);
            
            // Cargar historial completo y timeline
            await this.loadPatientConsultas(patient.id_persona);
            
            this.consultasManager.notifications.success(`Paciente seleccionado: ${patient.nombres} ${patient.apellidos}`);
            
            // Disparar evento
            document.dispatchEvent(new CustomEvent('patientSelected', {
                detail: patient
            }));
            
        } catch (error) {
            console.error('Error seleccionando paciente:', error);
            this.consultasManager.notifications.error('Error al seleccionar paciente');
        }
    }
    
    /**
     * Actualizar campos del formulario con datos del paciente
     */
    updatePatientFields(patient) {
        const fields = {
            'paciente': `${patient.nombres} ${patient.apellidos}`,
            'txtdocumento': patient.documento || '',
            'txtficha': patient.nro_ficha || '',
            'idPersona': patient.id_persona,
            'id_persona_file': patient.id_persona
        };
        
        Object.keys(fields).forEach(fieldId => {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = fields[fieldId];
            }
        });
    }
    
    /**
     * Actualizar panel de información del paciente
     */
    updatePatientPanel(patient) {
        // Actualizar nombre y CI
        const usernameElement = document.getElementById('profile-username');
        if (usernameElement) {
            usernameElement.textContent = `${patient.nombres} ${patient.apellidos}`;
        }
        
        const ciElement = document.getElementById('profile-ci');
        if (ciElement) {
            ciElement.textContent = `CI: ${patient.documento || 'Sin documento'}`;
        }
        
        // Mostrar edad si está disponible
        if (patient.fecha_nacimiento) {
            const edad = this.calculateAge(patient.fecha_nacimiento);
            const edadElement = document.getElementById('profile-edad');
            if (edadElement) {
                edadElement.textContent = `Edad: ${edad} años`;
            }
        }
    }
    
    /**
     * Cargar información adicional del paciente
     */
    async loadPatientAdditionalInfo(patientId) {
        try {
            // Cargar resumen de consultas
            const consultasResponse = await this.consultasManager.apiCall('ajax/consultas.ajax.php', {
                operacion: 'resumenConsulta',
                id_persona: patientId
            });
            
            if (consultasResponse) {
                this.updateConsultasInfo(consultasResponse);
            }
            
            // Cargar cuota
            const cuotaResponse = await this.consultasManager.apiCall('ajax/archivos.ajax.php', {
                operacion: 'mega',
                id_persona: patientId
            });
            
            if (cuotaResponse && cuotaResponse.cuota) {
                this.updateCuotaInfo(cuotaResponse.cuota);
            }
            
        } catch (error) {
            console.error('Error cargando información adicional:', error);
        }
    }
    
    /**
     * Actualizar información de consultas
     */
    updateConsultasInfo(info) {
        const cantElement = document.getElementById('txtCantConsulta');
        if (cantElement) {
            cantElement.textContent = info.cantidad_consultas || '0';
        }
        
        const ultElement = document.getElementById('txtUltConsulta');
        if (ultElement) {
            ultElement.textContent = info.maxima_fecha_registro || 'Sin consultas';
            
            // Hacer clickeable si hay consultas
            if (info.cantidad_consultas > 0) {
                ultElement.classList.add('consulta-link');
                ultElement.style.cursor = 'pointer';
                ultElement.addEventListener('click', () => {
                    this.showPatientHistory();
                });
            }
        }
    }
    
    /**
     * Actualizar información de cuota
     */
    updateCuotaInfo(cuota) {
        const cuotaElement = document.getElementById('cuota-valor');
        if (cuotaElement) {
            cuotaElement.textContent = cuota;
        }
    }
    
    /**
     * Cargar resumen de consultas del paciente
     */
    async loadPatientSummary(patientId) {
        try {
            const response = await this.consultasManager.apiCall('ajax/consultas.ajax.php', {
                operacion: 'resumenConsulta',
                id_persona: patientId
            });

            if (response) {
                // Actualizar información de consultas
                const cantidadConsultas = response.cantidad_consultas || '0';
                const ultimaConsulta = response.maxima_fecha_registro || 'Sin consultas';
                
                // Mostrar la información en la interfaz
                const txtCantConsulta = document.getElementById('txtCantConsulta');
                if (txtCantConsulta) {
                    txtCantConsulta.textContent = cantidadConsultas;
                }
                
                const txtUltConsulta = document.getElementById('txtUltConsulta');
                if (txtUltConsulta) {
                    txtUltConsulta.textContent = ultimaConsulta;
                    
                    // Si hay consultas, hacer que el elemento sea clickeable
                    if (parseInt(cantidadConsultas) > 0) {
                        txtUltConsulta.classList.add('consulta-link');
                        txtUltConsulta.style.cursor = 'pointer';
                        txtUltConsulta.style.color = '#007bff';
                        txtUltConsulta.title = 'Click para ver historial completo';
                        
                        // Eliminar eventos previos
                        txtUltConsulta.removeEventListener('click', this._showHistoryHandler);
                        
                        // Agregar evento de clic
                        this._showHistoryHandler = () => this.showPatientHistory();
                        txtUltConsulta.addEventListener('click', this._showHistoryHandler);
                    } else {
                        txtUltConsulta.classList.remove('consulta-link');
                        txtUltConsulta.style.cursor = 'default';
                        txtUltConsulta.style.color = '';
                        txtUltConsulta.title = '';
                    }
                }
            }
            
        } catch (error) {
            console.error('Error cargando resumen de consultas:', error);
        }
    }

    /**
     * Cargar historial de consultas del paciente
     */
    async loadPatientConsultas(patientId) {
        try {
            // Cargar tabla de consultas
            await this.loadConsultasTable(patientId);
            
            // Cargar timeline
            await this.loadPatientTimeline(patientId);
            
        } catch (error) {
            console.error('Error cargando consultas del paciente:', error);
        }
    }
    
    /**
     * Cargar tabla de consultas en el panel de historial
     */
    async loadConsultasTable(patientId) {
        try {
            const tableContainer = document.querySelector('#tabla-consultas tbody');
            if (!tableContainer) return;
            
            // Mostrar loading
            tableContainer.innerHTML = '<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando consultas...</td></tr>';
            
            const response = await this.consultasManager.apiCall('ajax/consultas.ajax.php', {
                operacion: 'historialConsultas',
                id_persona: patientId
            });
            
            if (response && response.length > 0) {
                let html = '';
                response.forEach(consulta => {
                    const fecha = new Date(consulta.fecha_registro);
                    html += `
                        <tr>
                            <td>${fecha.toLocaleDateString('es-ES')} ${fecha.toLocaleTimeString('es-ES')}</td>
                            <td>${consulta.nombres || ''} ${consulta.apellidos || ''}</td>
                            <td>
                                <span class="badge badge-info">${consulta.tipo_formulario || 'General'}</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary load-consulta" data-id="${consulta.id_consulta}">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-info view-consulta" data-id="${consulta.id_consulta}">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <a href="generar_pdf_consulta.php?id=${consulta.id_consulta}" 
                                   target="_blank" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                    `;
                });
                tableContainer.innerHTML = html;
                
                // Configurar eventos
                this.setupConsultasTableEvents();
                
            } else {
                tableContainer.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay consultas registradas</td></tr>';
            }
            
        } catch (error) {
            console.error('Error cargando tabla de consultas:', error);
            const tableContainer = document.querySelector('#tabla-consultas tbody');
            if (tableContainer) {
                tableContainer.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error al cargar consultas</td></tr>';
            }
        }
    }
    
    /**
     * Cargar timeline del paciente
     */
    async loadPatientTimeline(patientId) {
        try {
            const timelineContainer = document.getElementById('timeline');
            if (!timelineContainer) return;
            
            // Mostrar loading
            timelineContainer.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Cargando timeline...</p></div>';
            
            const response = await this.consultasManager.apiCall('ajax/consultas.ajax.php', {
                operacion: 'historialConsultas',
                id_persona: patientId
            });
            
            if (response && response.length > 0) {
                this.displayPatientHistory(response);
            } else {
                timelineContainer.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> No hay consultas registradas para este paciente</div>';
            }
            
        } catch (error) {
            console.error('Error cargando timeline:', error);
            const timelineContainer = document.getElementById('timeline');
            if (timelineContainer) {
                timelineContainer.innerHTML = '<div class="alert alert-danger">Error al cargar timeline de consultas</div>';
            }
        }
    }

    /**
     * Configurar eventos de la tabla de consultas
     */
    setupConsultasTableEvents() {
        // Botones de cargar consulta
        document.querySelectorAll('.load-consulta').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const consultaId = e.target.closest('button').dataset.id;
                await this.loadConsultaForEditing(consultaId);
            });
        });
        
        // Botones de ver detalle
        document.querySelectorAll('.view-consulta').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const consultaId = e.target.closest('button').dataset.id;
                await this.showConsultaDetail(consultaId);
            });
        });
    }
    
    /**
     * Cargar consulta para edición
     */
    async loadConsultaForEditing(consultaId) {
        try {
            this.consultasManager.notifications.info('Cargando consulta para edición...');
            // TODO: Implementar carga de consulta para edición
            console.log('🔄 Cargando consulta para edición:', consultaId);
            
        } catch (error) {
            console.error('Error cargando consulta para edición:', error);
            this.consultasManager.notifications.error('Error al cargar consulta');
        }
    }
    
    /**
     * Mostrar historial completo del paciente
     */
    async showPatientHistory() {
        if (!this.consultasManager.state.currentPatient) return;
        
        try {
            const response = await this.consultasManager.apiCall('ajax/consultas.ajax.php', {
                operacion: 'historialConsultas',
                id_persona: this.consultasManager.state.currentPatient.id_persona
            });
            
            if (response && response.length > 0) {
                this.displayPatientHistory(response);
            } else {
                this.consultasManager.notifications.info('No hay consultas registradas para este paciente');
            }
            
        } catch (error) {
            console.error('Error cargando historial:', error);
            this.consultasManager.notifications.error('Error al cargar historial');
        }
    }
    
    /**
     * Mostrar historial en timeline
     */
    displayPatientHistory(consultas) {
        const timelineContainer = document.getElementById('timeline');
        if (!timelineContainer) return;
        
        let timelineHTML = '<div class="timeline timeline-inverse">';
        
        consultas.forEach(consulta => {
            const fecha = new Date(consulta.fecha_registro);
            const fechaFormateada = fecha.toLocaleDateString('es-ES');
            
            timelineHTML += `
                <div class="time-label">
                    <span class="bg-primary">${fechaFormateada}</span>
                </div>
                <div>
                    <i class="fas fa-stethoscope bg-info"></i>
                    <div class="timeline-item">
                        <span class="time">
                            <i class="far fa-clock"></i> ${fecha.toLocaleTimeString('es-ES')}
                        </span>
                        <h3 class="timeline-header">
                            <a href="#">Consulta médica</a>
                        </h3>
                        <div class="timeline-body">
                            <strong>Doctor/a:</strong> ${consulta.nombre_doctor || ''} ${consulta.apellido_doctor || ''} - ${consulta.documento_doctor || 'No especificado'}<br>
                            <strong>Motivo:</strong> ${consulta.txtmotivo || 'No especificado'}<br>
                            <strong>Diagnóstico:</strong> ${consulta.consulta_textarea || consulta.diagnostico || 'No especificado'}
                        </div>
                        <div class="timeline-footer">
                            <button class="btn btn-info btn-sm ver-detalle-consulta" data-id="${consulta.id_consulta}">Ver detalles</button>
                            <a href="generar_pdf_consulta.php?id=${consulta.id_consulta}" target="_blank" class="btn btn-primary btn-sm">Descargar PDF</a>
                        </div>
                    </div>
                </div>
            `;
        });
        
        timelineHTML += '</div>';
        timelineContainer.innerHTML = timelineHTML;
        
        // Configurar eventos del timeline
        this.setupTimelineEvents();
        
        // Activar pestaña de timeline si no está activa
        const timelineTab = document.querySelector('#timeline-tab');
        if (timelineTab && !timelineTab.classList.contains('active')) {
            // Solo activar automáticamente si no hay otra pestaña activa
            const activeTab = document.querySelector('.nav-link.active');
            if (!activeTab || activeTab.getAttribute('href') === '#historial-panel') {
                timelineTab.click();
            }
        }
    }
    
    /**
     * Configurar eventos del timeline
     */
    setupTimelineEvents() {
        // Botones de cargar consulta
        document.querySelectorAll('.load-consulta').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const consultaId = e.target.closest('button').dataset.id;
                await this.consultasManager.loadConsulta(consultaId);
            });
        });
        
        // Botones de ver detalle
        document.querySelectorAll('.view-consulta').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const consultaId = e.target.closest('button').dataset.id;
                await this.showConsultaDetail(consultaId);
            });
        });
    }
    
    /**
     * Configurar eventos del timeline
     */
    setupTimelineEvents() {
        // Botones de ver detalle
        document.querySelectorAll('.ver-detalle-consulta').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const consultaId = e.target.closest('button').dataset.id;
                await this.showConsultaDetail(consultaId);
            });
        });
        
        // Enlaces de PDF ya funcionan por defecto (target="_blank")
    }
    
    /**
     * Mostrar detalle de consulta
     */
    async showConsultaDetail(consultaId) {
        try {
            const response = await this.consultasManager.apiCall('ajax/consultas.ajax.php', {
                operacion: 'obtenerConsulta',
                id_consulta: consultaId
            });
            
            if (response) {
                this.displayConsultaModal(response);
            }
            
        } catch (error) {
            console.error('Error mostrando detalle de consulta:', error);
            this.consultasManager.notifications.error('Error al cargar detalle de consulta');
        }
    }
    
    /**
     * Mostrar modal con detalle de consulta
     */
    displayConsultaModal(consulta) {
        const modalHtml = `
            <div class="consulta-detail">
                <h5>Consulta del ${new Date(consulta.fecha_registro).toLocaleDateString('es-ES')}</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Paciente:</strong> ${consulta.nombres || ''} ${consulta.apellidos || ''}<br>
                        <strong>Documento:</strong> ${consulta.documento || 'No especificado'}<br>
                        <strong>Ficha:</strong> ${consulta.nro_ficha || 'No especificado'}
                    </div>
                    <div class="col-md-6">
                        <strong>Doctor:</strong> ${consulta.nombre_doctor || ''} ${consulta.apellido_doctor || ''}<br>
                        <strong>Fecha:</strong> ${new Date(consulta.fecha_registro).toLocaleString('es-ES')}
                    </div>
                </div>
                <hr>
                <div class="consulta-content">
                    <h6>Motivo de consulta:</h6>
                    <p class="border p-2">${consulta.txtmotivo || 'No especificado'}</p>
                    
                    <h6>Diagnóstico:</h6>
                    <p class="border p-2">${consulta.consulta_textarea || consulta.diagnostico || 'No especificado'}</p>
                </div>
            </div>
        `;
        
        this.consultasManager.showModal('Detalle de Consulta', modalHtml, () => {
            // Acciones del modal si es necesario
        });
    }

    /**
     * Método utilitario para truncar texto
     */
    truncateText(text, maxLength = 100) {
        if (!text) return 'No especificado';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    /**
     * Limpiar búsqueda y formulario
     */
    clearSearch() {
        // Limpiar campos de búsqueda
        ['txtdocumento', 'txtficha', 'paciente', 'idPersona', 'id_persona_file'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.value = '';
            }
        });
        
        // Limpiar resumen de consultas
        const txtCantConsulta = document.getElementById('txtCantConsulta');
        const txtUltConsulta = document.getElementById('txtUltConsulta');
        
        if (txtCantConsulta) txtCantConsulta.textContent = '0';
        if (txtUltConsulta) {
            txtUltConsulta.textContent = 'Sin consultas';
            txtUltConsulta.classList.remove('consulta-link');
            txtUltConsulta.style.cursor = 'default';
            txtUltConsulta.style.color = '';
            txtUltConsulta.title = '';
            
            if (this._showHistoryHandler) {
                txtUltConsulta.removeEventListener('click', this._showHistoryHandler);
            }
        }
        
        // Limpiar tabla de consultas
        const tableContainer = document.querySelector('#tabla-consultas tbody');
        if (tableContainer) {
            tableContainer.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Seleccione un paciente para ver su historial</td></tr>';
        }
        
        // Limpiar panel de información
        this.clearPatientPanel();
        
        // Limpiar estado
        this.consultasManager.state.currentPatient = null;
        
        // Limpiar timeline
        const timelineContainer = document.getElementById('timeline');
        if (timelineContainer) {
            timelineContainer.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> Seleccione un paciente para ver su timeline de consultas</div>';
        }
        
        // Disparar evento
        document.dispatchEvent(new CustomEvent('patientCleared'));
    }
    
    /**
     * Limpiar panel de información del paciente
     */
    clearPatientPanel() {
        const elements = {
            'profile-username': '',
            'profile-ci': '',
            'txtCantConsulta': '0',
            'txtUltConsulta': 'Sin consultas',
            'cuota-valor': '0'
        };
        
        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = elements[id];
                element.classList.remove('consulta-link');
            }
        });
    }
    
    /**
     * Abrir modal para nueva persona
     */
    openNewPatientModal() {
        // Implementar modal de nueva persona
        const modal = document.getElementById('modalAgregarPersonas');
        if (modal) {
            $(modal).modal('show');
        }
    }
    
    /**
     * Utilities
     */
    calculateAge(birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        
        return age;
    }
    
    truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }
    
    /**
     * Mostrar modal genérico
     */
    showModal(title, content, onShown = null) {
        // Implementar sistema de modal genérico
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                html: content,
                width: '80%',
                showConfirmButton: false,
                showCloseButton: true,
                didOpen: () => {
                    if (onShown) onShown();
                }
            });
        }
    }
    
    closeModal() {
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
    }
}

// ================================
// SINGLETON INSTANCE
// ================================

// Instancia singleton del PatientManager
PatientManager.instance = null;

/**
 * Obtener instancia singleton del PatientManager
 */
PatientManager.getInstance = function(consultasManager = null) {
    if (!PatientManager.instance) {
        PatientManager.instance = new PatientManager(consultasManager);
    }
    return PatientManager.instance;
};

// Hacer la clase disponible globalmente
window.PatientManager = PatientManager;

// Exportar
window.PatientManager = PatientManager;
