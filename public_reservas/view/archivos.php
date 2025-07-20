<?php
// Verificar que se proporcione un código de seguimiento
if (!isset($_GET['codigo']) || empty($_GET['codigo'])) {
    echo "<div class='alert alert-danger'>Código de seguimiento no proporcionado.</div>";
    return;
}

$codigoSeguimiento = $_GET['codigo'];

// Obtener los archivos de la reserva
$archivos = ReservasPublicController::ctrObtenerArchivosPorCodigo($codigoSeguimiento);

// Si no hay archivos
if (empty($archivos)) {
    echo "<div class='alert alert-info'>
            <i class='fas fa-info-circle'></i> 
            No hay archivos adjuntos para esta reserva.
          </div>";
    return;
}

// Obtener información de la reserva (desde el primer archivo)
$reservaInfo = $archivos[0];
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-file-alt mr-2"></i>
                    Archivos de la Reserva
                </h4>
                <small>Código: <?php echo htmlspecialchars($codigoSeguimiento); ?></small>
            </div>
            
            <div class="card-body">
                <!-- Información de la reserva -->
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong><i class="fas fa-user mr-2"></i>Paciente:</strong> 
                            <?php echo htmlspecialchars($reservaInfo['paciente_nombre']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-calendar mr-2"></i>Fecha:</strong> 
                            <?php echo date('d/m/Y', strtotime($reservaInfo['fecha_reserva'])); ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong><i class="fas fa-clock mr-2"></i>Hora:</strong> 
                            <?php echo date('H:i', strtotime($reservaInfo['hora_inicio'])); ?>
                        </div>
                        <div class="col-md-6">
                            <strong><i class="fas fa-files-o mr-2"></i>Total archivos:</strong> 
                            <?php echo count($archivos); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Lista de archivos -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th><i class="fas fa-file mr-1"></i>Archivo</th>
                                <th><i class="fas fa-tag mr-1"></i>Tipo</th>
                                <th><i class="fas fa-weight-hanging mr-1"></i>Tamaño</th>
                                <th><i class="fas fa-calendar-plus mr-1"></i>Fecha Subida</th>
                                <th><i class="fas fa-cogs mr-1"></i>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archivos as $archivo): ?>
                            <tr>
                                <td>
                                    <i class="fas fa-file-<?php echo getFileIcon($archivo['tipo_archivo']); ?> mr-2 text-primary"></i>
                                    <?php echo htmlspecialchars($archivo['nombre_original']); ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo strtoupper($archivo['tipo_archivo']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatBytes($archivo['tamaño_archivo']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($archivo['fecha_subida'])); ?></td>
                                <td>
                                    <?php if (in_array($archivo['tipo_archivo'], ['pdf', 'jpg', 'jpeg', 'png'])): ?>
                                    <a href="ver_archivo.php?id=<?php echo $archivo['archivo_id']; ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       target="_blank"
                                       title="Ver archivo">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <a href="descargar_archivo.php?id=<?php echo $archivo['archivo_id']; ?>" 
                                       class="btn btn-sm btn-outline-success ml-1"
                                       title="Descargar archivo">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Botón para regresar -->
                <div class="text-center mt-4">
                    <a href="index.php?accion=consultar" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Volver a Consultar Reserva
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Función auxiliar para obtener el ícono del archivo
 */
function getFileIcon($extension) {
    $icons = [
        'pdf' => 'pdf',
        'jpg' => 'image',
        'jpeg' => 'image', 
        'png' => 'image',
        'doc' => 'word',
        'docx' => 'word'
    ];
    
    return $icons[$extension] ?? 'file';
}

/**
 * Función auxiliar para formatear bytes
 */
function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}
?>
