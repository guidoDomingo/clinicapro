# Documentación del Módulo de Anteojos en el Sistema Clínico

## Descripción General

El módulo de prescripción de anteojos es un componente especializado para la gestión de consultas oftalmológicas que permite almacenar y gestionar recetas de anteojos. Este módulo extiende el sistema de consultas médicas básicas añadiendo campos específicos para la prescripción de lentes correctivos.

## Estructura de la Base de Datos

### Tabla `consulta_anteojos`

Esta tabla almacena los datos específicos de las prescripciones de anteojos y se relaciona con la tabla principal de consultas médicas.

```sql
CREATE TABLE consulta_anteojos (
    id_consulta_anteojos SERIAL PRIMARY KEY,
    id_consulta INTEGER NOT NULL,
    esfera_od VARCHAR(10),
    cilindro_od VARCHAR(10),
    eje_od VARCHAR(10),
    dnp_od VARCHAR(10),
    add_od VARCHAR(10),
    nota_od TEXT,
    esfera_oi VARCHAR(10),
    cilindro_oi VARCHAR(10),
    eje_oi VARCHAR(10),
    dnp_oi VARCHAR(10),
    add_oi VARCHAR(10),
    nota_oi TEXT,
    dist_interpupilar VARCHAR(10),
    altura_od VARCHAR(10),
    altura_oi VARCHAR(10),
    FOREIGN KEY (id_consulta) REFERENCES consultas(id_consulta) ON DELETE CASCADE
);
```

## Componentes del Sistema

### 1. Formulario de Anteojos (`frmConsultaAnteojos.php`)

El formulario especializado para prescripciones de anteojos incluye:
- Campos para datos del paciente
- Sección específica para el ojo derecho (OD)
- Sección específica para el ojo izquierdo (OI)
- Campos para información adicional (distancia interpupilar)

### 2. Procesamiento del Formulario

El sistema implementa múltiples métodos para procesar los datos del formulario, con fallbacks para asegurar la compatibilidad con diferentes configuraciones de servidor:

#### Archivo Principal: `guardar-consulta-anteojos.php`
- Detecta automáticamente las capacidades del servidor
- Implementa una cascada de métodos para asegurar el funcionamiento incluso sin cURL

#### Métodos Alternativos:
- `guardar-consulta-anteojos-directo.php`: Utiliza el modelo directamente
- `guardar-consulta-anteojos-simple.php`: Método simple con dependencias mínimas

### 3. Operaciones CRUD

#### Crear/Actualizar Prescripción:
El sistema determina automáticamente si debe crear un nuevo registro o actualizar uno existente.

#### Obtener Datos:
`obtener-datos-anteojos.php` recupera y devuelve los datos de una prescripción existente.

## Funcionamiento del Proceso de Guardado

1. El usuario completa el formulario con los datos de la consulta y la prescripción
2. Al enviar el formulario, JavaScript maneja la petición AJAX a `guardar-consulta-anteojos.php`
3. El sistema:
   - Guarda primero la consulta base
   - Obtiene el ID de la consulta guardada
   - Guarda los datos específicos de anteojos
   - Devuelve el resultado al usuario

### Manejo de Errores y Fallbacks

El sistema está diseñado para ser resistente a fallos comunes:

1. Si cURL no está disponible, intenta usar `file_get_contents` con contexto stream
2. Si eso falla, intenta incluir directamente el archivo de guardar consultas
3. Si todo lo anterior falla, utiliza los archivos alternativos

## Integración con el Sistema Principal

- El formulario de anteojos se integra con el sistema de consultas médicas
- Los datos de anteojos se asocian siempre a una consulta médica principal
- Las consultas de anteojos aparecen en el historial médico del paciente

## Campos del Formulario

### Datos del Paciente
- Documento de identidad
- Número de ficha
- Búsqueda de paciente
- ID de persona (campo oculto)

### Datos Generales de la Consulta
- Motivo común (selección)
- Preformato (selección)
- Descripción del motivo
- Descripción de la consulta
- Receta general

### Datos de Ojo Derecho (OD)
- Esfera (ESF): Valores de +8.00 a -8.00
- Cilindro (CIL): Valores de +3.00 a -5.00
- Eje: Valor numérico
- DNP: Distancia naso-pupilar
- Adición: Valores de +0.00 a +3.50
- Altura: Valor numérico
- Nota: Texto libre

### Datos de Ojo Izquierdo (OI)
- Mismos campos que para OD

### Información Adicional
- Distancia Interpupilar: Valor numérico
- Próxima consulta: Fecha
- WhatsApp: Número para enviar la receta
- Email: Correo para enviar la receta

## Configuración y Requisitos

### Requisitos del Servidor
- PHP 7.2 o superior
- PostgreSQL 9.6 o superior

### Extensiones PHP Recomendadas
- cURL: Para funcionamiento óptimo
- PDO: Obligatorio
- pgsql: Obligatorio

### Cuando cURL no está disponible

El sistema funcionará incluso si cURL no está disponible en el servidor, pero se recomienda habilitar esta extensión para un rendimiento óptimo.

Para habilitar cURL en PHP:

#### En Windows:
1. Editar php.ini
2. Descomentar la línea `extension=curl`
3. Reiniciar el servidor web

#### En Linux:
```bash
sudo apt-get install php-curl
sudo systemctl restart apache2
```

## Depuración y Logs

El sistema incluye un mecanismo detallado de logs para facilitar la depuración:

- Logs básicos: `../logs/debug_consultas.log`
- Logs detallados: `../logs/debug_consultas_detallado.log`

## Personalización y Extensión

Para personalizar o extender el módulo:

1. Agregar campos adicionales a la tabla `consulta_anteojos`
2. Actualizar la clase `TableConsultaAnteojos` para incluir los nuevos campos
3. Modificar el formulario HTML para incluir los nuevos campos
4. Actualizar las funciones de procesamiento en los archivos PHP
