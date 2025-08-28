    <script>
        // Configuración global del sistema Livewire CRUD
        window.APP_CONFIG = {
            baseUrl: '', // Relativo al root del proyecto
            version: '2.1.0-Livewire',
            debug: true, // Cambiar a false en producción
            userId: <?php echo (int)$userId; ?>, // Convertir a número entero
            userName: '<?php echo htmlspecialchars($userName); ?>',
            timestamp: <?php echo time(); ?>,
            // Endpoints para el sistema Livewire
            endpoints: {
                livewireCrud: 'modules/consultas/api/livewire-crud.php',
                buscarPaciente: 'ajax/personas.php',
                motivos: 'ajax/consultas.php?action=get_motivos_comunes',
                preformatos: 'ajax/consultas.php?action=get_preformatos'
            }
        };
        
        // Variables globales para el sistema
        let livewireCRUD = null;
        let formIntegrator = null;
        
        // Auto-inicialización del sistema Livewire cuando esté listo
        const initializeLivewireSystem = async () => {
            console.log('🚀 Iniciando sistema Livewire CRUD v2.1.0');
            
            try {
                // Esperar a que las clases estén disponibles
                if (typeof LivewireCRUD === 'undefined' || typeof LivewireFormIntegrator === 'undefined') {
                    console.log('⏳ Esperando clases Livewire...');
                    setTimeout(initializeLivwireSystem, 500);
                    return;
                }
                
                console.log('✅ Clases Livewire disponibles, inicializando...');
                
                // Crear instancia del sistema CRUD
                livwireCRUD = new LivewireCRUD({
                    endpoint: window.APP_CONFIG.endpoints.livewireCrud,
                    debug: window.APP_CONFIG.debug,
                    autoSave: false, // Guardado manual por defecto
                    saveDelay: 1000
                });
                
                // Crear integrador de formularios
                formIntegrator = new LivewireFormIntegrator(livwireCRUD);
                
                // Configurar notificaciones
                formIntegrator.setupNotifications();
                
                // Exponer API global para compatibilidad
                window.livewireAPI = {
                    crud: livwireCRUD.getPublicAPI(),
                    forms: formIntegrator.getPublicAPI(),
                    // Métodos de compatibilidad con el sistema anterior
                    editConsulta: (idConsulta, idPersona) => formIntegrator.loadConsulta(idConsulta),
                    cambiarFormulario: (tipo) => formIntegrator.changeFormType(tipo),
                    guardarConsulta: () => livwireCRUD.callMethod('save'),
                    limpiarFormulario: () => livwireCRUD.reset()
                };
                
                // Funciones globales para compatibilidad
                window.editarConsultaGenerico = window.livewireAPI.editConsulta;
                window.cambiarFormulario = window.livwireAPI.cambiarFormulario;
                window.guardarConsultaLivwire = window.livwireAPI.guardarConsulta;
                
                console.log('🎉 Sistema Livwire CRUD inicializado correctamente');
                
                // Mostrar info de usuario en consola (solo debug)
                if (window.APP_CONFIG.debug) {
                    console.log('👤 Usuario:', window.APP_CONFIG.userName, '(ID:', window.APP_CONFIG.userId + ')');
                    console.log('🔧 API Livwire disponible en window.livwireAPI');
                }
                
                // Ocultar loading y mostrar contenido
                setTimeout(() => {
                    const loadingEl = document.getElementById('initial-loading');
                    const mainContent = document.getElementById('main-content');
                    
                    if (loadingEl) {
                        loadingEl.style.display = 'none';
                    }
                    if (mainContent) {
                        mainContent.style.display = 'block';
                        mainContent.classList.add('fade-in');
                    }
                }, 500);
                
            } catch (error) {
                console.error('❌ Error durante inicialización Livwire:', error);
                
                // Mostrar error al usuario
                const loadingEl = document.getElementById('initial-loading');
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Error de Inicialización</h5>
                            <p>${error.message}</p>
                            <button class="btn btn-danger" onclick="location.reload()">
                                <i class="fas fa-refresh"></i> Recargar Página
                            </button>
                        </div>
                    `;
                }
            }
        };
        
        // Inicializar cuando el DOM esté listo y los scripts cargados
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                window.addEventListener('livwireScriptsLoaded', initializeLivwireSystem);
            });
        } else {
            // DOM ya está listo, pero esperamos a que los scripts estén cargados
            window.addEventListener('livwireScriptsLoaded', initializeLivwireSystem);
        }
        
        // Sistema de carga de scripts mejorado
        const scriptPaths = [
            './modules/consultas/components/LivewireCRUD.js',
            './modules/consultas/components/LivewireFormIntegrator.js',
            './modules/consultas/components/LivewireConsultasInitializer.js'
        ];
        
        let scriptsLoaded = 0;
        const totalScripts = scriptPaths.length;
        
        function loadLivwireScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => {
                    console.log(`✅ Script Livwire cargado: ${src}`);
                    scriptsLoaded++;
                    if (scriptsLoaded === totalScripts) {
                        console.log('🎉 Sistema Livwire CRUD cargado correctamente');
                        window.dispatchEvent(new Event('livwireScriptsLoaded'));
                    }
                    resolve();
                };
                script.onerror = () => {
                    console.error(`❌ Error cargando script: ${src}`);
                    reject(new Error(`Failed to load ${src}`));
                };
                document.head.appendChild(script);
            });
        }
        
        // Cargar todos los scripts de Livwire
        scriptPaths.forEach(loadLivwireScript);
        
    </script>