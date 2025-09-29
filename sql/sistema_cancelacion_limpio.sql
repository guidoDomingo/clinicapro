-- ================================
-- LIMPIAR Y CONFIGURAR SISTEMA DE CANCELACIÓN
-- (Usando únicamente las tablas existentes del sistema de roles)
-- ================================

-- 1. Eliminar tablas innecesarias si existen
DROP TABLE IF EXISTS sistema_permisos CASCADE;
DROP TABLE IF EXISTS usuario_permisos CASCADE;

-- 2. Mantener sistema_parametros solo para configuraciones (sin permisos)
-- Esta tabla es útil para parámetros configurables del sistema

-- 3. Agregar permisos específicos para cancelación al sistema existente
INSERT INTO sys_permissions (perm_name, perm_description) VALUES
('cancelar_reservas_tardias', 'Permitir cancelar reservas fuera del tiempo límite normal'),
('ver_reservas_canceladas', 'Ver reservas que han sido canceladas'),
('cancelar_reservas_otros_usuarios', 'Cancelar reservas creadas por otros usuarios'),
('administrar_cancelaciones', 'Administrar el sistema completo de cancelaciones')
ON CONFLICT (perm_name) DO UPDATE SET
    perm_description = EXCLUDED.perm_description;

-- 4. Modificar tabla de reservas para incluir campos de cancelación (si no existen)
ALTER TABLE servicios_reservas 
ADD COLUMN IF NOT EXISTS fecha_cancelacion TIMESTAMP NULL,
ADD COLUMN IF NOT EXISTS motivo_cancelacion TEXT NULL,
ADD COLUMN IF NOT EXISTS cancelado_por INTEGER NULL,
ADD COLUMN IF NOT EXISTS puede_cancelar_tardia BOOLEAN DEFAULT false;

-- 5. Agregar foreign key para el usuario que canceló (si no existe)
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

-- 6. Crear índices para optimizar consultas de reservas
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_activo ON servicios_reservas(activo);
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_fecha_cancelacion ON servicios_reservas(fecha_cancelacion);
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_cancelado_por ON servicios_reservas(cancelado_por);
CREATE INDEX IF NOT EXISTS idx_servicios_reservas_fecha_reserva_activo ON servicios_reservas(fecha_reserva, activo);

-- 7. Crear función para verificar permisos usando el sistema existente
CREATE OR REPLACE FUNCTION verificar_permiso_usuario(p_user_id INTEGER, p_permiso_nombre VARCHAR(100))
RETURNS BOOLEAN AS $$
DECLARE
    tiene_permiso BOOLEAN := false;
BEGIN
    -- Verificar si el usuario tiene el permiso a través de roles
    SELECT COUNT(*) > 0 INTO tiene_permiso
    FROM sys_user_roles sur
    INNER JOIN sys_role_permissions srp ON sur.role_id = srp.role_id
    INNER JOIN sys_permissions sp ON srp.perm_id = sp.perm_id
    WHERE sur.user_id = p_user_id AND sp.perm_name = p_permiso_nombre;
    
    RETURN tiene_permiso;
END;
$$ LANGUAGE plpgsql;

-- 8. Crear función para obtener parámetro del sistema
CREATE OR REPLACE FUNCTION obtener_parametro_sistema(p_codigo VARCHAR(100), p_default VARCHAR(500) DEFAULT NULL)
RETURNS VARCHAR(500) AS $$
DECLARE
    valor VARCHAR(500);
BEGIN
    SELECT parametro_valor INTO valor
    FROM sistema_parametros
    WHERE parametro_codigo = p_codigo
    AND is_active = true
    LIMIT 1;
    
    RETURN COALESCE(valor, p_default);
END;
$$ LANGUAGE plpgsql;

-- 9. Crear función para verificar si una reserva puede ser cancelada
CREATE OR REPLACE FUNCTION puede_cancelar_reserva_usuario(p_reserva_id INTEGER, p_user_id INTEGER)
RETURNS TABLE(
    puede_cancelar BOOLEAN,
    motivo TEXT,
    horas_restantes NUMERIC,
    limite_horas INTEGER,
    permiso_especial BOOLEAN
) AS $$
DECLARE
    reserva_info RECORD;
    limite_horas_config INTEGER := 72; -- Default
    horas_hasta_cita NUMERIC;
    tiene_permiso_especial BOOLEAN := false;
    limite_horas_str VARCHAR(500);
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
    limite_horas_str := obtener_parametro_sistema('LIMITE_HORAS_CANCELACION', '72');
    limite_horas_config := CAST(limite_horas_str AS INTEGER);
    
    -- Calcular horas restantes hasta la cita
    SELECT EXTRACT(EPOCH FROM (reserva_info.fecha_reserva + reserva_info.hora_inicio::time - CURRENT_TIMESTAMP))/3600
    INTO horas_hasta_cita;
    
    -- Verificar permiso especial
    tiene_permiso_especial := verificar_permiso_usuario(p_user_id, 'cancelar_reservas_tardias');
    
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

-- 10. Crear vista para reservas con información de cancelación
CREATE OR REPLACE VIEW vista_reservas_completa AS
SELECT 
    sr.*,
    CASE 
        WHEN sr.activo = false THEN 'CANCELADA'
        ELSE sr.reserva_estado
    END as estado_efectivo,
    rp_cancelado.first_name || ' ' || rp_cancelado.last_name as nombre_quien_cancelo,
    CAST(obtener_parametro_sistema('LIMITE_HORAS_CANCELACION', '72') AS INTEGER) as limite_horas_sistema,
    EXTRACT(EPOCH FROM (sr.fecha_reserva + sr.hora_inicio::time - CURRENT_TIMESTAMP))/3600 as horas_restantes
FROM servicios_reservas sr
LEFT JOIN sys_users su_cancelado ON sr.cancelado_por = su_cancelado.user_id
LEFT JOIN person_system_user psu_cancelado ON su_cancelado.user_id = psu_cancelado.system_user_id
LEFT JOIN rh_person rp_cancelado ON psu_cancelado.person_id = rp_cancelado.person_id;

-- 11. Agregar rol específico para administradores de reservas si no existe
INSERT INTO sys_roles (role_name, role_description) VALUES
('administrador_reservas', 'Administrador de reservas con permisos especiales de cancelación')
ON CONFLICT (role_name) DO UPDATE SET
    role_description = EXCLUDED.role_description;

-- 12. Asignar permisos de cancelación al rol de administrador de reservas
DO $$
DECLARE
    role_id_admin INTEGER;
    perm_id_cancelar INTEGER;
    perm_id_ver INTEGER;
    perm_id_otros INTEGER;
    perm_id_admin_cancel INTEGER;
BEGIN
    -- Obtener ID del rol
    SELECT role_id INTO role_id_admin FROM sys_roles WHERE role_name = 'administrador_reservas';
    
    -- Obtener IDs de permisos
    SELECT perm_id INTO perm_id_cancelar FROM sys_permissions WHERE perm_name = 'cancelar_reservas_tardias';
    SELECT perm_id INTO perm_id_ver FROM sys_permissions WHERE perm_name = 'ver_reservas_canceladas';
    SELECT perm_id INTO perm_id_otros FROM sys_permissions WHERE perm_name = 'cancelar_reservas_otros_usuarios';
    SELECT perm_id INTO perm_id_admin_cancel FROM sys_permissions WHERE perm_name = 'administrar_cancelaciones';
    
    -- Asignar permisos si no existen
    INSERT INTO sys_role_permissions (role_id, perm_id) VALUES
    (role_id_admin, perm_id_cancelar),
    (role_id_admin, perm_id_ver),
    (role_id_admin, perm_id_otros),
    (role_id_admin, perm_id_admin_cancel)
    ON CONFLICT (role_id, perm_id) DO NOTHING;
END $$;

-- 13. Comentarios de documentación
COMMENT ON FUNCTION verificar_permiso_usuario IS 'Verifica si un usuario tiene un permiso específico a través del sistema de roles existente';
COMMENT ON FUNCTION obtener_parametro_sistema IS 'Obtiene un parámetro configurable del sistema con valor por defecto';
COMMENT ON FUNCTION puede_cancelar_reserva_usuario IS 'Verifica si una reserva puede ser cancelada por un usuario específico usando el sistema de roles';
COMMENT ON VIEW vista_reservas_completa IS 'Vista que incluye información completa de reservas con datos de cancelación';

-- 14. Mensaje de confirmación
DO $$
BEGIN
    RAISE NOTICE 'Sistema de cancelación limpio configurado correctamente';
    RAISE NOTICE 'Usando únicamente las tablas del sistema de roles existente';
    RAISE NOTICE 'Funciones creadas: verificar_permiso_usuario, obtener_parametro_sistema, puede_cancelar_reserva_usuario';
    RAISE NOTICE 'Vista creada: vista_reservas_completa';
    RAISE NOTICE 'Rol creado: administrador_reservas con permisos de cancelación';
    RAISE NOTICE 'Tablas innecesarias eliminadas: sistema_permisos, usuario_permisos';
END $$;