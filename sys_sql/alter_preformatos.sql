-- Agregar columna tipo_formulario a la tabla preformatos
-- Esta columna permitirá filtrar los preformatos por tipo de formulario
ALTER TABLE preformatos ADD COLUMN IF NOT EXISTS tipo_formulario VARCHAR(50) DEFAULT 'general';

-- Actualizar los preformatos existentes para asignarles el tipo de formulario 'general'
UPDATE preformatos SET tipo_formulario = 'general' WHERE tipo_formulario IS NULL OR tipo_formulario = '';

-- Agregar comentario a la columna
COMMENT ON COLUMN preformatos.tipo_formulario IS 'Tipo de formulario al que pertenece el preformato (general, anteojos, etc.)';
