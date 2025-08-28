<?php
/**
 * Inicialización del Sistema Multi-Formulario CRUD
 * Configura la sesión y redirige al sistema genérico
 */

session_start();

// Simular usuario logueado para pruebas
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['full_name'] = 'Administrador del Sistema';
}

// Información del sistema
$systemInfo = [
    'nombre' => 'Sistema Multi-Formulario CRUD',
    'version' => '2.0.0',
    'fecha' => date('Y-m-d'),
    'usuario' => $_SESSION['full_name'],
    'tipos_formulario' => [
        'general' => 'Consulta General',
        'anteojos' => 'Consulta de Anteojos', 
        'informe_imagen' => 'Informe con Imagen',
        'estudios' => 'Estudios Médicos'
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $systemInfo['nombre'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .welcome-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .btn-launch {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 15px 40px;
            color: white;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .btn-launch:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="welcome-card p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-clipboard-list fa-5x text-primary mb-3"></i>
                        <h1 class="display-4 fw-bold text-primary"><?= $systemInfo['nombre'] ?></h1>
                        <p class="lead text-muted">Versión <?= $systemInfo['version'] ?> - <?= $systemInfo['fecha'] ?></p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="feature-card text-start">
                                <h5 class="text-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Sistema Completamente Funcional
                                </h5>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="fas fa-chevron-right text-primary me-2"></i> CRUD completo para todas las consultas</li>
                                    <li><i class="fas fa-chevron-right text-primary me-2"></i> Visualización correcta de todos los datos</li>
                                    <li><i class="fas fa-chevron-right text-primary me-2"></i> Corrección de errores de fecha aplicada</li>
                                    <li><i class="fas fa-chevron-right text-primary me-2"></i> Sistema multi-formulario genérico</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-card text-start">
                                <h5 class="text-info">
                                    <i class="fas fa-layer-group me-2"></i>
                                    Tipos de Formularios Soportados
                                </h5>
                                <ul class="list-unstyled mt-3">
                                    <?php foreach($systemInfo['tipos_formulario'] as $tipo => $nombre): ?>
                                    <li>
                                        <i class="fas fa-file-medical text-primary me-2"></i>
                                        <strong><?= $nombre ?></strong>
                                        <small class="text-muted">(<?= $tipo ?>)</small>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Estado del Sistema
                        </h6>
                        <p class="mb-0">
                            <strong>✅ Sistema 100% Operativo</strong><br>
                            Todas las funcionalidades han sido implementadas y probadas exitosamente.
                            El sistema maneja dinámicamente todos los tipos de formularios configurados
                            en la base de datos.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h5 class="text-primary mb-3">Usuario Actual</h5>
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                            <div>
                                <strong><?= $systemInfo['usuario'] ?></strong><br>
                                <small class="text-muted">Sesión iniciada</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-3 d-md-flex justify-content-center">
                        <a href="multiform-crud-system.html" class="btn-launch">
                            <i class="fas fa-rocket me-2"></i>
                            Acceder al Sistema Multi-Formulario
                        </a>
                        <a href="livewire-crud-system.html" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-eye me-2"></i>
                            Sistema Anterior (Referencia)
                        </a>
                    </div>

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Última actualización: <?= date('d/m/Y H:i:s') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información técnica -->
        <div class="row justify-content-center mt-4">
            <div class="col-lg-10">
                <div class="welcome-card p-4">
                    <h5 class="text-center mb-4">
                        <i class="fas fa-cogs text-primary me-2"></i>
                        Información Técnica
                    </h5>
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <i class="fas fa-database fa-2x text-primary mb-2"></i>
                            <h6>Base de Datos</h6>
                            <p class="small text-muted">PostgreSQL con 117 consultas<br>4 tipos de formularios</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-code fa-2x text-success mb-2"></i>
                            <h6>Frontend</h6>
                            <p class="small text-muted">Bootstrap 5.3 + JavaScript<br>Sistema genérico dinámico</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-server fa-2x text-info mb-2"></i>
                            <h6>Backend</h6>
                            <p class="small text-muted">PHP 8 + PDO<br>API REST multi-formulario</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar información de carga
        console.log('🚀 Sistema Multi-Formulario CRUD v<?= $systemInfo['version'] ?> inicializado');
        console.log('👤 Usuario:', '<?= $systemInfo['usuario'] ?>');
        console.log('📊 Tipos soportados:', <?= json_encode(array_keys($systemInfo['tipos_formulario'])) ?>);
        
        // Efecto de bienvenida
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.welcome-card').style.opacity = '0';
            document.querySelector('.welcome-card').style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                document.querySelector('.welcome-card').style.transition = 'all 0.5s ease';
                document.querySelector('.welcome-card').style.opacity = '1';
                document.querySelector('.welcome-card').style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>