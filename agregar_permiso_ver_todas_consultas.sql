-- Script para agregar el permiso 'ver_todas_consultas'
-- Este permiso permite a los usuarios ver todas las consultas de todos los doctores

-- Insertar el nuevo permiso si no existe
INSERT INTO sys_permissions (perm_name, perm_description) 
SELECT 'ver_todas_consultas', 'Permite ver todas las consultas de todos los doctores'
WHERE NOT EXISTS (
    SELECT 1 FROM sys_permissions WHERE perm_name = 'ver_todas_consultas'
);

-- Verificar que el permiso se creó correctamente
SELECT * FROM sys_permissions WHERE perm_name = 'ver_todas_consultas';