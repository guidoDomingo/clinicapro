<div class="container icd11-container">
    <!-- Contenedor para alertas -->
    <div id="alerts-container"></div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Herramienta de Codificación ICD-11</h2>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <p>Esta página integra la herramienta oficial de codificación ICD-11 de la Organización Mundial de la Salud.</p>
                    </div> <!-- Panel de código y diagnóstico seleccionados -->
                   

                    <!-- Contenedor para la herramienta de codificación -->
                    <div class="coding-tool-container" style="height: 700px; margin-bottom: 20px; position: relative;">
                        <!-- Spinner de carga -->
                        <div id="loading-spinner" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; background-color: #f8f9fa; z-index: 1000;">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2">Cargando herramienta de codificación ICD-11...</p>
                            </div>
                        </div>

                        <!-- iframe con la herramienta oficial de codificación de la OMS -->
                        <iframe
                            id="coding-tool-iframe"
                            src="https://icd.who.int/ct/icd11_mms/es/2022-02"
                            style="width: 100%; height: 100%; border: 1px solid #ddd;"
                            onload="iframeLoaded()"
                            onerror="handleIframeError()">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Implementación interna del cliente ICD-11 para evitar problemas de carga -->
<script>
    // Cliente simplificado para la API ICD-11
    class ICD11ApiClient {
        constructor() {
            this.endpoint = 'ajax/icd11.ajax.php';
            this.initialized = false;

            // Evento personalizado para cuando se selecciona un código
            this.codeSelectedEvent = new CustomEvent('icd11:codeSelected', {
                bubbles: true,
                cancelable: true,
                detail: null
            });
        }

        // Inicializa el cliente
        async initialize() {
            if (this.initialized) {
                return Promise.resolve(true);
            }

            try {
                // Probar conectividad con el endpoint
                const formData = new FormData();
                formData.append('action', 'searchByCode');
                formData.append('code', 'MD12'); // Código de prueba

                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }

                const jsonResponse = await response.json();
                if (!jsonResponse.success) {
                    throw new Error(`Error en la API: ${jsonResponse.message || 'Desconocido'}`);
                }

                console.log('API ICD-11 funcionando correctamente');
                this.initialized = true;
                return true;
            } catch (error) {
                console.error('Error al inicializar el cliente ICD-11:', error);
                throw error;
            }
        }

        // Busca un término en la API
        async searchByTerm(term, language = 'es') {
            if (!term) {
                return Promise.reject(new Error('El término es requerido'));
            }

            if (!this.initialized) {
                await this.initialize();
            }

            const formData = new FormData();
            formData.append('action', 'searchByTerm');
            formData.append('term', term);
            formData.append('language', language);

            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }

                const jsonResponse = await response.json();
                if (!jsonResponse.success) {
                    throw new Error(jsonResponse.message || 'Error desconocido en la respuesta del servidor');
                }

                return jsonResponse.data;
            } catch (error) {
                console.error('Error en solicitud searchByTerm:', error);
                throw error;
            }
        }
        // Busca un código en la API
        async searchByCode(code) {
            if (!code) {
                return Promise.reject(new Error('El código es requerido'));
            }

            if (!this.initialized) {
                await this.initialize();
            }

            const formData = new FormData();
            formData.append('action', 'searchByCode');
            formData.append('code', code);
            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }

                const jsonResponse = await response.json();
                if (!jsonResponse.success) {
                    throw new Error(jsonResponse.message || 'Error desconocido en la respuesta del servidor');
                }

                return jsonResponse.data;
            } catch (error) {
                console.error('Error en solicitud searchByCode:', error);
                throw error;
            }
        }

        /**
         * Dispara el evento de código seleccionado
         * @param {Object} data - Datos del código seleccionado (code, title, uri)
         */
        dispatchCodeSelected(data) {
            // Asegurar que recibimos datos válidos
            if (!data || !data.code) {
                console.error('Datos de código seleccionado inválidos:', data);
                return;
            }

            // Crear un evento personalizado
            const codeSelectedEvent = new CustomEvent('icd11:codeSelected', {
                bubbles: true,
                cancelable: true,
                detail: data
            });

            // Disparar el evento en el documento
            document.dispatchEvent(codeSelectedEvent);

            // Actualizar los campos de código y diagnóstico si existen
            try {
                const codeField = document.getElementById('selected-code');
                const diagnosisField = document.getElementById('selected-diagnosis');

                if (codeField) {
                    codeField.value = data.code || '';
                }

                if (diagnosisField) {
                    diagnosisField.value = data.title || '';
                }

                console.log('Código ICD-11 seleccionado:', data);
            } catch (e) {
                console.error('Error al procesar código seleccionado:', e);
            }
        }
    }

    // Crear la instancia global
    window.icd11Client = new ICD11ApiClient();
    console.log('Cliente ICD-11 creado. Se inicializará cuando sea necesario.');

    // COMENTADO: Este event listener está duplicado con icd11-integration.js
    // y causaba inserción doble del contenido
    /*
    // Agregar manejador de eventos para la selección de códigos
    document.addEventListener('icd11:codeSelected', function(event) {
        try {
            console.log('Evento icd11:codeSelected recibido:', event.detail);

            // Actualizar los campos de código y diagnóstico
            const codeField = document.getElementById('selected-code');
            const diagnosisField = document.getElementById('selected-diagnosis');

            if (codeField && event.detail && event.detail.code) {
                codeField.value = event.detail.code || '';
                codeField.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                console.log('Campo de código actualizado:', event.detail.code);
            }

            if (diagnosisField && event.detail && event.detail.title) {
                diagnosisField.value = event.detail.title || '';
                diagnosisField.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                console.log('Campo de diagnóstico actualizado:', event.detail.title);
            }
        } catch (e) {
            console.error('Error al procesar evento de código seleccionado:', e);
        }
    });
    */
</script>
<script>
    function handleIframeError() {
        document.getElementById('loading-spinner').innerHTML =
            '<div class="alert alert-danger m-3">' +
            '<h4 class="alert-heading">Error al cargar la herramienta de codificación</h4>' +
            '<p>No se pudo cargar la herramienta de codificación desde el servidor de la OMS.</p>' +
            '<hr>' +
            '<p class="mb-0">Sugerencias:' +
            '<ul>' +
            '<li>Verifique su conexión a Internet</li>' +
            '<li>Compruebe que los servidores de la OMS estén accesibles</li>' +
            '<li>Intente recargar la página</li>' +
            '</ul></p></div>';
    }

    function iframeLoaded() {
        // Ocultar el spinner de carga
        document.getElementById('loading-spinner').style.display = 'none';

        // Configurar eventos para los botones
        setupButtonEvents();
    }

    function setupButtonEvents() {
        // Verificar que existen los elementos antes de asignar eventos
        const searchButton = document.getElementById('search-code-btn');
        const codeInput = document.getElementById('selected-code');
        const clearCodeButton = document.getElementById('clear-code-btn');
        const clearDiagnosisButton = document.getElementById('clear-diagnosis-btn');
        const saveButton = document.getElementById('save-selection-btn');

        // Verificar cada elemento antes de asignar eventos
        if (searchButton) {
            searchButton.addEventListener('click', function() {
                searchCodeFromApi();
            });
        }

        // Evento para buscar al presionar Enter en el campo de código
        if (codeInput) {
            codeInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchCodeFromApi();
                }
            });
        }

        // Botón para limpiar código
        if (clearCodeButton) {
            clearCodeButton.addEventListener('click', function() {
                if (codeInput) {
                    codeInput.value = '';
                    codeInput.focus();
                }
            });
        }

        // Botón para limpiar diagnóstico
        if (clearDiagnosisButton) {
            clearDiagnosisButton.addEventListener('click', function() {
                const diagnosisField = document.getElementById('selected-diagnosis');
                if (diagnosisField) {
                    diagnosisField.value = '';
                    diagnosisField.focus();
                }
            });
        }

        // Botón para guardar selección
        if (saveButton) {
            saveButton.addEventListener('click', function() {
                const code = document.getElementById('selected-code').value.trim();
                const diagnosis = document.getElementById('selected-diagnosis').value.trim();

                if (!code) {
                    showAlert('warning', 'Por favor, introduzca un código ICD-11.');
                    document.getElementById('selected-code').focus();
                    return;
                }

                if (!diagnosis) {
                    showAlert('warning', 'Por favor, introduzca un diagnóstico.');
                    document.getElementById('selected-diagnosis').focus();
                    return;
                }

                // Aquí puedes añadir el código para guardar o procesar la selección
                // Por ejemplo, enviarla a un servidor, mostrarla en otra parte, etc.

                // Por ahora, solo mostramos un mensaje de éxito
                showAlert('success', 'Selección guardada: ' + code + ' - ' + diagnosis);

                // Destacar los campos brevemente
                flashElement(document.getElementById('selected-code'));
                flashElement(document.getElementById('selected-diagnosis'));
            });

            // Botón para copiar al portapapeles
            document.getElementById('copy-to-clipboard-btn').addEventListener('click', function() {
                const code = document.getElementById('selected-code').value.trim();
                const diagnosis = document.getElementById('selected-diagnosis').value.trim();

                if (!code && !diagnosis) {
                    showAlert('warning', 'No hay datos para copiar.');
                    return;
                }

                const textToCopy = `${code} - ${diagnosis}`;

                // Copiar al portapapeles
                navigator.clipboard.writeText(textToCopy).then(function() {
                    showAlert('success', 'Copiado al portapapeles: ' + textToCopy);
                }, function(err) {
                    console.error('Error al copiar: ', err);
                    showAlert('danger', 'No se pudo copiar al portapapeles. Por favor, copie manualmente.');
                });
            });
        }

        function searchCodeFromApi() {
            const code = document.getElementById('selected-code').value.trim();
            if (!code) {
                showAlert('warning', 'Por favor, introduzca un código ICD-11 para buscar.');
                document.getElementById('selected-code').focus();
                return;
            }

            // Mostrar que estamos buscando
            const apiStatus = document.getElementById('api-status');
            apiStatus.innerHTML = '<span class="badge bg-info"><i class="fas fa-spinner fa-spin"></i> Buscando...</span>';

            // Verificar que existe el cliente ICD-11
            if (!window.icd11Client) {
                console.error('Error: Cliente ICD-11 no disponible');
                apiStatus.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>';
                showAlert('danger', 'Error interno: Cliente ICD-11 no disponible. Por favor, recargue la página.');
                setTimeout(() => {
                    apiStatus.innerHTML = '';
                }, 3000);
                return;
            }

            // Usar nuestro cliente ICD-11 para buscar el código
            window.icd11Client.searchByCode(code)
                .then(data => {
                    if (data && data.destinationEntities && data.destinationEntities.length > 0) {
                        // Obtener la primera entidad
                        const entity = data.destinationEntities[0];

                        // Actualizar el campo de diagnóstico con el resultado
                        document.getElementById('selected-diagnosis').value = entity.title || '';

                        // Guardar los datos completos en un atributo para uso posterior si es necesario
                        document.getElementById('selected-code').setAttribute('data-full-entity',
                            JSON.stringify(entity));

                        // Mostrar resultado positivo
                        apiStatus.innerHTML = '<span class="badge bg-success"><i class="fas fa-check"></i> Encontrado</span>';

                        // Destacar los campos brevemente
                        flashElement(document.getElementById('selected-code'));
                        flashElement(document.getElementById('selected-diagnosis'));

                        // Eliminar el estado después de un tiempo
                        setTimeout(() => {
                            apiStatus.innerHTML = '';
                        }, 3000);

                        // Opcional: mostrar el modal con detalles completos
                        if (typeof fetchEntityDetails === 'function' && entity.id) {
                            fetchEntityDetails(entity.id, code, entity.title);
                        }
                    } else {
                        // Mostrar error
                        apiStatus.innerHTML = '<span class="badge bg-danger"><i class="fas fa-times"></i> No encontrado</span>';
                        document.getElementById('selected-diagnosis').value = '';
                        showAlert('warning', `No se encontró el código ICD-11 "${code}". Por favor, verifique e intente de nuevo.`);

                        // Eliminar el estado después de un tiempo
                        setTimeout(() => {
                            apiStatus.innerHTML = '';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    apiStatus.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Error</span>';
                    showAlert('danger', 'Error al consultar la API: ' + (error.message || 'Error desconocido'));

                    // Eliminar el estado después de un tiempo
                    setTimeout(() => {
                        apiStatus.innerHTML = '';
                    }, 3000);
                });
        }

        function showAlert(type, message) {
            // Priorizar alertify si está disponible
            if (typeof alertify !== 'undefined') {
                switch(type) {
                    case 'success':
                        alertify.success(message);
                        break;
                    case 'warning':
                        alertify.warning(message);
                        break;
                    case 'danger':
                    case 'error':
                        alertify.error(message);
                        break;
                    default:
                        alertify.message(message);
                }
                return; // Salir temprano si se usó alertify
            }
            
            // Fallback: Crear el elemento de alerta Bootstrap
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

            // Encontrar el contenedor de alertas
            const container = document.getElementById('alerts-container');
            if (!container) {
                // Si no hay contenedor, usar console como último recurso
                console.log(`Alert ${type}: ${message}`);
                return;
            }

            // Insertar la alerta al inicio del contenedor
            container.appendChild(alertDiv);

            // Eliminar la alerta después de 5 segundos
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => {
                    if (container.contains(alertDiv)) {
                        container.removeChild(alertDiv);
                    }
                }, 150);
            }, 5000);
        }

        function flashElement(element) {
            // Añadir clase para destacar brevemente el elemento
            element.classList.add('bg-success', 'text-white');

            // Eliminar la clase después de un breve período
            setTimeout(function() {
                element.classList.remove('bg-success', 'text-white');
            }, 1000);
        }

        // Verificar si el iframe se cargó correctamente después de un tiempo
        window.addEventListener('load', function() {
            setTimeout(function() {
                const iframe = document.getElementById('coding-tool-iframe');
                if (iframe && document.getElementById('loading-spinner').style.display !== 'none') {
                    handleIframeError();
                }
            }, 15000); // Esperar 15 segundos como máximo

            // Inicializar tooltips de Bootstrap si está disponible
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    }
</script>

<script>
    // Código para integración directa con la API de ICD-11
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar botón directo para búsqueda API
        const searchContainer = document.createElement('div');
        searchContainer.className = 'card mb-3';
        searchContainer.innerHTML = `
            <div class="card-header bg-light">
                <h5 class="mb-0">Búsqueda directa API ICD-11</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="api-search-term">Buscar término médico:</label>
                            <input type="text" id="api-search-term" class="form-control" placeholder="Ej: tos, diabetes, covid">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button id="btn-api-search" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
                
                <div id="api-results" class="mt-3" style="display: none;">
                    <h6>Resultados de búsqueda:</h6>
                    <div class="list-group" id="api-results-list">
                    </div>
                </div>
            </div>
        `;

        // Insertar el contenedor de búsqueda antes del iframe
        const iframeContainer = document.querySelector('.coding-tool-container');
        if (iframeContainer) {
            iframeContainer.parentNode.insertBefore(searchContainer, iframeContainer);
        }

        // Configurar evento para el botón de búsqueda
        const searchButton = document.getElementById('btn-api-search');
        const searchInput = document.getElementById('api-search-term');
        const resultsContainer = document.getElementById('api-results');
        const resultsList = document.getElementById('api-results-list');

        if (searchButton && searchInput) {
            searchButton.addEventListener('click', async function() {
                const term = searchInput.value.trim();
                if (!term) return;
                // Mostrar spinner
                searchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando...';
                searchButton.disabled = true;

                try {
                    // Verificar que existe el cliente (debería existir siempre con nuestra implementación interna)
                    if (!window.icd11Client) {
                        console.error('El cliente ICD-11 no está disponible');
                        throw new Error('Error interno: cliente ICD-11 no disponible. Por favor, recargue la página.');
                    }

                    // Iniciar búsqueda (la inicialización se maneja dentro de searchByTerm)
                    console.log('Ejecutando búsqueda con término:', term);
                    const results = await window.icd11Client.searchByTerm(term);

                    // Vaciar y mostrar contenedor de resultados
                    resultsList.innerHTML = '';
                    resultsContainer.style.display = 'block';
                    // Si no hay resultados válidos de la API
                    if (!results || !results.destinationEntities || results.destinationEntities.length === 0) {
                        resultsList.innerHTML = '<div class="alert alert-info">No se encontraron resultados para este término en la API oficial de ICD-11</div>';
                        return;
                    }
                    // Mostrar los resultados
                    results.destinationEntities.slice(0, 10).forEach(function(entity) {
                        const code = entity.theCode || '';
                        const title = entity.title || '';

                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${code}</h6>
                            </div>
                            <p class="mb-1">${title}</p>
                        `; // Evento para seleccionar el código
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            try {
                                // Mostrar el modal de detalles si la función está disponible
                                if (typeof fetchEntityDetails === 'function') {
                                    // Guardar información básica para usar después
                                    const entityInfo = {
                                        code: code,
                                        title: title,
                                        uri: entity.id || ''
                                    };

                                    // Mostrar el modal con detalles ampliados
                                    fetchEntityDetails(entity.id, code, title);

                                    // Guardar en sessionStorage para recuperar en caso de error
                                    try {
                                        sessionStorage.setItem('lastSelectedIcd11', JSON.stringify(entityInfo));
                                    } catch (e) {
                                        console.warn('No se pudo guardar en sessionStorage', e);
                                    }

                                    return; // Salir temprano, el resto se maneja en el modal
                                }

                                // Código original como fallback si no está disponible el modal
                                // Verificar que el cliente existe y tiene el método
                                if (window.icd11Client) {
                                    // Disparar evento de código seleccionado
                                    if (typeof window.icd11Client.dispatchCodeSelected === 'function') {
                                        // Usar el método existente
                                        window.icd11Client.dispatchCodeSelected({
                                            code: code,
                                            title: title,
                                            uri: entity.id || ''
                                        });
                                    } else {
                                        console.warn('Método dispatchCodeSelected no encontrado, creando evento manualmente');

                                        // Crear y disparar evento manualmente
                                        const codeSelectedEvent = new CustomEvent('icd11:codeSelected', {
                                            bubbles: true,
                                            cancelable: true,
                                            detail: {
                                                code: code,
                                                title: title,
                                                uri: entity.id || ''
                                            }
                                        });

                                        // Disparar el evento en el documento
                                        document.dispatchEvent(codeSelectedEvent);
                                        console.log('Evento icd11:codeSelected disparado con datos:', code, title);
                                    }

                                    // Como respaldo, también actualizar directamente los campos
                                    const codeField = document.getElementById('selected-code');
                                    const diagnosisField = document.getElementById('selected-diagnosis');

                                    if (codeField) codeField.value = code || '';
                                    if (diagnosisField) diagnosisField.value = title || '';

                                    // Mostrar un mensaje de selección exitosa
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-success mt-3';
                                    alertDiv.innerHTML = `<i class="fas fa-check-circle"></i> Código <strong>${code}</strong> seleccionado correctamente.`;
                                    resultsContainer.appendChild(alertDiv);

                                    // Ocultar el mensaje después de 3 segundos
                                    setTimeout(() => {
                                        if (alertDiv.parentNode === resultsContainer) {
                                            alertDiv.style.display = 'none';
                                        }
                                    }, 3000);
                                } else {
                                    console.error('Error: window.icd11Client no está disponible');
                                    throw new Error('Error interno: cliente ICD-11 no disponible');
                                }
                            } catch (err) {
                                console.error('Error al seleccionar código:', err);
                                alert('Error al seleccionar el código. Por favor, intente nuevamente.');
                            }
                        });

                        resultsList.appendChild(item);
                    });
                } catch (error) {
                    console.error('Error al buscar término:', error); // Mostrar mensaje de error adaptado al tipo
                    if (error.message.includes('no disponible') || error.message.includes('Error interno')) {
                        resultsList.innerHTML = `
                            <div class="alert alert-danger">
                                <h5>Error en el cliente ICD-11</h5>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-danger" onclick="location.reload()">
                                        <i class="fas fa-sync"></i> Recargar página
                                    </button>
                                </div>
                            </div>
                        `;
                    } else {
                        resultsList.innerHTML = `
                            <div class="alert alert-danger">
                                <h5>Error en la búsqueda</h5>
                                <small>Solo se utilizan datos oficiales de la API de ICD-11.</small>
                            </div>
                        `;
                    }

                    resultsContainer.style.display = 'block';
                } finally {
                    // Restaurar botón
                    searchButton.innerHTML = '<i class="fas fa-search"></i> Buscar';
                    searchButton.disabled = false;
                }
            });
            // Permitir buscar presionando Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchButton.click();
                }
            });
        }
    });
</script>

<script>
    // Función simplificada para manejar errores de API
    function handleApiError(error) {
        console.error('Error en la API de ICD-11:', error);
        showAlert('danger', 'Error en la API de ICD-11: ' + error.message);
    }
    // Inicializar el cliente cuando sea necesario
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Documento cargado. El cliente ICD-11 se inicializará en la primera búsqueda.');

        // Agregar manejadores para cambios de pestaña
        setupTabNavigation();
    });
    // Función para configurar la navegación entre pestañas
    function setupTabNavigation() {
        try {
            // Encontrar todos los enlaces de navegación de pestañas
            const tabLinks = document.querySelectorAll('.nav-link');

            // Configurar manejador de eventos para cada enlace
            tabLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    // Identificar la pestaña actual y destino
                    const currentTab = link.getAttribute('href');
                    console.log('Cambio de pestaña a:', currentTab);

                    // Si se está yendo a la pestaña de registro después de ICD, preparar la transición
                    if (currentTab === '#activity' && document.querySelector('.nav-link.active')?.getAttribute('href') === '#icd') {
                        console.log('Transición de ICD a Registro, verificando textarea...');

                        // Esperar a que cambie la pestaña y luego verificar el textarea
                        setTimeout(() => {
                            const textarea = window.findConsultaTextarea();
                            if (textarea) {
                                console.log('Textarea de consulta encontrado después del cambio de pestaña');
                            } else {
                                console.warn('No se encontró el textarea después del cambio de pestaña');
                            }
                        }, 300);
                    }
                });
            });

            console.log('Configurada navegación entre pestañas para ICD-11');
        } catch (e) {
            console.error('Error al configurar navegación entre pestañas:', e);
        }
    }
</script>

<!-- Estilos para resaltar el textarea después de la inserción -->
<style>
    @keyframes highlightTextarea {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
        }

        50% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0.8);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    .highlight-textarea {
        animation: highlightTextarea 2s ease-out;
        background-color: #d4edda !important;
        border-color: #28a745 !important;
        transition: background-color 2s ease, border-color 2s ease;
    }
</style>

<!-- Las funciones para la integración de ICD-11 se cargan desde icd11-integration.js -->
<script>
    // Las funciones findConsultaTextarea y selectCodeWithoutDetails ahora se cargan desde icd11-integration.js
</script>

<!-- Modal para detalles de diagnóstico -->
<div class="modal fade" id="icd-details-modal" tabindex="-1" aria-labelledby="icd-details-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="icd-details-modal-label">Detalles del Diagnóstico</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="icd-details-loading" class="text-center p-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Cargando detalles desde la API oficial ICD-11...</p>
                </div>
                <div id="icd-details-content" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Código ICD-11</h6>
                                    <h4 class="card-title" id="icd-details-code">--</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h4 id="icd-details-title">--</h4>
                            <p class="text-muted small" id="icd-details-uri">--</p>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-4">
                        <h5>Descripción</h5>
                        <div id="icd-details-description" class="p-3 border rounded">
                            <p class="text-muted">No hay descripción disponible</p>
                        </div>
                    </div>

                    <!-- Sección de términos relacionados -->
                    <div class="row">
                        <div class="col-md-6">
                            <div id="icd-details-synonyms" style="display: none;">
                                <h5>Sinónimos</h5>
                                <ul class="list-group list-group-flush" id="icd-synonyms-list">
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="icd-details-inclusions" style="display: none;">
                                <h5>Términos de inclusión</h5>
                                <ul class="list-group list-group-flush" id="icd-inclusions-list">
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Enlaces y referencias -->
                    <div class="mt-3 border-top pt-3">
                        <div id="icd-details-links">
                            <!-- Los enlaces se agregarán dinámicamente -->
                        </div>
                    </div>
                </div>

                <div id="icd-details-error" class="alert alert-danger" style="display: none;">
                    <h6>Error al cargar detalles</h6>
                    <p id="icd-details-error-message">No se pudieron cargar los detalles desde la API.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="icd-details-use-btn">Usar este diagnóstico</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentEntityDetails = null;
    async function fetchEntityDetails(uri, code, title) {
        try {
            // Asegurarse de que tenemos un URI válido
            if (!uri) {
                throw new Error('URI no válido o vacío');
            }

            console.log('Obteniendo detalles para:', {
                uri,
                code,
                title
            });

            // Mostrar modal con spinner
            $('#icd-details-modal').modal('show');
            $('#icd-details-loading').show();
            $('#icd-details-content').hide();
            $('#icd-details-error').hide();

            // Configurar información básica mientras carga
            $('#icd-details-code').text(code);
            $('#icd-details-title').text(title);
            $('#icd-details-uri').text(uri);

            // Verificar que existe el cliente
            if (!window.icd11Client) {
                throw new Error('Cliente ICD-11 no disponible');
            }

            // Preparar FormData para la petición
            const formData = new FormData();
            formData.append('action', 'getEntityDetails');
            formData.append('uri', uri);

            // Registrar URI para depuración
            console.log('Enviando solicitud para URI:', uri);

            // Realizar petición AJAX para obtener detalles
            console.log('Enviando solicitud a icd11.ajax.php para obtener detalles');
            const response = await fetch('ajax/icd11.ajax.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Obtener texto de respuesta para mejor diagnóstico
            const responseText = await response.text();
            console.log('Respuesta del servidor (texto):', responseText.substring(0, 500));

            // Intentar analizar como JSON
            let jsonData;
            try {
                jsonData = JSON.parse(responseText);
            } catch (e) {
                throw new Error(`Error al analizar respuesta JSON: ${e.message}. Respuesta: ${responseText.substring(0, 200)}`);
            }

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}. ${jsonData?.message || ''}`);
            }

            // Ya tenemos jsonData parseado en el paso anterior
            const data = jsonData;
            if (!data.success) {
                throw new Error(data.message || 'Error desconocido');
            }

            // Guardar datos para uso posterior
            currentEntityDetails = data.data;

            // Verificar que tenemos datos válidos
            if (!data.data || typeof data.data !== 'object') {
                throw new Error('Datos inválidos o vacíos recibidos de la API');
            }

            // Registrar estructura para depuración
            console.log('Estructura de datos recibida:', JSON.stringify(data.data, null, 2).substring(0, 500));

            // Determinar el título real si está en formato anidado
            let displayTitle = title;
            if (data.data.title) {
                if (data.data.title['@value']) {
                    displayTitle = data.data.title['@value'];
                } else if (typeof data.data.title === 'string') {
                    displayTitle = data.data.title;
                }
            }

            console.log('Detalles obtenidos correctamente, título:', displayTitle);

            // Actualizar UI con los detalles
            displayEntityDetails(data.data, code, displayTitle);

        } catch (error) {
            console.error('Error al cargar detalles:', error);
            $('#icd-details-loading').hide();
            $('#icd-details-error').show();

            // Mostrar mensaje de error más detallado
            let errorHtml = `<p>${error.message || 'Error desconocido al obtener detalles'}</p>`;

            // Añadir consejos de solución según el error            // Ver si el error está relacionado con redirecciones o problemas de conectividad
            if (error.message.includes('301') || error.message.includes('302') ||
                error.message.includes('redirección') || error.message.includes('redirect')) {
                errorHtml += `
                    <div class="alert alert-info mt-2">
                        <p><strong>Sugerencia:</strong> Este error puede deberse a un problema de redirección URL.</p>
                        <ul>
                            <li>Intente refrescar la página y volver a intentarlo</li>
                            <li>La URL solicitada fue: "${uri}"</li>
                            <li>Pruebe con otro código ICD-11 conocido (ej: BA00 - Hipertensión)</li>
                        </ul>
                    </div>`;
            } else if (error.message.includes('500') || error.message.includes('Error al obtener detalles')) {
                errorHtml += `
                    <div class="alert alert-warning mt-2">
                        <p><strong>Sugerencia:</strong> Error del servidor de la API ICD-11.</p>
                        <ul>
                            <li>Intente más tarde ya que puede ser un problema temporal</li>
                            <li>Verifique la conexión a Internet</li>
                            <li>Algunos códigos ICD-11 pueden no tener detalles completos disponibles</li>
                            <li>Pruebe con búsqueda por término en lugar de por código</li>
                        </ul>
                        <p class="mt-2">
                            <a href="javascript:void(0)" onclick="selectCodeWithoutDetails('${code}', '${title}')" 
                               class="btn btn-sm btn-outline-primary">
                                Usar este código sin detalles adicionales
                            </a>
                        </p>
                    </div>`;
            } else if (error.message.includes('timeout') || error.message.includes('tiempo de espera')) {
                errorHtml += `
                    <div class="alert alert-warning mt-2">
                        <p><strong>Sugerencia:</strong> Tiempo de espera agotado al conectar con la API.</p>
                        <ul>
                            <li>Verifique su conexión a Internet</li>
                            <li>El servidor de la OMS puede estar experimentando lentitud</li>
                            <li>Intente nuevamente más tarde</li>
                        </ul>
                    </div>`;
            }

            $('#icd-details-error-message').html(errorHtml);
        }
    } // Mostrar los detalles en el modal
    function displayEntityDetails(entityData, code, title) {
        // Ocultar carga, mostrar contenido
        $('#icd-details-loading').hide();
        $('#icd-details-content').show();

        // Actualizar encabezado
        $('#icd-details-code').text(code || entityData.code || '--');

        // Determinar el título correcto según el formato de datos
        let displayTitle = title;
        if (entityData.title) {
            if (entityData.title['@value']) {
                displayTitle = entityData.title['@value'];
            } else if (typeof entityData.title === 'string') {
                displayTitle = entityData.title;
            }
        }

        $('#icd-details-title').text(displayTitle || entityData.label || '--');
        $('#icd-details-uri').text(entityData.id || entityData['@id'] || '--');

        // Intentar encontrar la descripción en diferentes lugares del objeto
        let description = '';

        // Buscar primero en definition.@value (formato nuevo de la API)
        if (entityData.definition && entityData.definition['@value']) {
            description = entityData.definition['@value'];
        }
        // Buscar en definitionElement si existe (formato anterior)
        else if (entityData.definitionElement && entityData.definitionElement.length > 0) {
            description = entityData.definitionElement.map(def => def.textContent || def.label).join('<br><br>');
        }
        // Buscar en longDefinition si existe
        else if (entityData.longDefinition) {
            description = entityData.longDefinition;
        }
        // Buscar en classKind si existe (suele tener información útil)
        else if (entityData.classKind) {
            description = `<strong>Tipo:</strong> ${entityData.classKind}`;
        }
        // Buscar en description si existe
        else if (entityData.description) {
            description = entityData.description;
        }
        // Si no hay descripción disponible
        else {
            description = '<em>No hay descripción detallada disponible para este código.</em>';
        } // Actualizar descripción
        $('#icd-details-description').html(description);

        // Procesar sinónimos en sección separada
        const synonymsContainer = $('#icd-details-synonyms');
        const synonymsList = $('#icd-synonyms-list');
        synonymsList.empty();

        if (entityData.synonym && entityData.synonym.length > 0) {
            entityData.synonym.forEach(syn => {
                const label = syn.label && syn.label['@value'] ? syn.label['@value'] :
                    (typeof syn.label === 'string' ? syn.label : '');
                if (label) {
                    synonymsList.append(`<li class="list-group-item">${label}</li>`);
                }
            });
            synonymsContainer.show();
        } else {
            synonymsContainer.hide();
        }

        // Procesar términos de inclusión en sección separada
        const inclusionsContainer = $('#icd-details-inclusions');
        const inclusionsList = $('#icd-inclusions-list');
        inclusionsList.empty();

        if (entityData.inclusion && entityData.inclusion.length > 0) {
            entityData.inclusion.forEach(inc => {
                const label = inc.label && inc.label['@value'] ? inc.label['@value'] :
                    (typeof inc.label === 'string' ? inc.label : '');
                if (label) {
                    inclusionsList.append(`<li class="list-group-item">${label}</li>`);
                }
            });
            inclusionsContainer.show();
        } else {
            inclusionsContainer.hide();
        }

        // Añadir enlaces y referencias
        const linksContainer = $('#icd-details-links');
        linksContainer.empty();

        // Añadir URL del navegador si está disponible
        if (entityData.browserUrl) {
            linksContainer.append(`
                <div class="mb-2">
                    <i class="fas fa-external-link-alt"></i> 
                    <a href="${entityData.browserUrl}" target="_blank">Ver en el navegador oficial ICD-11</a>
                </div>
            `);
        }

        // Añadir enlace a la API si hay ID
        if (entityData.id || entityData['@id']) {
            const apiUrl = entityData.id || entityData['@id'];
            linksContainer.append(`
                <div>
                    <i class="fas fa-code"></i> 
                    <small class="text-muted">URI API: ${apiUrl}</small>
                </div>
            `);
        } // Configurar botón "Usar este diagnóstico"
        $('#icd-details-use-btn').off('click').on('click', function() {
            // Preparar datos del diagnóstico
            const titleValue = displayTitle || title;

            // Recopilar sinónimos y términos de inclusión
            let synonyms = [];
            if (entityData.synonym && entityData.synonym.length > 0) {
                synonyms = entityData.synonym.map(syn =>
                        syn.label && syn.label['@value'] ? syn.label['@value'] :
                        (typeof syn.label === 'string' ? syn.label : ''))
                    .filter(label => label.length > 0);
            }

            // Crear objeto de datos completo
            const diagData = {
                code: code,
                title: titleValue,
                description: description,
                uri: entityData.id || entityData['@id'] || '',
                browserUrl: entityData.browserUrl || '',
                synonyms: synonyms,
                context: entityData['@context'] || ''
            };
            // Registrar datos completos para diagnóstico
            console.log('Datos completos del diagnóstico seleccionado:', diagData);

            // Actualizar el textarea de descripción de consulta (consulta-textarea)
            // Llamar a la función global findConsultaTextarea definida en icd11-integration.js
            const textareaResult = window.findConsultaTextarea();

            // COMENTADO: Esta función estaba duplicando la inserción de contenido
            // La funcionalidad ya está manejada en icd11-integration.js
            /*
            function processTextArea(textareaWrapper) {
                if (textareaWrapper) {
                    console.log('Editor/textarea encontrado, insertando diagnóstico...');

                    // Formatear el diagnóstico para agregar al textarea
                    // Para HTML, usamos <br> en lugar de \n
                    let diagnosisHtml = '';

                    if (textareaWrapper.isSummernote) {
                        // Formateo para Summernote (HTML)
                        diagnosisHtml = `<p><strong>[Diagnóstico ICD-11: ${code} - ${titleValue}]</strong></p>
                        <p>${description}</p>
                        <p>&nbsp;</p>`;
                    } else {
                        // Formateo para textarea normal
                        diagnosisHtml = `[Diagnóstico ICD-11: ${code} - ${titleValue}]\n${description}\n\n`;
                    }

                    // Obtener el contenido actual
                    let currentContent = textareaWrapper.getValue() || '';

                    // Verificar si ya tiene contenido de diagnóstico ICD
                    if ((textareaWrapper.isSummernote && !currentContent.includes('[Diagnóstico ICD-11:')) ||
                        (!textareaWrapper.isSummernote && !currentContent.startsWith('[Diagnóstico ICD-11:'))) {

                        // Insertar al principio
                        textareaWrapper.setValue(diagnosisHtml + currentContent);
                    } else {
                        // Reemplazar contenido
                        textareaWrapper.setValue(diagnosisHtml);
                    }

                    // Intentar enfocar después de insertar
                    try {
                        textareaWrapper.focus();

                        // Si es un elemento DOM y no Summernote, intentar hacer scroll
                        if (!textareaWrapper.isSummernote && textareaWrapper.element) {
                            textareaWrapper.element.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }

                        // Resaltar el textarea brevemente si no es Summernote
                        if (!textareaWrapper.isSummernote && textareaWrapper.element) {
                            textareaWrapper.element.classList.add('highlight-textarea');
                            setTimeout(() => {
                                textareaWrapper.element.classList.remove('highlight-textarea');
                            }, 2000);
                        }
                    } catch (e) {
                        console.error('Error al enfocar elemento después de insertar:', e);
                    }

                    console.log('Diagnóstico insertado correctamente en el editor/textarea');

                    // Intentar activar la pestaña de registro para mostrar al usuario el resultado
                    try {
                        const registroTabLink = document.querySelector('a[href="#activity"]');
                        if (registroTabLink && typeof $ !== 'undefined') {
                            $(registroTabLink).tab('show');
                            console.log('Pestaña de registro activada después de insertar diagnóstico');
                        }
                    } catch (e) {
                        console.error('Error al cambiar a la pestaña de registro:', e);
                    }

                    return true; // Éxito
                } else {
                    console.warn('No se encontró el editor/textarea de consulta');
                    return false; // Fallo
                }
            }
            */
            
            // Nueva función simplificada que solo maneja el evento sin insertar contenido duplicado
            function processTextArea(textareaWrapper) {
                // Solo registrar que se encontró el textarea, sin insertar contenido
                // La inserción real se maneja en icd11-integration.js
                if (textareaWrapper) {
                    console.log('✅ Editor/textarea encontrado, delegando a icd11-integration.js');
                    return true;
                } else {
                    console.warn('⚠️ No se encontró el editor/textarea de consulta');
                    return false;
                }
            }

            // Si tenemos un resultado pendiente (cambio de pestaña), esperamos la resolución
            if (textareaResult && textareaResult.pending) {
                textareaResult.resolve(textareaWrapper => {
                    const success = processTextArea(textareaWrapper);
                    if (!success) {
                        handleInsertionFailure();
                    }
                });
            } else {
                // Procesamiento directo
                const success = processTextArea(textareaResult);
                if (!success) {
                    handleInsertionFailure();
                }
            }

            // Función para manejar el caso de fallo en la inserción
            function handleInsertionFailure() {
                // Intentar crear un mensaje visual para guiar al usuario
                showAlert('warning', 'No se pudo insertar automáticamente en el editor de consulta. Por favor, copie manualmente el diagnóstico.');

                // Copiar al portapapeles como alternativa
                try {
                    // Crear un formato amigable para pegar
                    const textToCopy = `[Diagnóstico ICD-11: ${code} - ${titleValue}]\n${description}`;
                    navigator.clipboard.writeText(textToCopy).then(
                        () => showAlert('info', 'Diagnóstico copiado al portapapeles. Péguelo en el campo de descripción.'),
                        () => console.warn('No se pudo copiar al portapapeles')
                    );
                } catch (e) {
                    console.error('Error al copiar al portapapeles', e);
                }

                // Intentar activar la pestaña de registro
                try {
                    const registroTabLink = document.querySelector('a[href="#activity"]');
                    if (registroTabLink && typeof $ !== 'undefined') {
                        $(registroTabLink).tab('show');
                        console.log('Cambiado a pestaña de registro para facilitar la inserción manual');
                    }
                } catch (e) {
                    console.error('Error al cambiar a pestaña de registro:', e);
                }
            }

            // Disparar evento de código seleccionado
            if (window.icd11Client && typeof window.icd11Client.dispatchCodeSelected === 'function') {
                window.icd11Client.dispatchCodeSelected(diagData);
            } 
            // COMENTADO: Esta lógica de fallback podría estar causando duplicación
            // Ya se maneja todo en icd11-integration.js
            /*
            else {
                // Fallback: actualizar directamente los campos
                const codeField = document.getElementById('selected-code');
                const diagnosisField = document.getElementById('selected-diagnosis');

                if (codeField) codeField.value = code || '';
                if (diagnosisField) diagnosisField.value = titleValue || '';

                // Disparar eventos para notificar cambios
                if (codeField) codeField.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
                if (diagnosisField) diagnosisField.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            }
            */

            // Cerrar modal
            $('#icd-details-modal').modal('hide');
            // Mostrar confirmación y guiar al usuario hacia la pestaña de registro si es necesario
            const mensaje = textareaResult && !textareaResult.pending ?
                `Diagnóstico seleccionado: ${code} - ${titleValue} (agregado a la descripción de consulta)` :
                `Diagnóstico seleccionado: ${code} - ${titleValue}. Vaya a la pestaña "Nueva Consulta" para ver o editar la descripción.`;

            showAlert('success', mensaje);

            // Si no se encontró el textarea, intentar activar la pestaña de registro y mostrar un botón para navegar
            if (!textareaResult || textareaResult.pending) {
                setTimeout(() => {
                    // Crear un botón de navegación rápida
                    const navButton = document.createElement('div');
                    navButton.className = 'alert alert-info alert-dismissible fade show mt-3';
                    navButton.innerHTML = `
                        <strong>Consejo:</strong> Para ver el diagnóstico insertado, vaya a la pestaña "Nueva Consulta".
                        <button type="button" id="goto-create-btn" class="btn btn-info btn-sm ms-3">
                            <i class="fas fa-arrow-right"></i> Ir a Nueva Consulta
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;

                    // Insertar el botón
                    const alertsContainer = document.getElementById('alerts-container');
                    if (alertsContainer) {
                        alertsContainer.appendChild(navButton);

                        // Configurar el evento del botón
                        document.getElementById('goto-create-btn')?.addEventListener('click', function() {
                            // Intentar activar la pestaña "Nueva Consulta" 
                            const createTabButton = document.querySelector("button[onclick=\"showTab('create')\"]");
                            if (createTabButton) {
                                createTabButton.click();
                            }
                        });
                    }
                }, 500);
            }
        });
    }
</script>


<script>
    // La función para seleccionar código sin detalles se carga desde icd11-integration.js
    // donde se expone globalmente como window.selectCodeWithoutDetails

    // Cuando el usuario hace clic en "Usar este código sin detalles", se debe llamar a esta función
    function handleSelectCodeWithoutDetails(code, title) {
        // Llamar a la función global definida en icd11-integration.js
        if (typeof window.selectCodeWithoutDetails === 'function') {
            window.selectCodeWithoutDetails(code, title);
        } else {
            console.error('La función selectCodeWithoutDetails no está disponible. Asegúrate de que icd11-integration.js esté cargado correctamente.');
            showAlert('danger', 'Error al seleccionar el código: La función necesaria no está disponible');
        }
    }
</script>