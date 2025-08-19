<?php
echo "<!DOCTYPE html><html><head><title>Debug Tabs</title></head><body>";
echo "<h1>Debug de Tabs y Formularios</h1>";

// JavaScript para debug
?>
<script>
// Función para debuggear tabs
function debugTabs() {
    console.log("=== DEBUG TABS ===");
    
    // Encontrar todos los tabs
    const tabs = document.querySelectorAll('[data-form-type]');
    console.log("Tabs encontrados:", tabs.length);
    tabs.forEach(tab => {
        console.log(`- Tab: ${tab.getAttribute('data-form-type')}, visible: ${tab.style.display !== 'none'}`);
    });
    
    // Encontrar todos los formularios
    const formularios = document.querySelectorAll('[id*="formulario-"]');
    console.log("Formularios encontrados:", formularios.length);
    formularios.forEach(form => {
        console.log(`- Formulario: ${form.id}, visible: ${form.style.display !== 'none'}`);
        
        // Buscar select de preformatos en cada formulario
        const preformatoSelect = form.querySelector('#formatoConsulta');
        if (preformatoSelect) {
            console.log(`  ✅ Tiene select formatoConsulta: ${preformatoSelect.options.length} opciones`);
        } else {
            console.log(`  ❌ NO tiene select formatoConsulta`);
        }
    });
    
    // Verificar select específico
    const formatoConsulta = document.getElementById('formatoConsulta');
    if (formatoConsulta) {
        console.log("formatoConsulta encontrado:", {
            id: formatoConsulta.id,
            options: formatoConsulta.options.length,
            visible: formatoConsulta.offsetParent !== null,
            style: formatoConsulta.style.display
        });
    } else {
        console.log("❌ formatoConsulta NO encontrado");
    }
}

// Ejecutar debug cuando la página cargue
window.addEventListener('load', () => {
    setTimeout(debugTabs, 2000); // Esperar 2 segundos
});

// Hacer disponible globalmente
window.debugTabs = debugTabs;
</script>

<p>Abrir console del navegador y ejecutar: <code>debugTabs()</code></p>
<p>O esperar 2 segundos para debug automático</p>

</body></html>
<?php
