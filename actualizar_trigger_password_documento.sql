-- Actualizar la función del trigger para usar el documento como contraseña temporal
-- en lugar del email

CREATE OR REPLACE FUNCTION public.create_sys_user_from_register()
 RETURNS trigger
 LANGUAGE plpgsql
AS $function$
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
        CURRENT_TIMESTAMP + interval '30 days',  -- Ejemplo: la expiración se fija a 30 días
        NULL,
        NULL,
        false
    );
    RETURN NEW;
END;
$function$
;