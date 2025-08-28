<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Save Anteojos</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/alertifyjs/1.13.1/css/alertify.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/alertifyjs/1.13.1/css/themes/default.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; }
        .debug-section h3 { margin-top: 0; color: #333; }
        button { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .json-response { background: #000; color: #0f0; padding: 10px; font-family: monospace; white-space: pre-wrap; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🔍 Debug Save Anteojos</h1>
    
    <div class="debug-section">
        <h3>Test 1: Crear nueva consulta anteojos</h3>
        <button onclick="testSaveNew()">🆕 Probar Guardado Nuevo</button>
        <div id="result-new" class="json-response"></div>
    </div>
    
    <div class="debug-section">
        <h3>Test 2: Actualizar consulta existente</h3>
        <button onclick="testSaveUpdate()">📝 Probar Actualización</button>
        <div id="result-update" class="json-response"></div>
    </div>
    
    <div class="debug-section">
        <h3>Test 3: Verificar estructura de datos</h3>
        <button onclick="checkEndpointStatus()">🔍 Verificar Endpoint</button>
        <div id="result-status" class="json-response"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/alertifyjs/1.13.1/alertify.min.js"></script>
    <script>
        // Función para testear guardado nuevo
        async function testSaveNew() {
            const resultDiv = document.getElementById('result-new');
            resultDiv.textContent = '⏳ Enviando datos...';
            
            const testData = {
                id_persona: '45', // ID persona de prueba
                txtmotivo: 'Consulta de prueba para anteojos',
                od_esf: '-1.25',
                od_cil: '-0.50', 
                od_eje: '90',
                od_dnp: '32',
                od_add: '+1.00',
                od_altura: '20',
                od_nota: 'Nota OD',
                oi_esf: '-1.50',
                oi_cil: '-0.75',
                oi_eje: '85',
                oi_dnp: '31',
                oi_add: '+1.00',
                oi_altura: '20',
                oi_nota: 'Nota OI',
                dist_interpupilar: '63',
                consulta_textarea: 'Consulta de prueba',
                receta_textarea: 'Receta de prueba',
                txtnota: 'Notas generales de prueba',
                proximaconsulta: '2024-12-31',
                whatsapptxt: 'Mensaje WhatsApp',
                email: 'test@example.com',
                medico_id: '1'
            };
            
            try {
                const response = await fetch('modules/consultas/api/livewire-crud.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save',
                        formType: 'anteojos',
                        consultaId: null,
                        state: testData
                    })
                });
                
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                
                if (result.success) {
                    alertify.success('✅ Guardado exitoso!');
                } else {
                    alertify.error('❌ Error: ' + result.message);
                }
                
            } catch (error) {
                resultDiv.textContent = 'ERROR: ' + error.message;
                alertify.error('❌ Error de conexión: ' + error.message);
            }
        }
        
        // Función para testear actualización
        async function testSaveUpdate() {
            const resultDiv = document.getElementById('result-update');
            resultDiv.textContent = '⏳ Enviando datos para actualización...';
            
            const testData = {
                id_persona: '45',
                txtmotivo: 'Consulta ACTUALIZADA para anteojos',
                od_esf: '-2.00', // Valores diferentes para ver el cambio
                od_cil: '-1.00', 
                od_eje: '95',
                od_dnp: '33',
                od_add: '+1.25',
                od_altura: '21',
                od_nota: 'Nota OD ACTUALIZADA',
                oi_esf: '-2.25',
                oi_cil: '-1.25',
                oi_eje: '90',
                oi_dnp: '32',
                oi_add: '+1.25',
                oi_altura: '21',
                oi_nota: 'Nota OI ACTUALIZADA',
                dist_interpupilar: '64',
                consulta_textarea: 'Consulta ACTUALIZADA',
                receta_textarea: 'Receta ACTUALIZADA',
                txtnota: 'Notas ACTUALIZADAS',
                proximaconsulta: '2025-01-31',
                whatsapptxt: 'Mensaje WhatsApp ACTUALIZADO',
                email: 'updated@example.com',
                medico_id: '1'
            };
            
            try {
                const response = await fetch('modules/consultas/api/livewire-crud.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'save',
                        formType: 'anteojos',
                        consultaId: '1', // Usar ID de consulta existente
                        state: testData
                    })
                });
                
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                
                if (result.success) {
                    alertify.success('✅ Actualización exitosa!');
                } else {
                    alertify.error('❌ Error: ' + result.message);
                }
                
            } catch (error) {
                resultDiv.textContent = 'ERROR: ' + error.message;
                alertify.error('❌ Error de conexión: ' + error.message);
            }
        }
        
        // Función para verificar estado del endpoint
        async function checkEndpointStatus() {
            const resultDiv = document.getElementById('result-status');
            resultDiv.textContent = '⏳ Verificando endpoint...';
            
            try {
                const response = await fetch('modules/consultas/api/livewire-crud.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'refresh',
                        formType: 'anteojos',
                        state: {}
                    })
                });
                
                const result = await response.json();
                resultDiv.textContent = JSON.stringify(result, null, 2);
                
                if (result.success) {
                    alertify.success('✅ Endpoint funcionando correctamente');
                } else {
                    alertify.warning('⚠️ Endpoint respondió con error: ' + result.message);
                }
                
            } catch (error) {
                resultDiv.textContent = 'ERROR: ' + error.message;
                alertify.error('❌ Endpoint no accesible: ' + error.message);
            }
        }
    </script>
</body>
</html>