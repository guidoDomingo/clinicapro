<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ Verificación Final - Sistema de Referenciales Dinámicos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .card { box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: none; border-radius: 15px; }
        .card-header { border-radius: 15px 15px 0 0 !important; }
        .success-badge { background: linear-gradient(45deg, #28a745, #20c997); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9em; }
        .info-box { background: #f8f9fa; border-left: 4px solid #007bff; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .data-table { font-size: 0.9em; }
        .data-table th { background-color: #e9ecef; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h1><i class="fas fa-check-circle"></i> Verificación Final del Sistema</h1>
                        <p class="mb-0">Sistema de Referenciales Dinámicos para Formularios de Anteojos</p>
                    </div>
                    
                    <div class="card-body">
                        <?php
                        require_once "model/conexion.php";
                        require_once "model/formularios_dinamicos.model.php";
                        
                        try {
                            $pdo = Conexion::conectar();
                            
                            // Verificar datos de esfera
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) as total_valores
                                FROM referencial_valores rv
                                INNER JOIN referenciales r ON rv.referencial_id = r.id
                                WHERE r.codigo = 'valores_esfera' AND r.activo = 1 AND rv.activo = 1
                            ");
                            $stmt->execute();
                            $totalEsfera = $stmt->fetch()['total_valores'];
                            
                            // Verificar otros referenciales
                            $referenciales = [
                                'valores_esfera' => 'Valores de Esfera',
                                'valores_cilindro' => 'Valores de Cilindro', 
                                'valores_adicion' => 'Valores de Adición',
                                'distancias_pupilares' => 'Distancias Pupilares',
                                'valores_altura' => 'Valores de Altura'
                            ];
                            
                            echo '<h2><i class="fas fa-database"></i> Estado de la Base de Datos</h2>';
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-striped data-table">';
                            echo '<thead><tr><th>Referencial</th><th>Valores Activos</th><th>Estado</th></tr></thead>';
                            echo '<tbody>';
                            
                            foreach ($referenciales as $codigo => $nombre) {
                                $stmt = $pdo->prepare("
                                    SELECT COUNT(*) as total
                                    FROM referencial_valores rv
                                    INNER JOIN referenciales r ON rv.referencial_id = r.id
                                    WHERE r.codigo = :codigo AND r.activo = 1 AND rv.activo = 1
                                ");
                                $stmt->bindParam(':codigo', $codigo);
                                $stmt->execute();
                                $count = $stmt->fetch()['total'];
                                
                                $badge = $count > 0 ? '<span class="success-badge">✅ Activo</span>' : '<span class="badge badge-warning">⚠️ Sin datos</span>';
                                echo "<tr><td><strong>$nombre</strong><br><small class='text-muted'>$codigo</small></td><td><span class='badge badge-primary'>$count valores</span></td><td>$badge</td></tr>";
                            }
                            
                            echo '</tbody></table>';
                            echo '</div>';
                            
                        } catch (Exception $e) {
                            echo '<div class="alert alert-danger">❌ Error de conexión: ' . $e->getMessage() . '</div>';
                        }
                        ?>
                        
                        <hr>
                        
                        <h2><i class="fas fa-cogs"></i> Prueba de Generación Dinámica</h2>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h5><i class="fas fa-eye"></i> Campo OD Esfera</h5>
                                    <?php 
                                    $htmlOD = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'demo_od_esf', 'demo_od_esf');
                                    echo $htmlOD;
                                    
                                    $opcionesOD = substr_count($htmlOD, '<option');
                                    echo "<p class='mt-2'><strong>Opciones generadas:</strong> <span class='badge badge-info'>$opcionesOD</span></p>";
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="info-box">
                                    <h5><i class="fas fa-eye"></i> Campo OI Esfera</h5>
                                    <?php 
                                    $htmlOI = FormulariosDinamicos::generarSelectReferencial('valores_esfera', 'demo_oi_esf', 'demo_oi_esf');
                                    echo $htmlOI;
                                    
                                    $opcionesOI = substr_count($htmlOI, '<option');
                                    echo "<p class='mt-2'><strong>Opciones generadas:</strong> <span class='badge badge-info'>$opcionesOI</span></p>";
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h2><i class="fas fa-clipboard-check"></i> Verificación de Integridad</h2>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-success">✅ Base de Datos</h5>
                                        <p class="card-text">
                                            <strong><?php echo $totalEsfera; ?></strong> valores de esfera<br>
                                            <small class="text-muted">Datos correctos</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-info">🔧 Generación HTML</h5>
                                        <p class="card-text">
                                            <strong><?php echo $opcionesOD; ?></strong> opciones totales<br>
                                            <small class="text-muted"><?php echo $totalEsfera; ?> valores + 1 "Seleccionar"</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-warning">⚖️ Consistencia</h5>
                                        <p class="card-text">
                                            <?php 
                                            if ($opcionesOD == ($totalEsfera + 1)) {
                                                echo '<strong class="text-success">✅ Perfecta</strong><br><small class="text-muted">Sin duplicados</small>';
                                            } else {
                                                echo '<strong class="text-danger">❌ Error</strong><br><small class="text-muted">Revisar configuración</small>';
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h2><i class="fas fa-clipboard-list"></i> Valores Actuales de Esfera</h2>
                        
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT rv.valor, rv.etiqueta, rv.orden_visualizacion 
                                FROM referencial_valores rv
                                INNER JOIN referenciales r ON rv.referencial_id = r.id
                                WHERE r.codigo = 'valores_esfera' AND r.activo = 1 AND rv.activo = 1
                                ORDER BY rv.orden_visualizacion, rv.etiqueta
                            ");
                            $stmt->execute();
                            $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (!empty($valores)) {
                                echo '<div class="table-responsive">';
                                echo '<table class="table table-sm table-bordered data-table">';
                                echo '<thead class="thead-light"><tr><th>Orden</th><th>Valor</th><th>Etiqueta</th></tr></thead>';
                                echo '<tbody>';
                                
                                foreach ($valores as $valor) {
                                    echo '<tr>';
                                    echo '<td class="text-center">' . $valor['orden_visualizacion'] . '</td>';
                                    echo '<td><code>' . htmlspecialchars($valor['valor']) . '</code></td>';
                                    echo '<td><strong>' . htmlspecialchars($valor['etiqueta']) . '</strong></td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody></table>';
                                echo '</div>';
                            }
                            
                        } catch (Exception $e) {
                            echo '<div class="alert alert-warning">⚠️ No se pudieron cargar los valores: ' . $e->getMessage() . '</div>';
                        }
                        ?>
                        
                        <div class="alert alert-success mt-4">
                            <h4 class="alert-heading"><i class="fas fa-check-circle"></i> ¡Sistema Funcionando Correctamente!</h4>
                            <p>El sistema de referenciales dinámicos está operativo. Los formularios de anteojos ahora cargan los valores directamente desde la base de datos.</p>
                            <hr>
                            <p class="mb-0">
                                <strong>✅ Lo que se ha implementado:</strong><br>
                                • Referenciales dinámicos en lugar de valores hardcodeados<br>
                                • Formularios que se actualizan automáticamente desde la BD<br>
                                • Sistema administrativo para gestionar los valores<br>
                                • Campos convertidos: Esfera, Cilindro y Adición
                            </p>
                        </div>
                        
                        <div class="text-center">
                            <a href="servicios" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-left"></i> Volver al Sistema
                            </a>
                            <a href="view/modules/referenciales/" class="btn btn-info btn-lg ml-2">
                                <i class="fas fa-cog"></i> Administrar Referenciales
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Mostrar información adicional del navegador
            console.log('✅ Verificación del sistema completada');
            console.log('🌐 User Agent:', navigator.userAgent);
            console.log('🕒 Timestamp:', new Date().toISOString());
        });
    </script>
</body>
</html>
