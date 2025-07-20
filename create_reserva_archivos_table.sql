-- Tabla para almacenar archivos adjuntos de reservas
CREATE TABLE IF NOT EXISTS reserva_archivos (
    archivo_id SERIAL PRIMARY KEY,
    reserva_id INTEGER NOT NULL,
    codigo_seguimiento VARCHAR(50) NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tipo_archivo VARCHAR(10) NOT NULL,
    tamaño_archivo INTEGER NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subido_por INTEGER, -- ID del usuario que subió el archivo
    estado VARCHAR(20) DEFAULT 'ACTIVO',
    
    -- Relaciones
    CONSTRAINT fk_reserva_archivos_reserva 
        FOREIGN KEY (reserva_id) REFERENCES reservas(reserva_id) 
        ON DELETE CASCADE,
    
    -- Índices para búsqueda rápida
    INDEX idx_reserva_archivos_reserva_id (reserva_id),
    INDEX idx_reserva_archivos_codigo (codigo_seguimiento),
    INDEX idx_reserva_archivos_estado (estado)
);

-- Comentarios para documentación
COMMENT ON TABLE reserva_archivos IS 'Almacena información de archivos adjuntos a las reservas';
COMMENT ON COLUMN reserva_archivos.reserva_id IS 'ID de la reserva a la que pertenece el archivo';
COMMENT ON COLUMN reserva_archivos.codigo_seguimiento IS 'Código de seguimiento de la reserva para facilitar búsquedas';
COMMENT ON COLUMN reserva_archivos.nombre_original IS 'Nombre original del archivo subido por el usuario';
COMMENT ON COLUMN reserva_archivos.nombre_archivo IS 'Nombre del archivo en el servidor (con timestamp para evitar colisiones)';
COMMENT ON COLUMN reserva_archivos.ruta_archivo IS 'Ruta completa del archivo en el servidor';
COMMENT ON COLUMN reserva_archivos.tipo_archivo IS 'Extensión del archivo (pdf, jpg, png, etc.)';
COMMENT ON COLUMN reserva_archivos.tamaño_archivo IS 'Tamaño del archivo en bytes';
COMMENT ON COLUMN reserva_archivos.subido_por IS 'ID del usuario que subió el archivo';
COMMENT ON COLUMN reserva_archivos.estado IS 'Estado del archivo: ACTIVO, ELIMINADO, etc.';
