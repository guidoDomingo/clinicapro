-- Crear tabla para tipos de formularios
CREATE TABLE IF NOT EXISTS tipo_formularios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    creado_por INTEGER,
    modificado_por INTEGER
);

-- Insertar tipos de formularios existentes
INSERT INTO tipo_formularios (nombre, descripcion, codigo) VALUES 
('General', 'Formulario general para consultas básicas', 'general'),
('Anteojos', 'Formulario específico para consultas de anteojos', 'anteojos'),
('Estudios Médicos', 'Formulario para estudios médicos y equipos', 'estudios'),
('Informe + Imagen', 'Formulario para informes con imágenes OD/OI', 'informe_imagen'),
('Dermatología', 'Formulario específico para consultas dermatológicas', 'dermatologia'),
('Pediatría', 'Formulario específico para consultas pediátricas', 'pediatria'),
('Ginecología', 'Formulario específico para consultas ginecológicas', 'ginecologia');

-- Crear índices para optimizar consultas
CREATE INDEX IF NOT EXISTS idx_tipo_formularios_activo ON tipo_formularios(activo);
CREATE INDEX IF NOT EXISTS idx_tipo_formularios_codigo ON tipo_formularios(codigo);

COMMENT ON TABLE tipo_formularios IS 'Tabla para gestionar los tipos de formularios disponibles en el sistema';
COMMENT ON COLUMN tipo_formularios.nombre IS 'Nombre descriptivo del tipo de formulario';
COMMENT ON COLUMN tipo_formularios.codigo IS 'Código único para identificar el tipo de formulario';
COMMENT ON COLUMN tipo_formularios.activo IS 'Indica si el tipo de formulario está activo';
