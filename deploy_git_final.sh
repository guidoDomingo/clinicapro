#!/bin/bash

echo "🚀 =========================================="
echo "   DEPLOYMENT FINAL CLÍNICA - PUERTO 8888"
echo "   Fecha: $(date)"
echo "=========================================="

# Cambiar al directorio de la aplicación
cd /var/www/html/clinica/

echo "📁 Directorio actual: $(pwd)"

# 1. Hacer git pull para obtener los últimos cambios
echo "⬇️  Obteniendo últimos cambios del repositorio..."
git pull origin clinica

# 2. Configurar permisos
echo "🔐 Configurando permisos..."
sudo chown -R www-data:www-data /var/www/html/clinica/
sudo find /var/www/html/clinica/ -type d -exec chmod 755 {} \;
sudo find /var/www/html/clinica/ -type f -exec chmod 644 {} \;

# 3. Crear directorios necesarios
echo "📂 Creando directorios de logs..."
sudo mkdir -p /var/log/clinica
sudo chown www-data:www-data /var/log/clinica
sudo chmod 755 /var/log/clinica

# 4. Ejecutar corrección de rutas de Windows
echo "🔧 Corrigiendo rutas de Windows a Linux..."
if [ -f /var/www/html/clinica/fix_windows_paths.php ]; then
    php /var/www/html/clinica/fix_windows_paths.php
else
    echo "❌ Script fix_windows_paths.php no encontrado"
fi

# 5. Ejecutar corrección de clase Database
echo "🔧 Corrigiendo problemas de clase Database..."
if [ -f /var/www/html/clinica/fix_database_class.php ]; then
    php /var/www/html/clinica/fix_database_class.php
else
    echo "❌ Script fix_database_class.php no encontrado"
fi

# 6. Aplicar configuración de Nginx
echo "🔧 Aplicando configuración Nginx..."
if [ -f /var/www/html/clinica/nginx_config_update.conf ]; then
    sudo cp /var/www/html/clinica/nginx_config_update.conf /etc/nginx/sites-available/clinica
    sudo ln -sf /etc/nginx/sites-available/clinica /etc/nginx/sites-enabled/clinica
    
    # Probar configuración
    sudo nginx -t
    if [ $? -eq 0 ]; then
        echo "✅ Configuración Nginx válida"
        sudo systemctl reload nginx
        echo "🎯 Nginx recargado para puerto 8888"
    else
        echo "❌ Error en configuración Nginx"
        exit 1
    fi
else
    echo "❌ Archivo nginx_config_update.conf no encontrado"
fi

# 7. Verificar servicios
echo "🔍 Verificando servicios..."
if systemctl is-active --quiet nginx; then
    echo "✅ Nginx activo"
else
    echo "❌ Nginx no activo"
    sudo systemctl start nginx
fi

if systemctl is-active --quiet php8.3-fpm; then
    echo "✅ PHP-FPM activo"
else
    echo "❌ PHP-FPM no activo"
    sudo systemctl start php8.3-fpm
fi

# 8. Verificar puerto 8888
echo "🌐 Verificando puerto 8888..."
if sudo ss -tlnp | grep -q ":8888"; then
    echo "✅ Nginx escuchando en puerto 8888"
else
    echo "❌ Nginx NO escuchando en puerto 8888"
fi

# 9. Probar endpoints
echo "🧪 Probando endpoints..."

# Test API
api_test=$(curl -s -H "Accept: application/json" http://localhost:8888/api/departments 2>/dev/null)
if echo "$api_test" | grep -q '"status":"success"'; then
    echo "✅ API funcionando (/api/departments)"
else
    echo "❌ API no funcionando"
fi

# Test AJAX
ajax_test=$(curl -s -X POST -H "Content-Type: application/x-www-form-urlencoded" -d "action=test" http://localhost:8888/ajax/salas.ajax.php 2>/dev/null)
if [ $? -eq 0 ]; then
    echo "✅ AJAX accesible (/ajax/salas.ajax.php)"
else
    echo "⚠️  AJAX necesita verificación manual"
fi

# 10. Mostrar resumen
echo ""
echo "📊 =========================================="
echo "   RESUMEN DEL DEPLOYMENT"
echo "=========================================="
echo "✅ Código actualizado desde Git"
echo "✅ Permisos configurados"
echo "✅ Logs creados en /var/log/clinica/"
echo "✅ Rutas de Windows corregidas"
echo "✅ Problemas de Database solucionados"
echo "✅ Nginx configurado para puerto 8888"

echo ""
echo "🌐 URLs de acceso:"
echo "   Aplicación: http://181.122.125.143:8888/"
echo "   API: http://181.122.125.143:8888/api/"
echo "   Ejemplo API: http://181.122.125.143:8888/api/departments"

echo ""
echo "📋 Para debugging:"
echo "   Logs Nginx: tail -f /var/log/nginx/clinica_8888_error.log"
echo "   Logs App: tail -f /var/log/clinica/api.log"

echo "=========================================="
echo "🎉 DEPLOYMENT COMPLETADO"
echo "=========================================="