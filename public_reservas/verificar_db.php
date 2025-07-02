<?php
/**
 * Script para verificar la estructura de la base de datos necesaria para las reservas públicas
 */

// Incluir archivos necesarios
require_once __DIR__ . "/../model/conexion.php";
require_once __DIR__ . "/model/ReservasPublicModel.php";
require_once __DIR__ . "/controller/ReservasPublicController.php";

// Preparar salida HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Base de Datos - Reservas Públicas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .check-item { margin-bottom: 10px; }
        .check-success { color: green; }
        .check-warning { color: orange; }
        .check-error { color: red; }
        .code-block { 
            background-color: #f4f4f4; 
            padding: 10px; 
            border-radius: 5px;
            margin: 10px 0;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Diagnóstico de Base de Datos para Reservas Públicas</h1>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Verificación de Conexión</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $conexion = Conexion::conectar();
                    echo "<div class='check-item check-success'><i class='fas fa-check-circle'></i> Conexión a la base de datos establecida correctamente.</div>";
                } catch (PDOException $e) {
                    echo "<div class='check-item check-error'><i class='fas fa-times-circle'></i> Error de conexión: " . $e->getMessage() . "</div>";
                    echo "<div class='alert alert-danger'>No se puede continuar con la verificación. Por favor corrija el error de conexión.</div>";
                    exit;
                }
                ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Verificación de Tablas Requeridas</h5>
            </div>
            <div class="card-body">
                <?php
                $tablasRequeridas = [
                    'servicios_reservas' => 'Tabla principal de reservas',
                    'rh_verificacion' => 'Tabla para códigos de verificación',
                    'rh_person' => 'Tabla de personas (pacientes, médicos)',
                    'rs_servicios' => 'Tabla de servicios médicos',
                    'rh_doctors' => 'Tabla de médicos'
                ];
                
                $tablasExistentes = [];
                $tablasAusentes = [];
                
                try {
                    $stmt = $conexion->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
                    $todasLasTablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    foreach ($tablasRequeridas as $tabla => $descripcion) {
                        if (in_array($tabla, $todasLasTablas)) {
                            $tablasExistentes[] = $tabla;
                            echo "<div class='check-item check-success'><i class='fas fa-check-circle'></i> $tabla: $descripcion - <b>Encontrada</b></div>";
                        } else {
                            $tablasAusentes[] = $tabla;
                            echo "<div class='check-item check-error'><i class='fas fa-times-circle'></i> $tabla: $descripcion - <b>No encontrada</b></div>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<div class='check-item check-error'><i class='fas fa-times-circle'></i> Error al verificar tablas: " . $e->getMessage() . "</div>";
                }
                
                if (count($tablasAusentes) > 0) {
                    echo "<div class='alert alert-warning mt-3'>
                            <strong>Atención:</strong> Algunas tablas necesarias no existen. 
                            A continuación, se muestran los scripts SQL para crear las tablas faltantes.
                          </div>";
                    
                    echo "<h5 class='mt-4'>Scripts para crear tablas faltantes:</h5>";
                    
                    if (in_array('rh_verificacion', $tablasAusentes)) {
                        echo "<h6>Tabla rh_verificacion</h6>";
                        echo "<div class='code-block'>
CREATE TABLE rh_verificacion (
    id SERIAL PRIMARY KEY,
    paciente_id INTEGER NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    verificado BOOLEAN NOT NULL DEFAULT FALSE,
    email VARCHAR(100),
    CONSTRAINT fk_paciente FOREIGN KEY (paciente_id) REFERENCES rh_person(person_id)
);
                        </div>";
                    }
                    
                    if (in_array('servicios_reservas', $tablasAusentes)) {
                        echo "<h6>Tabla servicios_reservas</h6>";
                        echo "<div class='code-block'>
CREATE TABLE servicios_reservas (
    reserva_id SERIAL PRIMARY KEY,
    servicio_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    paciente_id INTEGER NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    reserva_estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
    observaciones TEXT,
    codigo_seguimiento VARCHAR(50),
    business_id INTEGER DEFAULT 1,
    agenda_id INTEGER,
    sala_id INTEGER,
    tarifa_id INTEGER,
    seguro_id INTEGER,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    updated_at TIMESTAMP,
    updated_by INTEGER,
    CONSTRAINT fk_servicio FOREIGN KEY (servicio_id) REFERENCES rs_servicios(serv_id),
    CONSTRAINT fk_doctor FOREIGN KEY (doctor_id) REFERENCES rh_doctors(doctor_id),
    CONSTRAINT fk_paciente FOREIGN KEY (paciente_id) REFERENCES rh_person(person_id)
);
                        </div>";
                    }
                }
                ?>
            </div>
        </div>
        
        <?php if (in_array('rh_verificacion', $tablasExistentes)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Estructura de la Tabla rh_verificacion</h5>
            </div>
            <div class="card-body">
                <?php
                try {
                    $stmt = $conexion->query("
                        SELECT column_name, data_type, is_nullable 
                        FROM information_schema.columns 
                        WHERE table_name = 'rh_verificacion'
                        ORDER BY ordinal_position
                    ");
                    
                    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($columnas) > 0) {
                        echo "<table class='table table-striped table-bordered'>";
                        echo "<thead><tr><th>Columna</th><th>Tipo</th><th>Nullable</th></tr></thead>";
                        echo "<tbody>";
                        
                        $columnasEsperadas = ['id', 'paciente_id', 'codigo', 'fecha_creacion', 'verificado', 'email'];
                        $columnasEncontradas = [];
                        
                        foreach ($columnas as $columna) {
                            echo "<tr>";
                            echo "<td>" . $columna['column_name'] . "</td>";
                            echo "<td>" . $columna['data_type'] . "</td>";
                            echo "<td>" . $columna['is_nullable'] . "</td>";
                            echo "</tr>";
                            
                            $columnasEncontradas[] = $columna['column_name'];
                        }
                        
                        echo "</tbody></table>";
                        
                        $columnasFaltantes = array_diff($columnasEsperadas, $columnasEncontradas);
                        
                        if (count($columnasFaltantes) > 0) {
                            echo "<div class='alert alert-warning mt-3'>";
                            echo "<strong>Atención:</strong> Faltan las siguientes columnas en la tabla rh_verificacion: ";
                            echo implode(', ', $columnasFaltantes);
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-success mt-3'>";
                            echo "<strong>Correcto:</strong> La tabla rh_verificacion tiene todas las columnas requeridas.";
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='alert alert-warning'>";
                        echo "La tabla rh_verificacion existe pero no se pudieron obtener sus columnas.";
                        echo "</div>";
                    }
                } catch (PDOException $e) {
                    echo "<div class='check-item check-error'><i class='fas fa-times-circle'></i> Error al verificar estructura de tabla: " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Prueba de Creación de Código de Verificación</h5>
            </div>
            <div class="card-body">
                <?php
                $pacienteId = 1; // ID de prueba, debe existir en la base de datos
                $codigoPrueba = 'TEST123';
                $emailPrueba = 'test@example.com';
                
                try {
                    // Verificar primero si el paciente existe
                    $stmt = $conexion->prepare("SELECT COUNT(*) FROM rh_person WHERE person_id = :id");
                    $stmt->bindParam(':id', $pacienteId, PDO::PARAM_INT);
                    $stmt->execute();
                    $pacienteExiste = ($stmt->fetchColumn() > 0);
                    
                    if (!$pacienteExiste) {
                        echo "<div class='alert alert-warning'>";
                        echo "No se puede realizar la prueba porque no existe un paciente con ID $pacienteId. ";
                        echo "Por favor, asegúrese de que haya registros en la tabla rh_person.";
                        echo "</div>";
                    } else {
                        $resultado = ReservasPublicModel::mdlGuardarCodigoVerificacion($pacienteId, $codigoPrueba, $emailPrueba);
                        
                        if ($resultado) {
                            echo "<div class='check-item check-success'><i class='fas fa-check-circle'></i> ";
                            echo "Código de verificación guardado correctamente para paciente ID $pacienteId.</div>";
                            
                            // Verificar que se haya guardado correctamente
                            $stmt = $conexion->prepare("SELECT codigo, email FROM rh_verificacion WHERE paciente_id = :id");
                            $stmt->bindParam(':id', $pacienteId, PDO::PARAM_INT);
                            $stmt->execute();
                            $verificacion = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($verificacion) {
                                echo "<div class='alert alert-success'>";
                                echo "<strong>Datos guardados:</strong><br>";
                                echo "Código: " . $verificacion['codigo'] . "<br>";
                                echo "Email: " . $verificacion['email'];
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='check-item check-error'><i class='fas fa-times-circle'></i> ";
                            echo "Error al guardar código de verificación.</div>";
                        }
                    }
                } catch (PDOException $e) {
                    echo "<div class='check-item check-error'><i class='fas fa-times-circle'></i> ";
                    echo "Error en la prueba de código de verificación: " . $e->getMessage() . "</div>";
                    
                    if (strpos($e->getMessage(), 'rh_verificacion') !== false) {
                        echo "<div class='alert alert-danger'>";
                        echo "La tabla rh_verificacion podría no existir o tener una estructura incorrecta.";
                        echo "</div>";
                    }
                } catch (Exception $e) {
                    echo "<div class='check-item check-error'><i class='fas fa-times-circle'></i> ";
                    echo "Excepción: " . $e->getMessage() . "</div>";
                }
                ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Soluciones Posibles</h5>
            </div>
            <div class="card-body">
                <h6>Correcciones para Problemas Comunes:</h6>
                <ol>
                    <li>
                        <strong>Tabla rh_verificacion no existe:</strong>
                        <p>Ejecute el siguiente SQL para crear la tabla:</p>
                        <div class="code-block">
CREATE TABLE rh_verificacion (
    id SERIAL PRIMARY KEY,
    paciente_id INTEGER NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    verificado BOOLEAN NOT NULL DEFAULT FALSE,
    email VARCHAR(100),
    CONSTRAINT fk_paciente FOREIGN KEY (paciente_id) REFERENCES rh_person(person_id)
);
                        </div>
                    </li>
                    <li>
                        <strong>Error en mdlGuardarCodigoVerificacion:</strong>
                        <p>Verifique que la tabla tenga las columnas correctas y que el método en ReservasPublicModel.php sea correcto.</p>
                    </li>
                    <li>
                        <strong>Falta paciente para pruebas:</strong>
                        <p>Inserte un registro de prueba en la tabla rh_person:</p>
                        <div class="code-block">
INSERT INTO rh_person (first_name, last_name, document_number, email, phone)
VALUES ('Usuario', 'Prueba', '12345678', 'test@example.com', '555-1234');
                        </div>
                    </li>
                </ol>
            </div>
        </div>
        
        <a href="index.php" class="btn btn-primary">Volver al Inicio</a>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
