<?php
/**
 * SCRIPT DE PRUEBA COMPLETA DEL SISTEMA GENÉRICO
 * Verifica que todos los formularios funcionen perfectamente
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Sistema Genérico - Todos los Formularios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid mt-4">
        <h1>🧪 Prueba Sistema Genérico - Todos los Formularios</h1>
        
        <div class="alert alert-info">
            <strong>🎯 Objetivo:</strong> Verificar que todos los formularios (general, anteojos, estudios, informe_imagen) 
            funcionen perfectamente con el nuevo sistema genérico y mapeo completo de base de datos.
        </div>

        <!-- Paciente seleccionado -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>👤 Paciente de Prueba</h5>
                <div class="alert alert-warning">
                    ID Paciente: <strong>45</strong> | ID Consulta de Prueba: <strong>112</strong>
                </div>
            </div>
        </div>

        <!-- Tabs para diferentes formularios -->
        <ul class="nav nav-tabs" id="formTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">📋 General</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="anteojos-tab" data-toggle="tab" href="#anteojos" role="tab">👓 Anteojos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="estudios-tab" data-toggle="tab" href="#estudios" role="tab">🔬 Estudios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="informe-tab" data-toggle="tab" href="#informe" role="tab">📸 Informe+Imagen</a>
            </li>
        </ul>

        <div class="tab-content mt-4" id="formTabsContent">
            <!-- FORMULARIO GENERAL -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>📋 Formulario General - Sistema Genérico</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Motivo de Consulta</label>
                                    <input type="text" id="txtmotivo" class="form-control" value="Prueba sistema genérico - General">
                                </div>
                                <div class="form-group">
                                    <label>Visión OD</label>
                                    <input type="text" id="visionod" class="form-control" value="20/20">
                                </div>
                                <div class="form-group">
                                    <label>Visión OI</label>
                                    <input type="text" id="visionoi" class="form-control" value="20/20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tensión OD</label>
                                    <input type="text" id="tensionod" class="form-control" value="15">
                                </div>
                                <div class="form-group">
                                    <label>Tensión OI</label>
                                    <input type="text" id="tensionoi" class="form-control" value="15">
                                </div>
                                <div class="form-group">
                                    <label>Próxima Consulta</label>
                                    <input type="date" id="proximaconsulta" class="form-control" value="2025-09-01">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Diagnóstico/Consulta</label>
                            <textarea id="consulta-textarea" class="form-control">
                                <p><strong>Diagnóstico generado por sistema genérico</strong></p>
                                <p>Todo el sistema de mapeo funciona perfectamente.</p>
                            </textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Receta</label>
                            <textarea id="receta-textarea" class="form-control">
                                <p><strong>Receta generada por sistema genérico</strong></p>
                                <p>El mapeo de HTML a BD es automático.</p>
                            </textarea>
                        </div>
                        
                        <button class="btn btn-primary" onclick="testGeneral()">🧪 Probar General</button>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO ANTEOJOS -->
            <div class="tab-pane fade" id="anteojos" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>👓 Formulario Anteojos - Sistema Genérico</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Ojo Derecho (OD)</h6>
                                <div class="form-group">
                                    <label>Esfera OD</label>
                                    <select id="od_esf" class="form-control">
                                        <option value="-2.00">-2.00</option>
                                        <option value="-1.50" selected>-1.50</option>
                                        <option value="-1.00">-1.00</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Cilindro OD</label>
                                    <select id="od_cil" class="form-control">
                                        <option value="-0.50" selected>-0.50</option>
                                        <option value="-0.75">-0.75</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Eje OD</label>
                                    <input type="text" id="od_eje" class="form-control" value="90">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Ojo Izquierdo (OI)</h6>
                                <div class="form-group">
                                    <label>Esfera OI</label>
                                    <select id="oi_esf" class="form-control">
                                        <option value="-2.00" selected>-2.00</option>
                                        <option value="-1.50">-1.50</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Cilindro OI</label>
                                    <select id="oi_cil" class="form-control">
                                        <option value="-0.75" selected>-0.75</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Eje OI</label>
                                    <input type="text" id="oi_eje" class="form-control" value="85">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Distancia Interpupilar</label>
                            <input type="text" id="dist_interpupilar" class="form-control" value="65">
                        </div>
                        
                        <button class="btn btn-success" onclick="testAnteojos()">🧪 Probar Anteojos</button>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO ESTUDIOS -->
            <div class="tab-pane fade" id="estudios" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>🔬 Formulario Estudios - Sistema Genérico</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Tipo de Estudio</label>
                            <select id="tipo_estudio" class="form-control">
                                <option value="OCT" selected>OCT (Tomografía de Coherencia Óptica)</option>
                                <option value="Angiofluoresceinografía">Angiofluoresceinografía</option>
                                <option value="Campo Visual">Campo Visual</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Observaciones del Estudio</label>
                            <textarea id="observaciones" class="form-control">
                                <p><strong>Resultados del estudio genérico</strong></p>
                                <p>El sistema mapea automáticamente todos los campos a la base de datos.</p>
                            </textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Fecha de Realización</label>
                            <input type="date" id="fecha_realizacion" class="form-control" value="2025-08-24">
                        </div>
                        
                        <button class="btn btn-info" onclick="testEstudios()">🧪 Probar Estudios</button>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO INFORME+IMAGEN -->
            <div class="tab-pane fade" id="informe" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5>📸 Formulario Informe+Imagen - Sistema Genérico</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Equipo Médico</label>
                            <input type="text" id="equipoMedico-informe-imagen" class="form-control" value="Oftalmoscopio Digital HD">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Descripción OD</label>
                                    <textarea id="descripcion-od-textarea-informe-imagen" class="form-control">
                                        <p><strong>Descripción OD - Sistema genérico</strong></p>
                                        <p>Mapeo automático funcional.</p>
                                    </textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Descripción OI</label>
                                    <textarea id="descripcion-oi-textarea-informe-imagen" class="form-control">
                                        <p><strong>Descripción OI - Sistema genérico</strong></p>
                                        <p>Todos los formularios funcionan igual.</p>
                                    </textarea>
                                </div>
                            </div>
                        </div>
                        
                        <button class="btn btn-warning" onclick="testInforme()">🧪 Probar Informe+Imagen</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card mt-4">
            <div class="card-header">
                <h5>📊 Resultados de Pruebas</h5>
            </div>
            <div class="card-body">
                <div id="testResults"></div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script src="modules/consultas/core/FormComponents.js"></script>
    <script src="modules/consultas/core/GenericFormComponents.js"></script>
    <script src="modules/consultas/core/DatabaseMapper.php"></script>

    <script>
        let testResults = [];

        // Inicializar Summernote en todos los textareas
        $(document).ready(function() {
            $('textarea').summernote({
                height: 150,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['fullscreen']]
                ]
            });
        });

        function testGeneral() {
            console.log('🧪 Probando formulario General...');
            const form = new GenericGeneralForm();
            
            try {
                form.init().then(() => {
                    const data = form.getSpecificData();
                    addTestResult('General', 'success', `✅ Datos obtenidos: ${Object.keys(data).length} campos`);
                    testSave('general', data);
                });
            } catch (error) {
                addTestResult('General', 'error', `❌ Error: ${error.message}`);
            }
        }

        function testAnteojos() {
            console.log('🧪 Probando formulario Anteojos...');
            const form = new GenericAnteojosForm();
            
            try {
                form.init().then(() => {
                    const data = form.getSpecificData();
                    addTestResult('Anteojos', 'success', `✅ Datos obtenidos: ${Object.keys(data).length} campos`);
                    testSave('anteojos', data);
                });
            } catch (error) {
                addTestResult('Anteojos', 'error', `❌ Error: ${error.message}`);
            }
        }

        function testEstudios() {
            console.log('🧪 Probando formulario Estudios...');
            const form = new GenericEstudiosForm();
            
            try {
                form.init().then(() => {
                    const data = form.getSpecificData();
                    addTestResult('Estudios', 'success', `✅ Datos obtenidos: ${Object.keys(data).length} campos`);
                    testSave('estudios', data);
                });
            } catch (error) {
                addTestResult('Estudios', 'error', `❌ Error: ${error.message}`);
            }
        }

        function testInforme() {
            console.log('🧪 Probando formulario Informe+Imagen...');
            const form = new GenericInformeImagenForm();
            
            try {
                form.init().then(() => {
                    const data = form.getSpecificData();
                    addTestResult('Informe+Imagen', 'success', `✅ Datos obtenidos: ${Object.keys(data).length} campos`);
                    testSave('informe_imagen', data);
                });
            } catch (error) {
                addTestResult('Informe+Imagen', 'error', `❌ Error: ${error.message}`);
            }
        }

        function testSave(formType, data) {
            const testData = {
                id_persona: 45,
                tipo_formulario: formType,
                txtmotivo: 'Prueba sistema genérico',
                ...data
            };

            console.log(`💾 Probando guardado para ${formType}:`, testData);

            fetch('modules/consultas/api/modern-api.php?action=create_consulta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(testData)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    addTestResult(`${formType} - Guardado`, 'success', 
                        `✅ Guardado exitoso: ID ${result.data.id_consulta}`);
                } else {
                    addTestResult(`${formType} - Guardado`, 'error', 
                        `❌ Error: ${result.message}`);
                }
            })
            .catch(error => {
                addTestResult(`${formType} - Guardado`, 'error', 
                    `❌ Error de conexión: ${error.message}`);
            });
        }

        function addTestResult(test, type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const html = `
                <div class="alert ${alertClass}">
                    <strong>${test}:</strong> ${message}
                    <small class="float-right">${new Date().toLocaleTimeString()}</small>
                </div>
            `;
            document.getElementById('testResults').innerHTML += html;
        }
    </script>
</body>
</html>