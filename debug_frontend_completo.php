<!DOCTYPE html>
<html>
<head>
    <title>Debug Frontend - Servicios</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { border: 1px solid #ccc; padding: 15px; margin: 10px 0; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h2>🔍 Debug Frontend - Dropdown de Servicios</h2>
    
    <div class="debug-section">
        <h3>1. Test del endpoint obtenerServiciosPorFechaMedico</h3>
        <button onclick="testServiciosEndpoint()">Probar Endpoint</button>
        <div id="resultadoServicios"></div>
    </div>
    
    <div class="debug-section">
        <h3>2. Test de cargarHorariosDisponibles</h3>
        <label>Servicio ID:</label>
        <select id="servicioTest">
            <option value="4">Cirugía de prueba (ID: 4)</option>
            <option value="8">prueba 789545612 (ID: 8)</option>
        </select>
        <button onclick="testHorarios()">Probar Horarios</button>
        <div id="resultadoHorarios"></div>
    </div>
    
    <div class="debug-section">
        <h3>3. Simulación completa del flujo</h3>
        <button onclick="simulacionCompleta()">Simular Flujo Completo</button>
        <div id="resultadoCompleto"></div>
    </div>

    <script>
        function testServiciosEndpoint() {
            $('#resultadoServicios').html('<p>Cargando...</p>');
            
            $.ajax({
                url: "ajax/servicios.ajax.php",
                method: "POST",
                data: {
                    action: "obtenerServiciosPorFechaMedico",
                    fecha: "2025-09-08",
                    doctor_id: 18
                },
                dataType: "json",
                success: function(respuesta) {
                    console.log("Respuesta servicios:", respuesta);
                    
                    let html = '<h4>Respuesta del endpoint:</h4>';
                    html += '<pre>' + JSON.stringify(respuesta, null, 2) + '</pre>';
                    
                    if (respuesta.data && respuesta.data.length > 0) {
                        html += '<h5>Servicios encontrados:</h5>';
                        respuesta.data.forEach(function(servicio) {
                            html += `<p class="success">- ID: ${servicio.servicio_id}, Nombre: ${servicio.servicio_nombre}</p>`;
                        });
                        
                        if (respuesta.data.length === 2) {
                            html += '<p class="success">✅ CORRECTO: Devuelve exactamente 2 servicios</p>';
                        } else {
                            html += '<p class="error">❌ ERROR: Debería devolver 2 servicios, devolvió ' + respuesta.data.length + '</p>';
                        }
                    } else {
                        html += '<p class="error">❌ No se encontraron servicios</p>';
                    }
                    
                    $('#resultadoServicios').html(html);
                },
                error: function(xhr, status, error) {
                    $('#resultadoServicios').html('<p class="error">Error: ' + error + '</p>');
                }
            });
        }
        
        function testHorarios() {
            let servicioId = $('#servicioTest').val();
            $('#resultadoHorarios').html('<p>Cargando horarios para servicio ' + servicioId + '...</p>');
            
            $.ajax({
                url: "ajax/servicios.ajax.php",
                method: "POST",
                data: {
                    action: "generarSlotsDisponibles",
                    servicio_id: servicioId,
                    doctor_id: 18,
                    fecha: "2025-09-08"
                },
                dataType: "json",
                success: function(respuesta) {
                    console.log("Respuesta horarios para servicio " + servicioId + ":", respuesta);
                    
                    let html = '<h4>Horarios para servicio ' + servicioId + ':</h4>';
                    
                    if (respuesta.data && respuesta.data.length > 0) {
                        let primerSlot = respuesta.data[0];
                        let ultimoSlot = respuesta.data[respuesta.data.length - 1];
                        
                        html += `<p>Slots generados: ${respuesta.data.length}</p>`;
                        html += `<p>Rango: ${primerSlot.hora_inicio} - ${ultimoSlot.hora_fin}</p>`;
                        html += `<p>Detalle ID: ${primerSlot.detalle_id}</p>`;
                        
                        // Verificar si es correcto según el servicio
                        if (servicioId == "4") {
                            // Cirugía de prueba - debe ser tarde (>= 13:00)
                            if (primerSlot.hora_inicio >= "13:00:00") {
                                html += '<p class="success">✅ CORRECTO: Cirugía de prueba muestra solo horarios de tarde</p>';
                            } else {
                                html += '<p class="error">❌ ERROR: Cirugía de prueba está mostrando horarios de mañana</p>';
                            }
                        } else if (servicioId == "8") {
                            // prueba 789545612 - debe ser mañana (< 13:00)
                            if (ultimoSlot.hora_fin <= "13:00:00") {
                                html += '<p class="success">✅ CORRECTO: prueba 789545612 muestra solo horarios de mañana</p>';
                            } else {
                                html += '<p class="error">❌ ERROR: prueba 789545612 está mostrando horarios de tarde también</p>';
                            }
                        }
                        
                        // Mostrar algunos slots
                        html += '<h5>Primeros 5 slots:</h5>';
                        for (let i = 0; i < Math.min(5, respuesta.data.length); i++) {
                            let slot = respuesta.data[i];
                            html += `<p>- ${slot.hora_inicio} - ${slot.hora_fin}</p>`;
                        }
                        
                    } else {
                        html += '<p class="warning">⚠️ No se encontraron horarios</p>';
                    }
                    
                    $('#resultadoHorarios').html(html);
                },
                error: function(xhr, status, error) {
                    $('#resultadoHorarios').html('<p class="error">Error: ' + error + '</p>');
                }
            });
        }
        
        function simulacionCompleta() {
            $('#resultadoCompleto').html('<p>Ejecutando simulación completa...</p>');
            
            let html = '<h4>Simulación del flujo completo:</h4>';
            let pasos = [];
            
            // Paso 1: Obtener servicios
            $.ajax({
                url: "ajax/servicios.ajax.php",
                method: "POST",
                data: {
                    action: "obtenerServiciosPorFechaMedico",
                    fecha: "2025-09-08",
                    doctor_id: 18
                },
                dataType: "json",
                success: function(respuesta) {
                    pasos.push("✅ Paso 1: Obtener servicios - " + respuesta.data.length + " servicios");
                    
                    if (respuesta.data.length >= 2) {
                        // Probar primer servicio
                        let servicio1 = respuesta.data[0];
                        let servicio2 = respuesta.data[1];
                        
                        $.ajax({
                            url: "ajax/servicios.ajax.php",
                            method: "POST",
                            data: {
                                action: "generarSlotsDisponibles",
                                servicio_id: servicio1.servicio_id,
                                doctor_id: 18,
                                fecha: "2025-09-08"
                            },
                            dataType: "json",
                            success: function(resp1) {
                                pasos.push(`✅ Paso 2: ${servicio1.servicio_nombre} - ${resp1.data.length} slots`);
                                
                                $.ajax({
                                    url: "ajax/servicios.ajax.php",
                                    method: "POST",
                                    data: {
                                        action: "generarSlotsDisponibles",
                                        servicio_id: servicio2.servicio_id,
                                        doctor_id: 18,
                                        fecha: "2025-09-08"
                                    },
                                    dataType: "json",
                                    success: function(resp2) {
                                        pasos.push(`✅ Paso 3: ${servicio2.servicio_nombre} - ${resp2.data.length} slots`);
                                        
                                        // Verificar diferencias
                                        let rango1 = resp1.data.length > 0 ? `${resp1.data[0].hora_inicio}-${resp1.data[resp1.data.length-1].hora_fin}` : 'Sin horarios';
                                        let rango2 = resp2.data.length > 0 ? `${resp2.data[0].hora_inicio}-${resp2.data[resp2.data.length-1].hora_fin}` : 'Sin horarios';
                                        
                                        pasos.push(`📊 ${servicio1.servicio_nombre}: ${rango1}`);
                                        pasos.push(`📊 ${servicio2.servicio_nombre}: ${rango2}`);
                                        
                                        if (rango1 !== rango2) {
                                            pasos.push("✅ FILTRADO FUNCIONANDO: Los servicios muestran horarios diferentes");
                                        } else {
                                            pasos.push("❌ FILTRADO NO FUNCIONA: Los servicios muestran los mismos horarios");
                                        }
                                        
                                        $('#resultadoCompleto').html(html + pasos.join('<br>'));
                                    }
                                });
                            }
                        });
                    }
                }
            });
        }
    </script>
</body>
</html>