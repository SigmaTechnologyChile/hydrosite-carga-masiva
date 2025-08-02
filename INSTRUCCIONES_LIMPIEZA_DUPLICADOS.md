# Script para Limpiar Registros Duplicados

## Ejecutar la limpieza de duplicados

Para ejecutar la limpieza de registros duplicados, puedes usar cualquiera de estos métodos:

### Método 1: Vía Artisan (Recomendado)
```bash
php artisan tinker
```

Luego ejecutar en tinker:
```php
// Reemplaza 1 con el ID de tu organización
$orgId = 1;
$controller = new App\Http\Controllers\Org\ReadingController();
$result = $controller->cleanDuplicateReadings($orgId);
echo $result->getContent();
```

### Método 2: Vía SQL directo (Si prefieres SQL)
```sql
-- Ver duplicados antes de eliminar (por service_id)
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

### Método 3: Crear una ruta temporal
Agregar en `routes/web.php`:
```php
Route::get('/clean-duplicates/{org_id}', [App\Http\Controllers\Org\ReadingController::class, 'cleanDuplicateReadings'])
    ->middleware('auth');
```

Luego visitar: `http://tu-dominio.com/clean-duplicates/1` (reemplaza 1 con el ID de tu organización)

## Verificación Post-Limpieza

Después de ejecutar la limpieza, verifica que no hay duplicados por service_id:
```sql
SELECT service_id, period, COUNT(*) as count 
FROM readings 
WHERE org_id = 1 
GROUP BY service_id, period 
HAVING count > 1;
```

Si la consulta no devuelve resultados, ¡la limpieza fue exitosa!

## Nuevas Reglas Implementadas

✅ **UNA LECTURA POR SERVICIO POR PERÍODO** - Sin excepciones
✅ **Bloqueo automático** de entradas duplicadas por service_id
✅ **Mensajes de error** informativos cuando se intenta duplicar
✅ **Limpieza de duplicados existentes** disponible

## Comportamiento Esperado

- ❌ **Intentar registrar segunda lectura** → Error: "Ya existe una lectura registrada para el servicio N° X en el período Y"
- ✅ **Primera lectura del período** → Se registra normalmente  
- ✅ **Lecturas de diferentes períodos** → Permitidas sin problemas
- ✅ **Diferentes servicios mismo período** → Permitidas sin problemas
