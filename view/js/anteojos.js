/**
 * JavaScript para el formulario de anteojos
 */
$(document).ready(function() {
    console.log('Inicializando formulario de anteojos');

    // Verificar que estamos en la página correcta con el tipo de formulario adecuado
    const urlParams = new URLSearchParams(window.location.search);
    const formType = urlParams.get('form_type');
    
    // Solo inicializar los componentes específicos si estamos en el formulario de anteojos
    if (formType === 'anteojos') {
        console.log('Detectado formulario de anteojos, inicializando componentes específicos');
        // Inicializar los selectores para esferas, cilindros y adiciones
        inicializarSelectoresAnteojos();
        
        // Cargar los preformatos específicos para anteojos
        // Forzar una pequeña espera para asegurar que cargar_datos.js haya cargado
        setTimeout(function() {
            console.log('Cargando preformatos específicos para anteojos');
            if (typeof cargarPreformatosConsulta === 'function') {
                console.log('Llamando a cargarPreformatosConsulta con tipo anteojos');
                cargarPreformatosConsulta('anteojos');
            } else {
                console.error('La función cargarPreformatosConsulta no está disponible');
            }
            
            if (typeof cargarPreformatosReceta === 'function') {
                console.log('Llamando a cargarPreformatosReceta con tipo anteojos');
                cargarPreformatosReceta('anteojos');
            } else {
                console.error('La función cargarPreformatosReceta no está disponible');
            }
        }, 500);
    }
});

/**
 * Inicializa los selectores para esferas, cilindros y adiciones
 */
function inicializarSelectoresAnteojos() {
    // Arrays para valores de esferas
    const esferasPositivas = [];
    const esferasNegativas = [];

    // Generar valores positivos de esferas (de 0.00 a +15.00 en incrementos de 0.25)
    for (let i = 0; i <= 15.0; i += 0.25) {
        const valor = i.toFixed(2);
        esferasPositivas.push(`+${valor}`);
    }

    // Generar valores negativos de esferas (de -0.25 a -15.00 en incrementos de 0.25)
    for (let i = 0.25; i <= 15.0; i += 0.25) {
        const valor = i.toFixed(2);
        esferasNegativas.push(`-${valor}`);
    }

    // Generar valores para cilindros (de -0.25 a -6.00 en incrementos de 0.25)
    const cilindros = [];
    for (let i = 0.25; i <= 6.0; i += 0.25) {
        const valor = i.toFixed(2);
        cilindros.push(`-${valor}`);
    }

    // Generar valores para adiciones (de +1.00 a +3.50 en incrementos de 0.25)
    const adiciones = [];
    for (let i = 1.0; i <= 3.5; i += 0.25) {
        const valor = i.toFixed(2);
        adiciones.push(`+${valor}`);
    }

    // Llenar selectores OD (Ojo Derecho)
    llenarSelector('od_esf', ['Neutro', ...esferasPositivas.reverse(), ...esferasNegativas]);
    llenarSelector('od_cil', ['Neutro', ...cilindros]);
    llenarSelector('od_adicion', ['Neutro', ...adiciones]);

    // Llenar selectores OI (Ojo Izquierdo)
    llenarSelector('oi_esf', ['Neutro', ...esferasPositivas.reverse(), ...esferasNegativas]);
    llenarSelector('oi_cil', ['Neutro', ...cilindros]);
    llenarSelector('oi_adicion', ['Neutro', ...adiciones]);

    // Inicializar Select2 para todos los selectores
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    });
}

/**
 * Llena un selector con los valores proporcionados
 */
function llenarSelector(selectorId, valores) {
    const selector = $(`#${selectorId}`);
    selector.empty();

    valores.forEach(valor => {
        selector.append(new Option(valor, valor));
    });
}
