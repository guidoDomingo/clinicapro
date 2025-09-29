-- ================================
-- SISTEMA PARAMETRIZABLE DE CANCELACIÓN DE RESERVAS
-- (Usando el sistema de roles existente)
-- ================================

-- 1. Tabla de parámetros del sistema (mantiene configuraciones parametrizables)
CREATE TABLE IF NOT EXISTS sistema_parametros (
    parametro_id SERIAL PRIMARY KEY,
    parametro_codigo VARCHAR(100) UNIQUE NOT NULL,
    parametro_nombre VARCHAR(200) NOT NULL,
    parametro_valor TEXT NOT NULL,
    parametro_descripcion TEXT,
    parametro_tipo VARCHAR(20) DEFAULT 'STRING', -- STRING, NUMBER, BOOLEAN, JSON
    parametro_categoria VARCHAR(50) DEFAULT 'GENERAL', -- RESERVAS, SISTEMA, UI, etc.
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Crear índices para optimizar consultas
CREATE INDEX IF NOT EXISTS idx_sistema_parametros_codigo ON sistema_parametros(parametro_codigo);
CREATE INDEX IF NOT EXISTS idx_sistema_parametros_categoria ON sistema_parametros(parametro_categoria);
CREATE INDEX IF NOT EXISTS idx_sistema_parametros_activo ON sistema_parametros(is_active);

-- 3. Insertar parámetros básicos para el sistema de cancelación
INSERT INTO sistema_parametros (parametro_codigo, parametro_nombre, parametro_valor, parametro_tipo, parametro_descripcion, parametro_categoria) VALUES
('LIMITE_HORAS_CANCELACION', 'Límite de Horas para Cancelación', '72', 'NUMBER', 'Número de horas antes de la cita que se permite cancelar reservas normalmente', 'RESERVAS'),
('MOSTRAR_RESERVAS_CANCELADAS', 'Mostrar Reservas Canceladas', 'true', 'BOOLEAN', 'Si se deben mostrar las reservas canceladas en las listas', 'RESERVAS'),
('COLOR_RESERVAS_CANCELADAS', 'Color para Reservas Canceladas', '#ffcccc', 'STRING', 'Color de fondo para mostrar reservas canceladas en la interfaz', 'RESERVAS'),
('DIAS_MANTENER_CANCELADAS', 'Días para Mantener Reservas Canceladas', '30', 'NUMBER', 'Número de días que se mantienen visibles las reservas canceladas', 'RESERVAS'),
('PERMITIR_CANCELACION_PASADAS', 'Permitir Cancelar Reservas Pasadas', 'false', 'BOOLEAN', 'Si se permite cancelar reservas que ya pasaron (solo con permisos especiales)', 'RESERVAS'),
('MENSAJE_CANCELACION_EXITOSA', 'Mensaje de Cancelación Exitosa', 'Su reserva ha sido cancelada exitosamente. El horario queda disponible para otros pacientes.', 'STRING', 'Mensaje que se muestra cuando se cancela una reserva exitosamente', 'RESERVAS'),
('MENSAJE_CANCELACION_FUERA_LIMITE', 'Mensaje Cancelación Fuera de Límite', 'No es posible cancelar esta reserva porque está fuera del tiempo límite permitido.', 'STRING', 'Mensaje cuando se intenta cancelar fuera del tiempo límite', 'RESERVAS')
ON CONFLICT (parametro_codigo) DO UPDATE SET
    parametro_valor = EXCLUDED.parametro_valor,
    parametro_descripcion = EXCLUDED.parametro_descripcion,
    updated_at = CURRENT_TIMESTAMP;

-- 4. Agregar permisos específicos para cancelación al sistema existente
INSERT INTO sys_permissions (perm_name, perm_description) VALUES
('cancelar_reservas_tardias', 'Permitir cancelar reservas fuera del tiempo límite normal'),
('ver_reservas_canceladas', 'Ver reservas que han sido canceladas'),
('cancelar_reservas_otros_usuarios', 'Cancelar reservas creadas por otros usuarios'),
('administrar_cancelaciones', 'Administrar el sistema completo de cancelaciones')
ON CONFLICT (perm_name) DO UPDATE SET
    perm_description = EXCLUDED.perm_description;

-- 5. Modificar tabla de reservas para incluir campos de cancelación
ALTER TABLE servicios_reservas 
ADD COLUMN IF NOT EXISTS fecha_cancelacion TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS motivo_cancelacion TEXT NULL,
ADD COLUMN IF NOT EXISTS cancelado_por INTEGER NULL,
ADD COLUMN IF NOT EXISTS puede_cancelar_tardia BOOLEAN DEFAULT false;

-- 6. Agregar foreign key para el usuario que canceló
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.table_constraints 
        WHERE constraint_name = 'fk_reservas_cancelado_por'
    ) THEN
        ALTER TABLE servicios_reservas 
        ADD CONSTRAINT fk_reservas_cancelado_por 
        FOREIGN KEY (cancelado_por) REFERENCES sys_users(user_id) ON DELETE SET NULL;
    END IF;
END $$;

-- 7. Crear índices para optimizar consultas de reservas
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_activo ON servicios_reservas(activo);
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_fecha_cancelacion ON servicios_reservas(fecha_cancelacion);
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_cancelado_por ON servicios_reservas(cancelado_por);
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_fecha_reserva_activo ON servicios_reservas(fecha_reserva, activo);

-- 8. Crear función para verificar permisos de cancelación
CREATE OR REPLACE FUNCTION verificar_permiso_cancelacion(p_user_id INTEGER, p_permiso VARCHAR(100))
RETURNS BOOLEAN AS $$
DECLARE
    tiene_permiso BOOLEAN := false;
BEGIN
    -- Verificar si el usuario tiene el permiso directamente o a través de roles
    SELECT COUNT(*) > 0 INTO tiene_permiso
    FROM (
        -- Permisos a través de roles
        SELECT 1
        FROM sys_user_roles sur
        INNER JOIN sys_role_permissions srp ON sur.role_id = srp.role_id
        INNER JOIN sys_permissions sp ON srp.perm_id = sp.perm_id
        WHERE sur.user_id = p_user_id AND sp.perm_name = p_permiso
    ) permisos;
    
    RETURN tiene_permiso;
END;
$$ LANGUAGE plpgsql;

-- 9. Crear función para obtener tiempo límite de cancelación
CREATE OR REPLACE FUNCTION obtener_limite_horas_cancelacion()
RETURNS INTEGER AS $$
DECLARE
    limite_horas INTEGER := 72; -- Default
BEGIN
    SELECT CAST(parametro_valor AS INTEGER) INTO limite_horas
    FROM sistema_parametros
    WHERE parametro_codigo = 'LIMITE_HORAS_CANCELACION'
    AND is_active = true
    LIMIT 1;
    
    RETURN COALESCE(limite_horas, 72);
END;
$$ LANGUAGE plpgsql;

-- 10. Crear función para verificar si una reserva puede ser cancelada
CREATE OR REPLACE FUNCTION puede_cancelar_reserva(p_reserva_id INTEGER, p_user_id INTEGER)
RETURNS TABLE(
    puede_cancelar BOOLEAN,
    motivo TEXT,
    horas_restantes NUMERIC,
    limite_horas INTEGER,
    permiso_especial BOOLEAN
) AS $$
DECLARE
    reserva_info RECORD;
    limite_horas_config INTEGER;
    horas_hasta_cita NUMERIC;
    tiene_permiso_especial BOOLEAN := false;
BEGIN
    -- Obtener información de la reserva
    SELECT fecha_reserva, hora_inicio, activo, reserva_estado
    INTO reserva_info
    FROM servicios_reservas
    WHERE reserva_id = p_reserva_id;
    
    -- Si la reserva no existe
    IF NOT FOUND THEN
        RETURN QUERY SELECT false, 'La reserva no existe', 0::NUMERIC, 72, false;
        RETURN;
    END IF;
    
    -- Si la reserva ya está cancelada
    IF NOT reserva_info.activo THEN
        RETURN QUERY SELECT false, 'La reserva ya está cancelada', 0::NUMERIC, 72, false;
        RETURN;
    END IF;
    
    -- Obtener límite de horas configurado
    limite_horas_config := obtener_limite_horas_cancelacion();
    
    -- Calcular horas restantes hasta la cita
    SELECT EXTRACT(EPOCH FROM (reserva_info.fecha_reserva + reserva_info.hora_inicio::time - CURRENT_TIMESTAMP))/3600
    INTO horas_hasta_cita;
    
    -- Verificar permiso especial
    tiene_permiso_especial := verificar_permiso_cancelacion(p_user_id, 'cancelar_reservas_tardias');
    
    -- Si está dentro del tiempo límite
    IF horas_hasta_cita >= limite_horas_config THEN
        RETURN QUERY SELECT true, 'Dentro del tiempo límite', horas_hasta_cita, limite_horas_config, false;
        RETURN;
    END IF;
    
    -- Si está fuera del tiempo límite pero tiene permiso especial
    IF tiene_permiso_especial THEN
        RETURN QUERY SELECT true, 'Permiso especial para cancelación tardía', horas_hasta_cita, limite_horas_config, true;
        RETURN;
    END IF;
    
    -- No puede cancelar
    RETURN QUERY SELECT false, 
        'Fuera del tiempo límite para cancelación (' || limite_horas_config || ' horas). Quedan ' || ROUND(horas_hasta_cita, 2) || ' horas.',
        horas_hasta_cita, limite_horas_config, false;
END;
$$ LANGUAGE plpgsql;

-- 11. Crear vista para reservas con información de cancelación
CREATE OR REPLACE VIEW vista_reservas_con_cancelacion AS
SELECT 
    sr.*,
    CASE 
        WHEN sr.activo = false THEN 'CANCELADA'
        ELSE sr.reserva_estado
    END as estado_efectivo,
    rp_cancelado.first_name || ' ' || rp_cancelado.last_name as nombre_quien_cancelo,
    sp.parametro_valor::INTEGER as limite_horas_sistema,
    EXTRACT(EPOCH FROM (sr.fecha_reserva + sr.hora_inicio::time - CURRENT_TIMESTAMP))/3600 as horas_restantes
FROM servicios_reservas sr
LEFT JOIN sys_users su_cancelado ON sr.cancelado_por = su_cancelado.user_id
LEFT JOIN person_system_user psu_cancelado ON su_cancelado.user_id = psu_cancelado.system_user_id
LEFT JOIN rh_person rp_cancelado ON psu_cancelado.person_id = rp_cancelado.person_id
CROSS JOIN (
    SELECT parametro_valor 
    FROM sistema_parametros 
    WHERE parametro_codigo = 'LIMITE_HORAS_CANCELACION' 
    AND is_active = true 
    LIMIT 1
) sp;

-- 12. Comentario de documentación
COMMENT ON TABLE sistema_parametros IS 'Parámetros configurables del sistema para personalizar comportamientos';
COMMENT ON FUNCTION verificar_permiso_cancelacion IS 'Verifica si un usuario tiene un permiso específico a través del sistema de roles';
COMMENT ON FUNCTION obtener_limite_horas_cancelacion IS 'Obtiene el límite de horas configurado para cancelación de reservas';
COMMENT ON FUNCTION puede_cancelar_reserva IS 'Verifica si una reserva puede ser cancelada por un usuario específico';
COMMENT ON VIEW vista_reservas_con_cancelacion IS 'Vista que incluye información completa de reservas con datos de cancelación';

-- 13. Mensaje de confirmación
DO $$
BEGIN
    RAISE NOTICE 'Sistema de cancelación parametrizable instalado correctamente';
    RAISE NOTICE 'Funciones creadas: verificar_permiso_cancelacion, obtener_limite_horas_cancelacion, puede_cancelar_reserva';
    RAISE NOTICE 'Vista creada: vista_reservas_con_cancelacion';
    RAISE NOTICE 'Parámetros configurables agregados a sistema_parametros';
    RAISE NOTICE 'Permisos agregados al sistema existente: cancelar_reservas_tardias, ver_reservas_canceladas, cancelar_reservas_otros_usuarios, administrar_cancelaciones';
END $$;