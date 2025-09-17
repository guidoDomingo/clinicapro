<!-- Script temporal para agregar monto SOLO al lado del servicio -->
<script>
$(document).ready(function() {
    console.log('=== Script de monto simplificado activado ===');
    
    // Montos de servicios
    var montosServicios = {
        'CONSULTAS': 330000,
        'CIRUGIA LASIK': 250000,
        'Cirugia Laser': 590000,
        'Tomografía ocular OCT': 500000,
        'Cirugía de cataratas por facoemulsificación': 3000000
    };
    
    // Función para obtener monto por nombre de servicio
    function obtenerMontoPorNombre(nombreServicio) {
        for (var nombre in montosServicios) {
            if (nombreServicio.toUpperCase().includes(nombre.toUpperCase())) {
                return montosServicios[nombre];
            }
        }
        return 0;
    }
    
    // Función para limpiar TODOS los elementos verdes de monto
    function limpiarMontosDuplicados() {
        // Remover elementos con clases de monto
        $('.precio-separado, .monto-servicio-agregado, .monto-agregado, .monto-estatico-agregado').remove();
        
        // Remover elementos que contengan solo "Monto: Gs."
        $('div, span').filter(function() {
            var texto = $(this).text().trim();
            return texto.match(/^\$?\s*Monto:\s*Gs\.\s*[\d,\.]+$/) && !$(this).hasClass('precio-inline');
        }).remove();
        
        console.log('🧹 Elementos verdes de monto removidos');
    }
    
    // Función para agregar precio SOLO al lado del servicio
    function agregarPrecioSoloInline() {
        // Primero limpiar todos los duplicados
        limpiarMontosDuplicados();
        
        // Buscar elemento con "Servicio:"
        $('*:contains("Servicio:")').each(function() {
            var $elemento = $(this);
            var textoCompleto = $elemento.text().trim();
            
            // Solo procesar líneas específicas del servicio
            if (textoCompleto.length < 100 && textoCompleto.includes('Servicio:') && !textoCompleto.includes('(Gs.')) {
                
                var servicioMatch = textoCompleto.match(/Servicio:\s*([^(\n\r]+)/);
                if (servicioMatch && servicioMatch[1]) {
                    var nombreServicio = servicioMatch[1].trim();
                    var monto = obtenerMontoPorNombre(nombreServicio);
                    
                    if (monto > 0) {
                        var montoFormateado = new Intl.NumberFormat('es-PY').format(monto);
                        
                        // Agregar el precio directamente al HTML
                        var htmlOriginal = $elemento.html();
                        var htmlNuevo = htmlOriginal.replace(
                            nombreServicio, 
                            nombreServicio + ' <span class="precio-inline text-muted">(Gs. ' + montoFormateado + ')</span>'
                        );
                        
                        if (htmlNuevo !== htmlOriginal) {
                            $elemento.html(htmlNuevo);
                            console.log('✅ Precio agregado inline:', nombreServicio, 'Gs. ' + montoFormateado);
                        }
                    }
                }
            }
        });
        
        // Limpiar de nuevo por si aparecieron duplicados
        setTimeout(limpiarMontosDuplicados, 100);
    }
    
    // Configurar select
    function configurarSelect() {
        var $select = $('#servicio_id, select[name="servicio_id"]');
        
        if ($select.length > 0) {
            $select.off('change.precio').on('change.precio', function() {
                var servicioSeleccionado = $(this).find('option:selected').text().trim();
                
                if (servicioSeleccionado && servicioSeleccionado !== 'Seleccione un servicio') {
                    setTimeout(agregarPrecioSoloInline, 300);
                } else {
                    $('.precio-inline').remove();
                    limpiarMontosDuplicados();
                }
            });
        }
    }
    
    // Observador para limpiar elementos no deseados
    function configurarLimpiador() {
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    $(mutation.addedNodes).each(function() {
                        if (this.nodeType === 1) {
                            var $node = $(this);
                            // Si es un elemento verde de monto, eliminarlo
                            if ($node.text().includes('Monto: Gs.') && 
                                ($node.css('color') === 'rgb(40, 167, 69)' || 
                                 $node.hasClass('text-success') ||
                                 $node.css('background-color').includes('rgb(212, 237, 218)'))) {
                                $node.remove();
                                console.log('🚫 Elemento verde de monto eliminado automáticamente');
                            }
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Ejecutar funciones
    setTimeout(configurarSelect, 500);
    setTimeout(configurarLimpiador, 1000);
    setTimeout(agregarPrecioSoloInline, 1500);
    
    // Limpiar periódicamente elementos verdes
    setInterval(limpiarMontosDuplicados, 2000);
    
    console.log('=== Script simplificado configurado ===');
});
</script>
<script>
$(document).ready(function() {
    console.log('=== Script de monto temporal activado ===');
    
    // Montos de servicios
    var montosServicios = {
        'CONSULTAS': 330000,
        'CIRUGIA LASIK': 250000,
        'Cirugia Laser': 590000,
        'Tomografía ocular OCT': 500000,
        'Cirugía de cataratas por facoemulsificación': 3000000
    };
    
    // Variable para evitar bucles infinitos
    var ultimoServicioProcesado = '';
    
    // Función para obtener monto por nombre de servicio
    function obtenerMontoPorNombre(nombreServicio) {
        for (var nombre in montosServicios) {
            if (nombreServicio.toUpperCase().includes(nombre.toUpperCase())) {
                console.log('✅ Monto encontrado para', nombre, ':', montosServicios[nombre]);
                return montosServicios[nombre];
            }
        }
        return 0;
    }
    
    // Función para agregar precio al lado del servicio de forma persistente
    function agregarPrecioPersistente() {
        // Buscar todos los elementos que contengan "Servicio:" sin precio ya agregado
        $('*:contains("Servicio:")').each(function() {
            var $elemento = $(this);
            var textoCompleto = $elemento.text().trim();
            
            // Solo procesar si es una línea específica del servicio (no un contenedor grande)
            if (textoCompleto.length < 200 && textoCompleto.includes('Servicio:')) {
                
                // Verificar si ya tiene precio agregado
                if (textoCompleto.includes('(Gs.')) {
                    return; // Ya tiene precio, continuar con el siguiente
                }
                
                // Extraer el nombre del servicio
                var servicioMatch = textoCompleto.match(/Servicio:\s*([^(\n\r]+)/);
                if (servicioMatch && servicioMatch[1]) {
                    var nombreServicio = servicioMatch[1].trim();
                    
                    // Evitar procesar el mismo servicio repetidamente
                    if (nombreServicio === ultimoServicioProcesado) {
                        return;
                    }
                    
                    console.log('🎯 Procesando servicio:', nombreServicio);
                    
                    var monto = obtenerMontoPorNombre(nombreServicio);
                    if (monto > 0) {
                        var montoFormateado = new Intl.NumberFormat('es-PY').format(monto);
                        
                        // Método 1: Intentar agregar el precio al HTML del elemento
                        try {
                            var htmlOriginal = $elemento.html();
                            var htmlNuevo = htmlOriginal.replace(
                                nombreServicio, 
                                nombreServicio + ' <span class="precio-inline text-success font-weight-bold">(Gs. ' + montoFormateado + ')</span>'
                            );
                            
                            if (htmlNuevo !== htmlOriginal && !htmlNuevo.includes('(Gs.')) {
                                $elemento.html(htmlNuevo);
                                console.log('🎉 Precio agregado via HTML:', 'Gs. ' + montoFormateado);
                                ultimoServicioProcesado = nombreServicio;
                                return;
                            }
                        } catch(e) {
                            console.log('❌ Error con método HTML:', e);
                        }
                        
                        // Método 2: Buscar el nodo de texto específico
                        try {
                            var nodoTexto = null;
                            $elemento.contents().each(function() {
                                if (this.nodeType === 3 && this.textContent.includes(nombreServicio)) {
                                    nodoTexto = this;
                                    return false;
                                }
                            });
                            
                            if (nodoTexto) {
                                var textoNuevo = nodoTexto.textContent.replace(
                                    nombreServicio, 
                                    nombreServicio + ' (Gs. ' + montoFormateado + ')'
                                );
                                nodoTexto.textContent = textoNuevo;
                                console.log('🎉 Precio agregado via texto:', 'Gs. ' + montoFormateado);
                                ultimoServicioProcesado = nombreServicio;
                                return;
                            }
                        } catch(e) {
                            console.log('❌ Error con método texto:', e);
                        }
                        
                        // Método 3: Agregar después del elemento
                        try {
                            if (!$elemento.next('.precio-separado').length) {
                                $elemento.after('<div class="precio-separado text-success font-weight-bold" style="font-size: 14px; margin-top: 2px;"><i class="fas fa-dollar-sign mr-1"></i>Monto: Gs. ' + montoFormateado + '</div>');
                                console.log('🎉 Precio agregado como elemento separado:', 'Gs. ' + montoFormateado);
                                ultimoServicioProcesado = nombreServicio;
                            }
                        } catch(e) {
                            console.log('❌ Error con método separado:', e);
                        }
                    }
                }
            }
        });
    }
    
    // Función para configurar el select
    function configurarSelect() {
        var $select = $('#servicio_id, select[name="servicio_id"]');
        
        if ($select.length > 0) {
            console.log('� Configurando select de servicios');
            
            $select.off('change.precio').on('change.precio', function() {
                var servicioSeleccionado = $(this).find('option:selected').text().trim();
                console.log('🎯 Servicio seleccionado en select:', servicioSeleccionado);
                
                if (servicioSeleccionado && servicioSeleccionado !== 'Seleccione un servicio') {
                    ultimoServicioProcesado = ''; // Reset para permitir procesamiento
                    
                    // Esperar a que se actualice el resumen y luego agregar el precio
                    setTimeout(function() {
                        agregarPrecioPersistente();
                    }, 100);
                    
                    setTimeout(function() {
                        agregarPrecioPersistente();
                    }, 500);
                    
                    setTimeout(function() {
                        agregarPrecioPersistente();
                    }, 1000);
                }
            });
        }
    }
    
    // Configurar observador de mutaciones para detectar cambios en el resumen
    function configurarObservador() {
        var targetNode = document.querySelector('body');
        
        if (targetNode) {
            var observer = new MutationObserver(function(mutations) {
                var debeActualizar = false;
                
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                        // Verificar si el cambio afecta al resumen
                        var $target = $(mutation.target);
                        if ($target.closest('.card:contains("Resumen"), .card:contains("Datos")').length > 0) {
                            debeActualizar = true;
                        }
                    }
                });
                
                if (debeActualizar) {
                    console.log('🔄 Cambios detectados en el resumen, re-aplicando precios');
                    ultimoServicioProcesado = ''; // Reset
                    setTimeout(agregarPrecioPersistente, 200);
                }
            });
            
            observer.observe(targetNode, {
                childList: true,
                subtree: true,
                characterData: true
            });
            
            console.log('👁️ Observador de mutaciones configurado');
        }
    }
    
    // Ejecutar todas las configuraciones
    setTimeout(configurarSelect, 500);
    setTimeout(configurarObservador, 1000);
    setTimeout(agregarPrecioPersistente, 1500);
    
    // Ejecutar periódicamente para mayor persistencia
    setInterval(function() {
        agregarPrecioPersistente();
    }, 3000);
    
    console.log('=== Script de monto temporal configurado ===');
});
</script>