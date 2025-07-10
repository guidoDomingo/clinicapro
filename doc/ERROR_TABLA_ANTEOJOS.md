# Solución al Error en Módulo de Anteojos

## Problema detectado

Al guardar datos en el formulario de anteojos, se produce el siguiente error:

```
error_anteojos: Error de base de datos: SQLSTATE[42703]: Undefined column: 7 ERROR: column "nota_od" of relation "consulta_anteojos" does not exist LINE 3: ...ta, esfera_od, cilindro_od, eje_od, dnp_od, add_od, nota_od, ^ (ID consulta: 50)
```

Este error ocurre porque la estructura de la tabla `consulta_anteojos` en la base de datos no coincide con la estructura que el código PHP espera. La tabla carece de algunas columnas necesarias, entre ellas `nota_od`.

## Solución implementada

Se han creado dos herramientas para solucionar este problema:

1. **Script SQL para actualizar la tabla**: `sys_sql/actualizar_tabla_anteojos.sql`
   Este script SQL verifica y añade todas las columnas faltantes en la tabla.

2. **Herramienta web para diagnóstico y corrección**: `actualizar_tabla_anteojos.php`
   Esta herramienta PHP permite:
   - Verificar la existencia de la tabla
   - Crear la tabla si no existe
   - Verificar que todas las columnas necesarias existan
   - Añadir las columnas faltantes
   - Verificar la integridad referencial

## Columnas necesarias para la tabla `consulta_anteojos`

La tabla `consulta_anteojos` debe tener la siguiente estructura:

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

## Cómo aplicar la solución

### Opción 1: Usando la herramienta web

1. Abra la URL: `http://localhost/clinica/actualizar_tabla_anteojos.php`
2. La herramienta detectará y corregirá automáticamente la estructura de la tabla
3. Verifique que todas las columnas se hayan añadido correctamente

### Opción 2: Ejecutando el script SQL manualmente

1. Acceda a su gestor de base de datos PostgreSQL
2. Abra el archivo `sys_sql/actualizar_tabla_anteojos.sql` 
3. Ejecute el script en su base de datos

## Verificación del módulo

Después de aplicar la solución, puede verificar que todo funcione correctamente:

1. Abra la URL: `http://localhost/clinica/verificar_anteojos.php`
2. La herramienta verificará que todos los componentes del módulo de anteojos estén correctamente configurados
3. Si todo está bien, puede usar el formulario de anteojos normalmente

## Posibles causas del problema

Este problema pudo haber surgido por alguna de estas razones:

1. La tabla `consulta_anteojos` se creó manualmente sin todas las columnas necesarias
2. La tabla se creó con un script antiguo que no incluía todas las columnas
3. Se actualizó el código PHP añadiendo nuevos campos sin actualizar la estructura de la base de datos

Para evitar problemas similares en el futuro, se recomienda usar las herramientas de migración y sincronizar siempre los cambios en el código con los cambios en la estructura de la base de datos.
