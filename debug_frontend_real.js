console.log("🔍 DEBUG SCRIPT CARGADO");

// Override de la función original para debugging
if (typeof cargarServiciosPorFechaMedico !== 'undefined') {
    const originalCargarServicios = cargarServiciosPorFechaMedico;
    
    cargarServiciosPorFechaMedico = function(fecha, doctorId) {
        console.log("🔄 cargarServiciosPorFechaMedico llamada:", {fecha, doctorId});
        
        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: {
                action: "obtenerServiciosPorFechaMedico",
                fecha: fecha,
                doctor_id: doctorId
            },
            dataType: "json",
            beforeSend: function () {
                console.log("📤 Enviando petición de servicios...");
                $('#selectServicio').html('<option value="">Cargando servicios...</option>');
            },
            success: function (respuesta) {
                console.log("📥 Respuesta de servicios recibida:", respuesta);

                if (respuesta.data && respuesta.data.length > 0) {
                    let options = '<option value="">Seleccione un servicio</option>';

                    respuesta.data.forEach(function (servicio) {
                        console.log("➕ Agregando servicio:", servicio);
                        if (servicio.servicio_id) {
                            options += `<option value="${servicio.servicio_id}">${servicio.servicio_nombre}</option>`;
                        } else if (servicio.id) {
                            options += `<option value="${servicio.id}">${servicio.nombre}</option>`;
                        }
                    });

                    console.log("🎯 HTML del dropdown generado:", options);
                    $('#selectServicio').html(options);
                    console.log("✅ Dropdown actualizado. Cantidad de opciones:", $('#selectServicio option').length);
                } else {
                    console.log("⚠️ No hay servicios disponibles");
                    $('#selectServicio').html('<option value="">No hay servicios disponibles</option>');
                }
            },
            error: function (xhr, status, error) {
                console.error("❌ Error en petición de servicios:", {xhr, status, error});
                $('#selectServicio').html('<option value="">Error al cargar servicios</option>');
            }
        });
    };
}

// Override de la función de cargar horarios para debugging
if (typeof cargarHorariosDisponibles !== 'undefined') {
    const originalCargarHorarios = cargarHorariosDisponibles;
    
    cargarHorariosDisponibles = function(servicioId, doctorId, fecha) {
        console.log("🕒 cargarHorariosDisponibles llamada:", {servicioId, doctorId, fecha});
        
        if (!servicioId || !doctorId || !fecha) {
            console.log("❌ Parámetros faltantes para cargar horarios");
            return;
        }

        $.ajax({
            url: "ajax/servicios.ajax.php",
            method: "POST",
            data: {
                action: "generarSlotsDisponibles",
                servicio_id: servicioId,
                doctor_id: doctorId,
                fecha: fecha
            },
            dataType: "json",
            beforeSend: function () {
                console.log("📤 Enviando petición de horarios...");
                $('#contenedorHorarios').html(`
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Cargando horarios disponibles...</p>
                    </div>
                `);
            },
            success: function (respuesta) {
                console.log("📥 Respuesta de horarios recibida:", respuesta);
                console.log("🔢 Cantidad de slots:", respuesta.data ? respuesta.data.length : 0);
                
                if (respuesta.data && respuesta.data.length > 0) {
                    const primerSlot = respuesta.data[0];
                    const ultimoSlot = respuesta.data[respuesta.data.length - 1];
                    console.log("⏰ Rango de horarios:", primerSlot.hora_inicio, "-", ultimoSlot.hora_fin);
                    console.log("🏥 Detalle ID:", primerSlot.detalle_id);
                }
                
                // Llamar a la función original para que renderice
                originalCargarHorarios.call(this, servicioId, doctorId, fecha);
            },
            error: function (xhr, status, error) {
                console.error("❌ Error en petición de horarios:", {xhr, status, error});
            }
        });
    };
}

// Interceptar cambios en el dropdown de servicios
$(document).ready(function() {
    $(document).on('change', '#selectServicio', function() {
        const servicioSeleccionado = $(this).val();
        const servicioTexto = $('#selectServicio option:selected').text();
        
        console.log("🎯 Servicio seleccionado en dropdown:", {
            valor: servicioSeleccionado,
            texto: servicioTexto
        });
        
        // Verificar variables globales
        console.log("🌐 Variables globales:", {
            servicioSeleccionado: window.servicioSeleccionado || 'NO DEFINIDO',
            proveedorSeleccionado: window.proveedorSeleccionado || 'NO DEFINIDO',
            fechaSeleccionada: window.fechaSeleccionada || 'NO DEFINIDO'
        });
    });
    
    // Interceptar cambios en el dropdown de médicos
    $(document).on('change', '#selectProveedor', function() {
        const medicoSeleccionado = $(this).val();
        const medicoTexto = $('#selectProveedor option:selected').text();
        
        console.log("👨‍⚕️ Médico seleccionado:", {
            valor: medicoSeleccionado,
            texto: medicoTexto
        });
    });
    
    console.log("🎉 Debug script inicializado correctamente");
});