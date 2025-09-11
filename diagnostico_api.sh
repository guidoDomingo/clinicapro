#!/bin/bash

echo "=== DIAGNÓSTICO DE API - SISTEMA CLÍNICA ==="
echo "Fecha: $(date)"
echo ""

echo "1. Verificando estructura de carpetas de API..."
if [ -d "/var/www/html/clinica/api" ]; then
    echo "✅ Carpeta /var/www/html/clinica/api existe"
    ls -la /var/www/html/clinica/api/
else
    echo "❌ Carpeta /var/www/html/clinica/api NO EXISTE"
fi

echo ""
echo "2. Verificando archivos críticos de API..."
critical_files=(
    "/var/www/html/clinica/api/index.php"
    "/var/www/html/clinica/api/.htaccess"
    "/var/www/html/clinica/api/routes/api.php"
    "/var/www/html/clinica/api/controllers/LocationController.php"
)

for file in "${critical_files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file existe"
        echo "   Permisos: $(stat -c '%a' "$file")"
    else
        echo "❌ $file NO EXISTE"
    fi
done

echo ""
echo "3. Probando acceso directo a API..."
echo "Probando: http://181.122.125.143/api/departments"
response=$(curl -s -w "\n%{http_code}" http://181.122.125.143/api/departments 2>/dev/null)
http_code=$(echo "$response" | tail -n1)
content=$(echo "$response" | head -n -1)

echo "Código HTTP: $http_code"
if [ "$http_code" = "200" ]; then
    echo "✅ API responde correctamente"
    # Verificar si es JSON válido
    if echo "$content" | jq . >/dev/null 2>&1; then
        echo "✅ Respuesta es JSON válido"
    else
        echo "❌ Respuesta NO es JSON válido"
        echo "Contenido (primeras 200 caracteres):"
        echo "$content" | head -c 200
    fi
else
    echo "❌ API no responde correctamente"
    echo "Contenido (primeras 200 caracteres):"
    echo "$content" | head -c 200
fi

echo ""
echo "4. Verificando configuración Nginx..."
nginx -t

echo ""
echo "5. Verificando logs de error de API..."
if [ -f "/var/log/nginx/clinica_error.log" ]; then
    echo "Últimas 5 líneas del log de errores:"
    tail -5 /var/log/nginx/clinica_error.log
else
    echo "No se encontró log de errores específico"
fi

echo ""
echo "6. Verificando proceso PHP-FPM..."
systemctl status php8.3-fpm --no-pager -l | head -10

echo ""
echo "7. Probando conexión a base de datos desde API..."
php -r "
require_once '/var/www/html/clinica/api/core/Database.php';
try {
    \$db = Api\Core\Database::getConnection();
    echo '✅ Conexión a BD desde API exitosa\n';
    
    // Probar consulta de departamentos
    \$stmt = \$db->query('SELECT COUNT(*) FROM departments');
    \$count = \$stmt->fetchColumn();
    echo '✅ Departamentos en BD: ' . \$count . '\n';
} catch (Exception \$e) {
    echo '❌ Error de BD desde API: ' . \$e->getMessage() . '\n';
}
" 2>/dev/null || echo "❌ No se pudo ejecutar prueba de BD"

echo ""
echo "8. Verificando permisos de archivos de API..."
chown -R www-data:www-data /var/www/html/clinica/api/
chmod -R 644 /var/www/html/clinica/api/*.php
chmod -R 644 /var/www/html/clinica/api/*/*.php
echo "✅ Permisos ajustados"

echo ""
echo "=== FIN DIAGNÓSTICO DE API ==="