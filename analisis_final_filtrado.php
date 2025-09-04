<?php
session_start();

// Configurar para mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Análisis Final - Problema de Filtrado</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #f5f5f5; padding: 10px; border-left: 4px solid #007bff; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>";

echo "<h1>🔍 ANÁLISIS FINAL - PROBLEMA DE FILTRADO</h1>";

// PASO 1: Información de sesión
echo "<div class='section'>";
echo "<h2>📋 1. INFORMACIÓN DE SESIÓN ACTUAL</h2>";

if (empty($_SESSION)) {
    echo "<p class='error'>❌ No hay sesión activa. Por favor, inicie sesión primero.</p>";
    echo "<p><a href='index.php?ruta=login'>👉 Ir al Login</a></p>";
    echo "</body></html>";
    exit;
}

echo "<table>";
echo "<tr><th>Clave</th><th>Valor</th></tr>";
foreach ($_SESSION as $key => $value) {
    if (is_array($value)) {
        $value = json_encode($value);
    }
    echo "<tr><td>$key</td><td>$value</td></tr>";
}
echo "</table>";

$doctor_id_sesion = $_SESSION['doctor_id'] ?? null;
$usuario_logueado = $_SESSION['usuario'] ?? 'DESCONOCIDO';

echo "<p><strong>🏥 Usuario logueado:</strong> $usuario_logueado</p>";
echo "<p><strong>👨‍⚕️ Doctor ID en sesión:</strong> " . ($doctor_id_sesion ?? 'NO DEFINIDO') . "</p>";
echo "</div>";

// PASO 2: Conexión y verificación
echo "<div class='section'>";
echo "<h2>🔌 2. CONEXIÓN Y DATOS BÁSICOS</h2>";

try {
    $pdo = new PDO("pgsql:host=localhost;dbname=clinica;port=5432", "postgres", "admin");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='ok'>✅ Conexión exitosa</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    echo "</body></html>";
    exit;
}

// Verificar datos del doctor actual
if ($doctor_id_sesion) {
    $sql = "SELECT rd.doctor_id, rp.first_name, rp.last_name 
            FROM rh_doctors rd 
            INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
            WHERE rd.doctor_id = :doctor_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['doctor_id' => $doctor_id_sesion]);
    $doctor_actual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor_actual) {
        echo "<p class='ok'>✅ Doctor encontrado en BD: {$doctor_actual['first_name']} {$doctor_actual['last_name']} (ID: {$doctor_actual['doctor_id']})</p>";
    } else {
        echo "<p class='error'>❌ No se encontró doctor con ID: $doctor_id_sesion</p>";
    }
} else {
    echo "<p class='error'>❌ No hay doctor_id en la sesión</p>";
}
echo "</div>";

// PASO 3: Análisis de reservas
echo "<div class='section'>";
echo "<h2>📅 3. ANÁLISIS DE RESERVAS PARA HOY</h2>";

$fecha_hoy = date('Y-m-d');
echo "<p><strong>Fecha de análisis:</strong> $fecha_hoy</p>";

// Obtener TODAS las reservas de hoy
$sql = "SELECT 
    sr.reserva_id,
    sr.doctor_id,
    sr.hora_inicio,
    dr.first_name || ' ' || dr.last_name as doctor_nombre,
    pr.first_name || ' ' || pr.last_name as paciente_nombre,
    sr.reserva_estado
FROM servicios_reservas sr
INNER JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id
INNER JOIN rh_person dr ON rd.person_id = dr.person_id
INNER JOIN rh_person pr ON sr.paciente_id = pr.person_id
WHERE sr.fecha_reserva = :fecha
ORDER BY sr.hora_inicio";

$stmt = $pdo->prepare($sql);
$stmt->execute(['fecha' => $fecha_hoy]);
$todas_reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>🔍 TODAS LAS RESERVAS DE HOY (" . count($todas_reservas) . " total)</h3>";

if (count($todas_reservas) > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Doctor ID</th><th>Doctor</th><th>Paciente</th><th>Hora</th><th>Estado</th><th>¿Es mío?</th></tr>";
    foreach ($todas_reservas as $res) {
        $es_mio = ($res['doctor_id'] == $doctor_id_sesion) ? 'SÍ' : 'NO';
        $clase_fila = ($res['doctor_id'] == $doctor_id_sesion) ? ' style="background-color: #d4edda;"' : '';
        
        echo "<tr$clase_fila>";
        echo "<td>{$res['reserva_id']}</td>";
        echo "<td>{$res['doctor_id']}</td>";
        echo "<td>{$res['doctor_nombre']}</td>";
        echo "<td>{$res['paciente_nombre']}</td>";
        echo "<td>{$res['hora_inicio']}</td>";
        echo "<td>{$res['reserva_estado']}</td>";
        echo "<td><strong>$es_mio</strong></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    $mis_reservas_count = 0;
    foreach ($todas_reservas as $res) {
        if ($res['doctor_id'] == $doctor_id_sesion) {
            $mis_reservas_count++;
        }
    }
    
    echo "<p><strong>📊 Resumen:</strong></p>";
    echo "<ul>";
    echo "<li>Total de reservas hoy: " . count($todas_reservas) . "</li>";
    echo "<li>Mis reservas (Doctor ID $doctor_id_sesion): <strong>$mis_reservas_count</strong></li>";
    echo "<li>Reservas de otros doctores: " . (count($todas_reservas) - $mis_reservas_count) . "</li>";
    echo "</ul>";
    
} else {
    echo "<p class='warning'>⚠️ No hay reservas para hoy</p>";
}
echo "</div>";

// PASO 4: Simulación del AJAX
echo "<div class='section'>";
echo "<h2>⚙️ 4. SIMULACIÓN DEL LLAMADO AJAX</h2>";
echo "<p>Simulando exactamente lo que hace el módulo de citas...</p>";

// Simular los parámetros que envía el JavaScript
$_POST['action'] = 'buscarReservas';
$_POST['fecha'] = $fecha_hoy;
$_POST['modulo'] = 'citas'; // ⭐ CLAVE: Parámetro que detecta el filtrado

// Capturar la salida del archivo AJAX
ob_start();
include 'ajax/servicios.ajax.php';
$ajax_response = ob_get_clean();

echo "<h3>📤 Parámetros enviados:</h3>";
echo "<pre>";
echo "action: buscarReservas\n";
echo "fecha: $fecha_hoy\n";
echo "modulo: citas\n";
echo "</pre>";

echo "<h3>📥 Respuesta del AJAX:</h3>";
echo "<pre>";
echo htmlspecialchars($ajax_response);
echo "</pre>";

// Intentar decodificar la respuesta JSON
$response_data = json_decode($ajax_response, true);
if ($response_data) {
    echo "<h3>📊 Análisis de la respuesta:</h3>";
    if (isset($response_data['data']) && is_array($response_data['data'])) {
        $reservas_ajax = $response_data['data'];
        echo "<p><strong>Reservas devueltas por AJAX:</strong> " . count($reservas_ajax) . "</p>";
        
        if (count($reservas_ajax) > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Doctor ID</th><th>Doctor</th><th>Paciente</th><th>Hora</th></tr>";
            foreach ($reservas_ajax as $res) {
                echo "<tr>";
                echo "<td>{$res['reserva_id']}</td>";
                echo "<td>{$res['doctor_id']}</td>";
                echo "<td>{$res['doctor']}</td>";
                echo "<td>{$res['paciente']}</td>";
                echo "<td>{$res['hora_inicio']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar si el filtrado funcionó
            $todas_del_doctor = true;
            foreach ($reservas_ajax as $res) {
                if ($res['doctor_id'] != $doctor_id_sesion) {
                    $todas_del_doctor = false;
                    break;
                }
            }
            
            if ($todas_del_doctor) {
                echo "<p class='ok'>✅ FILTRADO CORRECTO: Todas las reservas pertenecen al doctor logueado</p>";
            } else {
                echo "<p class='error'>❌ FILTRADO INCORRECTO: Hay reservas de otros doctores</p>";
                
                // Mostrar reservas que no son del doctor actual
                echo "<h4>🚨 Reservas que NO deberían mostrarse:</h4>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Doctor ID</th><th>Doctor</th><th>Debería ser</th></tr>";
                foreach ($reservas_ajax as $res) {
                    if ($res['doctor_id'] != $doctor_id_sesion) {
                        echo "<tr style='background-color: #f8d7da;'>";
                        echo "<td>{$res['reserva_id']}</td>";
                        echo "<td>{$res['doctor_id']}</td>";
                        echo "<td>{$res['doctor']}</td>";
                        echo "<td>Doctor ID: $doctor_id_sesion</td>";
                        echo "</tr>";
                    }
                }
                echo "</table>";
            }
        }
    }
} else {
    echo "<p class='error'>❌ No se pudo decodificar la respuesta JSON</p>";
}

echo "</div>";

// PASO 5: Diagnóstico final
echo "<div class='section'>";
echo "<h2>🎯 5. DIAGNÓSTICO FINAL</h2>";

if ($response_data && isset($response_data['data'])) {
    $reservas_filtradas = $response_data['data'];
    $problema_encontrado = false;
    
    foreach ($reservas_filtradas as $res) {
        if ($res['doctor_id'] != $doctor_id_sesion) {
            $problema_encontrado = true;
            break;
        }
    }
    
    if ($problema_encontrado) {
        echo "<p class='error'>❌ PROBLEMA CONFIRMADO: El filtrado no está funcionando correctamente</p>";
        
        echo "<h3>🔧 Posibles causas:</h3>";
        echo "<ol>";
        echo "<li>El archivo ajax/servicios.ajax.php no está detectando el parámetro 'modulo=citas'</li>";
        echo "<li>La lógica de filtrado por doctor_id no se está aplicando</li>";
        echo "<li>Hay un problema en la consulta SQL de filtrado</li>";
        echo "</ol>";
        
        echo "<h3>✅ Próximos pasos:</h3>";
        echo "<ol>";
        echo "<li>Revisar el archivo ajax/servicios.ajax.php líneas 570-580 aproximadamente</li>";
        echo "<li>Verificar que la variable \$esCitas se esté evaluando correctamente</li>";
        echo "<li>Asegurar que el filtrado por doctor_id se aplique cuando es módulo citas</li>";
        echo "</ol>";
        
    } else {
        echo "<p class='ok'>✅ SISTEMA FUNCIONANDO CORRECTAMENTE</p>";
        echo "<p>El filtrado está aplicándose correctamente. El problema puede ser:</p>";
        echo "<ul>";
        echo "<li>Cache del navegador</li>";
        echo "<li>El módulo no está enviando el parámetro 'modulo: citas'</li>";
        echo "<li>Problema temporal resuelto</li>";
        echo "</ul>";
    }
} else {
    echo "<p class='error'>❌ No se pudo obtener respuesta válida del AJAX</p>";
}

echo "<hr>";
echo "<p><strong>⏰ Análisis realizado:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>🔄 Para probar en vivo:</strong> <a href='index.php?ruta=citas' target='_blank'>Ir al módulo de citas</a></p>";
echo "</div>";

echo "</body></html>";

// Limpiar variables POST para no interferir
unset($_POST['action'], $_POST['fecha'], $_POST['modulo']);
?>