-- ===================================================
-- SCRIPT PARA ELIMINAR LECTURAS DEL SERVICIO 216
-- ===================================================

-- PASO 1: Verificar que existe el servicio 216
SELECT 
    s.id as service_id,
    s.nro as service_number,
    s.member_id,
    s.org_id,
    m.full_name,
    m.rut
FROM services s
LEFT JOIN members m ON s.member_id = m.id
WHERE s.nro = 216;

-- PASO 2: Contar cuántas lecturas tiene el servicio 216
SELECT 
    COUNT(*) as total_lecturas,
    MIN(period) as primera_lectura,
    MAX(period) as ultima_lectura
FROM readings r
INNER JOIN services s ON r.service_id = s.id
WHERE s.nro = 216;

-- PASO 3: Ver las lecturas del servicio 216 (opcional)
SELECT 
    r.id,
    r.period,
    r.current_reading,
    r.previous_reading,
    r.cm3,
    r.created_at
FROM readings r
INNER JOIN services s ON r.service_id = s.id
WHERE s.nro = 216
ORDER BY r.period DESC
LIMIT 10;

-- ===================================================
-- ELIMINACIÓN (EJECUTAR SOLO DESPUÉS DE VERIFICAR)
-- ===================================================

-- PASO 4: Eliminar todas las lecturas del servicio 216
-- ADVERTENCIA: Esta operación no se puede deshacer
-- Descomenta las siguientes líneas para ejecutar:

/*
DELETE r FROM readings r
INNER JOIN services s ON r.service_id = s.id
WHERE s.nro = 216;
*/

-- PASO 5: Verificar que se eliminaron (ejecutar después del DELETE)
-- SELECT COUNT(*) as lecturas_restantes FROM readings r
-- INNER JOIN services s ON r.service_id = s.id
-- WHERE s.nro = 216;
