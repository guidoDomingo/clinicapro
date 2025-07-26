-- Script SQL para crear la tabla de consulta_informe_imagen
-- Ejecutar este script en PostgreSQL para habilitar el nuevo formulario

-- Crear la tabla para almacenar datos específicos de informe+imagen
CREATE TABLE IF NOT EXISTS consulta_informe_imagen (
    id_consulta_informe_imagen SERIAL PRIMARY KEY,
    id_consulta INTEGER NOT NULL REFERENCES consultas(id_consulta) ON DELETE CASCADE,
    
    -- Información del equipo médico utilizado
    equipo_medico VARCHAR(100),
    
    -- Descripciones específicas por ojo
    descripcion_od TEXT,
    descripcion_oi TEXT,
    
    -- Información para compartir
    emails_compartir TEXT, -- Lista de emails separados por comas
    compartir_activo BOOLEAN DEFAULT FALSE,
    
    -- Campos de auditoría
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para mejorar rendimiento
    CONSTRAINT unique_consulta_informe_imagen UNIQUE (id_consulta)
);

-- Crear índices para optimizar consultas
CREATE INDEX IF NOT EXISTS idx_consulta_informe_imagen_consulta 
ON consulta_informe_imagen(id_consulta);

CREATE INDEX IF NOT EXISTS idx_consulta_informe_imagen_equipo 
ON consulta_informe_imagen(equipo_medico);

CREATE INDEX IF NOT EXISTS idx_consulta_informe_imagen_compartir 
ON consulta_informe_imagen(compartir_activo);

-- Agregar comentarios a la tabla y columnas
COMMENT ON TABLE consulta_informe_imagen IS 'Tabla para almacenar datos específicos de consultas de tipo informe+imagen';
COMMENT ON COLUMN consulta_informe_imagen.id_consulta IS 'Referencia a la consulta principal';
COMMENT ON COLUMN consulta_informe_imagen.equipo_medico IS 'Equipo médico utilizado para el estudio';
COMMENT ON COLUMN consulta_informe_imagen.descripcion_od IS 'Descripción específica para ojo derecho';
COMMENT ON COLUMN consulta_informe_imagen.descripcion_oi IS 'Descripción específica para ojo izquierdo';
COMMENT ON COLUMN consulta_informe_imagen.emails_compartir IS 'Lista de emails para compartir el informe';
COMMENT ON COLUMN consulta_informe_imagen.compartir_activo IS 'Indica si se ha configurado el compartir por email';

-- Función para actualizar automáticamente fecha_actualizacion
CREATE OR REPLACE FUNCTION update_consulta_informe_imagen_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger para actualizar fecha_actualizacion automáticamente
DROP TRIGGER IF EXISTS trigger_update_consulta_informe_imagen_timestamp ON consulta_informe_imagen;
CREATE TRIGGER trigger_update_consulta_informe_imagen_timestamp
    BEFORE UPDATE ON consulta_informe_imagen
    FOR EACH ROW
    EXECUTE FUNCTION update_consulta_informe_imagen_timestamp();

-- Verificar que la tabla se creó correctamente
SELECT 
    table_name,
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns 
WHERE table_name = 'consulta_informe_imagen'
ORDER BY ordinal_position;

-- Mensaje de confirmación
SELECT 'Tabla consulta_informe_imagen creada exitosamente con todos sus índices y triggers.' as resultado;
