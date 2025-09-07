// Debug script para slots de horario
console.log("=== DEPURACIÓN DE SLOTS DE HORARIO ===");

// Verificar si existen elementos con clase hora-slot
const horaSlots = document.querySelectorAll('.hora-slot');
console.log("Elementos con clase .hora-slot encontrados:", horaSlots.length);
horaSlots.forEach((slot, index) => {
    console.log(`Slot ${index}:`, {
        outerHTML: slot.outerHTML,
        dataHora: slot.getAttribute('data-hora'),
        classes: slot.className
    });
});

// Verificar si existen elementos con clase hora-btn  
const horaBtns = document.querySelectorAll('.hora-btn');
console.log("Elementos con clase .hora-btn encontrados:", horaBtns.length);
horaBtns.forEach((btn, index) => {
    console.log(`Botón ${index}:`, {
        outerHTML: btn.outerHTML,
        dataInicio: btn.getAttribute('data-inicio'),
        dataFin: btn.getAttribute('data-fin'),
        dataTexto: btn.getAttribute('data-texto'),
        dataAgendaId: btn.getAttribute('data-agenda-id'),
        classes: btn.className
    });
});

// Verificar elementos del resumen
console.log("=== ELEMENTOS DEL RESUMEN ===");
const resumenHora = document.getElementById('resumenHoraNew');
const resumenHoraHorario = document.getElementById('resumenHoraHorario');
console.log("resumenHoraNew:", resumenHora ? resumenHora.textContent : "NO ENCONTRADO");
console.log("resumenHoraHorario:", resumenHoraHorario ? resumenHoraHorario.textContent : "NO ENCONTRADO");

// Verificar inputs hidden
console.log("=== INPUTS HIDDEN ===");
const horaSeleccionada = document.getElementById('horaSeleccionada');
const horaInicioSeleccionada = document.getElementById('horaInicioSeleccionada');
const horaFinSeleccionada = document.getElementById('horaFinSeleccionada');
console.log("horaSeleccionada:", horaSeleccionada ? horaSeleccionada.value : "NO ENCONTRADO");
console.log("horaInicioSeleccionada:", horaInicioSeleccionada ? horaInicioSeleccionada.value : "NO ENCONTRADO");
console.log("horaFinSeleccionada:", horaFinSeleccionada ? horaFinSeleccionada.value : "NO ENCONTRADO");

// Agregar event listener para depurar clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.hora-slot') || e.target.closest('.hora-btn')) {
        console.log("=== CLICK DETECTADO ===");
        console.log("Elemento clickeado:", e.target.outerHTML);
        console.log("Elemento más cercano (.hora-slot):", e.target.closest('.hora-slot'));
        console.log("Elemento más cercano (.hora-btn):", e.target.closest('.hora-btn'));
        
        // Verificar atributos después del click
        setTimeout(() => {
            console.log("Estado del resumen después del click:");
            console.log("resumenHoraNew:", document.getElementById('resumenHoraNew')?.textContent);
            console.log("horaSeleccionada:", document.getElementById('horaSeleccionada')?.value);
        }, 100);
    }
});

console.log("=== SCRIPT DE DEPURACIÓN ACTIVADO ===");