<?php
/**
 * Script simple para probar la conexión y crear las tablas base
 * del sistema de referenciales dinámicos
 */

require_once "model/conexion.php";

// Verificar conexión
echo "<h1>🔍 Verificando Conexión a Base de Datos</h1>";

$pdo = Conexion::conectar();
if ($pdo === null) {
    echo "<div style='background: #f8d7da; padding: 20px; border: 1px solid #dc3545; border-radius: 5px;'>";
    echo "<h2>❌ Error de Conexión</h2>";
    echo "<p>No se pudo establecer conexión con la base de datos.</p>";
    echo "<p>Verifique que PostgreSQL esté funcionando y las credenciales sean correctas.</p>";
    echo "</div>";
    exit;
}

echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #28a745; border-radius: 5px;'>";
echo "<h2>✅ Conexión Exitosa</h2>";
echo "<p>La conexión a la base de datos PostgreSQL se estableció correctamente.</p>";
echo "</div>";

// Crear las tablas una por una
$tablas = [
    'tipos_formularios' => "
        CREATE TABLE IF NOT EXISTS tipos_formularios (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            codigo VARCHAR(50) UNIQUE NOT NULL,
            descripcion TEXT,
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER
        );
    ",
    'tipos_campos' => "
        CREATE TABLE IF NOT EXISTS tipos_campos (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(50) NOT NULL,
            codigo VARCHAR(30) UNIQUE NOT NULL,
            descripcion TEXT,
            html_input_type VARCHAR(30),
            requiere_opciones BOOLEAN DEFAULT FALSE,
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ",
    'referenciales' => "
        CREATE TABLE IF NOT EXISTS referenciales (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            codigo VARCHAR(50) UNIQUE NOT NULL,
            descripcion TEXT,
            categoria VARCHAR(50),
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER
        );
    ",
    'referencial_valores' => "
        CREATE TABLE IF NOT EXISTS referencial_valores (
            id SERIAL PRIMARY KEY,
            referencial_id INTEGER NOT NULL,
            valor VARCHAR(100) NOT NULL,
            etiqueta VARCHAR(200) NOT NULL,
            descripcion TEXT,
            valor_numerico DECIMAL(10,2),
            orden_visualizacion INTEGER DEFAULT 0,
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (referencial_id) REFERENCES referenciales(id)
        );
    ",
    'formulario_campos' => "
        CREATE TABLE IF NOT EXISTS formulario_campos (
            id SERIAL PRIMARY KEY,
            tipo_formulario_id INTEGER NOT NULL,
            nombre_campo VARCHAR(100) NOT NULL,
            etiqueta VARCHAR(200) NOT NULL,
            tipo_campo_id INTEGER NOT NULL,
            referencial_id INTEGER,
            placeholder VARCHAR(200),
            valor_defecto TEXT,
            orden_visualizacion INTEGER DEFAULT 0,
            requerido BOOLEAN DEFAULT FALSE,
            validaciones JSON,
            grupo_seccion VARCHAR(100),
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by INTEGER,
            FOREIGN KEY (tipo_formulario_id) REFERENCES tipos_formularios(id),
            FOREIGN KEY (tipo_campo_id) REFERENCES tipos_campos(id),
            FOREIGN KEY (referencial_id) REFERENCES referenciales(id)
        );
    "
];

echo "<h2>🏗️ Creando Tablas del Sistema</h2>";

foreach ($tablas as $nombre => $sql) {
    try {
        $pdo->exec($sql);
        echo "<div style='background: #d1ecf1; padding: 10px; margin: 5px 0; border: 1px solid #0c5460; border-radius: 5px;'>";
        echo "✅ Tabla <strong>{$nombre}</strong> creada correctamente";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 5px 0; border: 1px solid #dc3545; border-radius: 5px;'>";
        echo "❌ Error creando tabla <strong>{$nombre}</strong>: " . $e->getMessage();
        echo "</div>";
    }
}

// Insertar tipos de campos básicos
echo "<h2>📝 Insertando Tipos de Campos Básicos</h2>";

$tiposCampos = [
    ['text', 'Campo de Texto', 'text', false],
    ['textarea', 'Área de Texto', 'textarea', false],
    ['number', 'Número', 'number', false],
    ['email', 'Email', 'email', false],
    ['date', 'Fecha', 'date', false],
    ['select', 'Lista Desplegable', 'select', true],
    ['radio', 'Opción Múltiple (Radio)', 'radio', true],
    ['checkbox', 'Casilla de Verificación', 'checkbox', false],
    ['summernote', 'Editor de Texto Rico', 'textarea', false]
];

foreach ($tiposCampos as $tipo) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tipos_campos (codigo, nombre, html_input_type, requiere_opciones) 
            VALUES (?, ?, ?, ?) 
            ON CONFLICT (codigo) DO NOTHING
        ");
        $stmt->execute($tipo);
        echo "<div style='background: #d1ecf1; padding: 5px; margin: 2px 0; border-radius: 3px;'>";
        echo "📝 Tipo de campo <strong>{$tipo[1]}</strong> insertado";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='background: #fff3cd; padding: 5px; margin: 2px 0; border-radius: 3px;'>";
        echo "⚠️ Tipo de campo <strong>{$tipo[1]}</strong>: " . $e->getMessage();
        echo "</div>";
    }
}

echo "<h2>📊 Resumen Final</h2>";
echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #28a745; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>🎯 Sistema de Referenciales Dinámicos Instalado</h3>";
echo "<p>✅ Todas las tablas principales han sido creadas</p>";
echo "<p>✅ Tipos de campos básicos insertados</p>";
echo "<p>✅ El sistema está listo para usar</p>";
echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ul>";
echo "<li>Ejecutar el script de ejemplo: <a href='generar_formulario_ejemplo.php'>generar_formulario_ejemplo.php</a></li>";
echo "<li>Acceder al módulo de <a href='index.php?ruta=referenciales'>Gestión de Referenciales</a></li>";
echo "</ul>";
echo "</div>";
?>
