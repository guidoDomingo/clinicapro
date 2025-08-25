/**
 * ============================================
 * SISTEMA DE COMPONENTES DE FORMULARIO REFACTORIZADO
 * ============================================
 */

// Evitar redeclaración si ya existe
if (typeof BaseFormComponent !== 'undefined') {
    console.log('⚠️ FormComponents ya cargado, evitando redeclaración');
} else {

/**
 * Clase base para todos los componentes de formulario
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
    async initialize() {
        if (this.isInitialized) return;
        
        console.log(`🔧 Inicializando componente ${this.constructor.name}...`);
        
        // Intentar múltiples formas de encontrar el container
        const formType = this.getFormType();
        
        console.log(`🔍 Buscando container para tipo: ${formType}`);
        
        // Lista de selectores a probar
        const selectors = [
            `#formulario-${formType}`,
            `#form-${formType}`,
            `.formulario-${formType}`,
            `.form-${formType}`,
            `[data-form-type="${formType}"]`
        ];
        
        for (const selector of selectors) {
            this.container = document.querySelector(selector);
            if (this.container) {
                console.log(`✅ Container encontrado con selector: ${selector}`, this.container);
                break;
            } else {
                console.log(`❌ No encontrado con selector: ${selector}`);
            }
        }
        
        if (!this.container) {
            console.warn(`⚠️ Container no encontrado para ${formType}. Continuando sin container específico.`);
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
        
        // Limpiar completamente y agregar opción por defecto
        select.innerHTML = '';
        
        // Agregar opción por defecto vacía
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = '-- Seleccionar preformato --';
        defaultOption.selected = true;
        select.appendChild(defaultOption);
        
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
                console.log('    🔑 Key:', key);
                console.log('    📄 Content preview:', item.contenido.substring(0, 50) + '...');
                console.log('    🗺️ Map size después de guardar:', this.preformatoContent.size);
            } else {
                console.log('    ❌ Sin contenido para', item[textField]);
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
        
        // Evento estándar para selects normales
        select.addEventListener('change', (e) => {
            console.log('🎯 Evento change disparado para:', selectId, 'valor:', e.target.value);
            if (e.target.value && e.target.value !== '' && e.target.value !== '0') {
                this.applyPreformato(selectId, e.target.value);
            } else {
                console.log('🔄 Valor vacío o cero, no aplicando preformato');
            }
        });
        
        // Si es Select2, también escuchar el evento específico de Select2
        if (typeof $ !== 'undefined') {
            const $select = $(select);
            if ($select.hasClass('select2-hidden-accessible') || $select.data('select2')) {
                console.log('🔄 Configurando evento Select2 para:', selectId);
                $select.on('select2:select', (e) => {
                    console.log('🎯 Evento Select2 disparado para:', selectId, 'valor:', e.target.value);
                    if (e.target.value && e.target.value !== '' && e.target.value !== '0') {
                        this.applyPreformato(selectId, e.target.value);
                    } else {
                        console.log('🔄 Valor vacío o cero en Select2, no aplicando preformato');
                    }
                });
            }
        }
        
        console.log('✅ Eventos configurados para select:', selectId);
    }
    
    /**
     * Aplicar preformato genérico
     */
    applyPreformato(selectId, value) {
        console.log('🔄 applyPreformato(' + selectId + ', ' + value + ')');
        console.log('🏗️ Componente actual:', this.constructor.name);
        console.log('🆔 ID de formulario:', this.formType || 'no definido');
        
        const key = `${selectId}-${value}`;
        console.log('🔑 Buscando key:', key);
        
        const content = this.preformatoContent.get(key);
        console.log('🗺️ Map size:', this.preformatoContent.size);
        console.log('🗺️ Map keys:', Array.from(this.preformatoContent.keys()));
        
        if (!content) {
            console.log('📄 Sin contenido para:', key);
            console.log('💡 Contenido disponible:');
            this.preformatoContent.forEach((value, mapKey) => {
                console.log(`  - ${mapKey}: ${value.substring(0, 50)}...`);
            });
            
            // Verificar si hay otros componentes con contenido
            if (typeof window.ConsultasManager !== 'undefined') {
                console.log('🔍 Verificando otros componentes:');
                const manager = window.ConsultasManager.getInstance();
                if (manager && manager.state && manager.state.components) {
                    Object.keys(manager.state.components).forEach(compName => {
                        const comp = manager.state.components[compName];
                        if (comp && comp.preformatoContent) {
                            console.log(`  📦 ${compName}: ${comp.preformatoContent.size} elementos`);
                            comp.preformatoContent.forEach((val, k) => {
                                console.log(`    - ${k}: ${val.substring(0, 30)}...`);
                            });
                        }
                    });
                }
            }
            
            return;
        }
        
        console.log('📄 Contenido encontrado: SÍ');
        console.log('📄 Content preview:', content.substring(0, 100) + '...');
        
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
        console.log('🎯 TextareaId:', textareaId);
        console.log('📄 Content length:', content.length);
        
        const textarea = document.getElementById(textareaId);
        if (!textarea) {
            console.warn('⚠️ Textarea no encontrado:', textareaId);
            return;
        }
        
        console.log('✅ Textarea encontrado:', textarea);
        
        // Verificar si jQuery está disponible
        if (typeof $ === 'undefined') {
            console.log('📝 jQuery no disponible, usando textarea normal');
            textarea.value = content;
            console.log('✅ Contenido aplicado correctamente');
            return;
        }
        
        try {
            // Verificar si es Summernote - múltiples métodos de detección
            const $textarea = $(textarea);
            const hasSummernoteData = $textarea.data('summernote') !== undefined;
            const hasNoteEditor = $textarea.next('.note-editor').length > 0;
            const hasSummernoteFunction = typeof $textarea.summernote === 'function';
            
            console.log('🔍 Summernote detection:');
            console.log('  - hasSummernoteData:', hasSummernoteData);
            console.log('  - hasNoteEditor:', hasNoteEditor);
            console.log('  - hasSummernoteFunction:', hasSummernoteFunction);
            
            if (hasSummernoteFunction && (hasSummernoteData || hasNoteEditor)) {
                console.log('🔤 Usando Summernote para', textareaId);
                try {
                    $textarea.summernote('code', content);
                    console.log('✅ Contenido aplicado con Summernote');
                } catch (summernoteError) {
                    console.warn('⚠️ Error con Summernote, usando textarea normal:', summernoteError);
                    textarea.value = content;
                    console.log('✅ Contenido aplicado como fallback');
                }
            } else {
                console.log('📝 Usando textarea normal para', textareaId);
                textarea.value = content;
                console.log('✅ Contenido aplicado correctamente');
            }
        } catch (error) {
            console.error('❌ Error aplicando contenido:', error);
            // Fallback final
            textarea.value = content;
            console.log('✅ Contenido aplicado como último recurso');
        }
        
        // Disparar evento change para notificar cambios
        const changeEvent = new Event('change', { bubbles: true });
        textarea.dispatchEvent(changeEvent);
        console.log('📡 Evento change disparado');
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
        
        console.log(`📋 getFormData() para ${this.getFormType()}, container:`, this.container, 'initialized:', this.isInitialized);
        
        // Verificar que el container existe
        if (!this.container) {
            console.warn(`🚨 Container no encontrado para ${this.getFormType()}, intentando re-inicializar...`);
            await this.initialize();
            
            // Si sigue siendo null, intentar buscar el form directamente
            if (!this.container) {
                const formType = this.getFormType();
                this.container = document.querySelector(`#form-${formType}`) || 
                               document.querySelector(`#formulario-${formType}`) ||
                               document.querySelector(`.form-${formType}`);
                               
                console.log(`🔍 Búsqueda manual de container para ${formType}:`, this.container);
            }
        }
        
        // Si no podemos encontrar el container, usar document como fallback
        const searchRoot = this.container || document;
        
        if (!searchRoot) {
            throw new Error(`No se puede acceder al DOM para el formulario ${this.getFormType()}`);
        }
        
        console.log(`📍 Usando searchRoot:`, searchRoot === document ? 'document' : searchRoot.id || 'elemento sin ID');
        
        // Obtener campos básicos
        const basicFields = this.getBasicFields();
        console.log(`📝 Campos básicos para ${this.getFormType()}:`, basicFields);
        
        basicFields.forEach(field => {
            const element = searchRoot.querySelector(`#${field}`);
            if (element) {
                formData.append(field, element.value || '');
                console.log(`✅ Campo ${field}: "${element.value}"`);
            } else {
                console.warn(`🚨 Campo ${field} no encontrado en formulario ${this.getFormType()}`);
            }
        });
        
        // Obtener datos específicos del formulario
        const specificData = await this.getSpecificData();
        for (const [key, value] of Object.entries(specificData)) {
            formData.append(key, value);
        }
        
        console.log(`📊 FormData completa para ${this.getFormType()}:`, Array.from(formData.entries()));
        return formData;
    }
    
    /**
     * Cargar datos en el formulario
     */
    async loadData(data) {
        // Usar container si está disponible, sino buscar en documento
        const searchRoot = this.container || document;
        
        // Cargar campos básicos
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            const element = searchRoot.querySelector(`#${field}`);
            if (element && data[field] !== undefined) {
                element.value = data[field];
            }
        });
        
        // Cargar datos específicos
        await this.loadSpecificData(data);
    }
    
    /**
     * Validar formulario
     */
    async validate() {
        const errors = [];
        
        // Validaciones básicas
        const basicValidation = this.validateBasicFields();
        if (!basicValidation.valid) {
            errors.push(...basicValidation.errors);
        }
        
        // Validaciones específicas
        const specificValidation = await this.validateSpecificData();
        if (!specificValidation.valid) {
            errors.push(...specificValidation.errors);
        }
        
        return {
            valid: errors.length === 0,
            errors
        };
    }
    
    /**
     * Limpiar formulario
     */
    async clear() {
        // Usar container si está disponible, sino buscar en documento
        const searchRoot = this.container || document;
        
        // Limpiar campos básicos
        const basicFields = this.getBasicFields();
        basicFields.forEach(field => {
            const element = searchRoot.querySelector(`#${field}`);
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
        // Solo configurar eventos si tenemos container
        if (this.container) {
            // Detectar cambios para marcar como modificado
            this.container.addEventListener('input', () => {
                this.manager.state.hasUnsavedChanges = true;
            });
            
            this.container.addEventListener('change', () => {
                this.manager.state.hasUnsavedChanges = true;
            });
        } else {
            // Si no hay container específico, usar document como fallback
            document.addEventListener('input', (e) => {
                // Solo escuchar elementos que pertenezcan a este formulario
                const formType = this.getFormType();
                if (e.target.closest(`#formulario-${formType}`) || 
                    e.target.closest(`#form-${formType}`) ||
                    this.getBasicFields().includes(e.target.id)) {
                    this.manager.state.hasUnsavedChanges = true;
                }
            });
        }
        
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
    
    async validateSpecificData() {
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
    
    validateBasicFields() {
        // Implementar validaciones básicas si es necesario
        return { valid: true, errors: [] };
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
    
    async init() {
        console.log('🔧 Inicializando GeneralForm...');
        await this.initialize();
        this.isInitialized = true;
    }
    
    async initialize() {
        console.log('🔧 Inicializando GeneralForm...');
        
        // Llamar inicialización de la clase padre
        await super.initialize();
        
        // Inicializaciones específicas de GeneralForm
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
                action: 'get_motivos_comunes'
            });
            
            if (response.success && response.data) {
                this.populateSelect('motivoscomunes', response.data, 'id', 'nombre');
            }
        } catch (error) {
            console.error('Error cargando motivos comunes:', error);
        }
    }
    
    async getSpecificData() {
        const data = {};
        
        console.log('📋 GeneralForm.getSpecificData() - Buscando campos específicos...');
        
        // Obtener contenido de Summernote
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            console.log('✅ Encontrado consulta-textarea');
            if ($(consultaTextarea).data('summernote')) {
                data['consulta-textarea'] = $(consultaTextarea).summernote('code');
                console.log('📝 Summernote consulta:', data['consulta-textarea']?.substring(0, 50));
            } else {
                data['consulta-textarea'] = consultaTextarea.value;
                console.log('📝 Textarea consulta:', data['consulta-textarea']?.substring(0, 50));
            }
        } else {
            console.warn('❌ No se encontró consulta-textarea');
        }
        
        const recetaTextarea = document.getElementById('receta-textarea');
        if (recetaTextarea) {
            console.log('✅ Encontrado receta-textarea');
            if ($(recetaTextarea).data('summernote')) {
                data.receta_textarea = $(recetaTextarea).summernote('code');
                console.log('📝 Summernote receta:', data.receta_textarea?.substring(0, 50));
            } else {
                data.receta_textarea = recetaTextarea.value;
                console.log('📝 Textarea receta:', data.receta_textarea?.substring(0, 50));
            }
        } else {
            console.warn('❌ No se encontró receta-textarea');
        }
        
        console.log('📊 GeneralForm data específica:', data);
        return data;
    }
    
    async loadSpecificData(data) {
        // Cargar en Summernote - usar la clave correcta
        const consultaContent = data['consulta-textarea'] || data.diagnostico || data.consulta_textarea;
        if (consultaContent) {
            const consultaTextarea = document.getElementById('consulta-textarea');
            if (consultaTextarea) {
                if ($(consultaTextarea).data('summernote')) {
                    $(consultaTextarea).summernote('code', consultaContent);
                } else {
                    consultaTextarea.value = consultaContent;
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
        const consultaTextarea = document.getElementById('consulta-textarea');
        if (consultaTextarea) {
            if ($(consultaTextarea).data('summernote')) {
                $(consultaTextarea).summernote('code', '');
            } else {
                consultaTextarea.value = '';
            }
        }
        
        const recetaTextarea = document.getElementById('receta-textarea');
        if (recetaTextarea) {
            if ($(recetaTextarea).data('summernote')) {
                $(recetaTextarea).summernote('code', '');
            } else {
                recetaTextarea.value = '';
            }
        }
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO DE ANTEOJOS
 * ============================================
 */
class AnteojosFormComponent extends BaseFormComponent {
    getFormType() {
        return 'anteojos';
    }
    
    /**
     * Configuración de preformatos para AnteojosFormComponent
     */
    getPreformatoConfig() {
        return [
            {
                selectId: 'formatoConsulta',
                action: 'get_preformatos_consulta',
                tipo: 'consulta',
                valueField: 'id',
                textField: 'nombre'
            }
        ];
    }

    async init() {
        console.log('🔧 Inicializando AnteojosFormComponent...');
        await this.initializeFields();
        this.isInitialized = true;
    }

    async initialize() {
        console.log('🔧 Inicializando AnteojosFormComponent...');
        await this.initializeFields();
    }    async initializeFields() {
        // Cargar referenciales específicos de anteojos
        await this.loadReferenciales();
        
        // Cargar preformatos usando funcionalidad heredada
        await this.loadPreformatos();
    }
    
    async loadReferenciales() {
        try {
            console.log('👓 Cargando referenciales para formulario de anteojos desde base de datos');
            
            // Configuración escalable de referenciales - todos desde BD
            const referencialesConfig = [
                // Ojo Derecho (OD)
                { selectId: 'od_esf', codigo: 'valores_esfera', label: 'OD Esfera' },
                { selectId: 'od_cil', codigo: 'valores_cilindro', label: 'OD Cilindro' },
                { selectId: 'od_adicion', codigo: 'valores_adicion', label: 'OD Adición' },
                
                // Ojo Izquierdo (OI)
                { selectId: 'oi_esf', codigo: 'valores_esfera', label: 'OI Esfera' },
                { selectId: 'oi_cil', codigo: 'valores_cilindro', label: 'OI Cilindro' },
                { selectId: 'oi_adicion', codigo: 'valores_adicion', label: 'OI Adición' }
            ];
            
            for (const config of referencialesConfig) {
                // Verificar si el select existe antes de intentar poblarlo
                const selectElement = document.getElementById(config.selectId);
                if (!selectElement) {
                    console.log(`⏭️ Select ${config.selectId} (${config.label}) no encontrado, omitiendo...`);
                    continue;
                }
                
                console.log(`📊 Cargando ${config.label} desde BD (código: ${config.codigo})`);
                
                try {
                    // Usar endpoint específico para mejor rendimiento
                    const response = await this.loadReferencialFromDB(config.codigo);
                    
                    if (response.success && response.data && response.data.length > 0) {
                        console.log(`✅ Cargados ${response.data.length} valores para ${config.label} desde BD`);
                        this.populateSelect(config.selectId, response.data, 'valor', 'etiqueta');
                    } else {
                        console.error(`❌ No se encontraron datos en BD para ${config.label} (${config.codigo})`);
                        console.error(`Response:`, response);
                        // NO usar fallback - requerir que los datos estén en BD
                        throw new Error(`Referencial ${config.codigo} no encontrado en base de datos`);
                    }
                } catch (error) {
                    console.error(`❌ Error crítico cargando ${config.label}:`, error);
                    // No continuar si los datos no están en BD
                    throw error;
                }
            }
            
        } catch (error) {
            console.error('❌ Error crítico cargando referenciales de anteojos:', error);
            // Mostrar mensaje al usuario
            this.showErrorMessage('Los datos de referenciales no están disponibles en la base de datos. Contacte al administrador.');
        }
    }
    
    /**
     * Campos básicos específicos del formulario de anteojos
     */
    getBasicFields() {
        return [
            'txtmotivo-anteojos', 'proximaconsulta', 'whatsapptxt', 'email'
        ];
    }

    /**
     * Cargar referencial específico desde base de datos usando endpoint directo
     */
    async loadReferencialFromDB(codigo) {
        try {
            // Primero intentar con el endpoint específico si existe
            let endpointUrl = null;
            
            switch(codigo) {
                case 'valores_esfera':
                    endpointUrl = 'ajax/consulta/get_esfera_referencial.php';
                    break;
                case 'valores_cilindro':
                    endpointUrl = 'ajax/consulta/get_cilindro_referencial.php';
                    break;
                case 'valores_adicion':
                    endpointUrl = 'ajax/consulta/get_adicion_referencial.php';
                    break;
                default:
                    // Usar endpoint genérico de referenciales
                    endpointUrl = 'ajax/referenciales.ajax.php';
                    break;
            }
            
            console.log(`📡 Consultando endpoint: ${endpointUrl} para código: ${codigo}`);
            
            if (endpointUrl.includes('consulta/')) {
                // Endpoint específico - solo GET
                const response = await fetch(endpointUrl, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return await response.json();
            } else {
                // Endpoint genérico - POST con operación
                const formData = new FormData();
                formData.append('operacion', 'listarValoresReferenciales');
                formData.append('codigo_referencial', codigo);
                
                const response = await fetch(endpointUrl, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return await response.json();
            }
            
        } catch (error) {
            console.error(`Error cargando referencial ${codigo}:`, error);
            throw error;
        }
    }
    
    /**
     * Obtener datos específicos del formulario de anteojos
     */
    async getSpecificData() {
        const specificData = {};
        
        console.log('👓 Recolectando datos específicos de anteojos...');
        
        // Campos del Ojo Derecho (OD)
        const odFields = ['od_esf', 'od_cil', 'od_eje', 'od_adicion'];
        odFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                specificData[fieldId] = field.value || null;
                console.log(`📊 Campo OD ${fieldId}:`, field.value);
            }
        });
        
        // Campos del Ojo Izquierdo (OI)
        const oiFields = ['oi_esf', 'oi_cil', 'oi_eje', 'oi_adicion'];
        oiFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                specificData[fieldId] = field.value || null;
                console.log(`📊 Campo OI ${fieldId}:`, field.value);
            }
        });
        
        // Campos adicionales de anteojos
        const additionalFields = ['dist_interpupilar', 'altura_od', 'altura_oi', 'notas'];
        additionalFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                specificData[fieldId] = field.value || null;
                console.log(`📊 Campo adicional ${fieldId}:`, field.value);
            }
        });
        
        console.log('👓 Datos específicos de anteojos recolectados:', specificData);
        return specificData;
    }
    
    /**
     * Mostrar mensaje de error al usuario
     */
    showErrorMessage(message) {
        // Usar sistema de notificaciones si existe
        if (this.manager && this.manager.notifications) {
            this.manager.notifications.error(message);
        } else {
            // Fallback a alert
            console.error('CRITICAL ERROR:', message);
            alert('Error: ' + message);
        }
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO DE ESTUDIOS
 * ============================================
 */
class EstudiosFormComponent extends BaseFormComponent {
    getFormType() {
        return 'estudios';
    }
    
    /**
     * Campos básicos específicos del formulario de estudios
     */
    getBasicFields() {
        return [
            'txtmotivo-estudios', 'equipo_medico-estudios', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'proximaconsulta', 'whatsapptxt', 'email'
        ];
    }
    
    async init() {
        console.log('🔧 Inicializando EstudiosFormComponent...');
        await this.initializeFields();
        this.isInitialized = true;
    }
    
    async initialize() {
        console.log('🔧 Inicializando EstudiosFormComponent...');
        await this.initializeFields();
    }
    
    async initializeFields() {
        // Implementar lógica específica de estudios
        console.log('📊 Cargando preformatos para formulario de estudios');
    }
    
    /**
     * Obtener datos específicos del formulario de estudios
     */
    async getSpecificData() {
        const specificData = {};
        
        // Mapeo específico para campos de estudios
        // El campo equipo_medico-estudios se mapea a tipo_estudio para el backend
        const equipoMedico = document.getElementById('equipo_medico-estudios');
        if (equipoMedico) {
            specificData.tipo_estudio = equipoMedico.value || null;
        }
        
        // El campo consulta-textarea-estudios se mapea a observaciones para el backend  
        const observaciones = document.getElementById('consulta-textarea-estudios');
        if (observaciones) {
            specificData.observaciones = observaciones.value || null;
        }
        
        // Campo de nota específico para estudios
        const nota = document.getElementById('txtnota-estudios');
        if (nota) {
            specificData.txtnota = nota.value || null;
        }
        
        return specificData;
    }
    
    /**
     * Poblar datos específicos del formulario de estudios
     */
    async populateData(consultaData) {
        console.log('🔬 Poblando datos específicos para formulario de estudios:', consultaData);
        
        try {
            // Poblar equipo médico desde datos relacionados
            if (consultaData.related && consultaData.related.consulta_estudios) {
                const estudiosData = consultaData.related.consulta_estudios;
                
                // Poblar equipo médico
                if (estudiosData.equipo_medico) {
                    const equipoSelect = document.getElementById('equipo_medico-estudios');
                    if (equipoSelect) {
                        // Esperar un poco para asegurar que las opciones estén cargadas
                        setTimeout(() => {
                            equipoSelect.value = estudiosData.equipo_medico;
                            // Trigger para Select2
                            if (equipoSelect.classList.contains('select2-hidden-accessible') && typeof $ !== 'undefined') {
                                $(equipoSelect).val(estudiosData.equipo_medico).trigger('change');
                            }
                            console.log(`📋 Equipo médico poblado: ${estudiosData.equipo_medico}`);
                        }, 500);
                    }
                }
                
                // Poblar descripción/observaciones
                if (estudiosData.observaciones) {
                    const descripcionField = document.getElementById('consulta-textarea-estudios');
                    if (descripcionField) {
                        descripcionField.value = estudiosData.observaciones;
                        console.log(`📝 Observaciones pobladas: ${estudiosData.observaciones}`);
                    }
                }
                
                // Poblar nota
                if (estudiosData.txtnota || consultaData.main?.txtnota) {
                    const notaField = document.getElementById('txtnota-estudios');
                    if (notaField) {
                        notaField.value = estudiosData.txtnota || consultaData.main.txtnota;
                        console.log(`📝 Nota poblada: ${estudiosData.txtnota || consultaData.main.txtnota}`);
                    }
                }
            }
            
        } catch (error) {
            console.error('❌ Error poblando datos de estudios:', error);
        }
    }
}

/**
 * ============================================
 * COMPONENTE FORMULARIO DE INFORME IMAGEN
 * ============================================
 */
class InformeImagenFormComponent extends BaseFormComponent {
    getFormType() {
        return 'informe_imagen';
    }
    
    /**
     * Campos básicos específicos del formulario de informe imagen
     */
    getBasicFields() {
        return [
            'txtmotivo-informe-imagen', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'proximaconsulta', 'whatsapptxt', 'email'
        ];
    }
    
    async init() {
        console.log('🔧 Inicializando InformeImagenFormComponent...');
        await this.initializeFields();
        this.isInitialized = true;
    }
    
    async initialize() {
        console.log('🔧 Inicializando InformeImagenFormComponent...');
        await this.initializeFields();
    }
    
    /**
     * Campos básicos específicos del formulario de informe imagen
     */
    getBasicFields() {
        return [
            'txtmotivo-informe-imagen', 'visionod', 'visionoi', 'tensionod', 'tensionoi',
            'proximaconsulta', 'whatsapptxt', 'email'
        ];
    }

    async initializeFields() {
        // Implementar lógica específica de informe imagen
        console.log('🖼️ Cargando configuración para formulario de informe imagen');
    }
    
    /**
     * Obtener datos específicos del formulario de informe imagen
     */
    async getSpecificData() {
        const specificData = {};
        
        // Campo de equipo médico específico para informe imagen
        const equipoMedico = document.getElementById('equipoMedico-informe-imagen');
        if (equipoMedico) {
            specificData.equipo_medico = equipoMedico.value || null;
        }
        
        // Campo de descripción/observaciones para informe imagen
        const descripcion = document.getElementById('consulta-textarea-informe-imagen');
        if (descripcion) {
            specificData.descripcion = descripcion.value || null;
        }
        
        // Campo de nota específico para informe imagen
        const nota = document.getElementById('txtnota-informe-imagen');
        if (nota) {
            specificData.txtnota = nota.value || null;
        }
        
        return specificData;
    }
}

// Exportar las clases para uso en otros módulos
if (typeof window !== 'undefined') {
    window.BaseFormComponent = BaseFormComponent;
    window.GeneralForm = GeneralForm;
    window.AnteojosFormComponent = AnteojosFormComponent;
    window.EstudiosFormComponent = EstudiosFormComponent;
    window.InformeImagenFormComponent = InformeImagenFormComponent;
}

} // Cierre del guard de redeclaración
