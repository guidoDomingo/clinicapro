<?php
// Datos de prueba
$archivo_test = 'uploads/consultas/68b2564a82aa7_1756517962.docx';

if (file_exists($archivo_test)) {
    echo "✅ Archivo existe: $archivo_test<br>";
    echo "Tamaño: " . filesize($archivo_test) . " bytes<br>";
    
    echo "Tipo MIME: application/vnd.openxmlformats-officedocument.wordprocessingml.document<br>";
    $mime_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    echo "Checksum: " . md5_file($archivo_test) . "<br>";
    
    // Probar inserción directa en base de datos
    try {
        $config = [
            'host' => 'localhost',
            'dbname' => 'clinica',
            'username' => 'postgres',
            'password' => 'admin'
        ];
        
        $dsn = "pgsql:host={$config['host']};dbname={$config['dbname']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar si ya existe el archivo en la BD
        $checksum = md5_file($archivo_test);
        $sql = "SELECT id_archivo FROM archivos WHERE checksum = :checksum";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['checksum' => $checksum]);
        $archivo_existente = $stmt->fetchColumn();
        
        if ($archivo_existente) {
            echo "<br>✅ Archivo ya existe en BD con ID: $archivo_existente<br>";
            
            // Verificar si está vinculado a la consulta 179
            $sql = "SELECT id_archivo_consulta FROM archivos_consulta 
                    WHERE id_archivo = :id_archivo AND id_consulta = 179";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id_archivo' => $archivo_existente]);
            $vinculo = $stmt->fetchColumn();
            
            if (!$vinculo) {
                echo "🔗 Vinculando con consulta 179...<br>";
                $sql = "INSERT INTO archivos_consulta (id_consulta, id_archivo, fecha_adjunto)
                        VALUES (179, :id_archivo, NOW())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['id_archivo' => $archivo_existente]);
                
                echo "✅ Archivo vinculado correctamente<br>";
            } else {
                echo "✅ Archivo ya estaba vinculado con consulta 179<br>";
            }
        } else {
            echo "<br>🔄 Insertando archivo nuevo en BD...<br>";
            
            $pdo->beginTransaction();
            
            // Insertar en tabla archivos
            $sql = "INSERT INTO archivos (nombre_archivo, ruta_archivo, id_usuario, id_persona, origen, tamano_archivo, tipo_archivo, checksum, fecha_creacion)
                    VALUES (:nombre, :ruta, :id_usuario, :id_persona, 'consulta', :tamano, :tipo, :checksum, NOW())
                    RETURNING id_archivo";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nombre' => 'predica_diez_virgenes.docx',
                'ruta' => $archivo_test,
                'id_usuario' => 1,
                'id_persona' => 53,
                'tamano' => filesize($archivo_test),
                'tipo' => $mime_type,
                'checksum' => $checksum
            ]);
            
            $id_archivo = $stmt->fetchColumn();
            echo "✅ Archivo insertado con ID: $id_archivo<br>";
            
            // Vincular con consulta
            $sql = "INSERT INTO archivos_consulta (id_consulta, id_archivo, fecha_adjunto)
                    VALUES (179, :id_archivo, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id_archivo' => $id_archivo]);
            
            echo "✅ Archivo vinculado con consulta 179<br>";
            
            $pdo->commit();
            echo "✅ Transacción completada exitosamente<br>";
        }
        
        // Verificar resultado final
        echo "<br>🔍 Verificación final:<br>";
        $sql = "SELECT a.id_archivo, a.nombre_archivo, a.tamano_archivo, ac.fecha_adjunto
                FROM archivos a
                INNER JOIN archivos_consulta ac ON a.id_archivo = ac.id_archivo
                WHERE ac.id_consulta = 179
                ORDER BY ac.fecha_adjunto DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Total archivos en consulta 179: " . count($archivos) . "<br>";
        foreach ($archivos as $archivo) {
            echo "- ID: {$archivo['id_archivo']}, Nombre: {$archivo['nombre_archivo']}, Tamaño: {$archivo['tamano_archivo']}<br>";
        }
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "❌ Archivo no existe: $archivo_test<br>";
}
?>