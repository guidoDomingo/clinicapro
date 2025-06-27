<?php
/**
 * Página de diagnóstico para el módulo de reservas públicas
 * Muestra información detallada sobre la configuración y funcionamiento
 */

require_once __DIR__ . "/model/ReservasPublicModel.php";
require_once __DIR__ . "/controller/ReservasPublicController.php";

// Función para mostrar información en formato amigable
function mostrarInfo($titulo, $data) {
    echo "<div class='card mb-3'>";
    echo "<div class='card-header bg-primary text-white'><h5>$titulo</h5></div>";
    echo "<div class='card-body'>";
    
    if (is_array($data) && count($data) > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-striped table-bordered table-sm'>";
        
        // Encabezados
        echo "<thead><tr>";
        foreach (array_keys($data[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr></thead>";
        
        // Datos
        echo "<tbody>";
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                if (is_array($value)) {
                    echo "<td><pre>" . htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) . "</pre></td>";
                } else {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</tbody>";
        
        echo "</table>";
        echo "</div>";
    } elseif (is_array($data)) {
        echo "<div class='alert alert-warning'>No se encontraron datos.</div>";
    } else {
        echo "<div class='alert alert-info'>" . htmlspecialchars($data) . "</div>";
    }
    
    echo "</div>"; // card-body
    echo "</div>"; // card
}

// Fecha para pruebas (se puede cambiar por GET)
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$doctorId = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : null;
$servicioId = isset($_GET['servicio_id']) ? intval($_GET['servicio_id']) : null;

// Obtener todos los servicios
$servicios = ReservasPublicModel::mdlObtenerServicios();

// Obtener todos los médicos para la fecha seleccionada
$medicos = ReservasPublicModel::mdlObtenerMedicosDisponibles($fecha);

// Si no hay doctor seleccionado pero hay médicos disponibles, tomar el primero
if ($doctorId === null && !empty($medicos)) {
    $doctorId = $medicos[0]['doctor_id'];
}

// Si no hay servicio seleccionado pero hay servicios disponibles, tomar el primero
if ($servicioId === null && !empty($servicios)) {
    $servicioId = $servicios[0]['serv_id'];
}

// Verificar reservas existentes
$reservasExistentes = [];
// Verificar horarios disponibles
$horariosDisponibles = [];

if ($doctorId !== null) {
    $reservasExistentes = ReservasPublicModel::mdlVerificarReservasExistentes($fecha, $doctorId);
}

if ($doctorId !== null && $servicioId !== null) {
    $horariosDisponibles = ReservasPublicController::ctrObtenerHorariosDisponibles($fecha, $servicioId, $doctorId);
}

// Leer contenido del archivo de log
$logContent = '';
$logFile = 'c:/laragon/www/clinica/logs/public_reservas.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    // Limitar a las últimas 50 líneas
    $logLines = explode("\n", $logContent);
    $logLines = array_slice($logLines, max(0, count($logLines) - 50));
    $logContent = implode("\n", $logLines);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Reservas Públicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }
        .logs {
            background-color: #212529;
            color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand">Diagnóstico de Reservas Públicas</span>
            <a href="/clinica/public_reservas/" class="btn btn-outline-light">Volver al Módulo</a>
        </div>
    </nav>
    
    <div class="container my-4">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h4>Configuración de Pruebas</h4>
            </div>
            <div class="card-body">
                <form action="" method="get" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="servicio_id" class="form-label">Servicio</label>
                        <select class="form-select" id="servicio_id" name="servicio_id">
                            <?php foreach ($servicios as $servicio): ?>
                            <option value="<?php echo $servicio['serv_id']; ?>" <?php echo $servicioId == $servicio['serv_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($servicio['serv_descripcion']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="doctor_id" class="form-label">Médico</label>
                        <select class="form-select" id="doctor_id" name="doctor_id">
                            <?php foreach ($medicos as $medico): ?>
                            <option value="<?php echo $medico['doctor_id']; ?>" <?php echo $doctorId == $medico['doctor_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($medico['nombre_doctor']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <?php mostrarInfo("Servicios (" . count($servicios) . ")", $servicios); ?>
            </div>
            <div class="col-md-6">
                <?php mostrarInfo("Médicos Disponibles para $fecha (" . count($medicos) . ")", $medicos); ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <?php mostrarInfo("Reservas Existentes para Doctor ID: $doctorId (" . count($reservasExistentes) . ")", $reservasExistentes); ?>
            </div>
            <div class="col-md-6">
                <?php mostrarInfo("Horarios Disponibles (" . count($horariosDisponibles) . ")", $horariosDisponibles); ?>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-dark text-white">
                <h4>Últimas entradas del Registro (Log)</h4>
            </div>
            <div class="card-body">
                <div class="logs"><?php echo htmlspecialchars($logContent); ?></div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
