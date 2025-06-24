# Módulo de Especialidades Médicas

Este módulo permite gestionar las especialidades médicas dentro del sistema de la clínica.

## Características

- Listado de especialidades con paginación y búsqueda
- Creación de nuevas especialidades
- Edición de especialidades existentes
- Eliminación lógica (desactivación) de especialidades
- Visualización de estado activo/inactivo

## Requisitos previos

Asegúrese de que la tabla `especialidades` exista en su base de datos. Si no existe, puede crear la tabla utilizando el script SQL proporcionado:

```bash
psql -U su_usuario -d su_base_de_datos -f sys_sql/especialidades.sql
```

## Permisos

El módulo utiliza el permiso `administrar_especialidades`. Asegúrese de que este permiso esté asignado a los roles correspondientes.

## Acceso al módulo

El módulo es accesible desde el menú lateral en Referenciales -> Gestión de Especialidades.

## Estructura de la tabla

```sql
CREATE TABLE especialidades (
    especialidad_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Archivos del módulo

- `view/modules/especialidades.php`: Vista principal del módulo
- `view/js/especialidades.js`: Funcionalidades JavaScript para el módulo
- `ajax/especialidades.ajax.php`: Endpoints AJAX para operaciones CRUD
- `controller/EspecialidadesController.php`: Controlador para la lógica de negocio
- `model/EspecialidadesModel.php`: Modelo para operaciones de base de datos
- `sys_sql/especialidades.sql`: Script SQL para crear la tabla y permisos

## Fecha de implementación

Módulo implementado el 24 de junio de 2025.
