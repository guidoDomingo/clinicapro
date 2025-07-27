<?php
/**
 * Script de prueba para simular el guardado de un formulario de estudios
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simular los datos POST que envía el formulario
    echo "<h2>🔧 Simulación de guardado de estudios</h2>";
    echo "<h3>📤 Datos enviados:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Incluir el archivo de guardado
    include 'ajax/guardar-consulta-estudios.php';
    
} else {
    // Mostrar formulario de prueba
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test Formulario Estudios</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .form-group { margin: 10px 0; }
            label { display: block; font-weight: bold; }
            input, select, textarea { width: 300px; padding: 5px; }
            button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        </style>
    </head>
    <body>
        <h2>🧪 Test Formulario de Estudios</h2>
        <form method="POST">
            <div class="form-group">
                <label>ID Persona:</label>
                <input type="text" name="idPersona" value="1" required>
            </div>
            
            <div class="form-group">
                <label>Tipo de Formulario:</label>
                <input type="text" name="form_type" value="estudios" readonly>
            </div>
            
            <div class="form-group">
                <label>Equipo Médico:</label>
                <select name="equipo_medico">
                    <option value="">Seleccionar</option>
                    <option value="cirrus_700">Cirrus 700</option>
                    <option value="cirrus_500c" selected>Cirrus 500c</option>
                    <option value="oct_triton">OCT Triton</option>
                    <option value="humphrey">Humphrey</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Descripción del Estudio:</label>
                <textarea name="consulta-textarea" rows="4">Estudio de OCT macular que muestra arquitectura foveal conservada, grosor macular dentro de parámetros normales.</textarea>
            </div>
            
            <div class="form-group">
                <label>Email para compartir:</label>
                <input type="text" name="txtEmailShare" value="test@example.com">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="gridCheck" value="1" checked> Enviar informe
                </label>
            </div>
            
            <div class="form-group">
                <label>ID Usuario:</label>
                <input type="text" name="id_user" value="1">
            </div>
            
            <div class="form-group">
                <label>ID Reserva:</label>
                <input type="text" name="id_reserva" value="0">
            </div>
            
            <div class="form-group">
                <label>Médico ID:</label>
                <input type="text" name="medico_id" value="1">
            </div>
            
            <button type="submit">🚀 Guardar Consulta de Estudios</button>
        </form>
    </body>
    </html>
    <?php
}
?>
