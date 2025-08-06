-- =============================================================================
-- SISTEMA DE REFERENCIALES DINÁMICOS PARA FORMULARIOS DE CONSULTAS MÉDICAS
-- =============================================================================
-- Este script crea las tablas necesarias para un sistema dinámico de formularios
-- y referenciales que permitirá administrar campos y opciones desde el módulo
-- de referenciales sin codificación estática.
-- =============================================================================

-- Tabla de tipos de formularios (ya existe, pero verificamos estructura)
CREATE TABLE IF NOT EXISTS tipos_formularios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    activo INTEGER DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    updated_by INTEGER
);

-- Tabla de tipos de campos disponibles para formularios
CREATE TABLE IF NOT EXISTS tipos_campos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    html_input_type VARCHAR(50) NOT NULL, -- text, select, textarea, number, date, checkbox, radio, etc.
    requiere_opciones BOOLEAN DEFAULT FALSE, -- si necesita opciones predefinidas (select, radio, checkbox)
    activo INTEGER DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de campos de formularios dinámicos
CREATE TABLE IF NOT EXISTS formulario_campos (
    id SERIAL PRIMARY KEY,
    tipo_formulario_id INTEGER NOT NULL,
    nombre_campo VARCHAR(100) NOT NULL,
    etiqueta VARCHAR(200) NOT NULL,
    tipo_campo_id INTEGER NOT NULL,
    placeholder VARCHAR(200),
    orden_visualizacion INTEGER DEFAULT 1,
    requerido BOOLEAN DEFAULT FALSE,
    validaciones JSON, -- reglas de validación en formato JSON
    atributos_html JSON, -- atributos adicionales como class, id, etc.
    descripcion_ayuda TEXT,
    grupo_seccion VARCHAR(100), -- para agrupar campos en secciones
    activo INTEGER DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER,
    FOREIGN KEY (tipo_formulario_id) REFERENCES tipos_formularios(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_campo_id) REFERENCES tipos_campos(id),
    UNIQUE(tipo_formulario_id, nombre_campo)
);

-- Tabla de opciones para campos select, radio, checkbox
CREATE TABLE IF NOT EXISTS campo_opciones (
    id SERIAL PRIMARY KEY,
    formulario_campo_id INTEGER NOT NULL,
    valor VARCHAR(200) NOT NULL,
    etiqueta VARCHAR(200) NOT NULL,
    orden_visualizacion INTEGER DEFAULT 1,
    seleccionado_por_defecto BOOLEAN DEFAULT FALSE,
    activo INTEGER DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (formulario_campo_id) REFERENCES formulario_campos(id) ON DELETE CASCADE
);

-- Tabla de referenciales para opciones dinámicas (reemplaza las opciones estáticas)
CREATE TABLE IF NOT EXISTS referenciales (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    categoria VARCHAR(100), -- oftalmologia, cardiologia, general, etc.
    activo INTEGER DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INTEGER
);

-- Tabla de valores de referenciales
CREATE TABLE IF NOT EXISTS referencial_valores (
    id SERIAL PRIMARY KEY,
    referencial_id INTEGER NOT NULL,
    valor VARCHAR(500) NOT NULL,
    etiqueta VARCHAR(500) NOT NULL,
    valor_numerico DECIMAL(10,4), -- para valores numéricos como dioptrías
    orden_visualizacion INTEGER DEFAULT 1,
    descripcion TEXT,
    activo INTEGER DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referencial_id) REFERENCES referenciales(id) ON DELETE CASCADE
);

-- Tabla de configuración de formularios (configuraciones específicas por formulario)
CREATE TABLE IF NOT EXISTS formulario_configuraciones (
    id SERIAL PRIMARY KEY,
    tipo_formulario_id INTEGER NOT NULL,
    configuracion_clave VARCHAR(100) NOT NULL,
    configuracion_valor TEXT,
    descripcion TEXT,
    activo INTEGER DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tipo_formulario_id) REFERENCES tipos_formularios(id) ON DELETE CASCADE,
    UNIQUE(tipo_formulario_id, configuracion_clave)
);

-- =============================================================================
-- INSERTAR TIPOS DE CAMPOS BÁSICOS
-- =============================================================================

INSERT INTO tipos_campos (nombre, codigo, descripcion, html_input_type, requiere_opciones) VALUES
('Texto Simple', 'text', 'Campo de texto simple de una línea', 'text', FALSE),
('Área de Texto', 'textarea', 'Campo de texto multilínea', 'textarea', FALSE),
('Número', 'number', 'Campo numérico', 'number', FALSE),
('Fecha', 'date', 'Selector de fecha', 'date', FALSE),
('Email', 'email', 'Campo de correo electrónico', 'email', FALSE),
('Teléfono', 'tel', 'Campo de teléfono', 'tel', FALSE),
('Lista Desplegable', 'select', 'Menú desplegable con opciones', 'select', TRUE),
('Radio Buttons', 'radio', 'Botones de opción exclusiva', 'radio', TRUE),
('Checkboxes', 'checkbox', 'Casillas de verificación múltiple', 'checkbox', TRUE),
('Rango Numérico', 'range', 'Control deslizante para rangos', 'range', FALSE),
('Color', 'color', 'Selector de color', 'color', FALSE),
('Archivo', 'file', 'Subida de archivos', 'file', FALSE),
('Editor Rico', 'summernote', 'Editor de texto enriquecido', 'textarea', FALSE),
('Select2', 'select2', 'Lista desplegable con búsqueda', 'select', TRUE)
ON CONFLICT (codigo) DO NOTHING;

-- =============================================================================
-- INSERTAR REFERENCIALES PARA OFTALMOLOGÍA (ANTEOJOS)
-- =============================================================================

-- Referenciales para valores de dioptrías
INSERT INTO referenciales (nombre, codigo, descripcion, categoria) VALUES
('Valores de Esfera', 'valores_esfera', 'Valores posibles para esfera en recetas de anteojos', 'oftalmologia'),
('Valores de Cilindro', 'valores_cilindro', 'Valores posibles para cilindro en recetas de anteojos', 'oftalmologia'),
('Valores de Adición', 'valores_adicion', 'Valores posibles para adición en recetas de anteojos', 'oftalmologia')
ON CONFLICT (codigo) DO NOTHING;

-- Valores de esfera
INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion) 
SELECT r.id, v.valor, v.etiqueta, v.valor_numerico, v.orden
FROM referenciales r,
(VALUES 
    ('', 'Seleccionar', NULL, 0),
    ('0.00', '0.00', 0.00, 1),
    ('+0.25', '+0.25', 0.25, 2),
    ('+0.50', '+0.50', 0.50, 3),
    ('+0.75', '+0.75', 0.75, 4),
    ('+1.00', '+1.00', 1.00, 5),
    ('+1.25', '+1.25', 1.25, 6),
    ('+1.50', '+1.50', 1.50, 7),
    ('+1.75', '+1.75', 1.75, 8),
    ('+2.00', '+2.00', 2.00, 9),
    ('+2.25', '+2.25', 2.25, 10),
    ('+2.50', '+2.50', 2.50, 11),
    ('+2.75', '+2.75', 2.75, 12),
    ('+3.00', '+3.00', 3.00, 13),
    ('+4.00', '+4.00', 4.00, 14),
    ('+5.00', '+5.00', 5.00, 15),
    ('+6.00', '+6.00', 6.00, 16),
    ('+7.00', '+7.00', 7.00, 17),
    ('+8.00', '+8.00', 8.00, 18),
    ('-0.25', '-0.25', -0.25, 19),
    ('-0.50', '-0.50', -0.50, 20),
    ('-0.75', '-0.75', -0.75, 21),
    ('-1.00', '-1.00', -1.00, 22),
    ('-1.25', '-1.25', -1.25, 23),
    ('-1.50', '-1.50', -1.50, 24),
    ('-1.75', '-1.75', -1.75, 25),
    ('-2.00', '-2.00', -2.00, 26),
    ('-2.25', '-2.25', -2.25, 27),
    ('-2.50', '-2.50', -2.50, 28),
    ('-2.75', '-2.75', -2.75, 29),
    ('-3.00', '-3.00', -3.00, 30),
    ('-4.00', '-4.00', -4.00, 31),
    ('-5.00', '-5.00', -5.00, 32),
    ('-6.00', '-6.00', -6.00, 33),
    ('-7.00', '-7.00', -7.00, 34),
    ('-8.00', '-8.00', -8.00, 35)
) AS v(valor, etiqueta, valor_numerico, orden)
WHERE r.codigo = 'valores_esfera';

-- Valores de cilindro (similar a esfera pero con algunas diferencias)
INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion) 
SELECT r.id, v.valor, v.etiqueta, v.valor_numerico, v.orden
FROM referenciales r,
(VALUES 
    ('', 'Seleccionar', NULL, 0),
    ('0.00', '0.00', 0.00, 1),
    ('-0.25', '-0.25', -0.25, 2),
    ('-0.50', '-0.50', -0.50, 3),
    ('-0.75', '-0.75', -0.75, 4),
    ('-1.00', '-1.00', -1.00, 5),
    ('-1.25', '-1.25', -1.25, 6),
    ('-1.50', '-1.50', -1.50, 7),
    ('-1.75', '-1.75', -1.75, 8),
    ('-2.00', '-2.00', -2.00, 9),
    ('-2.25', '-2.25', -2.25, 10),
    ('-2.50', '-2.50', -2.50, 11),
    ('-2.75', '-2.75', -2.75, 12),
    ('-3.00', '-3.00', -3.00, 13),
    ('-4.00', '-4.00', -4.00, 14),
    ('-5.00', '-5.00', -5.00, 15),
    ('+0.25', '+0.25', 0.25, 16),
    ('+0.50', '+0.50', 0.50, 17),
    ('+0.75', '+0.75', 0.75, 18),
    ('+1.00', '+1.00', 1.00, 19),
    ('+1.25', '+1.25', 1.25, 20),
    ('+1.50', '+1.50', 1.50, 21),
    ('+1.75', '+1.75', 1.75, 22),
    ('+2.00', '+2.00', 2.00, 23),
    ('+2.25', '+2.25', 2.25, 24),
    ('+2.50', '+2.50', 2.50, 25),
    ('+2.75', '+2.75', 2.75, 26),
    ('+3.00', '+3.00', 3.00, 27)
) AS v(valor, etiqueta, valor_numerico, orden)
WHERE r.codigo = 'valores_cilindro';

-- Valores de adición
INSERT INTO referencial_valores (referencial_id, valor, etiqueta, valor_numerico, orden_visualizacion) 
SELECT r.id, v.valor, v.etiqueta, v.valor_numerico, v.orden
FROM referenciales r,
(VALUES 
    ('', 'Seleccionar', NULL, 0),
    ('0.00', '0.00', 0.00, 1),
    ('+0.25', '+0.25', 0.25, 2),
    ('+0.50', '+0.50', 0.50, 3),
    ('+0.75', '+0.75', 0.75, 4),
    ('+1.00', '+1.00', 1.00, 5),
    ('+1.25', '+1.25', 1.25, 6),
    ('+1.50', '+1.50', 1.50, 7),
    ('+1.75', '+1.75', 1.75, 8),
    ('+2.00', '+2.00', 2.00, 9),
    ('+2.25', '+2.25', 2.25, 10),
    ('+2.50', '+2.50', 2.50, 11),
    ('+2.75', '+2.75', 2.75, 12),
    ('+3.00', '+3.00', 3.00, 13),
    ('+3.25', '+3.25', 3.25, 14),
    ('+3.50', '+3.50', 3.50, 15)
) AS v(valor, etiqueta, valor_numerico, orden)
WHERE r.codigo = 'valores_adicion';

-- =============================================================================
-- REFERENCIALES PARA EQUIPOS MÉDICOS (ESTUDIOS)
-- =============================================================================

INSERT INTO referenciales (nombre, codigo, descripcion, categoria) VALUES
('Equipos Médicos', 'equipos_medicos', 'Lista de equipos médicos disponibles para estudios', 'estudios_medicos')
ON CONFLICT (codigo) DO NOTHING;

INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion) 
SELECT r.id, v.valor, v.etiqueta, v.orden
FROM referenciales r,
(VALUES 
    ('', 'Seleccionar equipo', 1),
    ('cirrus_700', 'Cirrus 700', 2),
    ('cirrus_500c', 'Cirrus 500c', 3),
    ('oct_triton', 'OCT Triton', 4),
    ('humphrey', 'Humphrey', 5),
    ('topcon', 'Topcon', 6),
    ('zeiss_icare', 'Zeiss iCare', 7),
    ('canon_cr2', 'Canon CR-2', 8),
    ('heidelberg_spectralis', 'Heidelberg Spectralis', 9),
    ('optovue_avanti', 'Optovue Avanti', 10),
    ('otro', 'Otro equipo', 99)
) AS v(valor, etiqueta, orden)
WHERE r.codigo = 'equipos_medicos';

-- =============================================================================
-- REFERENCIALES GENERALES
-- =============================================================================

-- Especialidades médicas
INSERT INTO referenciales (nombre, codigo, descripcion, categoria) VALUES
('Especialidades Médicas', 'especialidades_medicas', 'Lista de especialidades médicas disponibles', 'general')
ON CONFLICT (codigo) DO NOTHING;

INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion) 
SELECT r.id, v.valor, v.etiqueta, v.orden
FROM referenciales r,
(VALUES 
    ('oftalmologia', 'Oftalmología', 1),
    ('cardiologia', 'Cardiología', 2),
    ('neurologia', 'Neurología', 3),
    ('dermatologia', 'Dermatología', 4),
    ('pediatria', 'Pediatría', 5),
    ('medicina_interna', 'Medicina Interna', 6),
    ('traumatologia', 'Traumatología', 7),
    ('ginecologia', 'Ginecología', 8),
    ('urologia', 'Urología', 9),
    ('endocrinologia', 'Endocrinología', 10)
) AS v(valor, etiqueta, orden)
WHERE r.codigo = 'especialidades_medicas';

-- Estados de consultas
INSERT INTO referenciales (nombre, codigo, descripcion, categoria) VALUES
('Estados de Consulta', 'estados_consulta', 'Estados posibles para una consulta médica', 'general')
ON CONFLICT (codigo) DO NOTHING;

INSERT INTO referencial_valores (referencial_id, valor, etiqueta, orden_visualizacion) 
SELECT r.id, v.valor, v.etiqueta, v.orden
FROM referenciales r,
(VALUES 
    ('pendiente', 'Pendiente', 1),
    ('en_progreso', 'En Progreso', 2),
    ('completada', 'Completada', 3),
    ('cancelada', 'Cancelada', 4),
    ('reprogramada', 'Reprogramada', 5)
) AS v(valor, etiqueta, orden)
WHERE r.codigo = 'estados_consulta';

-- =============================================================================
-- COMENTARIOS Y DOCUMENTACIÓN
-- =============================================================================

-- Añadir comentarios a las tablas para documentación
COMMENT ON TABLE tipos_formularios IS 'Tipos de formularios disponibles en el sistema de consultas';
COMMENT ON TABLE tipos_campos IS 'Tipos de campos HTML que se pueden usar en los formularios dinámicos';
COMMENT ON TABLE formulario_campos IS 'Definición de campos específicos para cada tipo de formulario';
COMMENT ON TABLE campo_opciones IS 'Opciones disponibles para campos de tipo select, radio, checkbox';
COMMENT ON TABLE referenciales IS 'Referenciales maestros para opciones dinámicas del sistema';
COMMENT ON TABLE referencial_valores IS 'Valores específicos para cada referencial';
COMMENT ON TABLE formulario_configuraciones IS 'Configuraciones específicas por tipo de formulario';

-- =============================================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- =============================================================================

CREATE INDEX IF NOT EXISTS idx_formulario_campos_tipo_formulario ON formulario_campos(tipo_formulario_id);
CREATE INDEX IF NOT EXISTS idx_formulario_campos_activo ON formulario_campos(activo);
CREATE INDEX IF NOT EXISTS idx_campo_opciones_formulario_campo ON campo_opciones(formulario_campo_id);
CREATE INDEX IF NOT EXISTS idx_referencial_valores_referencial ON referencial_valores(referencial_id);
CREATE INDEX IF NOT EXISTS idx_referencial_valores_activo ON referencial_valores(activo);
CREATE INDEX IF NOT EXISTS idx_referenciales_categoria ON referenciales(categoria);
CREATE INDEX IF NOT EXISTS idx_referenciales_codigo ON referenciales(codigo);

-- =============================================================================
-- TRIGGER PARA ACTUALIZAR fecha_actualizacion
-- =============================================================================

CREATE OR REPLACE FUNCTION actualizar_fecha_modificacion()
RETURNS TRIGGER AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplicar trigger a las tablas que lo necesiten
DROP TRIGGER IF EXISTS tr_tipos_formularios_actualizar ON tipos_formularios;
CREATE TRIGGER tr_tipos_formularios_actualizar
    BEFORE UPDATE ON tipos_formularios
    FOR EACH ROW
    EXECUTE FUNCTION actualizar_fecha_modificacion();

-- =============================================================================
-- FIN DEL SCRIPT
-- =============================================================================
-- Para ejecutar este script:
-- 1. Conectarse a la base de datos PostgreSQL
-- 2. Ejecutar: \i /ruta/al/archivo/crear_sistema_referenciales_dinamicos.sql
-- 3. Verificar que todas las tablas se crearon correctamente
-- =============================================================================
