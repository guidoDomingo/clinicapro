<?php
/**
 * Script para visualizar todos los datos existentes en la base de datos
 * Especialmente enfocado en referenciales y motivos comunes
 */

// Incluir configuración del entorno
require_once __DIR__ . '/config/environment_setup.php';
use Config\EnvironmentSetup;

try {
    // Obtener configuración de base de datos dinámicamente
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔍 Exploración de Datos en la Base de Datos</h1>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin: 30px 0; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .info { color: blue; }
    </style>";
    
    // 1. Ver todas las tablas disponibles
    echo "<div class='section'>";
    echo "<h2>📋 1. Tablas Disponibles en la Base de Datos</h2>";
    $stmt = $pdo->query("
        SELECT schemaname, tablename, tableowner 
        FROM pg_tables 
        WHERE schemaname = 'public' 
        ORDER BY tablename
    ");
    $tablas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Esquema</th><th>Nombre de Tabla</th><th>Propietario</th></tr>";
    foreach ($tablas as $tabla) {
        echo "<tr><td>{$tabla['schemaname']}</td><td><strong>{$tabla['tablename']}</strong></td><td>{$tabla['tableowner']}</td></tr>";
    }
    echo "</table>";
    echo "<p class='info'>📊 Total de tablas: " . count($tablas) . "</p>";
    echo "</div>";
    
    // 2. Explorar tabla referenciales
    echo "<div class='section'>";
    echo "<h2>📚 2. Tabla 'referenciales' - Estructura y Datos</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM referenciales ORDER BY id");
        $referenciales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($referenciales)) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th><th>Categoría</th><th>Activo</th><th>Creado</th></tr>";
            foreach ($referenciales as $ref) {
                echo "<tr>";
                echo "<td>{$ref['id']}</td>";
                echo "<td><strong>{$ref['codigo']}</strong></td>";
                echo "<td>{$ref['nombre']}</td>";
                echo "<td>" . (isset($ref['descripcion']) ? $ref['descripcion'] : 'N/A') . "</td>";
                echo "<td>" . (isset($ref['categoria']) ? $ref['categoria'] : 'N/A') . "</td>";
                echo "<td>" . ($ref['activo'] ? '✅ Sí' : '❌ No') . "</td>";
                echo "<td>" . (isset($ref['created_at']) ? $ref['created_at'] : 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<p class='success'>📊 Total de referenciales: " . count($referenciales) . "</p>";
        } else {
            echo "<p class='warning'>⚠️ No hay datos en la tabla referenciales</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error accediendo a tabla referenciales: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // 3. Explorar tabla referencial_valores
    echo "<div class='section'>";
    echo "<h2>📝 3. Tabla 'referencial_valores' - Valores por Referencial</h2>";
    try {
        $stmt = $pdo->query("
            SELECT rv.*, r.codigo, r.nombre as nombre_referencial
            FROM referencial_valores rv
            JOIN referenciales r ON rv.referencial_id = r.id
            ORDER BY r.codigo, rv.orden, rv.texto
        ");
        $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($valores)) {
            // Agrupar por referencial
            $valoresPorReferencial = [];
            foreach ($valores as $valor) {
                $codigo = $valor['codigo'];
                if (!isset($valoresPorReferencial[$codigo])) {
                    $valoresPorReferencial[$codigo] = [
                        'nombre' => $valor['nombre_referencial'],
                        'valores' => []
                    ];
                }
                $valoresPorReferencial[$codigo]['valores'][] = $valor;
            }
            
            foreach ($valoresPorReferencial as $codigo => $data) {
                echo "<h3>📋 Referencial: <strong>$codigo</strong> - {$data['nombre']}</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Valor</th><th>Texto</th><th>Descripción</th><th>Orden</th><th>Activo</th></tr>";
                foreach ($data['valores'] as $valor) {
                    echo "<tr>";
                    echo "<td>{$valor['id']}</td>";
                    echo "<td><strong>{$valor['valor']}</strong></td>";
                    echo "<td>{$valor['texto']}</td>";
                    echo "<td>" . (isset($valor['descripcion']) ? $valor['descripcion'] : 'N/A') . "</td>";
                    echo "<td>" . (isset($valor['orden']) ? $valor['orden'] : 'N/A') . "</td>";
                    echo "<td>" . ($valor['activo'] ? '✅ Sí' : '❌ No') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<p class='info'>📊 Total valores: " . count($data['valores']) . "</p><br>";
            }
        } else {
            echo "<p class='warning'>⚠️ No hay valores en la tabla referencial_valores</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error accediendo a tabla referencial_valores: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // 4. Explorar tabla motivos_comunes
    echo "<div class='section'>";
    echo "<h2>💭 4. Tabla 'motivos_comunes'</h2>";
    try {
        $stmt = $pdo->query("SELECT * FROM motivos_comunes ORDER BY tipo_formulario, nombre");
        $motivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($motivos)) {
            // Agrupar por tipo de formulario
            $motivosPorTipo = [];
            foreach ($motivos as $motivo) {
                $tipo = $motivo['tipo_formulario'] ?? 'general';
                if (!isset($motivosPorTipo[$tipo])) {
                    $motivosPorTipo[$tipo] = [];
                }
                $motivosPorTipo[$tipo][] = $motivo;
            }
            
            foreach ($motivosPorTipo as $tipo => $motivosDelTipo) {
                echo "<h3>📋 Motivos para: <strong>$tipo</strong></h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Activo</th><th>Creado</th></tr>";
                foreach ($motivosDelTipo as $motivo) {
                    echo "<tr>";
                    echo "<td>{$motivo['id_motivo']}</td>";
                    echo "<td><strong>{$motivo['nombre']}</strong></td>";
                    echo "<td>" . (isset($motivo['descripcion']) ? $motivo['descripcion'] : 'N/A') . "</td>";
                    echo "<td>" . ($motivo['activo'] ? '✅ Sí' : '❌ No') . "</td>";
                    echo "<td>" . (isset($motivo['created_at']) ? $motivo['created_at'] : 'N/A') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<p class='info'>📊 Total motivos: " . count($motivosDelTipo) . "</p><br>";
            }
            
            echo "<p class='success'>📊 Total global de motivos comunes: " . count($motivos) . "</p>";
        } else {
            echo "<p class='warning'>⚠️ No hay datos en la tabla motivos_comunes</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error accediendo a tabla motivos_comunes: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    // 5. Buscar otras tablas relacionadas con formularios
    echo "<div class='section'>";
    echo "<h2>🔍 5. Otras Tablas Relacionadas con Formularios</h2>";
    $tablasInteres = ['preformatos', 'consultas', 'pacientes', 'usuarios', 'especialidades'];
    
    foreach ($tablasInteres as $tabla) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabla");
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>📊 <strong>$tabla:</strong> {$resultado['total']} registros</p>";
        } catch (Exception $e) {
            echo "<p class='warning'>⚠️ Tabla '$tabla' no existe o no es accesible</p>";
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>✅ 6. Resumen del Estado de Datos</h2>";
    echo "<ul>";
    echo "<li><strong>Referenciales:</strong> " . (isset($referenciales) ? count($referenciales) : 0) . " definidos</li>";
    echo "<li><strong>Valores de Referenciales:</strong> " . (isset($valores) ? count($valores) : 0) . " valores total</li>";
    echo "<li><strong>Motivos Comunes:</strong> " . (isset($motivos) ? count($motivos) : 0) . " motivos definidos</li>";
    echo "<li><strong>Tablas Disponibles:</strong> " . count($tablas) . " tablas en el esquema público</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Error de conexión a la base de datos:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>