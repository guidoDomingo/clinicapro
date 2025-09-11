#!/bin/bash

echo "=== DESPLIEGUE FINAL DE API ==="
echo "Fecha: $(date)"
echo ""

echo "🔧 Aplicando configuración Nginx..."
if [ -f /var/www/html/clinica/nginx_config_update.conf ]; then
    sudo cp /var/www/html/clinica/nginx_config_update.conf /etc/nginx/sites-available/clinica
    sudo ln -sf /etc/nginx/sites-available/clinica /etc/nginx/sites-enabled/
    
    # Crear directorio de logs si no existe
    sudo mkdir -p /var/log/nginx
    sudo chmod 755 /var/log/nginx
    
    # Probar configuración de Nginx
    echo "⚡ Probando configuración Nginx..."
    sudo nginx -t
    
    if [ $? -eq 0 ]; then
        echo "✅ Configuración Nginx válida. Reiniciando..."
        sudo systemctl reload nginx
        echo "🎯 Nginx configurado para puertos 80 y 8888"
    else
        echo "❌ Error en configuración Nginx"
        return 1
    fi
else
    echo "❌ Archivo nginx_config_update.conf no encontrado"
    return 1
fi

echo ""
echo "2. Configurando tablas de base de datos..."
if [ -f "/var/www/html/clinica/setup_ubicaciones.php" ]; then
    echo "📝 Ejecutando configuración de ubicaciones..."
    php /var/www/html/clinica/setup_ubicaciones.php
else
    echo "⚠️ setup_ubicaciones.php no encontrado"
fi

echo ""
echo "3. Verificando permisos..."
sudo chown -R www-data:www-data /var/www/html/clinica/api/
sudo chmod 644 /var/www/html/clinica/api/*.php
sudo chmod 644 /var/www/html/clinica/api/*/*.php
echo "✅ Permisos configurados"

echo ""
echo "4. Probando APIs..."

# Función para probar API
test_api() {
    local url=$1
    local name=$2
    
    echo "🧪 Probando $name: $url"
    
    response=$(curl -s -w "\n%{http_code}" "$url" 2>/dev/null)
    http_code=$(echo "$response" | tail -n1)
    content=$(echo "$response" | head -n -1)
    
    if [ "$http_code" = "200" ]; then
        # Verificar si es JSON válido
        if echo "$content" | jq . >/dev/null 2>&1; then
            status=$(echo "$content" | jq -r '.status' 2>/dev/null)
            if [ "$status" = "success" ]; then
                echo "   ✅ OK - JSON válido con status: success"
            else
                echo "   ⚠️  JSON válido pero status: $status"
            fi
        else
            echo "   ❌ Respuesta no es JSON válido"
        fi
    else
        echo "   ❌ HTTP $http_code"
        if [ ${#content} -lt 200 ]; then
            echo "   📄 Respuesta: $content"
        else
            echo "   📄 Respuesta (truncada): ${content:0:100}..."
        fi
    fi
}

# Probar ambos puertos
test_api "http://181.122.125.143/api/departments" "Departamentos (puerto 80)"
test_api "http://181.122.125.143:8888/api/departments" "Departamentos (puerto 8888)"
test_api "http://181.122.125.143/api/especialidades" "Especialidades (puerto 80)"
test_api "http://181.122.125.143:8888/api/especialidades" "Especialidades (puerto 8888)"

echo ""
echo "5. Verificando logs de errores..."
if [ -f "/var/log/nginx/clinica_error.log" ]; then
    error_count=$(wc -l < /var/log/nginx/clinica_error.log)
    echo "📋 Log de errores tiene $error_count líneas"
    
    # Mostrar últimos errores si existen
    if [ $error_count -gt 0 ]; then
        echo "🔍 Últimos 3 errores:"
        tail -3 /var/log/nginx/clinica_error.log
    else
        echo "✅ No hay errores en el log"
    fi
else
    echo "⚠️ Log de errores no encontrado"
fi

echo ""
echo "6. Estado final de servicios..."
systemctl is-active --quiet nginx && echo "✅ Nginx: Activo" || echo "❌ Nginx: Inactivo"
systemctl is-active --quiet php8.3-fpm && echo "✅ PHP-FPM: Activo" || echo "❌ PHP-FPM: Inactivo"

echo ""
echo "=== DESPLIEGUE COMPLETADO ==="
echo ""
echo "📋 URLs para probar en el navegador:"
echo "   • http://181.122.125.143/update_api_simple.php"
echo "   • http://181.122.125.143:8888/rhpersonas (aplicación principal)"
echo "   • http://181.122.125.143/api/departments"
echo "   • http://181.122.125.143:8888/api/departments"
echo ""
echo "🐛 Si hay errores, revisar:"
echo "   • tail -f /var/log/nginx/clinica_error.log"
echo "   • tail -f /var/log/clinica/application.log"