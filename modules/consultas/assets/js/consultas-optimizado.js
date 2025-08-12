/**
 * Sistema de Consultas - JavaScript Optimizado
 * Versión: 2.1.0
 * Arquitectura: Modular, Escalable y Mantenible
 */

;(function(window, document, $) {
    'use strict';
    
    // ====================================
    // CONFIGURACIÓN GLOBAL DEL MÓDULO
    // ====================================
    const ConsultasSystem = {
        version: '2.1.0',
        config: {
            debug: false,
            endpoints: {
                personas: 'ajax/persona.ajax.php',
                consultas: 'ajax/consulta.ajax.php'
            },
            selectors: {
                containerPrincipal: '.consultas-contenedor',
                tipoTabs: '.consultas-tipo-tab',
                formularios: '.formulario-especifico',
                selectorTipo: '#form_type_selector'
            },
            clases: {
                activo: 'active',
                cargando: 'consultas-loading',
                oculto: 'consultas-hidden'
            },
            tiempos: {
                transicion: 300,
                busqueda: 500,
                notificacion: 3000
            }
        },
        estado: {
            tipoActual: 'general',
            pacienteActual: null,
            formularioCargado: false
        },
        cache: new Map()
    };

    // ====================================
    // UTILIDADES GENERALES
    // ====================================
    const Utils = {
        /**
         * Logger inteligente con niveles
         */
        log: function(mensaje, nivel = 'info', datos = null) {
            if (!ConsultasSystem.config.debug && nivel !== 'error') return;
            
            const timestamp = new Date().toISOString();
            const prefix = `[ConsultasSystem ${timestamp}]`;
            
            switch (nivel) {
                case 'error':
                    console.error(prefix, mensaje, datos);
                    break;
                case 'warn':
                    console.warn(prefix, mensaje, datos);
                    break;
                case 'debug':
                    console.debug(prefix, mensaje, datos);
                    break;
                default:
                    console.log(prefix, mensaje, datos);
            }
        },

        /**
         * Debounce optimizado para búsquedas
         */
        debounce: function(func, wait, immediate) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    timeout = null;
                    if (!immediate) func.apply(this, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(this, args);
            };
        },

        /**
         * Validador de datos
         */
        validar: function(valor, tipo) {
            switch (tipo) {
                case 'email':
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
                case 'telefono':
                    return /^\+?[\d\s\-\(\)]{7,15}$/.test(valor);
                case 'documento':
                    return /^\d{6,12}$/.test(valor);
                case 'numero':
                    return !isNaN(parseFloat(valor)) && isFinite(valor);
                default:
                    return valor && valor.trim().length > 0;
            }
        },

        /**
         * Sanitizador de strings
         */
        sanitizar: function(str) {
            if (typeof str !== 'string') return str;
            return str.trim().replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
        },

        /**
         * Generar ID único
         */
        generarId: function(prefijo = 'consultas') {
            return `${prefijo}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        }
    };

    // ====================================
    // GESTOR DE FORMULARIOS
    // ====================================
    const FormularioManager = {
        /**
         * Cambiar tipo de formulario de forma segura
         */
        cambiarTipo: function(nuevoTipo) {
            Utils.log(`Cambiando formulario a: ${nuevoTipo}`);
            
            if (!this.validarTipo(nuevoTipo)) {
                Utils.log(`Tipo de formulario inválido: ${nuevoTipo}`, 'error');
                return false;
            }

            try {
                // Actualizar estado
                ConsultasSystem.estado.tipoActual = nuevoTipo;
                
                // Actualizar URL sin recarga
                this.actualizarURL(nuevoTipo);
                
                // Actualizar interfaz
                this.actualizarTabs(nuevoTipo);
                this.mostrarFormulario(nuevoTipo);
                
                // Actualizar selector oculto
                this.actualizarSelector(nuevoTipo);
                
                // Disparar evento personalizado
                this.dispararEvento('formulario:cambiado', {
                    tipoAnterior: ConsultasSystem.estado.tipoActual,
                    tipoNuevo: nuevoTipo
                });
                
                // Re-inicializar componentes específicos
                setTimeout(() => {
                    this.inicializarFormularioActual(nuevoTipo);
                }, ConsultasSystem.config.tiempos.transicion);
                
                Utils.log(`Formulario cambiado exitosamente a: ${nuevoTipo}`);
                return true;
                
            } catch (error) {
                Utils.log(`Error al cambiar formulario: ${error.message}`, 'error');
                NotificationManager.mostrar('Error al cambiar formulario', 'error');
                return false;
            }
        },

        /**
         * Validar tipo de formulario
         */
        validarTipo: function(tipo) {
            const tiposValidos = ['general', 'anteojos', 'estudios', 'informe_imagen'];
            return tiposValidos.includes(tipo);
        },

        /**
         * Actualizar URL del navegador
         */
        actualizarURL: function(tipo) {
            const url = new URL(window.location);
            url.searchParams.set('form_type', tipo);
            window.history.pushState({formType: tipo}, '', url);
        },

        /**
         * Actualizar pestañas visuales
         */
        actualizarTabs: function(tipoActivo) {
            document.querySelectorAll(ConsultasSystem.config.selectors.tipoTabs)
                .forEach(tab => {
                    const tipoTab = tab.dataset.formType;
                    if (tipoTab === tipoActivo) {
                        tab.classList.add(ConsultasSystem.config.clases.activo);
                    } else {
                        tab.classList.remove(ConsultasSystem.config.clases.activo);
                    }
                });
        },

        /**
         * Mostrar formulario correspondiente
         */
        mostrarFormulario: function(tipo) {
            // Ocultar todos los formularios
            document.querySelectorAll(ConsultasSystem.config.selectors.formularios)
                .forEach(form => {
                    form.style.display = 'none';
                });

            // Mostrar formulario específico
            const formularioActivo = document.getElementById(`formulario-${tipo}`);
            if (formularioActivo) {
                formularioActivo.style.display = 'block';
                Utils.log(`Formulario mostrado: ${tipo}`);
            } else {
                Utils.log(`Formulario no encontrado: formulario-${tipo}`, 'error');
                NotificationManager.mostrar(`Error: Formulario ${tipo} no encontrado`, 'error');
            }
        },

        /**
         * Actualizar selector oculto
         */
        actualizarSelector: function(tipo) {
            const selector = document.querySelector(ConsultasSystem.config.selectors.selectorTipo);
            if (selector) {
                selector.value = tipo;
            }
        },

        /**
         * Disparar evento personalizado
         */
        dispararEvento: function(nombre, datos) {
            const evento = new CustomEvent(nombre, {
                detail: datos,
                bubbles: true,
                cancelable: true
            });
            document.dispatchEvent(evento);
        },

        /**
         * Inicializar formulario específico
         */
        inicializarFormularioActual: function(tipo) {
            Utils.log(`Inicializando formulario específico: ${tipo}`);
            
            switch (tipo) {
                case 'anteojos':
                    this.inicializarAnteojos();
                    break;
                case 'estudios':
                    this.inicializarEstudios();
                    break;
                case 'informe_imagen':
                    this.inicializarInformeImagen();
                    break;
                case 'general':
                default:
                    this.inicializarGeneral();
                    break;
            }
        },

        /**
         * Inicialización específica para formulario general
         */
        inicializarGeneral: function() {
            Utils.log('Configurando formulario general');
            BuscadorPacientes.inicializar();
        },

        /**
         * Inicialización específica para anteojos
         */
        inicializarAnteojos: function() {
            Utils.log('Configurando formulario de anteojos');
            BuscadorPacientes.inicializar();
            this.configurarCamposNumericos(['esf_od', 'cil_od', 'eje_od', 'esf_oi', 'cil_oi', 'eje_oi']);
        },

        /**
         * Inicialización específica para estudios
         */
        inicializarEstudios: function() {
            Utils.log('Configurando formulario de estudios');
            BuscadorPacientes.inicializar();
            EmailManager.inicializar();
        },

        /**
         * Inicialización específica para informe imagen
         */
        inicializarInformeImagen: function() {
            Utils.log('Configurando formulario de informe imagen');
            BuscadorPacientes.inicializar();
            EditorManager.inicializar();
        },

        /**
         * Configurar campos numéricos con validación
         */
        configurarCamposNumericos: function(campos) {
            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (elemento) {
                    elemento.addEventListener('input', function() {
                        const valor = this.value;
                        if (valor && !Utils.validar(valor, 'numero')) {
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                        }
                    });
                }
            });
        }
    };

    // ====================================
    // BUSCADOR DE PACIENTES OPTIMIZADO
    // ====================================
    const BuscadorPacientes = {
        cache: new Map(),
        ultimaBusqueda: '',

        /**
         * Inicializar buscador de pacientes
         */
        inicializar: function() {
            Utils.log('Inicializando buscador de pacientes');
            this.configurarAutocompletado();
            this.configurarBotonesBusqueda();
        },

        /**
         * Configurar autocompletado inteligente
         */
        configurarAutocompletado: function() {
            const inputBusqueda = $('#inputBuscarPersona, #txtdocumento, #txtficha');
            
            if (inputBusqueda.length === 0) {
                Utils.log('No se encontraron campos de búsqueda', 'warn');
                return;
            }

            inputBusqueda.autocomplete({
                source: Utils.debounce((request, response) => {
                    this.buscarPacientes(request.term, response);
                }, ConsultasSystem.config.tiempos.busqueda),
                minLength: 2,
                delay: ConsultasSystem.config.tiempos.busqueda,
                select: (event, ui) => {
                    if (ui.item && ui.item.id) {
                        this.seleccionarPaciente(ui.item);
                    }
                },
                focus: (event, ui) => {
                    event.preventDefault();
                }
            });

            Utils.log('Autocompletado configurado exitosamente');
        },

        /**
         * Buscar pacientes con cache inteligente
         */
        buscarPacientes: function(termino, callback) {
            termino = Utils.sanitizar(termino);
            
            if (termino.length < 2) {
                callback([]);
                return;
            }

            // Verificar cache
            if (this.cache.has(termino)) {
                Utils.log(`Usando cache para búsqueda: ${termino}`);
                callback(this.cache.get(termino));
                return;
            }

            Utils.log(`Buscando pacientes: ${termino}`);

            $.ajax({
                url: ConsultasSystem.config.endpoints.personas,
                type: 'POST',
                dataType: 'json',
                data: {
                    accion: 'buscar_por_nombre',
                    termino: termino
                },
                success: (data) => {
                    const resultados = this.procesarResultados(data);
                    
                    // Guardar en cache
                    this.cache.set(termino, resultados);
                    
                    // Limpiar cache si crece mucho
                    if (this.cache.size > 100) {
                        this.limpiarCache();
                    }
                    
                    callback(resultados);
                },
                error: (xhr, status, error) => {
                    Utils.log(`Error en búsqueda de pacientes: ${error}`, 'error');
                    callback([]);
                    NotificationManager.mostrar('Error en la búsqueda de pacientes', 'warning');
                }
            });
        },

        /**
         * Procesar resultados de búsqueda
         */
        procesarResultados: function(data) {
            if (!Array.isArray(data)) {
                return [];
            }

            return data.map(paciente => ({
                id: paciente.id_persona || paciente.id,
                label: `${paciente.nombres || paciente.nombre || ''} ${paciente.apellidos || ''} - CI: ${paciente.documento || 'Sin documento'}`.trim(),
                value: `${paciente.nombres || paciente.nombre || ''} ${paciente.apellidos || ''}`.trim(),
                data: paciente
            }));
        },

        /**
         * Seleccionar paciente encontrado
         */
        seleccionarPaciente: function(pacienteItem) {
            Utils.log('Paciente seleccionado:', pacienteItem);
            
            ConsultasSystem.estado.pacienteActual = pacienteItem.data;
            
            // Rellenar campos del formulario
            this.rellenarCamposPaciente(pacienteItem.data);
            
            // Disparar evento
            FormularioManager.dispararEvento('paciente:seleccionado', {
                paciente: pacienteItem.data
            });
            
            NotificationManager.mostrar('Paciente seleccionado correctamente', 'success');
        },

        /**
         * Rellenar campos con datos del paciente
         */
        rellenarCamposPaciente: function(paciente) {
            const mapeosCampos = {
                'txtdocumento': paciente.documento || paciente.cedula,
                'txtficha': paciente.nro_ficha,
                'inputBuscarPersona': `${paciente.nombres || paciente.nombre || ''} ${paciente.apellidos || ''}`.trim(),
                'id_persona_consulta': paciente.id_persona || paciente.id,
                'idPersona': paciente.id_persona || paciente.id
            };

            Object.entries(mapeosCampos).forEach(([campo, valor]) => {
                const elemento = document.getElementById(campo);
                if (elemento && valor) {
                    elemento.value = valor;
                }
            });
        },

        /**
         * Configurar botones de búsqueda
         */
        configurarBotonesBusqueda: function() {
            const btnBuscar = document.getElementById('btnBuscarPersona');
            const btnLimpiar = document.getElementById('btnLimpiarPersona');

            if (btnBuscar) {
                btnBuscar.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.buscarPorParametros();
                });
            }

            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.limpiarFormulario();
                });
            }
        },

        /**
         * Búsqueda por parámetros específicos
         */
        buscarPorParametros: function() {
            const documento = Utils.sanitizar(document.getElementById('txtdocumento')?.value || '');
            const ficha = Utils.sanitizar(document.getElementById('txtficha')?.value || '');
            const nombre = Utils.sanitizar(document.getElementById('inputBuscarPersona')?.value || '');

            if (!documento && !ficha && !nombre) {
                NotificationManager.mostrar('Ingrese al menos un criterio de búsqueda', 'warning');
                return;
            }

            Utils.log('Buscando por parámetros:', {documento, ficha, nombre});

            // Mostrar loading
            NotificationManager.mostrarLoading('Buscando paciente...');

            $.ajax({
                url: ConsultasSystem.config.endpoints.personas,
                type: 'POST',
                dataType: 'json',
                data: {
                    operacion: 'buscarparam',
                    documento: documento,
                    nro_ficha: ficha,
                    nombre: nombre
                },
                success: (response) => {
                    NotificationManager.ocultarLoading();
                    
                    if (response.status === 'success') {
                        if (response.multiple && Array.isArray(response.data)) {
                            this.mostrarResultadosMultiples(response.data);
                        } else {
                            this.rellenarCamposPaciente(response.data);
                            NotificationManager.mostrar('Paciente encontrado', 'success');
                        }
                    } else {
                        NotificationManager.mostrar(response.message || 'No se encontraron resultados', 'info');
                    }
                },
                error: (xhr, status, error) => {
                    NotificationManager.ocultarLoading();
                    Utils.log(`Error en búsqueda por parámetros: ${error}`, 'error');
                    NotificationManager.mostrar('Error en la búsqueda', 'error');
                }
            });
        },

        /**
         * Mostrar resultados múltiples
         */
        mostrarResultadosMultiples: function(resultados) {
            // TODO: Implementar modal de selección múltiple
            Utils.log('Múltiples resultados encontrados:', resultados);
            NotificationManager.mostrar(`Se encontraron ${resultados.length} pacientes. Seleccione uno del autocompletado.`, 'info');
        },

        /**
         * Limpiar formulario de paciente
         */
        limpiarFormulario: function() {
            const campos = ['txtdocumento', 'txtficha', 'inputBuscarPersona', 'id_persona_consulta', 'idPersona'];
            
            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (elemento) {
                    elemento.value = '';
                    elemento.classList.remove('is-invalid', 'is-valid');
                }
            });

            ConsultasSystem.estado.pacienteActual = null;
            
            FormularioManager.dispararEvento('paciente:limpiado', {});
            NotificationManager.mostrar('Formulario limpiado', 'info');
        },

        /**
         * Limpiar cache de búsquedas
         */
        limpiarCache: function() {
            const keys = Array.from(this.cache.keys());
            // Mantener solo los últimos 50
            keys.slice(0, -50).forEach(key => this.cache.delete(key));
            Utils.log('Cache de búsquedas limpiado');
        }
    };

    // ====================================
    // GESTOR DE NOTIFICACIONES
    // ====================================
    const NotificationManager = {
        /**
         * Mostrar notificación
         */
        mostrar: function(mensaje, tipo = 'info', duracion = ConsultasSystem.config.tiempos.notificacion) {
            if (typeof Swal !== 'undefined') {
                const iconos = {
                    success: 'success',
                    error: 'error',
                    warning: 'warning',
                    info: 'info'
                };

                Swal.fire({
                    title: this.obtenerTitulo(tipo),
                    text: mensaje,
                    icon: iconos[tipo] || 'info',
                    timer: duracion,
                    showConfirmButton: tipo === 'error',
                    toast: tipo !== 'error',
                    position: tipo === 'error' ? 'center' : 'top-end'
                });
            } else {
                // Fallback para navegadores sin SweetAlert
                console.log(`[${tipo.toUpperCase()}] ${mensaje}`);
                alert(`${this.obtenerTitulo(tipo)}: ${mensaje}`);
            }
        },

        /**
         * Mostrar loading
         */
        mostrarLoading: function(mensaje = 'Cargando...') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: mensaje,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        },

        /**
         * Ocultar loading
         */
        ocultarLoading: function() {
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
        },

        /**
         * Obtener título según tipo
         */
        obtenerTitulo: function(tipo) {
            const titulos = {
                success: 'Éxito',
                error: 'Error',
                warning: 'Advertencia',
                info: 'Información'
            };
            return titulos[tipo] || 'Notificación';
        }
    };

    // ====================================
    // GESTORES ESPECÍFICOS
    // ====================================
    const EmailManager = {
        inicializar: function() {
            Utils.log('Inicializando gestor de emails');
            // TODO: Implementar validación de emails
        }
    };

    const EditorManager = {
        inicializar: function() {
            Utils.log('Inicializando editor de texto');
            // TODO: Implementar editor rich text
        }
    };

    // ====================================
    // FUNCIÓN GLOBAL PARA CAMBIO DE FORMULARIOS
    // ====================================
    window.cambiarFormularioSeguro = function(tipo) {
        return FormularioManager.cambiarTipo(tipo);
    };

    // ====================================
    // INICIALIZACIÓN DEL SISTEMA
    // ====================================
    document.addEventListener('DOMContentLoaded', function() {
        Utils.log('🚀 Iniciando Sistema de Consultas Optimizado v' + ConsultasSystem.version);
        
        // Configurar modo debug
        ConsultasSystem.config.debug = window.CONSULTAS_CONFIG?.debug || false;
        
        // Obtener tipo inicial
        const tipoInicial = window.CONSULTAS_CONFIG?.tipoFormulario || 'general';
        ConsultasSystem.estado.tipoActual = tipoInicial;
        
        // Inicializar componentes
        FormularioManager.inicializarFormularioActual(tipoInicial);
        
        // Configurar navegación del historial
        window.addEventListener('popstate', function(event) {
            if (event.state && event.state.formType) {
                FormularioManager.cambiarTipo(event.state.formType);
            }
        });
        
        // Hacer disponible globalmente para debugging
        if (ConsultasSystem.config.debug) {
            window.ConsultasSystem = ConsultasSystem;
            Utils.log('Sistema disponible globalmente en window.ConsultasSystem');
        }
        
        Utils.log('✅ Sistema de Consultas inicializado correctamente');
    });

    // ====================================
    // MANEJO DE ERRORES GLOBALES
    // ====================================
    window.addEventListener('error', function(event) {
        Utils.log(`Error global capturado: ${event.message}`, 'error', {
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno
        });
    });

    // ====================================
    // EXPORTAR MÓDULO (para uso externo si es necesario)
    // ====================================
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = ConsultasSystem;
    }

})(window, document, jQuery);
