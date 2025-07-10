# Solución al Problema con cURL en el Módulo de Anteojos

## Problema Original

El sistema presentaba el siguiente error al intentar guardar una consulta de anteojos:

```
Fatal error: Uncaught Error: Call to undefined function curl_init()
```

Este error ocurre porque la extensión cURL de PHP no está habilitada en el servidor.

## Solución Implementada

Se han creado múltiples mecanismos de fallback para manejar la falta de la extensión cURL:

### 1. Archivo Principal (guardar-consulta-anteojos.php)

Este archivo ahora implementa una cascada de métodos para guardar la consulta:

- **Método 1**: Intenta usar cURL si está disponible
- **Método 2**: Si cURL no está disponible, intenta usar `file_get_contents` con un contexto de stream
- **Método 3**: Si lo anterior falla, intenta incluir directamente el archivo guardar-consulta.ajax.php
- **Método 4**: Si todos los métodos anteriores fallan, utiliza archivos alternativos creados específicamente para casos donde cURL no está disponible

### 2. Archivo Alternativo para Uso Directo del Modelo (guardar-consulta-anteojos-directo.php)

Este archivo utiliza directamente la clase `ModelConsulta` para guardar la consulta base sin depender de cURL.

### 3. Archivo Alternativo Simple (guardar-consulta-anteojos-simple.php)

Este es el enfoque más simple que utiliza SQL directo para insertar los datos, con dependencias mínimas.

## Cómo Habilitar cURL en PHP (Solución Permanente)

Para una solución permanente, se recomienda habilitar la extensión cURL en PHP:

### En Windows con Laragon:

1. Abra el archivo php.ini en su instalación de Laragon:
   - Puede encontrarlo en `C:\laragon\bin\php\php-[version]\php.ini`
   - O simplemente haga clic derecho en el icono de Laragon → PHP → php.ini

2. Busque la línea `;extension=curl` (con punto y coma al inicio)

3. Quite el punto y coma para que quede como `extension=curl`

4. Guarde el archivo y reinicie los servicios de Laragon

### En Linux:

```bash
sudo apt-get install php-curl
sudo systemctl restart apache2  # o el servicio web que esté usando
```

## Verificación

Para verificar si la extensión cURL está habilitada, puede:

1. Acceder al archivo `phpinfo.php` en la raíz del proyecto
2. Buscar la sección "curl" en la página

Si no encuentra una sección de curl, significa que la extensión no está habilitada.

## Nota Importante

El sistema ahora es resistente a la falta de cURL y utilizará automáticamente los métodos alternativos cuando sea necesario. Sin embargo, para un rendimiento óptimo, se recomienda habilitar la extensión cURL.
