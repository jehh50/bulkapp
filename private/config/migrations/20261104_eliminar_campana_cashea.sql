-- Retiro de la campaña CASHEA (servicios.id = 2) y de sus catálogos.
-- Estado: APLICADA (2026-11-04). Se ejecutó a mano sobre la BD, después de 20261103_eliminar_cashea.sql.
--
-- Verificado sobre la BD (solo lectura) antes de escribir esto:
--   - users.servicio_id = 2 y usuario_servicios con servicio_id = 2: 0 filas.
--   - gestion, resultados, clientes, formulario_servicios y formulario_envios con servicio 2: 0 filas.
--   - gestion no referencia efectivo 12/13 ni los noefectivo de servicio 2.
--
-- Orden: primero los catálogos (efectivo tiene FK a servicios) y luego la campaña.
-- La última sentencia solo borra la campaña si no queda ninguna referencia.

START TRANSACTION;

DELETE FROM subcontacto
 WHERE efectivo_id IN (SELECT id FROM efectivo WHERE servicio_id = 2);

DELETE FROM efectivo  WHERE servicio_id = 2;
DELETE FROM noefectivo WHERE servicio_id = 2;

DELETE FROM servicios
 WHERE id = 2
   AND NOT EXISTS (SELECT 1 FROM users u WHERE u.servicio_id = 2)
   AND NOT EXISTS (SELECT 1 FROM usuario_servicios us WHERE us.servicio_id = 2)
   AND NOT EXISTS (SELECT 1 FROM clientes c WHERE c.servicio_id = 2)
   AND NOT EXISTS (SELECT 1 FROM gestion g WHERE g.servicio_id = '2')
   AND NOT EXISTS (SELECT 1 FROM resultados r WHERE r.servicio_id = 2)
   AND NOT EXISTS (SELECT 1 FROM formulario_servicios fs WHERE fs.servicio_id = 2)
   AND NOT EXISTS (SELECT 1 FROM formulario_envios fe WHERE fe.servicio_id = 2);

-- Revisar antes del COMMIT: la campaña 2 debe haber desaparecido de `servicios`.
COMMIT;
