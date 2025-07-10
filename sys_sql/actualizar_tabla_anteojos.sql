-- Script para actualizar la tabla consulta_anteojos añadiendo las columnas faltantes
-- Ejecutar este script en la base de datos para corregir el error:
-- "column "nota_od" of relation "consulta_anteojos" does not exist"

-- Primero verificamos si las columnas ya existen para evitar errores
DO $$
BEGIN
    -- Añadir columna nota_od si no existe
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='consulta_anteojos' AND column_name='nota_od'
    ) THEN
        ALTER TABLE consulta_anteojos ADD COLUMN nota_od TEXT;
        RAISE NOTICE 'Columna nota_od añadida';
    ELSE
        RAISE NOTICE 'La columna nota_od ya existe';
    END IF;

    -- Añadir columna nota_oi si no existe
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='consulta_anteojos' AND column_name='nota_oi'
    ) THEN
        ALTER TABLE consulta_anteojos ADD COLUMN nota_oi TEXT;
        RAISE NOTICE 'Columna nota_oi añadida';
    ELSE
        RAISE NOTICE 'La columna nota_oi ya existe';
    END IF;

    -- Añadir otras columnas posiblemente faltantes
    -- Columnas para OD (Ojo Derecho)
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='consulta_anteojos' AND column_name='add_od'
    ) THEN
        ALTER TABLE consulta_anteojos ADD COLUMN add_od VARCHAR(10);
        RAISE NOTICE 'Columna add_od añadida';
    END IF;

    -- Columnas para OI (Ojo Izquierdo)
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='consulta_anteojos' AND column_name='add_oi'
    ) THEN
        ALTER TABLE consulta_anteojos ADD COLUMN add_oi VARCHAR(10);
        RAISE NOTICE 'Columna add_oi añadida';
    END IF;

    -- Columna para distancia interpupilar
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='consulta_anteojos' AND column_name='dist_interpupilar'
    ) THEN
        ALTER TABLE consulta_anteojos ADD COLUMN dist_interpupilar VARCHAR(10);
        RAISE NOTICE 'Columna dist_interpupilar añadida';
    END IF;

    -- Columnas para altura
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='consulta_anteojos' AND column_name='altura_od'
    ) THEN
        ALTER TABLE consulta_anteojos ADD COLUMN altura_od VARCHAR(10);
        RAISE NOTICE 'Columna altura_od añadida';
    END IF;

    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name='consulta_anteojos' AND column_name='altura_oi'
    ) THEN
        ALTER TABLE consulta_anteojos ADD COLUMN altura_oi VARCHAR(10);
        RAISE NOTICE 'Columna altura_oi añadida';
    END IF;

END $$;

-- Verificación de estructura después de los cambios
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'consulta_anteojos' 
ORDER BY ordinal_position;
