# Implementación del Filtro de Citas por Doctor Logueado

## Resumen de los cambios realizados:

### 1. Archivo AJAX modificado (ajax/servicios.ajax.php)

**Cambios principales:**
- Se agregó detección del parámetro `modulo` para identificar cuando la llamada viene de citas
- Cuando `modulo === 'citas'`:
  - Se fuerza el filtro por el doctor logueado (`$_SESSION['doctor_id']` o `$_SESSION['usuario_id']`)
  - Se fuerza el filtro por estado `CONFIRMADA`
- Cuando NO es citas (servicios), mantiene el comportamiento original

**Líneas modificadas:**
```php
// Detectar si estamos en el módulo de citas
$esCitas = isset($_POST['modulo']) && $_POST['modulo'] === 'citas';

// Si estamos en citas, forzar filtro por doctor logueado
if ($esCitas) {
    $doctorIdSesion = isset($_SESSION['doctor_id']) ? $_SESSION['doctor_id'] : (isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null);
    if ($doctorIdSesion) {
        $doctorId = intval($doctorIdSesion);
    }
}

// Si estamos en citas, forzar filtro por estado CONFIRMADA
if ($esCitas) {
    $estado = 'CONFIRMADA';
}
```

### 2. JavaScript modificado (view/js/reservas_new_doctor.js)

**Cambios principales:**
- Se agregó el parámetro `modulo: 'citas'` en todas las llamadas AJAX de búsqueda de reservas
- Esto se aplica tanto en la función `cargarReservasPorFecha()` como en `buscarReservas()`

**Líneas modificadas:**
```javascript
data: {
    action: 'buscarReservas',
    fecha: fecha,
    modulo: 'citas' // Identificar que es el módulo de citas
}
```

### 3. Funcionamiento esperado:

**En el módulo de CITAS (ruta=citas):**
- Solo muestra reservas del doctor que está logueado
- Solo muestra reservas con estado `CONFIRMADA`
- Los filtros de doctor y estado se aplican automáticamente

**En el módulo de SERVICIOS (ruta=servicios):**
- Mantiene el comportamiento original
- Muestra todas las reservas según los filtros que seleccione el usuario
- No se aplican filtros automáticos

### 4. Variables de sesión utilizadas:

El sistema busca el doctor logueado en este orden:
1. `$_SESSION['doctor_id']` (preferencia)
2. `$_SESSION['usuario_id']` (alternativa)

### 5. Archivos de prueba creados:

- `test_ajax_sim.php`: Simula la llamada AJAX para verificar el funcionamiento
- `test_citas_filter.php`: Prueba los filtros directamente
- `create_test_reservas.php`: Crea reservas de prueba para testing

## Para verificar que funciona:

1. **Acceder al módulo de citas:** `http://localhost/clinica/index.php?ruta=citas`
   - Debería mostrar solo las reservas confirmadas del doctor logueado

2. **Acceder al módulo de servicios:** `http://localhost/clinica/index.php?ruta=servicios`  
   - Debería mostrar todas las reservas según los filtros seleccionados

3. **Revisar los logs:** `logs/reservas.log`
   - Debería mostrar la diferencia en los filtros aplicados

## Nota importante:

El doctor logueado se determina por la sesión actual. Asegúrate de que el usuario tenga `doctor_id` o `usuario_id` en la sesión para que el filtro funcione correctamente.