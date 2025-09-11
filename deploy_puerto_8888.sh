#!/bin/bash

echo "🚀 =========================================="
echo "   DEPLOYMENT CLÍNICA - OPTIMIZADO PUERTO 8888"
echo "   Fecha: $(date)"
echo "=========================================="

# Función para log de errores
log_error() {
    echo "❌ ERROR: $1" >&2
}

# Función para log de éxito
log_success() {
    echo "✅ ÉXITO: $1"
}

# Verificar si estamos en el directorio correcto
if [ ! -f /var/www/html/clinica/deploy_final.sh ]; then
    log_error "Script debe ejecutarse desde /var/www/html/clinica/"
    exit 1
fi

cd /var/www/html/clinica/

echo "📁 Directorio actual: $(pwd)"

# 1. Crear directorios necesarios
echo "📂 Creando estructura de directorios..."
sudo mkdir -p /var/log/clinica
sudo mkdir -p /var/log/nginx
sudo chown -R www-data:www-data /var/log/clinica
sudo chmod -R 755 /var/log/clinica

# 2. Configurar permisos de archivos
echo "🔐 Configurando permisos..."
sudo chown -R www-data:www-data /var/www/html/clinica
sudo find /var/www/html/clinica -type d -exec chmod 755 {} \;
sudo find /var/www/html/clinica -type f -exec chmod 644 {} \;
sudo chmod 755 /var/www/html/clinica/api/
sudo chmod 644 /var/www/html/clinica/api/*.php

# 3. Aplicar configuración Nginx optimizada para puerto 8888
echo "🔧 Aplicando configuración Nginx para puerto 8888..."
if [ -f /var/www/html/clinica/nginx_config_update.conf ]; then
    sudo cp /var/www/html/clinica/nginx_config_update.conf /etc/nginx/sites-available/clinica
    sudo ln -sf /etc/nginx/sites-available/clinica /etc/nginx/sites-enabled/
    
    # Deshabilitar sitio default si existe
    sudo rm -f /etc/nginx/sites-enabled/default
    
    echo "⚡ Probando configuración Nginx..."
    sudo nginx -t
    
    if [ $? -eq 0 ]; then
        log_success "Configuración Nginx válida. Aplicando..."
        sudo systemctl reload nginx
        log_success "Nginx configurado para puertos 80 y 8888"
    else
        log_error "Error en configuración Nginx"
        exit 1
    fi
else
    log_error "Archivo nginx_config_update.conf no encontrado"
    exit 1
fi

# 4. Verificar servicios
echo "🔍 Verificando servicios..."

# Verificar Nginx
if systemctl is-active --quiet nginx; then
    log_success "Nginx está activo"
else
    log_error "Nginx no está activo"
    sudo systemctl start nginx
fi

# Verificar PHP-FPM
if systemctl is-active --quiet php8.3-fpm; then
    log_success "PHP-FPM está activo"
else
    log_error "PHP-FPM no está activo"
    sudo systemctl start php8.3-fpm
fi

# 5. Verificar configuración de la base de datos
echo "🗄️  Verificando conectividad de base de datos..."
if [ -f /var/www/html/clinica/config.php ]; then
    log_success "Archivo config.php encontrado"
else
    log_error "Archivo config.php no encontrado"
fi

# 6. Probar endpoints de API
echo "🧪 Probando endpoints de API en puerto 8888..."

# Test básico con curl
test_endpoint() {
    local url=$1
    local name=$2
    
    echo "🔍 Probando: $name"
    echo "   URL: $url"
    
    response=$(curl -s -w "%{http_code}" -H "Accept: application/json" "$url" 2>/dev/null)
    http_code="${response: -3}"
    content="${response%???}"
    
    if [ "$http_code" = "200" ]; then
        echo "✅ Status: $http_code"
        # Verificar si es JSON válido
        if echo "$content" | jq . >/dev/null 2>&1; then
            echo "✅ Respuesta JSON válida"
            return 0
        else
            echo "⚠️  Status 200 pero respuesta no es JSON válido"
            echo "   Contenido: ${content:0:100}..."
            return 1
        fi
    else
        echo "❌ Status: $http_code"
        echo "   Contenido: ${content:0:100}..."
        return 1
    fi
}

# Verificar que curl y jq estén disponibles
if ! command -v curl &> /dev/null; then
    echo "📦 Instalando curl..."
    sudo apt-get update && sudo apt-get install -y curl
fi

if ! command -v jq &> /dev/null; then
    echo "📦 Instalando jq..."
    sudo apt-get install -y jq
fi

# Probar endpoints principales
echo "🌐 Probando API en puerto 8888..."
base_url="http://localhost:8888"

declare -A endpoints=(
    ["/api/departments"]="Departamentos"
    ["/api/cities"]="Ciudades"
    ["/api/especialidades"]="Especialidades"
    ["/api/persons"]="Personas"
)

success_count=0
total_count=${#endpoints[@]}

for endpoint in "${!endpoints[@]}"; do
    name="${endpoints[$endpoint]}"
    url="$base_url$endpoint"
    
    if test_endpoint "$url" "$name"; then
        ((success_count++))
    fi
    echo ""
done

# 7. Mostrar resumen
echo "📊 =========================================="
echo "   RESUMEN DEL DEPLOYMENT"
echo "=========================================="
echo "✅ Directorios creados: /var/log/clinica, /var/log/nginx"
echo "✅ Permisos configurados: www-data"
echo "✅ Nginx configurado para puertos 80 y 8888"
echo "📊 API Tests: $success_count/$total_count exitosos"

if [ $success_count -eq $total_count ]; then
    echo ""
    echo "🎉 ¡DEPLOYMENT COMPLETADO EXITOSAMENTE!"
    echo "   La API está funcionando correctamente en puerto 8888"
    echo ""
    echo "🌐 URLs de acceso:"
    echo "   Aplicación: http://181.122.125.143:8888"
    echo "   API: http://181.122.125.143:8888/api/"
    echo ""
    echo "📋 Para probar manualmente:"
    echo "   curl -H 'Accept: application/json' http://181.122.125.143:8888/api/departments"
    echo ""
else
    echo ""
    echo "⚠️  DEPLOYMENT PARCIALMENTE EXITOSO"
    echo "   Revisar logs de errores:"
    echo "   - tail -f /var/log/nginx/clinica_8888_error.log"
    echo "   - tail -f /var/log/clinica/api.log"
fi

echo "=========================================="