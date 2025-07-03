<?php
/**
 * Script para verificar y habilitar la extensión PostgreSQL para PHP
 * 
 * Este script verifica si la extensión pdo_pgsql está habilitada y proporciona
 * instrucciones para habilitarla si es necesario.
 */

// Encabezado HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de extensión PostgreSQL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        code {
            background-color: #f5f5f5;
            padding: 3px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        .steps {
            background-color: #fff8e1;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        ul {
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <h1>Verificación de la Extensión PostgreSQL para PHP</h1>';

// Verificar si la extensión está cargada
$pgsqlLoaded = extension_loaded('pdo_pgsql');

if ($pgsqlLoaded) {
    echo '<div class="success">
        <h2>✅ La extensión PDO PostgreSQL está habilitada</h2>
        <p>La extensión <code>pdo_pgsql</code> está correctamente instalada y habilitada en PHP.</p>
        <p>El sistema puede conectar a bases de datos PostgreSQL sin problemas.</p>
        <p><a href="index.php">Volver al inicio</a></p>
    </div>';
} else {
    echo '<div class="error">
        <h2>❌ La extensión PDO PostgreSQL NO está habilitada</h2>
        <p>La extensión <code>pdo_pgsql</code> no está habilitada en su instalación de PHP.</p>
        <p>Esta extensión es necesaria para que la aplicación se conecte a la base de datos PostgreSQL.</p>
    </div>';

    // Obtener información sobre PHP
    echo '<div class="info">
        <h2>Información de PHP</h2>
        <ul>
            <li><strong>Versión de PHP:</strong> ' . phpversion() . '</li>
            <li><strong>Ruta de php.ini:</strong> ' . php_ini_loaded_file() . '</li>
            <li><strong>Sistema Operativo:</strong> ' . PHP_OS . '</li>
        </ul>
    </div>';

    // Lista de extensiones cargadas
    echo '<div class="info">
        <h2>Extensiones PHP cargadas</h2>
        <p>Las siguientes extensiones están actualmente habilitadas:</p>
        <code>' . implode(', ', get_loaded_extensions()) . '</code>
    </div>';

    // Instrucciones para habilitar la extensión
    echo '<div class="steps">
        <h2>Pasos para habilitar la extensión PDO PostgreSQL</h2>
        
        <h3>Para Windows (Laragon/XAMPP/WAMP):</h3>
        <ol>
            <li>Abra el archivo <code>php.ini</code> ubicado en: ' . php_ini_loaded_file() . '</li>
            <li>Busque la línea que contiene <code>;extension=pdo_pgsql</code> o <code>;extension=php_pdo_pgsql.dll</code></li>
            <li>Quite el punto y coma (;) del inicio de la línea para descomentarla</li>
            <li>Si la línea no existe, agréguela: <code>extension=pdo_pgsql</code></li>
            <li>Guarde el archivo</li>
            <li>Reinicie el servidor web (Laragon, XAMPP, etc.)</li>
        </ol>

        <h3>Para Linux (Ubuntu/Debian):</h3>
        <ol>
            <li>Instale la extensión usando: <code>sudo apt-get install php-pgsql</code></li>
            <li>Reinicie Apache: <code>sudo systemctl restart apache2</code></li>
        </ol>

        <h3>Para Linux (CentOS/RHEL):</h3>
        <ol>
            <li>Instale la extensión usando: <code>sudo yum install php-pgsql</code></li>
            <li>Reinicie Apache: <code>sudo systemctl restart httpd</code></li>
        </ol>
        
        <p>Una vez que haya seguido estos pasos, <a href="check_and_enable_pgsql.php">actualice esta página</a> para verificar si la extensión está habilitada correctamente.</p>
    </div>';
}

// Verificar la extensión de PostgreSQL
if ($pgsqlLoaded) {
    echo '<div class="info">
        <h2>Información de la extensión PDO PostgreSQL</h2>';
    
    // Mostrar la versión del cliente PostgreSQL si está disponible
    if (function_exists('pg_version')) {
        $version = pg_version();
        echo '<p><strong>Versión del cliente PostgreSQL:</strong> ' . $version['client'] . '</p>';
    }
    
    // Intentar una conexión de prueba si se proporciona información de conexión
    if (isset($_POST['test_connection']) && $_POST['test_connection'] == 1) {
        $host = $_POST['host'] ?? 'localhost';
        $port = $_POST['port'] ?? '5432';
        $dbname = $_POST['dbname'] ?? 'clinica';
        $user = $_POST['user'] ?? 'postgres';
        $password = $_POST['password'] ?? '';
        
        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $conn = new PDO($dsn, $user, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo '<div class="success">
                <p>✅ Conexión de prueba exitosa a la base de datos PostgreSQL.</p>
            </div>';
        } catch (PDOException $e) {
            echo '<div class="error">
                <p>❌ Error al conectar a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</p>
            </div>';
        }
    }
    
    // Formulario para probar la conexión
    echo '<h3>Probar conexión a PostgreSQL</h3>
        <form method="post" action="check_and_enable_pgsql.php">
            <input type="hidden" name="test_connection" value="1">
            <table>
                <tr>
                    <td><label for="host">Servidor:</label></td>
                    <td><input type="text" name="host" id="host" value="localhost"></td>
                </tr>
                <tr>
                    <td><label for="port">Puerto:</label></td>
                    <td><input type="text" name="port" id="port" value="5432"></td>
                </tr>
                <tr>
                    <td><label for="dbname">Base de datos:</label></td>
                    <td><input type="text" name="dbname" id="dbname" value="clinica"></td>
                </tr>
                <tr>
                    <td><label for="user">Usuario:</label></td>
                    <td><input type="text" name="user" id="user" value="postgres"></td>
                </tr>
                <tr>
                    <td><label for="password">Contraseña:</label></td>
                    <td><input type="password" name="password" id="password" value=""></td>
                </tr>
                <tr>
                    <td></td>
                    <td><button type="submit">Probar conexión</button></td>
                </tr>
            </table>
        </form>
    </div>';
}

// Pie de página
echo '<div style="margin-top: 30px; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
    <p>Clínica - Diagnóstico de conexión a PostgreSQL - ' . date('Y-m-d H:i:s') . '</p>
</div>
</body>
</html>';
