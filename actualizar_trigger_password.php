<?php
/**
 * Script para actualizar la función del trigger
 * para que use el documento como contraseña en lugar del email
 */

require_once __DIR__ . '/config/environment_setup.php';

try {
    echo "🔧 Actualizando función del trigger...\n";
    
    // Inicializar configuración
    \EnvironmentSetup::initialize();
    $dbConfig = \EnvironmentSetup::getDatabaseConfig();
    
    // Conectar a la base de datos
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado a la base de datos\n";
    
    // Función SQL actualizada
    $sql = "
    CREATE OR REPLACE FUNCTION public.create_sys_user_from_register()
     RETURNS trigger
     LANGUAGE plpgsql
    AS \$function\$
    BEGIN
        INSERT INTO public.sys_users (
            reg_id,
            user_email,
            user_pass,
            user_expire,
            user_first_login,
            user_last_login,
            user_is_active
        )
        VALUES (
            NEW.reg_id,
            NEW.reg_email,
            md5(NEW.reg_document),  -- Cambio: usa el documento en lugar del email
            CURRENT_TIMESTAMP + interval '30 days',
            NULL,
            NULL,
            false
        );
        RETURN NEW;
    END;
    \$function\$;
    ";
    
    // Ejecutar la actualización
    $pdo->exec($sql);
    
    echo "✅ Función del trigger actualizada exitosamente\n";
    echo "📋 Ahora la contraseña temporal será el MD5 del número de documento\n";
    echo "📋 en lugar del MD5 del email\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 ¡Actualización completada!\n";
?>