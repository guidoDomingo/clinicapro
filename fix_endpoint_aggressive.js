// Fix agresivo para endpoint
(function() {
    console.log('🔧 Aplicando fix agresivo para endpoint...');
    
    // Interceptar fetch para corregir URLs incorrectas
    const originalFetch = window.fetch;
    
    window.fetch = function(...args) {
        let url = args[0];
        
        // Si es una string y contiene clinica.test, corregirla
        if (typeof url === 'string' && url.includes('clinica.test')) {
            const newURL = url.replace('http://clinica.test/', 'http://localhost/clinica/');
            console.log('🔄 Corrigiendo URL:', url, '->', newURL);
            args[0] = newURL;
        }
        
        // Si es un objeto Request
        if (url instanceof Request && url.url.includes('clinica.test')) {
            const newURL = url.url.replace('http://clinica.test/', 'http://localhost/clinica/');
            console.log('🔄 Corrigiendo Request URL:', url.url, '->', newURL);
            args[0] = new Request(newURL, {
                method: url.method,
                headers: url.headers,
                body: url.body,
                mode: url.mode,
                credentials: url.credentials,
                cache: url.cache,
                redirect: url.redirect,
                referrer: url.referrer
            });
        }
        
        return originalFetch.apply(this, args);
    };
    
    console.log('✅ Fix agresivo aplicado - todas las llamadas a clinica.test serán redirigidas a localhost/clinica');
    
    // También corregir instancias existentes si las hay
    if (window.livwireAPI?.crud?._instance) {
        const currentEndpoint = window.livwireAPI.crud._instance.config.endpoint;
        if (currentEndpoint.includes('clinica.test')) {
            const newEndpoint = currentEndpoint.replace('http://clinica.test/', 'http://localhost/clinica/');
            window.livwireAPI.crud._instance.config.endpoint = newEndpoint;
            console.log('🔄 Endpoint corregido en instancia existente:', newEndpoint);
        }
    }
})();