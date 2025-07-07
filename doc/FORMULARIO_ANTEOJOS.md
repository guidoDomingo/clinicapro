# Módulo de Formulario para Anteojos

Este documento explica el funcionamiento del módulo de preformatos dinámicos para el formulario de anteojos en la plataforma clínica.

## Índice
1. [Estructura General](#estructura-general)
2. [Funcionamiento](#funcionamiento)
3. [Componentes](#componentes)
4. [Carga Dinámica de Preformatos](#carga-dinámica-de-preformatos)
5. [Integración con Base de Datos](#integración-con-base-de-datos)
6. [Personalización y Extensión](#personalización-y-extensión)

## Estructura General

El sistema de formularios dinámicos permite:
- Cambiar entre diferentes tipos de formularios (general, anteojos, etc.)
- Cargar preformatos específicos para cada tipo de formulario
- Adaptar los controles de interfaz según el tipo de formulario activo
- Llenar datos dinámicamente en los campos del formulario

## Funcionamiento

1. **Carga Inicial**: 
   - Al cargar la página de consultas, se detecta el parámetro `form_type` en la URL
   - Se cargan los componentes específicos según el tipo de formulario

2. **Para Formularios de Anteojos**:
   - Se inicializan los selectores especiales para valores de esferas, cilindros y adiciones
   - Se cargan preformatos específicos para anteojos usando `tipo_formulario=anteojos`
   - Se presenta la interfaz especializada con campos OD (ojo derecho) y OI (ojo izquierdo)

3. **Flujo de Trabajo**:
   - El usuario selecciona los valores de los selectores para generar la receta
   - Puede aplicar preformatos predefinidos para textos comunes
   - Los datos se guardan en el mismo formato que una consulta regular

## Componentes

El sistema está compuesto por los siguientes archivos principales:

- **`view/modules/consultas.php`**: Contenedor principal que detecta el tipo de formulario
- **`view/inc/consulta_forms/frmConsultaAnteojos.php`**: Plantilla HTML específica para anteojos
- **`view/js/cargar_datos.js`**: Script principal que maneja la carga dinámica de datos
- **`ajax/preformatos.ajax.php`**: Controlador AJAX para obtener datos de preformatos
- **`controller/preformatos.controller.php`**: Lógica de acceso a datos de preformatos

## Carga Dinámica de Preformatos

El sistema utiliza dos funciones principales para la carga de datos:

### `cargarMotivos(tipoFormulario, selectorId)`
Esta función carga los motivos comunes específicos para cada tipo de formulario.

### `cargarPreformatos(tipoPreformato, tipoFormulario, selectorId)`
Esta función carga preformatos específicos según:
- **tipoPreformato**: 'consulta', 'receta', etc.
- **tipoFormulario**: 'general', 'anteojos', etc.

### Ejemplo:
```javascript
// Cargar preformatos de receta para formulario de anteojos
cargarPreformatos('receta', 'anteojos', 'formatoreceta');
```

## Integración con Base de Datos

Los preformatos se almacenan en la tabla `preformatos` con los siguientes campos relevantes:

- `id_preformato`: Identificador único
- `nombre`: Nombre visible del preformato
- `contenido`: Texto del preformato
- `tipo`: Tipo de preformato ('consulta', 'receta', etc.)
- `tipo_formulario`: Tipo de formulario ('general', 'anteojos', etc.)
- `activo`: Estado del preformato

### Consulta para obtener preformatos específicos:

```sql
SELECT * FROM preformatos 
WHERE tipo = 'receta' 
  AND tipo_formulario = 'anteojos' 
  AND activo = true
```

## Personalización y Extensión

Para agregar nuevos preformatos de anteojos:

1. Acceder al panel de administración de preformatos
2. Crear un nuevo preformato con:
   - **Tipo**: 'receta'
   - **Tipo Formulario**: 'anteojos'
   - **Contenido**: El texto del preformato

Para modificar los valores disponibles en los selectores de esferas, cilindros o adiciones:

1. Editar la función `inicializarSelectoresAnteojos()` en `cargar_datos.js`
2. Ajustar los rangos e incrementos según sea necesario

## Solución de Problemas

Si no se cargan los preformatos específicos para anteojos:

1. Verificar que se está accediendo con el parámetro `form_type=anteojos` en la URL
2. Comprobar que existen preformatos en la base de datos con `tipo_formulario='anteojos'`
3. Revisar la consola del navegador para identificar posibles errores
4. Si no hay preformatos en la base de datos, se creará automáticamente una plantilla predeterminada

Si los selectores de valores ópticos no se cargan correctamente:

1. Verificar que los IDs de los elementos HTML coincidan con los esperados (`od_esf`, `od_cil`, etc.)
2. Comprobar que la biblioteca Select2 está correctamente inicializada
