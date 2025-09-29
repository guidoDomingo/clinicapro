-- Script para agregar parámetros del sistema y permisos de cancelación

-- 1. Crear tabla de parámetros del sistema si no existe
CREATE TABLE IF NOT EXISTS sistema_parametros (
    parametro_id SERIAL PRIMARY KEY,
    parametro_codigo VARCHAR(50) UNIQUE NOT NULL,
    parametro_nombre VARCHAR(100) NOT NULL,
    parametro_valor VARCHAR(500) NOT NULL,
    parametro_tipo VARCHAR(20) DEFAULT 'TEXT', -- TEXT, NUMBER, BOOLEAN, JSON
    parametro_descripcion TEXT,
    parametro_categoria VARCHAR(50) DEFAULT 'GENERAL',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Insertar parámetro para tiempo límite de cancelación
INSERT INTO sistema_parametros (
    parametro_codigo, 
    parametro_nombre, 
    parametro_valor, 
    parametro_tipo, 
    parametro_descripcion, 
    parametro_categoria
) VALUES (
    'LIMITE_HORAS_CANCELACION', 
    'Límite de Horas para Cancelación', 
    '72', 
    'NUMBER', 
    'Número de horas antes de la cita que se permite cancelar reservas normalmente', 
    'RESERVAS'
) ON CONFLICT (parametro_codigo) DO UPDATE SET
    parametro_valor = EXCLUDED.parametro_valor,
    updated_at = CURRENT_TIMESTAMP;

-- 3. Crear tabla de permisos si no existe
CREATE TABLE IF NOT EXISTS sistema_permisos (
    permiso_id SERIAL PRIMARY KEY,
    permiso_codigo VARCHAR(50) UNIQUE NOT NULL,
    permiso_nombre VARCHAR(100) NOT NULL,
    permiso_descripcion TEXT,
    permiso_categoria VARCHAR(50) DEFAULT 'GENERAL',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Crear permiso especial para cancelación
INSERT INTO sistema_permisos (
    permiso_codigo,
    permiso_nombre,
    permiso_descripcion,
    permiso_categoria
) VALUES (
    'CANCELAR_RESERVAS_TARDIAS',
    'Cancelar Reservas Fuera de Tiempo',
    'Permite cancelar reservas aunque haya pasado el tiempo límite establecido en el sistema',
    'RESERVAS'
) ON CONFLICT (permiso_codigo) DO NOTHING;

INSERT INTO sistema_permisos (
    permiso_codigo,
    permiso_nombre,
    permiso_descripcion,
    permiso_categoria
) VALUES (
    'VER_RESERVAS_CANCELADAS',
    'Ver Reservas Canceladas',
    'Permite visualizar reservas canceladas en los listados del sistema',
    'RESERVAS'
) ON CONFLICT (permiso_codigo) DO NOTHING;

-- 5. Crear tabla de permisos de usuario si no existe
CREATE TABLE IF NOT EXISTS usuario_permisos (
    usuario_permiso_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    permiso_id INTEGER NOT NULL,
    otorgado_por INTEGER,
    fecha_otorgado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expira TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (permiso_id) REFERENCES sistema_permisos(permiso_id),
    UNIQUE(user_id, permiso_id)
);

-- 6. Agregar campos adicionales a servicios_reservas para mejor control
ALTER TABLE servicios_reservas 
ADD COLUMN IF NOT EXISTS fecha_cancelacion TIMESTAMP,
ADD COLUMN IF NOT EXISTS motivo_cancelacion TEXT,
ADD COLUMN IF NOT EXISTS cancelado_por INTEGER,
ADD COLUMN IF NOT EXISTS puede_cancelar_tardia BOOLEAN DEFAULT FALSE;

-- 7. Crear índices para optimización
CREATE INDEX IF NOT EXISTS idx_parametros_codigo ON sistema_parametros(parametro_codigo);
CREATE INDEX IF NOT EXISTS idx_permisos_codigo ON sistema_permisos(permiso_codigo);
CREATE INDEX IF NOT EXISTS idx_usuario_permisos_user ON usuario_permisos(user_id);
CREATE INDEX IF NOT EXISTS idx_reservas_fecha_cancelacion ON servicios_reservas(fecha_cancelacion);

-- 8. Insertar algunos parámetros adicionales útiles
INSERT INTO sistema_parametros (parametro_codigo, parametro_nombre, parametro_valor, parametro_tipo, parametro_descripcion, parametro_categoria) VALUES
('MOSTRAR_RESERVAS_CANCELADAS', 'Mostrar Reservas Canceladas', 'true', 'BOOLEAN', 'Si mostrar o no las reservas canceladas en los listados del sistema', 'RESERVAS'),
('COLOR_RESERVAS_CANCELADAS', 'Color de Reservas Canceladas', '#ffcccc', 'TEXT', 'Color de fondo para mostrar reservas canceladas en la interfaz', 'RESERVAS'),
('DIAS_MANTENER_CANCELADAS', 'Días para Mantener Reservas Canceladas Visibles', '30', 'NUMBER', 'Número de días que las reservas canceladas permanecen visibles en el sistema', 'RESERVAS')
ON CONFLICT (parametro_codigo) DO NOTHING;

-- 9. Verificar que todo se creó correctamente
SELECT 'Parámetros creados:' as tipo, COUNT(*) as cantidad FROM sistema_parametros WHERE parametro_categoria = 'RESERVAS'
UNION ALL
SELECT 'Permisos creados:' as tipo, COUNT(*) as cantidad FROM sistema_permisos WHERE permiso_categoria = 'RESERVAS';

-- Script completado exitosamente
SELECT 'Sistema de cancelación parametrizable creado exitosamente' as resultado;