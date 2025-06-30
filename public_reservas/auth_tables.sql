-- Tablas para el sistema de autenticación de reservas públicas

-- Tabla para almacenar los datos de autenticación de pacientes
CREATE TABLE IF NOT EXISTS reservas_pacientes_auth (
    auth_id SERIAL PRIMARY KEY,
    paciente_id INTEGER NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_login TIMESTAMP,
    CONSTRAINT fk_paciente_id FOREIGN KEY (paciente_id) REFERENCES rh_person(person_id) ON DELETE CASCADE,
    CONSTRAINT uk_email_paciente UNIQUE (email)
);

-- Tabla para los tokens de "recordarme"
CREATE TABLE IF NOT EXISTS reservas_auth_tokens (
    token_id SERIAL PRIMARY KEY,
    paciente_id INTEGER NOT NULL,
    token VARCHAR(100) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP,
    CONSTRAINT fk_paciente_token FOREIGN KEY (paciente_id) REFERENCES rh_person(person_id) ON DELETE CASCADE,
    CONSTRAINT uk_token UNIQUE (token)
);

-- Crear índices para mejorar el rendimiento
CREATE INDEX IF NOT EXISTS idx_paciente_id ON reservas_pacientes_auth(paciente_id);
CREATE INDEX IF NOT EXISTS idx_email ON reservas_pacientes_auth(email);
CREATE INDEX IF NOT EXISTS idx_token ON reservas_auth_tokens(token);
CREATE INDEX IF NOT EXISTS idx_token_paciente_id ON reservas_auth_tokens(paciente_id);
