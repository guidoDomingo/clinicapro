-- Script SQL para configurar el permiso de administrar_turnos

-- 1. Insertar el permiso si no existe
INSERT INTO permisos (nombre, descripcion)
SELECT 'administrar_turnos', 'Administrar gestión de turnos'
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE nombre = 'administrar_turnos');

-- 2. Asignar el permiso al rol de administrador (generalmente ID 1)
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 1, (SELECT permiso_id FROM permisos WHERE nombre = 'administrar_turnos')
WHERE NOT EXISTS (
    SELECT 1 FROM roles_permisos 
    WHERE rol_id = 1 
    AND permiso_id = (SELECT permiso_id FROM permisos WHERE nombre = 'administrar_turnos')
);

-- 3. Verificar que se insertó correctamente
SELECT 'Permiso creado:' as tipo, p.permiso_id, p.nombre, p.descripcion 
FROM permisos p 
WHERE p.nombre = 'administrar_turnos';

-- 4. Verificar la asignación al rol
SELECT 'Asignación al rol:' as tipo, rp.rol_id, r.nombre as rol_nombre, p.nombre as permiso_nombre
FROM roles_permisos rp
JOIN roles r ON r.rol_id = rp.rol_id
JOIN permisos p ON p.permiso_id = rp.permiso_id
WHERE p.nombre = 'administrar_turnos';

-- 5. Mostrar todos los usuarios que tendrían acceso (a través de roles)
SELECT 'Usuarios con acceso:' as tipo, u.usuario_id, u.nombre, u.usuario, r.nombre as rol
FROM usuarios u
JOIN usuarios_roles ur ON ur.usuario_id = u.usuario_id
JOIN roles r ON r.rol_id = ur.rol_id
JOIN roles_permisos rp ON rp.rol_id = r.rol_id
JOIN permisos p ON p.permiso_id = rp.permiso_id
WHERE p.nombre = 'administrar_turnos';
