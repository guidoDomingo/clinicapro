# Módulo de Motivos Comunes

Este módulo permite gestionar los motivos comunes de consulta dentro del sistema de la clínica.

## Características

- Listado de motivos comunes con paginación y búsqueda
- Creación de nuevos motivos comunes
- Edición de motivos existentes
- Eliminación lógica (desactivación) de motivos
- Visualización de estado activo/inactivo

## Requisitos previos

Asegúrese de que la tabla `motivos_comunes` exista en su base de datos. Esta tabla generalmente ya está creada en el sistema, pero si no existe, puede crear la tabla utilizando el script SQL proporcionado:

```bash
psql -U su_usuario -d su_base_de_datos -f sys_sql/motivos_comunes.sql
```

## Permisos

El módulo utiliza el permiso `administrar_motivos`. Asegúrese de que este permiso esté asignado a los roles correspondientes.

## Acceso al módulo

El módulo es accesible desde el menú lateral en Referenciales -> Motivos Comunes.

## Estructura de la tabla

```sql
CREATE TABLE motivos_comunes (
    id_motivo SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INTEGER
);
```

## Archivos del módulo

- `view/modules/motivos.php`: Vista principal del módulo
- `view/js/motivos.js`: Funcionalidades JavaScript para el módulo
- `ajax/motivos.ajax.php`: Endpoints AJAX para operaciones CRUD
- `controller/MotivosController.php`: Controlador para la lógica de negocio
- `model/MotivosModel.php`: Modelo para operaciones de base de datos
- `sys_sql/motivos_comunes.sql`: Script SQL para crear los permisos necesarios
- `verificar_permisos_motivos.php`: Script para verificar los permisos del módulo
- `test_motivos.php`: Script para probar la funcionalidad del módulo

## Fecha de implementación

Módulo implementado el 24 de junio de 2025.
