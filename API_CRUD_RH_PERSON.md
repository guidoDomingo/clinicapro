# API CRUD para rh_person - Documentación Completa

## 📋 Tabla de la Base de Datos

La tabla `rh_person` contiene los siguientes campos:

```sql
CREATE TABLE public.rh_person (
    person_id serial4 NOT NULL,
    document_number varchar(20) NOT NULL,
    birth_date date NOT NULL,
    first_name varchar(255) NOT NULL,
    last_name varchar(255) NOT NULL,
    phone_number varchar(50) NULL,
    gender bpchar(1) NULL,
    record_number varchar(50) NULL,
    address varchar(255) NULL,
    email varchar(100) NULL,
    department_id int4 NULL,
    city_id int4 NULL,
    is_minor bool NULL DEFAULT false,
    guardian_name varchar(255) NULL,
    guardian_document varchar(20) NULL,
    created_at timestamptz NULL DEFAULT CURRENT_TIMESTAMP,
    registered_by int4 NULL,
    modified_by int4 NULL,
    last_modified_at timestamptz NULL,
    last_accessed_at timestamptz NULL,
    owner_id int4 NULL,
    is_active bool NOT NULL DEFAULT false,
    business_id int4 NULL,
    profile_photo varchar(255) NULL,
    CONSTRAINT rh_person_gender_check CHECK ((gender = ANY (ARRAY['M'::bpchar, 'F'::bpchar, 'O'::bpchar, 'U'::bpchar]))),
    CONSTRAINT rh_person_pkey PRIMARY KEY (person_id)
);
```

## 🚀 Endpoints Disponibles

### Base URL
```
http://clinica_estable.test/api/
```

---

## 📖 1. Listar Personas (GET)

### Endpoint
```
GET /api/persons
```

### Parámetros de consulta (opcionales)
- `page` (int): Número de página (default: 1)
- `per_page` (int): Registros por página (default: 20, máx: 100)
- `search` (string): Término de búsqueda en nombre, apellido, documento o teléfono
- `order_column` (string): Columna para ordenar (default: person_id)
- `order_direction` (string): Dirección de orden asc/desc (default: desc)

### Ejemplo de respuesta
```json
{
    "status": "success",
    "message": "Persons retrieved successfully",
    "data": [
        {
            "person_id": 1,
            "document_number": "12345678",
            "birth_date": "1990-01-15",
            "first_name": "Juan",
            "last_name": "Pérez",
            "phone_number": "0981234567",
            "gender": "M",
            "record_number": "EXP001",
            "address": "Av. España 123",
            "email": "juan.perez@email.com",
            "department_id": 1,
            "city_id": 1,
            "is_minor": false,
            "guardian_name": null,
            "guardian_document": null,
            "created_at": "2025-09-20T10:00:00Z",
            "registered_by": 1,
            "modified_by": null,
            "last_modified_at": null,
            "last_accessed_at": null,
            "owner_id": null,
            "is_active": true,
            "business_id": 1,
            "profile_photo": "person_1_1695211200.jpg"
        }
    ],
    "pagination": {
        "total": 50,
        "per_page": 20,
        "current_page": 1,
        "last_page": 3,
        "from": 1,
        "to": 20
    }
}
```

---

## 👤 2. Obtener Persona Específica (GET)

### Endpoint
```
GET /api/persons/show?id={person_id}
```

### Parámetros
- `id` (required): ID de la persona

### Ejemplo de respuesta
```json
{
    "status": "success",
    "message": "Person retrieved successfully",
    "data": {
        "person_id": 1,
        "document_number": "12345678",
        "birth_date": "1990-01-15",
        "first_name": "Juan",
        "last_name": "Pérez",
        // ... resto de campos
    }
}
```

---

## ➕ 3. Crear Nueva Persona (POST)

### Endpoint
```
POST /api/persons
```

### Campos requeridos
- `document_number` (string): Número de documento
- `birth_date` (date): Fecha de nacimiento (YYYY-MM-DD)
- `first_name` (string): Nombre
- `last_name` (string): Apellido

### Campos opcionales
- `phone_number` (string): Teléfono
- `gender` (char): Género (M/F/O/U)
- `record_number` (string): Número de expediente
- `address` (string): Dirección
- `email` (string): Email
- `department_id` (int): ID del departamento
- `city_id` (int): ID de la ciudad
- `guardian_name` (string): Nombre del tutor (requerido si es menor)
- `guardian_document` (string): Documento del tutor (requerido si es menor)
- `business_id` (int): ID de la empresa
- `is_active` (boolean): Estado activo (default: true)

### Ejemplo de petición
```json
{
    "document_number": "87654321",
    "birth_date": "1995-05-20",
    "first_name": "María",
    "last_name": "González",
    "phone_number": "0987654321",
    "gender": "F",
    "email": "maria.gonzalez@email.com",
    "address": "Calle Principal 456",
    "department_id": 1,
    "city_id": 2,
    "business_id": 1
}
```

### Ejemplo de respuesta
```json
{
    "status": "success",
    "message": "Person created successfully",
    "data": {
        "person_id": 2,
        "document_number": "87654321",
        "birth_date": "1995-05-20",
        "first_name": "María",
        "last_name": "González",
        // ... resto de campos creados
    }
}
```

---

## ✏️ 4. Actualizar Persona (PUT)

### Endpoint
```
PUT /api/persons?id={person_id}
```

### Parámetros
- `id` (required): ID de la persona a actualizar

### Body
Mismos campos que la creación, todos opcionales.

### Ejemplo de petición
```json
{
    "phone_number": "0981111111",
    "email": "nuevo.email@email.com",
    "address": "Nueva dirección 789"
}
```

### Ejemplo de respuesta
```json
{
    "status": "success",
    "message": "Person updated successfully",
    "data": {
        // ... datos actualizados de la persona
    }
}
```

---

## 🗑️ 5. Eliminar Persona (DELETE)

### Endpoint
```
DELETE /api/persons?id={person_id}
```

### Parámetros
- `id` (required): ID de la persona a eliminar

### Ejemplo de respuesta
```json
{
    "status": "success",
    "message": "Person deleted successfully"
}
```

**Nota:** Esta es una eliminación física. También elimina registros relacionados en `rh_doctors` si existen.

---

## 🔍 6. Búsqueda de Personas (GET)

### Endpoint
```
GET /api/persons/search
```

### Parámetros de búsqueda (opcionales)
- `document` (string): Búsqueda parcial por documento
- `name` (string): Búsqueda parcial por nombre
- `lastname` (string): Búsqueda parcial por apellido
- `record` (string): Búsqueda parcial por número de expediente
- `gender` (char): Búsqueda exacta por género
- `q` (string): Búsqueda general (método legacy)

### Ejemplo
```
GET /api/persons/search?name=Juan&gender=M
```

### Ejemplo de respuesta
```json
{
    "status": "success",
    "message": "Search completed successfully",
    "data": [
        // ... personas que coinciden con los criterios
    ]
}
```

---

## ✅ 7. Obtener Personas Activas (GET)

### Endpoint
```
GET /api/persons/active
```

### Ejemplo de respuesta
```json
{
    "status": "success",
    "data": [
        // ... solo personas con is_active = true
    ]
}
```

---

## 📸 8. Subir Foto de Perfil (POST)

### Endpoint
```
POST /api/persons/upload-photo?id={person_id}
```

### Parámetros
- `id` (required): ID de la persona

### Body
- `profile_photo` (file): Archivo de imagen (JPG, PNG, GIF, máx 10MB)

### Ejemplo de respuesta
```json
{
    "status": "success",
    "message": "Profile photo uploaded successfully",
    "data": {
        "photo_url": "view/uploads/profile/person_1_1695211200.jpg"
    }
}
```

---

## 🏥 9. Información Profesional

### Obtener información profesional
```
GET /api/persons/professional?person_id={person_id}
```

### Guardar información profesional
```
POST /api/persons/professional
```

#### Body para información profesional
```json
{
    "person_id": 1,
    "profesion": "Médico",
    "direccion_corporativa": "Clínica Central",
    "email_profesional": "doctor@clinica.com",
    "denominacion_corporativa": "Dr. Juan Pérez",
    "ruc": "12345678-9",
    "whatsapp": "0981234567",
    "plan": "Premium",
    "business_id": 1
}
```

**Nota:** Si la profesión es "Médico", automáticamente se crea/actualiza un registro en la tabla `rh_doctors`.

---

## 🎯 Alias y Compatibilidad

### Alias disponibles
- `GET /api/people` → Alias de `/api/persons`
- `GET /api/specialties` → Alias de `/api/especialidades`

---

## ⚠️ Validaciones y Restricciones

### Validaciones de creación/actualización:
1. **Documento único**: No se permiten documentos duplicados
2. **Email único**: No se permiten emails duplicados (si se proporciona)
3. **Formato de documento**: Solo números y guiones, máximo 20 caracteres
4. **Formato de email**: Validación de formato de email
5. **Fecha de nacimiento**: Formato YYYY-MM-DD
6. **Menores de edad**: Si es menor de 18 años, requiere `guardian_name` y `guardian_document`
7. **Género**: Solo valores M, F, O, U permitidos

### Códigos de estado HTTP:
- `200`: Éxito
- `201`: Creado exitosamente
- `400`: Error de validación/datos inválidos
- `404`: Recurso no encontrado
- `409`: Conflicto (documento/email duplicado)
- `500`: Error interno del servidor

---

## 🔧 Ejemplos de Uso con cURL

### Crear una persona
```bash
curl -X POST http://clinica_estable.test/api/persons \
  -H "Content-Type: application/json" \
  -d '{
    "document_number": "12345678",
    "birth_date": "1990-01-15",
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@email.com",
    "phone_number": "0981234567"
  }'
```

### Buscar personas
```bash
curl "http://clinica_estable.test/api/persons/search?name=Juan&gender=M"
```

### Subir foto de perfil
```bash
curl -X POST http://clinica_estable.test/api/persons/upload-photo?id=1 \
  -F "profile_photo=@/path/to/photo.jpg"
```

---

## 📁 Estructura de Archivos

```
api/
├── models/
│   └── RhPerson.php          # Modelo de la entidad persona
├── controllers/
│   └── RhPersonController.php # Controlador con todos los endpoints
└── routes/
    └── api.php               # Definición de rutas
```

¡La API está completamente funcional y lista para usar! 🎉