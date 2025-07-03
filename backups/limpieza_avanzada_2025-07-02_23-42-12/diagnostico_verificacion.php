<?php
/**
 * Script de diagnóstico para verificar la tabla rh_verificacion
 * y solucionar problemas relacionados con la verificación de reservas
 */

require_once __DIR__ . "/../model/conexion.php";

// Función para verificar si una tabla existe
function tableExists($tableName) {
    try {
        $stmt = Conexion::conectar()->prepare("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_name = :table_name
            ) AS table_exists;
        ");
        $stmt->bindParam(":table_name", $tableName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al verificar tabla: " . $e->getMessage() . "</div>";
        return false;
    }
}

// Función para verificar la estructura de una tabla
function checkTableStructure($tableName, $expectedColumns) {
    try {
        $stmt = Conexion::conectar()->prepare("
            SELECT column_name, data_type 
            FROM information_schema.columns 
            WHERE table_name = :table_name
            ORDER BY ordinal_position;
        ");
        $stmt->bindParam(":table_name", $tableName, PDO::PARAM_STR);
        $stmt->execute();
        
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'column_name');
        
        $missingColumns = array_diff($expectedColumns, $columnNames);
        $extraColumns = array_diff($columnNames, $expectedColumns);
        
        return [
            'columns' => $columns,
            'missing' => $missingColumns,
            'extra' => $extraColumns,
            'valid' => empty($missingColumns)
        ];
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al verificar estructura de tabla: " . $e->getMessage() . "</div>";
        return [
            'columns' => [],
            'missing' => $expectedColumns,
            'extra' => [],
            'valid' => false
        ];
    }
}

// Verificar si se solicitó la creación de la tabla
if (isset($_POST['create_table'])) {
    try {
        $sql = file_get_contents(__DIR__ . "/../sql/crear_tabla_verificacion.sql");
        $pdo = Conexion::conectar();
        $pdo->exec($sql);
        echo "<div class='alert alert-success'>¡Tabla creada exitosamente!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al crear tabla: " . $e->getMessage() . "</div>";
    }
}

// Verificar si se solicitó la simulación de una verificación
if (isset($_POST['simulate_verification'])) {
    $pacienteId = $_POST['paciente_id'];
    $codigo = $_POST['codigo_verificacion'];
    $email = $_POST['email'];
    
    try {
        // Incluir el modelo para usar las funciones
        require_once __DIR__ . "/../model/ReservasPublicModel.php";
        
        // Guardar código
        $guardado = ReservasPublicModel::mdlGuardarCodigoVerificacion($pacienteId, $codigo, $email);
        
        // Verificar código
        $verificado = ReservasPublicModel::mdlVerificarCodigo($pacienteId, $codigo);
        
        echo "<div class='alert alert-info'>
            <strong>Simulación completada:</strong><br>
            Código guardado: " . ($guardado ? "Sí" : "No") . "<br>
            Código verificado: " . ($verificado ? "Sí" : "No") . "
        </div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error en simulación: " . $e->getMessage() . "</div>";
    }
}

// Verificar la tabla
$tableExists = tableExists("rh_verificacion");

// Columnas esperadas
$expectedColumns = [
    'verificacion_id',
    'person_id',
    'codigo_verificacion',
    'email',
    'fecha_generacion',
    'fecha_expiracion',
    'usado',
    'fecha_uso',
    'tipo'
];

// Verificar estructura si la tabla existe
$tableStructure = $tableExists ? checkTableStructure("rh_verificacion", $expectedColumns) : null;

// Verificar los logs de verificación
$logFile = "c:/laragon/www/clinica/logs/verificacion.log";
$logExists = file_exists($logFile);
$logContent = $logExists ? file_get_contents($logFile) : null;
$logPermissions = $logExists ? substr(sprintf('%o', fileperms($logFile)), -4) : null;
$logWritable = $logExists ? is_writable($logFile) : false;

// Contar registros de verificación si la tabla existe
$verificationCount = 0;
if ($tableExists) {
    try {
        $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) FROM rh_verificacion");
        $stmt->execute();
        $verificationCount = $stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al contar registros: " . $e->getMessage() . "</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Verificación</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .status-card { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto; }
        .table-responsive { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico del Sistema de Verificación</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card status-card">
                    <div class="card-header bg-info text-white">
                        <h4>Estado de la Tabla</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Tabla rh_verificacion:</strong> 
                            <?php if ($tableExists): ?>
                                <span class="badge badge-success">Existe</span>
                            <?php else: ?>
                                <span class="badge badge-danger">No existe</span>
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($tableExists): ?>
                            <p><strong>Estructura válida:</strong> 
                                <?php if ($tableStructure['valid']): ?>
                                    <span class="badge badge-success">Sí</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">No</span>
                                <?php endif; ?>
                            </p>
                            
                            <p><strong>Registros en la tabla:</strong> <?php echo $verificationCount; ?></p>
                            
                            <?php if (!empty($tableStructure['missing'])): ?>
                                <p><strong>Columnas faltantes:</strong> <?php echo implode(", ", $tableStructure['missing']); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (!$tableExists): ?>
                            <form method="post" class="mt-3">
                                <button type="submit" name="create_table" class="btn btn-primary">Crear Tabla</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card status-card">
                    <div class="card-header bg-info text-white">
                        <h4>Estado de Logs</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Archivo de log:</strong> 
                            <?php if ($logExists): ?>
                                <span class="badge badge-success">Existe</span>
                            <?php else: ?>
                                <span class="badge badge-danger">No existe</span>
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($logExists): ?>
                            <p><strong>Permisos:</strong> <?php echo $logPermissions; ?></p>
                            <p><strong>Escritura permitida:</strong> 
                                <?php if ($logWritable): ?>
                                    <span class="badge badge-success">Sí</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">No</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Tamaño:</strong> <?php echo round(filesize($logFile) / 1024, 2); ?> KB</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>Simulación de Verificación</h4>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="form-group">
                        <label for="paciente_id">ID del Paciente:</label>
                        <input type="number" class="form-control" id="paciente_id" name="paciente_id" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="codigo_verificacion">Código de Verificación:</label>
                        <input type="text" class="form-control" id="codigo_verificacion" name="codigo_verificacion" value="<?php echo strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6)); ?>" required>
                    </div>
                    <button type="submit" name="simulate_verification" class="btn btn-primary">Simular Verificación</button>
                </form>
            </div>
        </div>
        
        <?php if ($tableExists && $verificationCount > 0): ?>
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h4>Últimas Verificaciones</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Paciente ID</th>
                                <th>Código</th>
                                <th>Email</th>
                                <th>Generado</th>
                                <th>Expira</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $stmt = Conexion::conectar()->prepare(
                                    "SELECT * FROM rh_verificacion ORDER BY fecha_generacion DESC LIMIT 10"
                                );
                                $stmt->execute();
                                
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['verificacion_id'] . "</td>";
                                    echo "<td>" . $row['person_id'] . "</td>";
                                    echo "<td>" . $row['codigo_verificacion'] . "</td>";
                                    echo "<td>" . $row['email'] . "</td>";
                                    echo "<td>" . $row['fecha_generacion'] . "</td>";
                                    echo "<td>" . $row['fecha_expiracion'] . "</td>";
                                    echo "<td>" . ($row['usado'] ? "Usado" : "Pendiente") . "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='7'>Error al obtener verificaciones: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($logExists && $logContent): ?>
        <div class="card mt-4">
            <div class="card-header bg-secondary text-white">
                <h4>Contenido del Log de Verificación</h4>
            </div>
            <div class="card-body">
                <pre><?php echo htmlspecialchars(substr($logContent, -5000)); ?></pre>
                <p class="text-muted">Mostrando los últimos 5000 caracteres del log.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
