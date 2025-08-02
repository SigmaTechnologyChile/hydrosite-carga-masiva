# Corrección: UNA LECTURA POR SERVICIO POR PERÍODO

## Problema Identificado
El sistema permitía registrar múltiples lecturas para el mismo servicio en el mismo período, causando registros duplicados.

## Solución Implementada

### 1. REGLA ESTRICTA: Una Lectura por Servicio por Período
- ❌ **No se permite** registrar más de una lectura por service_id en el mismo período
- ✅ **Bloqueo automático** de intentos de duplicación
- 🔒 **Verificación por service_id + period + org_id**

### 2. Modificación del método `createOrUpdateReading()`
**Nueva lógica restrictiva:**
```php
// Verificación ESTRICTA por service_id y period
$existingReading = Reading::where('service_id', $service->id)
    ->where('period', $request->period)
    ->where('org_id', $org->id)
    ->first();

if ($existingReading) {
    throw new \Exception('Ya existe una lectura registrada para el servicio N° ' . $service->nro . ' en el período ' . $request->period);
}
```

### 3. Modificación del método `store()` (lecturas masivas)
**Misma verificación estricta:**
```php
$existingServiceReading = Reading::where('service_id', $service_id)
    ->where('period', $lectura['period'])
    ->where('org_id', $org_id)
    ->first();

if ($existingServiceReading) {
    $errores[] = 'Ya existe una lectura para el servicio N° ' . $lectura['numero'] . ' en el período';
    continue; // Salta esta lectura
}
```

### 4. Método `cleanDuplicateReadings()` actualizado
**Funcionalidades:**
- ✅ **Detecta duplicados** por service_id + period + org_id
- ✅ **Mantiene el registro más reciente** (ID más alto)
- ✅ **Elimina todos los duplicados** automáticamente
- ✅ **Log detallado** con números de servicios afectados
- ✅ **Transacciones seguras** con rollback en caso de error

## Comportamiento Actual

### ✅ **Permitido:**
- Primera lectura del período para un servicio
- Lecturas de diferentes períodos para el mismo servicio
- Lecturas del mismo período para diferentes servicios

### ❌ **BLOQUEADO:**
- Segunda lectura del mismo período para el mismo servicio
- Cualquier intento de duplicación por service_id
- Registro continuo de la misma lectura para un servicio

### 🔄 **Respuestas del Sistema:**
- **Éxito**: "Lectura registrada correctamente"
- **Error de duplicado**: "Ya existe una lectura registrada para el servicio N° X en el período Y. No se pueden registrar lecturas duplicadas."

## Limpieza de Duplicados Existentes

### Opciones de Ejecución:
1. **Vía Artisan Tinker** (Recomendado)
2. **Vía SQL directo**
3. **Vía ruta temporal web**

### SQL actualizado para verificar duplicados por servicio:
```sql
-- Ver duplicados por service_id antes de eliminar
SELECT service_id, period, COUNT(*) as count 
FROM readings 
WHERE org_id = 1 
GROUP BY service_id, period 
HAVING count > 1;

-- Eliminar duplicados manteniendo el ID más alto (más reciente)
DELETE r1 FROM readings r1
INNER JOIN readings r2 
WHERE r1.org_id = 1 
AND r2.org_id = 1
AND r1.service_id = r2.service_id 
AND r1.period = r2.period 
AND r1.id < r2.id;
```

## Compatibilidad
- ✅ Mantiene todas las funcionalidades existentes
- ✅ Compatible con DTE (boletas/facturas)
- ✅ Compatible con carga masiva de lecturas
- ✅ Mantiene cálculos de costos y tramos

## Pruebas Recomendadas
1. ✅ Registrar lectura nueva para servicio → Debe funcionar
2. ❌ Intentar registrar segunda lectura mismo servicio y período → Debe dar error
3. ✅ Registrar lectura período diferente mismo servicio → Debe funcionar
4. ✅ Registrar lectura mismo período servicio diferente → Debe funcionar
5. 🧹 Ejecutar limpieza de duplicados → Debe eliminar duplicados por service_id
