/**
 * ============================================
 * CONSULTAS MANAGER - CONTROLADOR CENTRAL
 * ============================================
 * 
 * Sistema centralizado para gestionar todas las operaciones
 * del módulo de consultas de manera escalable y mantenible.
 * 
 * @version 2.0.0
 * @author Sistema Clinica
 * @created 2025-01-17
 */

class ConsultasManager {
    constructor() {
        // Estado central de la aplicación
        this.state = {
            currentFormType: 'general',
            currentPatient: null,
            currentConsulta: null,
            isLoading: false,
            isEditing: false,
            hasUnsavedChanges: false,
            components: {}
        };
        
        // Configuración de tipos de formularios
        this.formTypes = {
            general: {
                title: 'Consulta General',
                icon: 'fas fa-notes-medical',
                component: 'GeneralForm',
                endpoint: 'ajax/guardar-consulta.ajax.php'
            },
            anteojos: {
                title: 'Anteojos',
                icon: 'fas fa-glasses',
                component: 'AnteojosFormComponent',
                endpoint: 'ajax/guardar-consulta-anteojos.php'
            },
            estudios: {
                title: 'Estudios Médicos',
                icon: 'fas fa-x-ray',
                component: 'EstudiosFormComponent',
                endpoint: 'ajax/guardar-consulta-estudios.php'
            },
            informe_imagen: {
                title: 'Informe + Imagen',
                icon: 'fas fa-images',
                component: 'InformeImagenFormComponent',
                endpoint: 'ajax/guardar-consulta-informe-imagen.php'
            }
        };
        
        // Event listeners centralizados
        this.eventHandlers = new Map();
        
        // Sistema de notificaciones
        this.notifications = new NotificationSystem();
        
        // Inicialización
        this.init();
    }
    
    /**
     * Inicialización del sistema
     */
    async init() {
        console.log('🚀 Inicializando ConsultasManager...');
        
        try {
            // 1. Configurar estado inicial
            await this.setupInitialState();
            
            // 2. Registrar event listeners globales
            this.setupGlobalEventListeners();
            
            // 3. Inicializar componentes base
            await this.initializeBaseComponents();
            
            // 4. Configurar sistema de navegación
            this.setupNavigation();
            
            // 5. Configurar auto-guardado
            this.setupAutoSave();
            
            console.log('✅ ConsultasManager inicializado correctamente');
            
            // Procesar parámetros URL si existen
            await this.processUrlParameters();
            
            // Mostrar el formulario inicial
            await this.showForm(this.state.currentFormType);
            
            // DEBUGGING: Exponer manager globalmente
            if (window.DEBUG_MODE || window.location.href.includes('debug')) {
                window.consultasManager = this;
                window.testPreformatos = async () => {
                    console.log('🧪 TEST MANUAL: Forzando recarga de preformatos...');
                    const generalComponent = this.formComponents.get('general');
                    const anteojosComponent = this.formComponents.get('anteojos');
                    
                    if (generalComponent && generalComponent.forceReloadPreformatos) {
                        console.log('🔄 Recargando preformatos GENERAL...');
                        await generalComponent.forceReloadPreformatos();
                    }
                    
                    if (anteojosComponent && anteojosComponent.loadPreformatos) {
                        console.log('🔄 Recargando preformatos ANTEOJOS...');
                        await anteojosComponent.loadPreformatos();
                    }
                    
                    console.log('✅ Test completado. Revisa los selects en ambos formularios');
                };
                window.testPreformatoFill = (selectId = 'formatoConsulta', optionIndex = 1) => {
                    console.log(`🧪 TEST FILL: Aplicando preformato ${optionIndex} del select ${selectId}`);
                    const currentType = this.state.currentFormType;
                    const component = this.formComponents.get(currentType);
                    if (component && component.testPreformatoApplication) {
                        component.testPreformatoApplication(selectId, optionIndex);
                    } else if (component && component.applyPreformato) {
                        // Para componentes que no tienen testPreformatoApplication pero sí applyPreformato
                        const select = document.getElementById(selectId);
                        if (select && select.options[optionIndex]) {
                            select.selectedIndex = optionIndex;
                            select.value = select.options[optionIndex].value;
                            select.dispatchEvent(new Event('change'));
                        }
                    } else {
                        console.error('❌ Componente no disponible o sin métodos de preformatos');
                    }
                };
                console.log('🐛 Manager expuesto globalmente como window.consultasManager');
                console.log('🧪 Función de test disponible como window.testPreformatos()');
                console.log('📝 Función de test de relleno como window.testPreformatoFill("formatoConsulta", 1)');
            }

        } catch (error) {
            console.error('❌ Error inicializando ConsultasManager:', error);
            this.notifications.error('Error al inicializar el sistema');
        }
    }
    
    /**
     * Configurar estado inicial basado en URL y localStorage
     */
    async setupInitialState() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Determinar tipo de formulario - priorizar URL sobre localStorage
        let formType = urlParams.get('form_type');
        if (!formType) {
            // Solo usar localStorage si no hay parámetro de URL
            formType = localStorage.getItem('consultas_last_form_type') || 'general';
        }
        
        this.state.currentFormType = this.formTypes[formType] ? formType : 'general';
        
        // Si no hay parámetro de URL específico, usar general como default
        if (!urlParams.get('form_type') && !urlParams.get('consulta_id')) {
            this.state.currentFormType = 'general';
        }
        
        // Información de paciente si existe
        const pacienteId = urlParams.get('paciente_id');
        if (pacienteId) {
            await this.loadPatient(pacienteId);
        }
        
        // Información de consulta si existe
        const consultaId = urlParams.get('consulta_id');
        if (consultaId) {
            await this.loadConsulta(consultaId);
        }
        
        console.log('📊 Estado inicial configurado:', this.state);
    }
    
    /**
     * Inicializar componentes base del sistema
     */
    async initializeBaseComponents() {
        console.log('🔧 Inicializando componentes base...');
        
        // Registrar componentes de formulario
        this.formComponents = new Map();
        this.formComponents.set('general', new GeneralForm(this)); // Pasar referencia del manager
        this.formComponents.set('anteojos', new AnteojosFormComponent(this));
        this.formComponents.set('estudios', new EstudiosFormComponent(this));
        this.formComponents.set('informe_imagen', new InformeImagenFormComponent(this));
        
        // Inicializar cada componente
        for (const [type, component] of this.formComponents) {
            try {
                await component.initialize();
                console.log(`✅ Componente ${type} inicializado`);
                
                // CARGA ESPECIAL DE PREFORMATOS PARA GENERAL
                if (type === 'general' && component.loadPreformatos) {
                    console.log('🔄 Cargando preformatos filtrados para general...');
                    setTimeout(async () => {
                        await component.loadPreformatos();
                    }, 1500); // Delay para asegurar inicialización completa
                }
                
                // CARGA ESPECIAL DE PREFORMATOS PARA ANTEOJOS
                if (type === 'anteojos' && component.loadPreformatos) {
                    console.log('🔄 Cargando preformatos filtrados para anteojos...');
                    setTimeout(async () => {
                        await component.loadPreformatos();
                    }, 1500); // Delay para asegurar inicialización completa
                }
            } catch (error) {
                console.warn(`⚠️ Error inicializando componente ${type}:`, error);
            }
        }
        
        // Configurar eventos de formularios
        this.setupFormEventListeners();
        
        // Cargar preformatos y motivos comunes
        await Promise.all([
            this.loadMotivosComunes(),
            this.loadPreformatos()
        ]);
        
        console.log('✅ Componentes base inicializados');
    }
    
    /**
     * Configurar event listeners globales
     */
    setupGlobalEventListeners() {
        // Prevenir pérdida de datos
        window.addEventListener('beforeunload', (e) => {
            if (this.state.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
                return 'Tienes cambios sin guardar. ¿Estás seguro de salir?';
            }
        });
        
        // Manejo de teclas rápidas
        document.addEventListener('keydown', (e) => {
            // Ctrl+S para guardar
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                this.saveCurrentForm();
            }
            
            // Ctrl+N para nueva consulta
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                this.newConsulta();
            }
            
            // Esc para limpiar formulario
            if (e.key === 'Escape') {
                this.clearForm();
            }
        });
        
        // Sistema de notificaciones en tiempo real
        document.addEventListener('consultaUpdated', (e) => {
            console.log('📬 Consulta actualizada:', e.detail);
            this.refreshConsultasList();
        });
    }
    
    /**
     * Alias para cambiar tipo de formulario (compatibilidad)
     */
    async switchFormType(newType) {
        return await this.changeFormType(newType);
    }
    
    /**
     * Cambiar tipo de formulario dinámicamente
     */
    async changeFormType(newType) {
        if (!this.formTypes[newType]) {
            throw new Error(`Tipo de formulario inválido: ${newType}`);
        }
        
        // Prevenir cambios duplicados
        if (this.state.currentFormType === newType) {
            console.log(`🔄 Formulario ${newType} ya está activo`);
            return;
        }
        
        console.log(`🔄 Cambiando formulario: ${this.state.currentFormType} → ${newType}`);
        
        // Verificar cambios pendientes
        if (this.state.hasUnsavedChanges) {
            const confirmed = await this.confirmUnsavedChanges();
            if (!confirmed) return;
        }
        
        // Guardar estado anterior
        const oldType = this.state.currentFormType;
        
        try {
            // Mostrar loading
            this.setLoading(true, 'Cambiando formulario...');
            
            // Actualizar estado
            this.state.currentFormType = newType;
            
            // Cargar componente si no existe
            if (!this.state.components[newType]) {
                await this.loadFormComponent(newType);
            }
            
            // Mostrar nuevo formulario
            await this.showForm(newType);
            
            // Actualizar URL sin recargar página
            this.updateUrl();
            
            // Guardar preferencia
            localStorage.setItem('consultas_last_form_type', newType);
            
            // Notificar cambio (solo una vez)
            if (!this._notificationSent || this._notificationSent !== newType) {
                this.notifications.success(`Formulario cambiado a ${this.formTypes[newType].title}`);
                this._notificationSent = newType;
                
                // Limpiar flag después de un tiempo
                setTimeout(() => {
                    this._notificationSent = null;
                }, 1000);
            }
            
            // Disparar evento
            document.dispatchEvent(new CustomEvent('formTypeChanged', {
                detail: { oldType, newType }
            }));
            
        } catch (error) {
            console.error('❌ Error cambiando tipo de formulario:', error);
            this.notifications.error('Error al cambiar formulario');
            
            // Revertir estado
            this.state.currentFormType = oldType;
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Cargar componente de formulario dinámicamente
     */
    async loadFormComponent(formType) {
        const config = this.formTypes[formType];
        
        try {
            // Cargar el componente
            const ComponentClass = window[config.component];
            if (!ComponentClass) {
                throw new Error(`Componente ${config.component} no encontrado`);
            }
            
            // Instanciar componente
            this.state.components[formType] = new ComponentClass(this);
            
            console.log(`✅ Componente ${formType} cargado`);
            
        } catch (error) {
            console.error(`❌ Error cargando componente ${formType}:`, error);
            throw error;
        }
    }
    
    /**
     * Mostrar formulario específico
     */
    async showForm(formType) {
        console.log(`👁️ Mostrando formulario: ${formType}`);
        
        const container = document.getElementById(`formulario-${formType}`);
        if (!container) {
            throw new Error(`Contenedor para formulario ${formType} no encontrado`);
        }
        
        // Ocultar todos los formularios primero
        this.hideAllForms();
        
        // Mostrar contenedor específico
        container.style.display = 'block';
        container.classList.add('active');
        
        // Actualizar estado visual de los tabs
        this.updateTabsVisualState(formType);
        
        // Inicializar componente si es necesario
        if (this.state.components[formType]) {
            await this.state.components[formType].show();
        }
        
        // Asegurar que los motivos comunes estén poblados
        this.populateMotivosComunes();
        
        // FORZAR RECARGA DE PREFORMATOS para el formulario activo
        setTimeout(async () => {
            if (formType === 'anteojos') {
                // Para anteojos, usar el sistema original que ya funciona con IDs específicos
                console.log(`👓 Cargando preformatos de anteojos usando sistema original...`);
                if (typeof window.cargarPreformatosConsulta === 'function') {
                    window.cargarPreformatosConsulta('anteojos');
                }
                if (typeof window.cargarPreformatosReceta === 'function') {
                    window.cargarPreformatosReceta('anteojos');
                }
                // También usar el sistema sin duplicados con IDs específicos
                if (typeof cargarPreformatosSinDuplicados === 'function') {
                    cargarPreformatosSinDuplicados('consulta', 'anteojos', 'formatoConsulta-anteojos');
                    cargarPreformatosSinDuplicados('receta', 'anteojos', 'formatoreceta-anteojos');
                }
                
                // CARGAR REFERENCIALES DINÁMICOS
                console.log(`⚙️ Cargando referenciales dinámicos para anteojos...`);
                const anteojosComponent = this.state.components['anteojos'];
                if (anteojosComponent && anteojosComponent.loadReferenciales) {
                    setTimeout(() => {
                        anteojosComponent.loadReferenciales();
                    }, 1000); // Delay para asegurar que el DOM esté listo
                }
            } else if (formType === 'estudios') {
                // Para estudios, usar el sistema sin duplicados con IDs específicos
                console.log(`🔬 Cargando preformatos de estudios usando IDs específicos...`);
                if (typeof cargarPreformatosSinDuplicados === 'function') {
                    cargarPreformatosSinDuplicados('consulta', 'estudios', 'formatoConsulta-estudios');
                }
                // También cargar con sistema original si existe
                if (typeof window.cargarPreformatosConsulta === 'function') {
                    window.cargarPreformatosConsulta('estudios');
                }
            } else if (formType === 'informe_imagen') {
                // Para informe imagen, usar el sistema sin duplicados con IDs específicos
                console.log(`🖼️ Cargando preformatos de informe imagen usando IDs específicos...`);
                if (typeof cargarPreformatosSinDuplicados === 'function') {
                    cargarPreformatosSinDuplicados('consulta', 'informe_imagen', 'formatoConsulta-informe-imagen');
                }
                // También cargar con sistema original si existe
                if (typeof window.cargarPreformatosConsulta === 'function') {
                    window.cargarPreformatosConsulta('informe_imagen');
                }
            } else {
                // Para otros formularios, usar el sistema nuevo
                const component = this.formComponents.get(formType);
                if (component && component.loadPreformatos) {
                    console.log(`🔄 Recargando preformatos para formulario ${formType}...`);
                    await component.loadPreformatos();
                }
            }
        }, 500); // Delay un poco más largo para anteojos
        
        // Actualizar título
        this.updateTitle(formType);
        
        // Focus en primer campo
        const firstInput = container.querySelector('input[type="text"], textarea, select');
        if (firstInput && !firstInput.disabled) {
            setTimeout(() => firstInput.focus(), 100);
        }
        
        console.log(`✅ Formulario ${formType} mostrado`);
    }
    
    /**
     * Ocultar todos los formularios
     */
    hideAllForms() {
        document.querySelectorAll('.formulario-especifico').forEach(form => {
            form.style.display = 'none';
            form.classList.remove('active');
        });
    }
    
    /**
     * Actualizar estado visual de los tabs
     */
    updateTabsVisualState(activeFormType) {
        // Remover clase active de todos los tabs
        document.querySelectorAll('.form-type-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Agregar clase active al tab seleccionado
        const activeTab = document.querySelector(`[data-form-type="${activeFormType}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }
        
        console.log(`🎨 Estado visual de tabs actualizado para: ${activeFormType}`);
    }
    
    /**
     * Ocultar todos los formularios
     */
    hideAllForms() {
        document.querySelectorAll('.formulario-especifico').forEach(form => {
            form.style.display = 'none';
            form.classList.remove('active');
        });
    }
    
    /**
     * Actualizar estado visual de los tabs
     */
    updateTabsVisualState(activeFormType) {
        // Remover clase active de todos los tabs
        document.querySelectorAll('.form-type-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Agregar clase active al tab seleccionado
        const activeTab = document.querySelector(`[data-form-type="${activeFormType}"]`);
        if (activeTab) {
            activeTab.classList.add('active');
        }
        
        console.log(`🎨 Estado visual de tabs actualizado para: ${activeFormType}`);
    }
    
    /**
     * Actualizar título del formulario
     */
    updateTitle(formType) {
        const title = this.formTypes[formType]?.title || 'Formulario';
        
        // Actualizar título en el header si existe
        const titleElement = document.querySelector('.form-title, .formulario-title, h2');
        if (titleElement) {
            titleElement.textContent = title;
        }
        
        // Actualizar título de la página
        if (document.title.includes('Consultas')) {
            document.title = `${title} - Consultas Médicas`;
        }
    }
    
    /**
     * Cargar datos de paciente
     */
    async loadPatient(patientId) {
        try {
            this.setLoading(true, 'Cargando datos del paciente...');
            
            const response = await this.apiCall('ajax/persona.ajax.php', {
                operacion: 'getPersonById',
                idPersona: patientId
            });
            
            if (response.status === 'success') {
                this.state.currentPatient = response.data;
                this.updatePatientInfo(response.data);
                console.log('👤 Paciente cargado:', response.data);
            }
            
        } catch (error) {
            console.error('❌ Error cargando paciente:', error);
            this.notifications.error('Error al cargar datos del paciente');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Cargar consulta específica
     */
    async loadConsulta(consultaId) {
        try {
            this.setLoading(true, 'Cargando consulta...');
            
            const response = await this.apiCall('ajax/consultas.ajax.php', {
                operacion: 'detalleConsulta',
                id_consulta: consultaId
            });
            
            if (response && response.id_consulta) {
                this.state.currentConsulta = response;
                this.state.isEditing = true;
                
                // Cambiar formulario si es necesario
                if (response.tipo_formulario && response.tipo_formulario !== this.state.currentFormType) {
                    await this.changeFormType(response.tipo_formulario);
                }
                
                // Cargar datos en formulario
                await this.loadDataIntoForm(response);
                
                console.log('📋 Consulta cargada:', response);
            }
            
        } catch (error) {
            console.error('❌ Error cargando consulta:', error);
            this.notifications.error('Error al cargar la consulta');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Guardar formulario actual
     */
    async saveCurrentForm() {
        const formType = this.state.currentFormType;
        const component = this.state.components[formType];
        
        if (!component) {
            throw new Error(`Componente ${formType} no encontrado`);
        }
        
        try {
            this.setLoading(true, 'Guardando consulta...');
            
            // Obtener datos del formulario
            const formData = await component.getFormData();
            
            // Validar datos
            const validation = await component.validateData(formData);
            if (!validation.valid) {
                throw new Error(validation.message);
            }
            
            // Guardar
            const result = await this.saveConsulta(formData, formType);
            
            if (result.success) {
                this.state.hasUnsavedChanges = false;
                this.state.currentConsulta = result.data;
                
                this.notifications.success(
                    this.state.isEditing ? 'Consulta actualizada' : 'Consulta guardada'
                );
                
                // Actualizar lista de consultas
                this.refreshConsultasList();
                
                // Disparar evento
                document.dispatchEvent(new CustomEvent('consultaSaved', {
                    detail: result.data
                }));
            }
            
        } catch (error) {
            console.error('❌ Error guardando consulta:', error);
            this.notifications.error('Error al guardar: ' + error.message);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Realizar llamada a la API de manera consistente
     */
    async apiCall(endpoint, data) {
        const formData = new FormData();
        
        // Agregar datos
        Object.keys(data).forEach(key => {
            if (data[key] !== null && data[key] !== undefined) {
                formData.append(key, data[key]);
            }
        });
        
        // Agregar ID de usuario si está disponible
        const userId = document.body.getAttribute('data-user-id');
        if (userId) {
            formData.append('id_user', userId);
        }
        
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const text = await response.text();
        
        try {
            return JSON.parse(text);
        } catch (e) {
            // Si no es JSON válido, devolver como texto
            return { raw: text, status: text.includes('ok') ? 'success' : 'error' };
        }
    }
    
    /**
     * Sistema de notificaciones mejorado
     */
    setLoading(isLoading, message = '') {
        this.state.isLoading = isLoading;
        
        if (isLoading) {
            // Mostrar loading overlay
            this.showLoadingOverlay(message);
        } else {
            // Ocultar loading overlay
            this.hideLoadingOverlay();
        }
    }

    /**
     * Confirmar cambios no guardados antes de cambiar de formulario
     */
    async confirmUnsavedChanges() {
        return new Promise((resolve) => {
            try {
                // Usar alertify si está disponible, sino usar confirm nativo
                if (typeof alertify !== 'undefined' && alertify.confirm) {
                    alertify.confirm(
                        'Cambios sin guardar',
                        'Hay cambios sin guardar. ¿Deseas continuar sin guardar?',
                        function() { resolve(true); },   // OK
                        function() { resolve(false); }   // Cancel
                    );
                } else {
                    // Fallback a confirm nativo
                    const confirmed = confirm('Hay cambios sin guardar. ¿Deseas continuar sin guardar?');
                    resolve(confirmed);
                }
            } catch (error) {
                console.error('❌ Error en confirmUnsavedChanges:', error);
                // En caso de error, permitir el cambio
                resolve(true);
            }
        });
    }

    /**
     * Mostrar overlay de carga
     */
    showLoadingOverlay(message = 'Cargando...') {
        // Crear o actualizar overlay
        let overlay = document.getElementById('loading-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                font-family: Arial, sans-serif;
            `;
            
            overlay.innerHTML = `
                <div style="
                    background: white;
                    padding: 20px 30px;
                    border-radius: 8px;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                ">
                    <div style="
                        border: 3px solid #f3f3f3;
                        border-top: 3px solid #007bff;
                        border-radius: 50%;
                        width: 30px;
                        height: 30px;
                        animation: spin 1s linear infinite;
                        margin: 0 auto 15px auto;
                    "></div>
                    <div id="loading-message">${message}</div>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                </div>
            `;
            
            document.body.appendChild(overlay);
        } else {
            const messageEl = overlay.querySelector('#loading-message');
            if (messageEl) {
                messageEl.textContent = message;
            }
            overlay.style.display = 'flex';
        }
    }
    
    /**
     * Ocultar overlay de carga
     */
    hideLoadingOverlay() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    /**
     * Actualizar URL sin recargar página
     */
    updateUrl() {
        const url = new URL(window.location);
        url.searchParams.set('form_type', this.state.currentFormType);
        
        if (this.state.currentPatient) {
            url.searchParams.set('paciente_id', this.state.currentPatient.id_persona);
        }
        
        if (this.state.currentConsulta && this.state.isEditing) {
            url.searchParams.set('consulta_id', this.state.currentConsulta.id_consulta);
        }
        
        window.history.pushState(null, '', url);
    }
    
    /**
     * Configurar event listeners de formularios
     */
    setupFormEventListeners() {
        // Event listeners para cambios de estado
        document.addEventListener('consulta:saved', (e) => {
            this.handleConsultaUpdate(e.detail);
        });
        
        document.addEventListener('consulta:deleted', (e) => {
            this.handleConsultaDelete(e.detail);
        });
        
        document.addEventListener('formType:changed', (e) => {
            this.switchFormType(e.detail.type);
        });
    }
    
    /**
     * Cargar motivos comunes
     */
    async loadMotivosComunes() {
        try {
            // Cargar motivos comunes para todos los tipos de formulario
            const formTypes = ['general', 'anteojos', 'estudios', 'informe_imagen'];
            const motivosPromises = formTypes.map(async (tipo) => {
                const response = await fetch(`modules/consultas/api/consultas-api.php?action=getMotivosComunes&tipo_formulario=${tipo}`);
                const data = await response.json();
                return {
                    tipo,
                    motivos: data.success ? data.data : []
                };
            });
            
            const motivosResults = await Promise.all(motivosPromises);
            
            // Organizar motivos por tipo
            this.state.motivosPorTipo = {};
            motivosResults.forEach(result => {
                this.state.motivosPorTipo[result.tipo] = result.motivos;
            });
            
            // Mantener compatibilidad con el estado anterior (usar motivos generales)
            this.state.motivosComunes = this.state.motivosPorTipo.general || [];
            
            // Poblar todos los selects de motivos comunes
            this.populateMotivosComunes();
        } catch (error) {
            console.warn('Error cargando motivos comunes:', error);
            this.state.motivosComunes = [];
            this.state.motivosPorTipo = {};
        }
    }
    
    /**
     * Poblar todos los selects de motivos comunes
     */
    populateMotivosComunes() {
        console.log('🎯 Poblando motivos comunes en todos los formularios...');
        
        const selectConfig = [
            { id: 'motivoscomunes', tipo: 'general' },
            { id: 'motivoscomunes-anteojos', tipo: 'anteojos' }, 
            { id: 'motivoscomunes-estudios', tipo: 'estudios' },
            { id: 'motivoscomunes-informe-imagen', tipo: 'informe_imagen' }
        ];
        
        selectConfig.forEach(config => {
            const select = document.getElementById(config.id);
            if (select) {
                // Obtener motivos para este tipo específico o usar generales como fallback
                const motivos = this.state.motivosPorTipo?.[config.tipo] || this.state.motivosPorTipo?.general || [];
                
                // Limpiar opciones existentes (excepto la primera)
                const firstOption = select.querySelector('option');
                select.innerHTML = '';
                if (firstOption) {
                    select.appendChild(firstOption);
                }
                
                // Agregar opciones de motivos comunes
                motivos.forEach(motivo => {
                    const option = document.createElement('option');
                    option.value = motivo.descripcion || motivo.nombre || motivo.text || motivo;
                    option.textContent = motivo.descripcion || motivo.nombre || motivo.text || motivo;
                    select.appendChild(option);
                });
                
                console.log(`✅ ${motivos.length} motivos del tipo "${config.tipo}" cargados en ${config.id}`);
                
                // Si es Select2, refrescar
                if (select.classList.contains('select2bs4') || select.classList.contains('select2-hidden-accessible')) {
                    if (typeof $ !== 'undefined' && $(select).data('select2')) {
                        $(select).trigger('change.select2');
                    }
                }
            } else {
                console.warn(`⚠️ Select ${config.id} no encontrado`);
            }
        });
    }
    
    /**
     * Cargar preformatos
     */
    async loadPreformatos() {
        try {
            const response = await fetch('modules/consultas/api/consultas-api.php?action=getPreformatos');
            const data = await response.json();
            this.state.preformatos = data.success ? data.data : [];
        } catch (error) {
            console.warn('Error cargando preformatos:', error);
            this.state.preformatos = [];
        }
    }
    
    /**
     * Manejar actualización de consulta
     */
    handleConsultaUpdate(consultaData) {
        console.log('📝 Consulta actualizada:', consultaData);
        // Actualizar estado local y UI si es necesario
        this.state.lastUpdate = new Date();
    }
    
    /**
     * Manejar eliminación de consulta
     */
    handleConsultaDelete(consultaId) {
        console.log('🗑️ Consulta eliminada:', consultaId);
        // Limpiar formulario si era la consulta activa
        if (this.state.activeConsultaId === consultaId) {
            this.clearActiveConsulta();
        }
    }
    
    /**
     * Limpiar consulta activa
     */
    clearActiveConsulta() {
        this.state.activeConsultaId = null;
        this.state.formMode = 'create';
        
        // Limpiar formularios
        const forms = document.querySelectorAll('#consultas-panel form');
        forms.forEach(form => {
            if (form.reset) form.reset();
        });
        
        // Emitir evento de limpieza
        document.dispatchEvent(new CustomEvent('consulta:cleared'));
    }
    
    /**
     * Configurar sistema de navegación
     */
    setupNavigation() {
        console.log('🧭 Configurando sistema de navegación...');
        
        // Configurar navegación entre formularios
        const formTypeButtons = document.querySelectorAll('[data-form-type]');
        formTypeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const formType = button.dataset.formType;
                this.switchFormType(formType);
            });
        });
        
        // Configurar breadcrumbs si existen
        this.updateBreadcrumbs();
        
        // Configurar navegación con teclado
        document.addEventListener('keydown', (e) => {
            // Alt + 1-4 para cambiar entre formularios
            if (e.altKey && e.key >= '1' && e.key <= '4') {
                e.preventDefault();
                const formTypes = ['general', 'anteojos', 'estudios', 'informe_imagen'];
                const index = parseInt(e.key) - 1;
                if (formTypes[index]) {
                    this.switchFormType(formTypes[index]);
                }
            }
        });
        
        console.log('✅ Sistema de navegación configurado');
    }
    
    /**
     * Configurar auto-guardado
     */
    setupAutoSave() {
        console.log('💾 Configurando sistema de auto-guardado...');
        
        // Auto-guardado cada 30 segundos si hay cambios
        this.autoSaveInterval = setInterval(() => {
            if (this.state.hasUnsavedChanges && !this.state.isLoading) {
                console.log('💾 Ejecutando auto-guardado...');
                this.autoSave();
            }
        }, 30000); // 30 segundos
        
        // Detectar cambios en formularios
        document.addEventListener('input', (e) => {
            if (e.target.closest('.consulta-form')) {
                this.state.hasUnsavedChanges = true;
                this.state.lastEditTime = new Date();
            }
        });
        
        // Detectar cambios en select
        document.addEventListener('change', (e) => {
            if (e.target.closest('.consulta-form')) {
                this.state.hasUnsavedChanges = true;
                this.state.lastEditTime = new Date();
            }
        });
        
        console.log('✅ Sistema de auto-guardado configurado');
    }
    
    /**
     * Ejecutar auto-guardado
     */
    async autoSave() {
        try {
            const activeForm = document.querySelector('.consulta-form.active');
            if (!activeForm) return;
            
            console.log('💾 Guardando automáticamente...');
            await this.saveCurrentForm(true); // true = auto-guardado silencioso
            
            this.state.hasUnsavedChanges = false;
            this.state.lastSaveTime = new Date();
            
            // Mostrar indicador de guardado
            this.showAutoSaveIndicator();
            
        } catch (error) {
            console.warn('⚠️ Error en auto-guardado:', error);
        }
    }
    
    /**
     * Mostrar indicador de auto-guardado
     */
    showAutoSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'auto-save-indicator';
        indicator.innerHTML = '💾 Guardado automático';
        indicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 9999;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s;
        `;
        
        document.body.appendChild(indicator);
        
        // Animación de aparición
        requestAnimationFrame(() => {
            indicator.style.opacity = '1';
        });
        
        // Remover después de 3 segundos
        setTimeout(() => {
            indicator.style.opacity = '0';
            setTimeout(() => {
                if (indicator.parentNode) {
                    indicator.parentNode.removeChild(indicator);
                }
            }, 300);
        }, 3000);
    }
    
    /**
     * Actualizar breadcrumbs de navegación
     */
    updateBreadcrumbs() {
        const breadcrumbContainer = document.querySelector('.breadcrumb');
        if (!breadcrumbContainer) return;
        
        const formTypeNames = {
            general: 'Consulta General',
            anteojos: 'Prescripción de Anteojos',
            estudios: 'Estudios Médicos',
            informe_imagen: 'Informe por Imagen'
        };
        
        const currentFormName = formTypeNames[this.state.currentFormType] || 'Consulta';
        
        breadcrumbContainer.innerHTML = `
            <li class="breadcrumb-item"><a href="#" onclick="window.history.back()">Consultas</a></li>
            <li class="breadcrumb-item active">${currentFormName}</li>
        `;
    }
    
    /**
     * Procesar parámetros de URL
     */
    async processUrlParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Verificar si hay un tipo de formulario especificado
        const formType = urlParams.get('form_type');
        if (formType && this.formTypes[formType]) {
            await this.switchFormType(formType);
        }
        
        // Verificar si hay un ID de consulta especificado
        const consultaId = urlParams.get('consulta_id');
        if (consultaId) {
            await this.loadConsulta(consultaId);
        }
        
        // Verificar si hay un ID de paciente especificado
        const patientId = urlParams.get('patient_id');
        if (patientId) {
            await this.selectPatient(patientId);
        }
        
        console.log('🔗 Parámetros de URL procesados');
    }
    
    /**
     * Cargar consulta específica
     */
    async loadConsulta(consultaId) {
        try {
            console.log(`📋 Cargando consulta ID: ${consultaId}`);
            
            const response = await fetch(`modules/consultas/api/consultas-api.php?action=getConsulta&id=${consultaId}`);
            const data = await response.json();
            
            if (data.success && data.data) {
                this.state.currentConsulta = data.data;
                this.state.formMode = 'edit';
                
                // Cargar datos en el formulario
                this.populateForm(data.data);
                
                console.log('✅ Consulta cargada:', data.data);
            } else {
                console.warn('⚠️ Consulta no encontrada:', consultaId);
            }
            
        } catch (error) {
            console.error('❌ Error cargando consulta:', error);
        }
    }
    
    /**
     * Seleccionar paciente
     */
    async selectPatient(patientId) {
        try {
            const patientManager = PatientManager.getInstance();
            await patientManager.selectPatient(patientId);
            
            console.log(`👤 Paciente seleccionado: ${patientId}`);
            
        } catch (error) {
            console.error('❌ Error seleccionando paciente:', error);
        }
    }
    
    /**
     * Poblar formulario con datos
     */
    populateForm(data) {
        // Implementar lógica para poblar formulario con datos de consulta
        const activeForm = document.querySelector('.consulta-form.active');
        if (!activeForm) return;
        
        Object.keys(data).forEach(key => {
            const field = activeForm.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = data[key] || '';
            }
        });
        
        console.log('📝 Formulario poblado con datos');
    }
    
    /**
     * Obtener instancia única (Singleton)
     */
    static getInstance() {
        if (!ConsultasManager.instance) {
            ConsultasManager.instance = new ConsultasManager();
        }
        return ConsultasManager.instance;
    }
}

/**
 * ============================================
 * SISTEMA DE NOTIFICACIONES
 * ============================================
 */
class NotificationSystem {
    constructor() {
        this.container = this.createContainer();
    }
    
    createContainer() {
        let container = document.getElementById('notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifications-container';
            container.className = 'notifications-container';
            document.body.appendChild(container);
        }
        return container;
    }
    
    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${this.getIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        // Agregar al contenedor
        this.container.appendChild(notification);
        
        // Animación de entrada
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Auto-ocultar
        if (duration > 0) {
            setTimeout(() => this.hide(notification), duration);
        }
        
        // Evento de cerrar
        notification.querySelector('.notification-close').addEventListener('click', () => {
            this.hide(notification);
        });
        
        return notification;
    }
    
    success(message) {
        return this.show(message, 'success');
    }
    
    error(message) {
        return this.show(message, 'error', 0); // No auto-ocultar errores
    }
    
    warning(message) {
        return this.show(message, 'warning');
    }
    
    info(message) {
        return this.show(message, 'info');
    }
    
    hide(notification) {
        notification.classList.add('hide');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
    
    getIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Exportar para uso global
window.ConsultasManager = ConsultasManager;
window.NotificationSystem = NotificationSystem;
