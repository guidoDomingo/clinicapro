/**
 * ============================================
 * COMPONENTES BASE PARA FORMULARIOS
 * ============================================
 */

/**
 * Clase base para todos los componentes de formularios
 */
class BaseFormComponent {
    constructor(manager) {
        this.manager = manager;
        this.container = null;
        this.isInitialized = false;
        this.validators = [];
        this.eventListeners = new Map();
        this.preformatoContent = new Map(); // Para almacenar el contenido de preformatos
    }
    
    /**
     * Inicializar componente
     */
    async init() {
        if (this.isInitialized) return;
        
        console.log(`🔧 Inicializando componente ${this.constructor.name}...`);
        
        // Encontrar contenedor
        this.container = document.getElementById(`formulario-${this.getFormType()}`);
        if (!this.container) {
            throw new Error(`Contenedor para ${this.getFormType()} no encontrado`);
        }
        
        // Configurar eventos
        this.setupEvents();
        
        // Inicializar campos específicos
        await this.initializeFields();
        
        this.isInitialized = true;
        console.log(`✅ Componente ${this.constructor.name} inicializado`);
    }
    
    /**
     * Obtener el user ID de forma consistente
     */
    getUserId() {
        return window.APP_CONFIG?.userId || null;
    }
    
    /**
     * Cargar preformatos genérico - puede ser sobrescrito por las clases hijas
     */
    async loadPreformatos() {
        const userId = this.getUserId();
        const formType = this.getFormType();
        
        console.log(`🔍 DEBUG ${this.constructor.name}.loadPreformatos()`, {formType, userId});
        
        if (!userId) {
            console.warn(`⚠️ No se pudo obtener el user ID para ${formType}`);
            return;
        }
        
        // Configuración por defecto que puede ser sobrescrita
        const preformatoConfig = this.getPreformatoConfig();
        
        for (const config of preformatoConfig) {
            await this.loadPreformatoType(config, userId, formType);
        }
    }
    
    /**
     * Configuración de preformatos - debe ser implementado por las clases hijas
     * Retorna un array de objetos con: {selectId, action, tipo, valueField, textField}
     */
    getPreformatoConfig() {
        return [];
    }
    
    /**
     * Cargar un tipo específico de preformato
     */
    async loadPreformatoType(config, userId, formType) {
        try {
            const requestData = {
                action: config.action,
                tipo_formulario: formType,
                tipo: config.tipo
            };
            
            if (userId) {
                requestData.usuario_id = userId;
            }
            
            console.log(`📤 Enviando request para ${config.tipo}:`, requestData);
            
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', requestData);
            
            console.log(`📥 Respuesta ${config.tipo}:`, response);
            console.log(`🔍 ANÁLISIS DETALLADO ${config.tipo}:`);
            console.log('   - success:', response.success);
            console.log('   - data tipo:', typeof response.data);
            console.log('   - data es array:', Array.isArray(response.data));
            console.log('   - data length:', response.data?.length);
            console.log('   - data contenido:', response.data);

            if (response.success === true && response.data && response.data.length > 0) {
                console.log(`✅ Poblando select ${config.selectId} con`, response.data.length, 'elementos');
                this.populateSelect(config.selectId, response.data, config.valueField, config.textField);
            } else {
                console.log(`❌ Sin preformatos para ${config.tipo} - Success:`, response.success, 'Data length:', response.data?.length);
            }
            
        } catch (error) {
            console.error(`❌ Error cargando preformatos de ${config.tipo}:`, error);
        }
    }
    
    /**
     * Poblar select genérico
     */
    populateSelect(selectId, data, valueField, textField) {
        console.log('🔧 populateSelect called:', {selectId, dataLength: data.length, valueField, textField});
        console.log('📊 Data received:', data);
        
        const select = document.getElementById(selectId);
        if (!select) {
            console.warn(`⚠️ Select ${selectId} no encontrado`);
            return;
        }
        
        console.log('🎯 Select element found:', select);
        console.log('🧹 LIMPIANDO COMPLETAMENTE el select (todas las opciones)...');
        
        // Limpiar completamente las opciones, manteniendo solo la primera (placeholder)
        const firstOption = select.firstElementChild;
        select.innerHTML = '';
        if (firstOption && (firstOption.value === '' || firstOption.value === '0')) {
            select.appendChild(firstOption);
        }
        
        console.log('➕ Adding', data.length, 'new options...');
        
        // Agregar nuevas opciones
        data.forEach((item, index) => {
            const option = document.createElement('option');
            option.value = item[valueField];
            option.textContent = item[textField];
            select.appendChild(option);
            
            console.log(`  Option ${index + 1}: ${item[textField]} (value: ${item[valueField]} )`);
            
            // Guardar contenido si existe
            if (item.contenido) {
                const key = `${selectId}-${item[valueField]}`;
                this.preformatoContent.set(key, item.contenido);
                console.log('    📝 Contenido guardado para', item[textField]);
            }
        });
        
        console.log('✅ PopulateSelect completed. Total options:', select.options.length);
        
        // Configurar eventos de change si no existen
        this.setupSelectChangeEvent(selectId);
    }
    
    /**
     * Configurar evento de cambio para select de preformatos
     */
    setupSelectChangeEvent(selectId) {
        const select = document.getElementById(selectId);
        if (!select || select.hasAttribute('data-preformato-event')) return;
        
        select.setAttribute('data-preformato-event', 'true');
        select.addEventListener('change', (e) => {
            if (e.target.value && e.target.value !== '0') {
                this.applyPreformato(selectId, e.target.value);
            }
        });
    }
    
    /**
     * Aplicar preformato genérico
     */
    applyPreformato(selectId, value) {
        console.log('🔄 applyPreformato(' + selectId + ', ' + value + ')');
        
        const key = `${selectId}-${value}`;
        const content = this.preformatoContent.get(key);
        
        if (!content) {
            console.log('📄 Sin contenido para:', key);
            return;
        }
        
        console.log('📄 Contenido encontrado: SÍ');
        
        // Determinar textarea objetivo basado en el selectId
        let targetTextarea = this.getTargetTextarea(selectId);
        
        if (!targetTextarea) {
            console.warn('⚠️ No se pudo determinar textarea objetivo para:', selectId);
            return;
        }
        
        console.log('🎯 Target textarea:', targetTextarea);
        
        // Aplicar contenido
        this.fillTextarea(targetTextarea, content);
    }
    
    /**
     * Obtener textarea objetivo basado en el selectId - puede ser sobrescrito
     */
    getTargetTextarea(selectId) {
        const mapping = {
            'formatoConsulta': 'consulta-textarea',
            'formatoreceta': 'receta-textarea',
            'formatoReceta': 'receta-textarea'
        };
        return mapping[selectId] || null;
    }
    
    /**
     * Llenar textarea con contenido
     */
    fillTextarea(textareaId, content) {
        console.log('📝 Rellenando textarea con contenido...');
        
        const textarea = document.getElementById(textareaId);
        if (textarea) {
            // Verificar si es Summernote
            if (typeof $(textarea).summernote === 'function' && $(textarea).hasClass('note-editable')) {
                console.log('🔤 Usando Summernote para', textareaId);
                $(textarea).summernote('code', content);
            } else if ($(textarea).next('.note-editor').length > 0) {
                console.log('🔤 Usando Summernote para', textareaId);
                $(textarea).summernote('code', content);
            } else {
                console.log('📝 Usando textarea normal para', textareaId);
                textarea.value = content;
            }
            console.log('✅ Contenido aplicado correctamente');
        } else {
            console.warn('⚠️ Textarea no encontrado:', textareaId);
        }
    }
    
    /**
     * Mostrar componente
     */
    async show() {
        if (!this.isInitialized) {
            await this.init();
        }
        
        // Lógica específica de mostrar
        await this.onShow();
    }
    
    /**
     * Obtener datos del formulario
     */
    async getFormData() {
        const formData = new FormData();
        
        // Datos básicos comunes
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            const element = this.container.querySelector(`#${field}`);
            if (element) {
                formData.append(field, element.value);
            }
        });
        
        // Datos específicos del formulario
        const specificData = await this.getSpecificData();
        Object.keys(specificData).forEach(key => {
            formData.append(key, specificData[key]);
        });
        
        // Agregar tipo de formulario
        formData.append('form_type', this.getFormType());
        
        // Agregar ID de paciente
        if (this.manager.state.currentPatient) {
            formData.append('idPersona', this.manager.state.currentPatient.id_persona);
        }
        
        // Agregar ID de consulta si está editando
        if (this.manager.state.isEditing && this.manager.state.currentConsulta) {
            formData.append('id_consulta', this.manager.state.currentConsulta.id_consulta);
        }
        
        return formData;
    }
    
    /**
     * Validar datos del formulario
     */
    async validateData(formData) {
        const errors = [];
        
        // Validaciones básicas
        if (!this.manager.state.currentPatient) {
            errors.push('Debe seleccionar un paciente');
        }
        
        // Validaciones específicas
        const specificValidation = await this.validateSpecificData(formData);
        if (!specificValidation.valid) {
            errors.push(...specificValidation.errors);
        }
        
        // Ejecutar validadores personalizados
        for (const validator of this.validators) {
            const result = await validator(formData);
            if (!result.valid) {
                errors.push(result.message);
            }
        }
        
        return {
            valid: errors.length === 0,
            errors,
            message: errors.join(', ')
        };
    }
    
    /**
     * Cargar datos en el formulario
     */
    async loadData(data) {
        console.log(`📥 Cargando datos en ${this.constructor.name}:`, data);
        
        // Cargar campos básicos
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            if (data[field] !== undefined) {
                const element = this.container.querySelector(`#${field}`);
                if (element) {
                    element.value = data[field];
                }
            }
        });
        
        // Cargar datos específicos
        await this.loadSpecificData(data);
        
        // Marcar como sin cambios
        this.manager.state.hasUnsavedChanges = false;
    }
    
    /**
     * Limpiar formulario
     */
    async clear() {
        // Limpiar campos básicos
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            const element = this.container.querySelector(`#${field}`);
            if (element) {
                element.value = '';
            }
        });
        
        // Limpiar campos específicos
        await this.clearSpecificFields();
        
        // Marcar como sin cambios
        this.manager.state.hasUnsavedChanges = false;
    }
    
    /**
     * Configurar eventos del formulario
     */
    setupEvents() {
        // Detectar cambios para marcar como modificado
        this.container.addEventListener('input', () => {
            this.manager.state.hasUnsavedChanges = true;
        });
        
        this.container.addEventListener('change', () => {
            this.manager.state.hasUnsavedChanges = true;
        });
        
        // Eventos específicos del componente
        this.setupSpecificEvents();
    }
    
    /**
     * Métodos abstractos que deben implementar las clases hijas
     */
    getFormType() {
        throw new Error('getFormType() debe ser implementado por la clase hija');
    }
    
    getBasicFields() {
        return ['txtmotivo', 'proximaconsulta', 'whatsapptxt', 'email'];
    }
    
    async initializeFields() {
        // Implementar en clase hija si es necesario
    }
    
    async onShow() {
        // Implementar en clase hija si es necesario
    }
    
    async getSpecificData() {
        return {};
    }
    
    async validateSpecificData(formData) {
        return { valid: true, errors: [] };
    }
    
    async loadSpecificData(data) {
        // Implementar en clase hija si es necesario
    }
    
    async clearSpecificFields() {
        // Implementar en clase hija si es necesario
    }
    
    setupSpecificEvents() {
        // Implementar en clase hija si es necesario
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO GENERAL
 * ============================================
 */
class GeneralForm extends BaseFormComponent {
    getFormType() {
        return 'general';
    }
    
    getBasicFields() {
        return [
            'txtmotivo', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'proximaconsulta', 'whatsapptxt', 'email'
        ];
    }
    
    /**
     * Configuración de preformatos para GeneralForm
     */
    getPreformatoConfig() {
        return [
            {
                selectId: 'formatoConsulta',
                action: 'get_preformatos_consulta',
                tipo: 'consulta',
                valueField: 'id',
                textField: 'nombre'
            },
            {
                selectId: 'formatoreceta',
                action: 'get_preformatos_receta',
                tipo: 'receta',
                valueField: 'id',
                textField: 'nombre'
            }
        ];
    }
    
    async initialize() {
        console.log('🔧 Inicializando GeneralForm...');
        await this.initializeFields();
    }
    
    async initializeFields() {
        // Inicializar editores Summernote
        await this.initializeSummernote();

        // Delay para evitar conflictos con scripts del sistema original
        console.log('⏳ Esperando para evitar conflictos con scripts originales...');
        await new Promise(resolve => setTimeout(resolve, 2000));

        // Cargar motivos comunes
        await this.loadMotivosComunes();
        
        // Cargar preformatos usando funcionalidad heredada
        await this.loadPreformatos();
    }
    
    async initializeSummernote() {
        if (typeof $.fn.summernote === 'undefined') return;
        
        const textareas = ['consulta-textarea', 'receta-textarea'];
        
        textareas.forEach(id => {
            const element = document.getElementById(id);
            if (element && !$(element).data('summernote')) {
                $(element).summernote({
                    height: 200,
                    placeholder: `Escriba aquí...`,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen']]
                    ],
                    callbacks: {
                        onChange: () => {
                            this.manager.state.hasUnsavedChanges = true;
                        }
                    }
                });
            }
        });
    }
    
    async loadMotivosComunes() {
        try {
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', {
                action: 'get_motivos_comunes',
                tipo_formulario: 'general'
            });
            
            if (response.status === 'success' && response.data) {
                this.populateSelect('motivoscomunes', response.data, 'id_motivo', 'motivo');
            }
        } catch (error) {
            console.error('Error cargando motivos comunes:', error);
        }
    }
    
    async loadPreformatos() {
        // Obtener ID de usuario desde configuración global
        const userId = window.APP_CONFIG?.userId || null;
        
        console.log('🔍 DEBUG GeneralForm.loadPreformatos()');
        console.log('🆔 UserId obtenido:', userId, '(tipo:', typeof userId, ')');
        console.log('📊 APP_CONFIG completo:', window.APP_CONFIG);
        
        // Cargar preformatos de consulta
        try {
            const requestData = {
                action: 'get_preformatos_consulta',
                tipo_formulario: 'general',
                tipo: 'consulta' // Filtrar específicamente por tipo consulta
            };
            
            if (userId) {
                requestData.usuario_id = userId;
            }
            
            console.log('📤 Enviando request para consultas:', requestData);
            
            const consultaResponse = await this.manager.apiCall('modules/consultas/api/consultas-api.php', requestData);
            
            console.log('📥 Respuesta consultas:', consultaResponse);
            console.log('🔍 ANÁLISIS DETALLADO consultas:');
            console.log('   - success:', consultaResponse.success);
            console.log('   - data tipo:', typeof consultaResponse.data);
            console.log('   - data es array:', Array.isArray(consultaResponse.data));
            console.log('   - data length:', consultaResponse.data?.length);
            console.log('   - data contenido:', consultaResponse.data);
            
            // CORREGIDO: Cambiar de .status a .success
            if (consultaResponse.success === true && consultaResponse.data && consultaResponse.data.length > 0) {
                console.log('✅ Poblando select formatoConsulta con', consultaResponse.data.length, 'elementos');
                // CORREGIDO: Usar 'id' y 'nombre' según la API
                this.populateSelect('formatoConsulta', consultaResponse.data, 'id', 'nombre');
            } else {
                console.log('❌ Sin datos en respuesta de consultas - Success:', consultaResponse.success, 'Data length:', consultaResponse.data?.length);
            }
        } catch (error) {
            console.error('❌ Error cargando preformatos consulta:', error);
        }
        
        // Cargar preformatos de receta
        try {
            const requestData = {
                action: 'get_preformatos_receta',
                tipo_formulario: 'general',
                tipo: 'receta' // Filtrar específicamente por tipo receta
            };
            
            if (userId) {
                requestData.usuario_id = userId;
            }
            
            console.log('📤 Enviando request para recetas:', requestData);
            
            const recetaResponse = await this.manager.apiCall('modules/consultas/api/consultas-api.php', requestData);
            
            console.log('📥 Respuesta recetas:', recetaResponse);
            console.log('🔍 ANÁLISIS DETALLADO recetas:');
            console.log('   - success:', recetaResponse.success);
            console.log('   - data tipo:', typeof recetaResponse.data);
            console.log('   - data es array:', Array.isArray(recetaResponse.data));
            console.log('   - data length:', recetaResponse.data?.length);
            console.log('   - data contenido:', recetaResponse.data);
            
            // CORREGIDO: Cambiar de .status a .success
            if (recetaResponse.success === true && recetaResponse.data && recetaResponse.data.length > 0) {
                console.log('✅ Poblando select formatoreceta con', recetaResponse.data.length, 'elementos');
                // CORREGIDO: Usar 'id' y 'nombre' según la API
                this.populateSelect('formatoreceta', recetaResponse.data, 'id', 'nombre');
            } else {
                console.log('❌ Sin datos en respuesta de recetas - Success:', recetaResponse.success, 'Data length:', recetaResponse.data?.length);
            }
        } catch (error) {
            console.error('❌ Error cargando preformatos receta:', error);
        }
    }
    
    populateSelect(selectId, data, valueField, textField) {
        console.log('🔧 populateSelect called:', {selectId, dataLength: data.length, valueField, textField});
        console.log('📊 Data received:', data);
        
        const select = document.getElementById(selectId);
        if (!select) {
            console.error('❌ Select element not found:', selectId);
            return;
        }
        
        console.log('🎯 Select element found:', select);
        console.log('🧹 LIMPIANDO COMPLETAMENTE el select (todas las opciones)...');
        
        // LIMPIAR TODAS LAS OPCIONES (incluso la primera)
        select.innerHTML = '';
        
        // Agregar opción por defecto
        const defaultOption = new Option('Seleccionar preformato...', '');
        defaultOption.selected = true;
        select.add(defaultOption);
        
        console.log('➕ Adding', data.length, 'new options...');
        
        // Agregar opciones
        data.forEach((item, index) => {
            console.log(`  Option ${index + 1}:`, item[textField], '(value:', item[valueField], ')');
            const option = new Option(item[textField], item[valueField]);
            
            // CORREGIDO: El API devuelve 'texto' no 'contenido'
            if (item.texto) {
                option.setAttribute('data-contenido', item.texto);
                console.log(`    📝 Contenido guardado para ${item[textField]}`);
            } else if (item.contenido) {
                option.setAttribute('data-contenido', item.contenido);
                console.log(`    📝 Contenido guardado para ${item[textField]} (campo legacy)`);
            }
            
            select.add(option);
        });
        
        console.log('✅ PopulateSelect completed. Total options:', select.options.length);
        
        // Forzar actualización visual si es Select2
        if ($(select).hasClass('select2bs4')) {
            console.log('🔄 Actualizando Select2...');
            $(select).trigger('change.select2');
        }
        
        // Configurar evento change
        select.addEventListener('change', () => {
            if (select.value !== 'Seleccionar') {
                this.applyPreformato(selectId, select.value);
            }
        });
    }
    
    applyPreformato(selectId, value) {
        console.log(`🔄 applyPreformato(${selectId}, ${value})`);
        
        const select = document.getElementById(selectId);
        if (!select) {
            console.error(`❌ Select ${selectId} no encontrado`);
            return;
        }
        
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) {
            console.error(`❌ Opción seleccionada no encontrada`);
            return;
        }
        
        const contenido = selectedOption.getAttribute('data-contenido');
        console.log(`📄 Contenido encontrado:`, contenido ? 'SÍ' : 'NO');
        
        if (!contenido) {
            console.warn(`⚠️ No hay contenido para la opción seleccionada`);
            return;
        }
        
        let targetId;
        if (selectId === 'formatoConsulta') {
            targetId = 'consulta-textarea';
        } else if (selectId === 'formatoreceta') {
            targetId = 'receta-textarea';
        }
        
        console.log(`🎯 Target textarea: ${targetId}`);
        
        if (targetId) {
            const target = document.getElementById(targetId);
            if (target) {
                console.log(`📝 Rellenando textarea con contenido...`);
                if ($(target).data('summernote')) {
                    console.log(`🔤 Usando Summernote para ${targetId}`);
                    $(target).summernote('code', contenido);
                } else {
                    console.log(`📄 Usando textarea normal para ${targetId}`);
                    target.value = contenido;
                }
                console.log(`✅ Contenido aplicado correctamente`);
            } else {
                console.error(`❌ Textarea ${targetId} no encontrado`);
            }
        } else {
            console.error(`❌ No se pudo determinar el target para ${selectId}`);
        }
    }
    
    async getSpecificData() {
        const data = {};
        
        // Obtener contenido de Summernote
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            if ($(consultaTextarea).data('summernote')) {
                data.diagnostico = $(consultaTextarea).summernote('code');
            } else {
                data.diagnostico = consultaTextarea.value;
            }
        }
        
        const recetaTextarea = document.getElementById('receta-textarea');
        if (recetaTextarea) {
            if ($(recetaTextarea).data('summernote')) {
                data.receta_textarea = $(recetaTextarea).summernote('code');
            } else {
                data.receta_textarea = recetaTextarea.value;
            }
        }
        
        return data;
    }
    
    async loadSpecificData(data) {
        // Cargar en Summernote
        if (data.diagnostico) {
            const consultaTextarea = document.getElementById('consulta-textarea');
            if (consultaTextarea) {
                if ($(consultaTextarea).data('summernote')) {
                    $(consultaTextarea).summernote('code', data.diagnostico);
                } else {
                    consultaTextarea.value = data.diagnostico;
                }
            }
        }
        
        if (data.receta_textarea) {
            const recetaTextarea = document.getElementById('receta-textarea');
            if (recetaTextarea) {
                if ($(recetaTextarea).data('summernote')) {
                    $(recetaTextarea).summernote('code', data.receta_textarea);
                } else {
                    recetaTextarea.value = data.receta_textarea;
                }
            }
        }
    }
    
    async clearSpecificFields() {
        // Limpiar Summernote
        const textareas = ['consulta-textarea', 'receta-textarea'];
        textareas.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                if ($(element).data('summernote')) {
                    $(element).summernote('code', '');
                } else {
                    element.value = '';
                }
            }
        });
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO ANTEOJOS
 * ============================================
 */
class AnteojosForm extends BaseFormComponent {
    getFormType() {
        return 'anteojos';
    }
    
    getBasicFields() {
        return [
            'txtmotivo', 'proximaconsulta', 'whatsapptxt', 'email',
            'ejeod', 'dnpod', 'notaod', 'altura_od',
            'ejeoi', 'dnpoi', 'notaoi', 'altura_oi', 'dist_interpupilar'
        ];
    }
    
    async initializeFields() {
        // Cargar valores de esferas, cilindros, etc. desde la base de datos
        await this.loadSelectValues();
        
        // Cargar motivos comunes específicos para anteojos
        await this.loadMotivosComunes();
        
        // Cargar preformatos de receta para anteojos
        await this.loadPreformatos();
    }
    
    async loadSelectValues() {
        const selects = {
            'od_esf': 'esfera',
            'od_cil': 'cilindro',
            'od_adicion': 'adicion',
            'oi_esf': 'esfera',
            'oi_cil': 'cilindro',
            'oi_adicion': 'adicion'
        };
        
        for (const [selectId, tipo] of Object.entries(selects)) {
            try {
                const response = await this.manager.apiCall('ajax/referenciales.ajax.php', {
                    operacion: 'getValores',
                    tipo: tipo
                });
                
                if (response.status === 'success' && response.data) {
                    this.populateSelect(selectId, response.data, 'valor', 'valor');
                }
            } catch (error) {
                console.error(`Error cargando valores para ${selectId}:`, error);
            }
        }
    }
    
    async loadPreformatos() {
        // Obtener ID de usuario desde configuración global
        const userId = window.APP_CONFIG?.userId || null;
        
        // Cargar preformatos específicos para anteojos (tipo receta)
        try {
            const requestData = {
                action: 'get_preformatos_receta',
                tipo_formulario: 'anteojos',
                tipo: 'receta' // Filtrar específicamente por tipo receta
            };
            
            if (userId) {
                requestData.usuario_id = userId;
            }
            
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', requestData);
            
            if (response.status === 'success' && response.data) {
                this.populateSelect('formatoReceta', response.data, 'id_preformato', 'nombre');
            }
        } catch (error) {
            console.error('Error cargando preformatos anteojos:', error);
        }
    }

    // Reutilizar métodos de GeneralForm adaptándolos
    async loadMotivosComunes() {
        try {
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', {
                action: 'get_motivos_comunes',
                tipo_formulario: 'anteojos'
            });
            
            if (response.status === 'success' && response.data) {
                this.populateSelect('motivoscomunes', response.data, 'id_motivo', 'motivo');
            }
        } catch (error) {
            console.error('Error cargando motivos comunes anteojos:', error);
        }
    }
    
    populateSelect(selectId, data, valueField, textField) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        // Limpiar opciones excepto la primera
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        // Agregar opciones
        data.forEach(item => {
            const option = new Option(item[textField], item[valueField]);
            select.add(option);
        });
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO ESTUDIOS
 * ============================================
 */
class EstudiosForm extends BaseFormComponent {
    getFormType() {
        return 'estudios';
    }
    
    async initializeFields() {
        await this.initializeSummernote();
        await this.loadMotivosComunes();
    }
    
    async initializeSummernote() {
        if (typeof $.fn.summernote === 'undefined') return;
        
        const element = document.getElementById('consulta-textarea');
        if (element && !$(element).data('summernote')) {
            $(element).summernote({
                height: 300,
                placeholder: 'Descripción del estudio médico...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen']]
                ],
                callbacks: {
                    onChange: () => {
                        this.manager.state.hasUnsavedChanges = true;
                    }
                }
            });
        }
    }
    
    async loadMotivosComunes() {
        try {
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', {
                action: 'get_motivos_comunes',
                tipo_formulario: 'estudios'
            });
            
            if (response.status === 'success' && response.data) {
                this.populateSelect('motivoscomunes', response.data, 'id_motivo', 'motivo');
            }
        } catch (error) {
            console.error('Error cargando motivos comunes estudios:', error);
        }
    }
    
    populateSelect(selectId, data, valueField, textField) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        data.forEach(item => {
            const option = new Option(item[textField], item[valueField]);
            select.add(option);
        });
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO INFORME+IMAGEN
 * ============================================
 */
class InformeImagenForm extends BaseFormComponent {
    getFormType() {
        return 'informe_imagen';
    }
    
    getBasicFields() {
        return [
            'txtmotivo', 'equipoMedico', 'whatsapptxt', 'email',
            'proximaconsulta', 'txtEmailShare'
        ];
    }
    
    async initializeFields() {
        await this.initializeSummernote();
        await this.initializeFileUpload();
        await this.initializeTagify();
        await this.loadMotivosComunes();
    }
    
    async initializeSummernote() {
        if (typeof $.fn.summernote === 'undefined') return;
        
        const textareas = ['consulta-textarea', 'descripcion-od-textarea', 'descripcion-oi-textarea'];
        
        textareas.forEach(id => {
            const element = document.getElementById(id);
            if (element && !$(element).data('summernote')) {
                $(element).summernote({
                    height: 150,
                    placeholder: `Descripción...`,
                    toolbar: [
                        ['font', ['bold', 'underline', 'clear']],
                        ['para', ['ul', 'ol']],
                        ['insert', ['link']]
                    ],
                    callbacks: {
                        onChange: () => {
                            this.manager.state.hasUnsavedChanges = true;
                        }
                    }
                });
            }
        });
    }
    
    async initializeFileUpload() {
        // Inicializar sistema de upload de archivos específico para OD/OI
        this.setupFileUploadHandlers();
    }
    
    setupFileUploadHandlers() {
        // Implementar handlers para upload de archivos por ojo
        const uploadButtons = ['btnUploadOD', 'btnUploadOI'];
        uploadButtons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (button) {
                button.addEventListener('click', (e) => {
                    const tipo = buttonId.includes('OD') ? 'od' : 'oi';
                    this.handleFileUpload(tipo);
                });
            }
        });
    }
    
    async handleFileUpload(tipo) {
        // Crear input file temporal
        const input = document.createElement('input');
        input.type = 'file';
        input.multiple = true;
        input.accept = 'image/*,.pdf';
        
        input.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            this.processFiles(files, tipo);
        });
        
        input.click();
    }
    
    async processFiles(files, tipo) {
        // Procesar archivos seleccionados
        for (const file of files) {
            await this.addFileToTable(file, tipo);
        }
    }
    
    async addFileToTable(file, tipo) {
        const tableId = `tabla-archivos-${tipo}`;
        const tbody = document.getElementById(tableId);
        if (!tbody) return;
        
        const row = tbody.insertRow();
        row.innerHTML = `
            <td>${tbody.rows.length}</td>
            <td>
                <i class="fas fa-file"></i> ${file.name}
                <small class="d-block text-muted">${this.formatFileSize(file.size)}</small>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewFile('${file.name}')">
                    <i class="fas fa-eye"></i> Ver
                </button>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile(this)">
                    <i class="fas fa-trash"></i> Quitar
                </button>
            </td>
        `;
        
        // Guardar referencia del archivo
        row._fileData = file;
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    async initializeTagify() {
        // Inicializar Tagify para emails
        const emailInput = document.getElementById('txtEmailShare');
        if (emailInput && typeof Tagify !== 'undefined') {
            this.tagifyInstance = new Tagify(emailInput, {
                pattern: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                placeholder: 'Agregar emails...'
            });
        }
    }
    
    async loadMotivosComunes() {
        try {
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', {
                action: 'get_motivos_comunes',
                tipo_formulario: 'informe_imagen'
            });
            
            if (response.status === 'success' && response.data) {
                this.populateSelect('motivoscomunes', response.data, 'id_motivo', 'motivo');
            }
        } catch (error) {
            console.error('Error cargando motivos comunes informe+imagen:', error);
        }
    }
    
    populateSelect(selectId, data, valueField, textField) {
        const select = document.getElementById(selectId);
        if (!select) return;
        
        while (select.options.length > 1) {
            select.remove(1);
        }
        
        data.forEach(item => {
            const option = new Option(item[textField], item[valueField]);
            select.add(option);
        });
    }

    /**
     * Método público para forzar recarga de preformatos
     * Útil para debugging desde consola del navegador
     */
    async forceReloadPreformatos() {
        console.log('🔄 FORZANDO recarga de preformatos...');
        await this.loadPreformatos();
        console.log('✅ Recarga forzada completada');
    }

    /**
     * Método público para testear aplicación de preformatos
     */
    testPreformatoApplication(selectId, optionIndex = 1) {
        console.log(`🧪 TEST: Aplicando preformato ${optionIndex} del select ${selectId}`);
        
        const select = document.getElementById(selectId);
        if (select && select.options[optionIndex]) {
            select.selectedIndex = optionIndex;
            select.value = select.options[optionIndex].value;
            
            // Disparar evento change
            select.dispatchEvent(new Event('change'));
            
            console.log(`✅ Test completado para ${selectId}`);
        } else {
            console.error(`❌ No se pudo hacer el test para ${selectId}`);
        }
    }

    /**
     * Obtener información de debugging del componente
     */
    getDebugInfo() {
        return {
            manager: !!this.manager,
            initialized: true,
            hasLoadMethod: typeof this.loadPreformatos === 'function',
            currentFormType: this.manager?.currentFormType || 'unknown'
        };
    }
}

/**
 * ============================================
 * COMPONENTES ESPECÍFICOS DE FORMULARIOS
 * ============================================
 */

/**
 * Componente para formularios generales
 */
class GeneralFormComponent extends BaseFormComponent {
    getFormType() {
        return 'general';
    }
    
    async initialize() {
        console.log('🔧 Inicializando GeneralFormComponent...');
        // Lógica específica para formularios generales
        await this.loadMotivosComunes();
    }
    
    async loadMotivosComunes() {
        try {
            // Implementar carga de motivos comunes
            console.log('📋 Cargando motivos comunes para formulario general');
        } catch (error) {
            console.warn('Error cargando motivos comunes generales:', error);
        }
    }
}

/**
 * Componente para formularios de anteojos
 */
class AnteojosFormComponent extends BaseFormComponent {
    getFormType() {
        return 'anteojos';
    }
    
    async initialize() {
        console.log('🔧 Inicializando AnteojosFormComponent...');
        // Lógica específica para formularios de anteojos
        await this.loadReferenciales();
        await this.loadPreformatos();
    }
    
    async loadReferenciales() {
        try {
            console.log('👓 Cargando referenciales para formulario de anteojos');
        } catch (error) {
            console.warn('Error cargando referenciales de anteojos:', error);
        }
    }

    async loadPreformatos() {
        console.log('🔍 DEBUG AnteojosFormComponent.loadPreformatos()');
        
        try {
            // Obtener usuario actual - usar la misma lógica que GeneralForm
            const userId = window.APP_CONFIG?.userId || null;
            console.log('🆔 UserId obtenido:', userId, '(tipo:', typeof userId, ')');
            console.log('📊 APP_CONFIG completo:', window.APP_CONFIG);
            
            if (!userId) {
                console.warn('⚠️ No se pudo obtener el user ID para anteojos');
                return;
            }

            // Cargar preformatos específicos para anteojos
            const requestData = {
                action: 'get_preformatos_consulta',
                tipo_formulario: 'anteojos',
                tipo: 'consulta' // Filtrar específicamente por tipo consulta
            };
            
            if (userId) {
                requestData.usuario_id = userId;
            }

            console.log('📤 Enviando request para preformatos de anteojos:', requestData);
            
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', requestData);
            
            console.log('📥 Respuesta preformatos anteojos:', response);
            console.log('🔍 ANÁLISIS DETALLADO anteojos:');
            console.log('   - success:', response.success);
            console.log('   - data tipo:', typeof response.data);
            console.log('   - data es array:', Array.isArray(response.data));
            console.log('   - data length:', response.data?.length);
            console.log('   - data contenido:', response.data);

            if (response.success === true && response.data && response.data.length > 0) {
                console.log('✅ Poblando select formatoConsulta (anteojos) con', response.data.length, 'elementos');
                this.populateSelect('formatoConsulta', response.data, 'id', 'nombre');
            } else {
                console.log('❌ Sin preformatos para anteojos - Success:', response.success, 'Data length:', response.data?.length);
            }

        } catch (error) {
            console.error('❌ Error cargando preformatos de anteojos:', error);
        }
    }

    populateSelect(selectId, data, valueField, textField) {
        console.log(`🔧 populateSelect anteojos called: {selectId: '${selectId}', dataLength: ${data.length}, valueField: '${valueField}', textField: '${textField}'}`);
        console.log('📊 Data received:', data);
        
        const select = document.getElementById(selectId);
        if (!select) {
            console.error(`❌ Select ${selectId} no encontrado en formulario anteojos`);
            return;
        }
        
        console.log('🎯 Select element found:', select);
        console.log('🧹 LIMPIANDO COMPLETAMENTE el select (todas las opciones)...');
        
        // Limpiar completamente
        select.innerHTML = '';
        
        // Agregar opción por defecto
        const defaultOption = new Option('Seleccionar preformato...', '');
        defaultOption.selected = true;
        select.add(defaultOption);
        
        console.log('➕ Adding', data.length, 'new options...');
        
        // Agregar opciones
        data.forEach((item, index) => {
            console.log(`  Option ${index + 1}:`, item[textField], '(value:', item[valueField], ')');
            const option = new Option(item[textField], item[valueField]);
            
            // CORREGIDO: El API devuelve 'texto' no 'contenido'
            if (item.texto) {
                option.setAttribute('data-contenido', item.texto);
                console.log(`    📝 Contenido guardado para ${item[textField]}`);
            } else if (item.contenido) {
                option.setAttribute('data-contenido', item.contenido);
                console.log(`    📝 Contenido guardado para ${item[textField]} (campo legacy)`);
            }
            
            select.add(option);
        });
        
        console.log('✅ PopulateSelect anteojos completed. Total options:', select.options.length);
        
        // Forzar actualización visual si es Select2
        if ($(select).hasClass('select2bs4')) {
            console.log('🔄 Actualizando Select2...');
            $(select).trigger('change.select2');
        }
        
        // Configurar evento change
        select.addEventListener('change', () => {
            if (select.value !== 'Seleccionar') {
                this.applyPreformato(selectId, select.value);
            }
        });
    }

    applyPreformato(selectId, value) {
        console.log(`🔄 applyPreformato anteojos (${selectId}, ${value})`);
        
        const select = document.getElementById(selectId);
        if (!select) {
            console.error(`❌ Select ${selectId} no encontrado`);
            return;
        }
        
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) {
            console.error(`❌ Opción seleccionada no encontrada`);
            return;
        }
        
        const contenido = selectedOption.getAttribute('data-contenido');
        console.log(`📄 Contenido encontrado:`, contenido ? 'SÍ' : 'NO');
        
        if (!contenido) {
            console.warn(`⚠️ No hay contenido para la opción seleccionada`);
            return;
        }
        
        // Para anteojos, buscar textarea específico (si existe)
        let targetId;
        if (selectId === 'formatoConsulta') {
            // Podría ser un textarea específico para anteojos o reutilizar consulta-textarea
            targetId = 'consulta-textarea'; // ajustar según la estructura real
        }
        
        console.log(`🎯 Target textarea: ${targetId}`);
        
        if (targetId) {
            const target = document.getElementById(targetId);
            if (target) {
                console.log(`📝 Rellenando textarea con contenido...`);
                if ($(target).data('summernote')) {
                    console.log(`🔤 Usando Summernote para ${targetId}`);
                    $(target).summernote('code', contenido);
                } else {
                    console.log(`📄 Usando textarea normal para ${targetId}`);
                    target.value = contenido;
                }
                console.log(`✅ Contenido aplicado correctamente`);
            } else {
                console.error(`❌ Textarea ${targetId} no encontrado`);
            }
        } else {
            console.error(`❌ No se pudo determinar el target para ${selectId}`);
        }
    }
}

/**
 * Componente para formularios de estudios
 */
class EstudiosFormComponent extends BaseFormComponent {
    getFormType() {
        return 'estudios';
    }
    
    async initialize() {
        console.log('🔧 Inicializando EstudiosFormComponent...');
        // Lógica específica para formularios de estudios
        await this.loadPreformatos();
    }
    
    async loadPreformatos() {
        try {
            console.log('📊 Cargando preformatos para formulario de estudios');
            
            // Obtener ID de usuario desde configuración global
            const userId = window.APP_CONFIG?.userId || null;
            
            // Cargar preformatos específicos para estudios
            const requestData = {
                action: 'getPreformatos',
                tipo_formulario: 'estudios',
                tipo: 'orden_estudios' // Filtrar específicamente por tipo orden_estudios
            };
            
            if (userId) {
                requestData.usuario_id = userId;
            }
            
            const response = await this.manager.apiCall('modules/consultas/api/consultas-api.php', requestData);
            
            if (response.status === 'success' && response.data) {
                // Buscar el select de preformatos en el formulario de estudios
                const preformatoSelect = document.querySelector('#formulario-estudios select[name="preformatos"]');
                if (preformatoSelect) {
                    // Limpiar opciones existentes
                    preformatoSelect.innerHTML = '<option value="">Seleccionar preformato...</option>';
                    
                    // Agregar nuevas opciones
                    response.data.forEach(preformato => {
                        const option = document.createElement('option');
                        option.value = preformato.id;
                        option.textContent = preformato.nombre;
                        preformatoSelect.appendChild(option);
                    });
                }
            }
        } catch (error) {
            console.warn('Error cargando preformatos de estudios:', error);
        }
    }
}

/**
 * Componente para formularios de informe imagen
 */
class InformeImagenFormComponent extends BaseFormComponent {
    getFormType() {
        return 'informe_imagen';
    }
    
    async initialize() {
        console.log('🔧 Inicializando InformeImagenFormComponent...');
        // Lógica específica para formularios de informe imagen
        await this.loadConfiguracion();
    }
    
    async loadConfiguracion() {
        try {
            console.log('🖼️ Cargando configuración para formulario de informe imagen');
        } catch (error) {
            console.warn('Error cargando configuración de informe imagen:', error);
        }
    }
}

// Exportar componentes
window.BaseFormComponent = BaseFormComponent;
window.GeneralFormComponent = GeneralFormComponent;
window.AnteojosFormComponent = AnteojosFormComponent;
window.EstudiosFormComponent = EstudiosFormComponent;
window.InformeImagenFormComponent = InformeImagenFormComponent;
window.GeneralForm = GeneralForm;
window.AnteojosForm = AnteojosForm;
window.EstudiosForm = EstudiosForm;
window.InformeImagenForm = InformeImagenForm;
