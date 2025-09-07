// Script para debuggear elementos disponibles en la página de agendas

$(document).ready(function() {
    console.log('=== DEBUG: Elementos disponibles ===');
    
    // Buscar todos los elementos que contengan "filtro"
    $('[id*="filtro"]').each(function() {
        console.log('Elemento con filtro encontrado:', this.id, $(this));
    });
    
    // Buscar todos los elementos que contengan "servicio"
    $('[id*="servicio"]').each(function() {
        console.log('Elemento con servicio encontrado:', this.id, $(this));
    });
    
    // Buscar todos los elementos que contengan "tabla"
    $('[id*="tabla"]').each(function() {
        console.log('Elemento con tabla encontrado:', this.id, $(this));
    });
    
    // Buscar tabs disponibles
    $('[data-toggle="tab"]').each(function() {
        console.log('Tab encontrado:', $(this).attr('href'), $(this).text());
    });
    
    // Buscar contenido de tabs
    $('.tab-pane').each(function() {
        console.log('Tab pane encontrado:', this.id, $(this));
    });
    
    console.log('=== FIN DEBUG ===');
});