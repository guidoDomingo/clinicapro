-- Script SQL para la tabla de especialidades
-- Ejecutar este script si la tabla no existe en la base de datos

CREATE TABLE IF NOT EXISTS especialidades (
    especialidad_id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar algunas especialidades comunes si la tabla está vacía
INSERT INTO especialidades (nombre, descripcion, activo)
SELECT 'Cardiología', 'Especialidad médica que se ocupa de las enfermedades del corazón y del aparato circulatorio', TRUE
WHERE NOT EXISTS (SELECT 1 FROM especialidades LIMIT 1);

INSERT INTO especialidades (nombre, descripcion, activo)
SELECT 'Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades', TRUE
WHERE NOT EXISTS (SELECT 1 FROM especialidades LIMIT 1);

INSERT INTO especialidades (nombre, descripcion, activo)
SELECT 'Dermatología', 'Especialidad médica encargada del estudio de la piel, su estructura, función y enfermedades', TRUE
WHERE NOT EXISTS (SELECT 1 FROM especialidades LIMIT 1);

-- Agregar el permiso para gestionar especialidades
INSERT INTO permisos (nombre, descripcion)
SELECT 'administrar_especialidades', 'Permiso para gestionar las especialidades médicas'
WHERE NOT EXISTS (SELECT 1 FROM permisos WHERE nombre = 'administrar_especialidades');

-- Asignar el permiso al rol de administrador (asumiendo que el rol_id=1 es el administrador)
INSERT INTO roles_permisos (rol_id, permiso_id)
SELECT 1, (SELECT permiso_id FROM permisos WHERE nombre = 'administrar_especialidades')
WHERE NOT EXISTS (
    SELECT 1 FROM roles_permisos 
    WHERE rol_id = 1 
    AND permiso_id = (SELECT permiso_id FROM permisos WHERE nombre = 'administrar_especialidades')
);
