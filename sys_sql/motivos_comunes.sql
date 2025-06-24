-- Script SQL para la tabla de motivos_comunes
-- Nota: Esta tabla ya existe en la base de datos, pero este script se proporciona en caso de ser necesario recrearla

CREATE TABLE IF NOT EXISTS motivos_comunes (
    id_motivo SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INTEGER
);

-- Agregar el permiso para gestionar motivos comunes
INSERT INTO permisos (nombre, descripcion)
SELECT 'administrar_motivos', 'Permiso para gestionar los motivos comunes'
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE nombre = 'administrar_motivos');

-- Asignar el permiso al rol de administrador (asumiendo que el rol_id=1 es el administrador)
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 1, (SELECT permiso_id FROM permisos WHERE nombre = 'administrar_motivos')
WHERE NOT EXISTS (
    SELECT 1 FROM roles_permisos 
    WHERE rol_id = 1 
    AND permiso_id = (SELECT permiso_id FROM permisos WHERE nombre = 'administrar_motivos')
);

-- Insertar algunos motivos comunes de ejemplo si la tabla está vacía
INSERT INTO motivos_comunes (nombre, descripcion, activo)
SELECT 'Control rutinario', 'Visita de control programada regularmente', TRUE
WHERE NOT EXISTS (SELECT 1 FROM motivos_comunes WHERE nombre = 'Control rutinario');

INSERT INTO motivos_comunes (nombre, descripcion, activo)
SELECT 'Dolor agudo', 'Paciente con dolor agudo que requiere atención inmediata', TRUE
WHERE NOT EXISTS (SELECT 1 FROM motivos_comunes WHERE nombre = 'Dolor agudo');

INSERT INTO motivos_comunes (nombre, descripcion, activo)
SELECT 'Seguimiento de tratamiento', 'Visita de seguimiento para evaluar progreso de un tratamiento', TRUE
WHERE NOT EXISTS (SELECT 1 FROM motivos_comunes WHERE nombre = 'Seguimiento de tratamiento');

INSERT INTO motivos_comunes (nombre, descripcion, activo)
SELECT 'Exámenes de rutina', 'Evaluación de exámenes médicos rutinarios', TRUE
WHERE NOT EXISTS (SELECT 1 FROM motivos_comunes WHERE nombre = 'Exámenes de rutina');

INSERT INTO motivos_comunes (nombre, descripcion, activo)
SELECT 'Malestar general', 'Paciente con malestar general sin causa aparente', TRUE
WHERE NOT EXISTS (SELECT 1 FROM motivos_comunes WHERE nombre = 'Malestar general');
