-- Retiro del flujo Cashea (cobranza).
-- Estado: APLICADA (2026-11-04). El repositorio no la ejecuta; se aplica a mano sobre la BD.
--
-- Las tres tablas están vacías (verificado el 2026-11-03): no se pierde ningún dato.

DROP TABLE IF EXISTS results_cashea;        -- tenía FK a status
DROP TABLE IF EXISTS cashea_customers;
DROP TABLE IF EXISTS carga_temportal_cashea;

-- La campaña 2 y sus catálogos se retiran en 20261104_eliminar_campana_cashea.sql.

-- PENDIENTE DE DECISIÓN (no incluido en esta migración):
--
-- 1. Campaña servicios.id = 2 (CASHEA, inactiva):
--    - 82 usuarios activos la tienen como campaña principal (users.servicio_id = 2)
--      y 121 la tienen asignada en usuario_servicios. Hay que reasignarlos antes de borrarla.
--    - efectivo tiene 2 filas y noefectivo 7 con servicio_id = 2 (FK a servicios en efectivo).
--    - No hay filas en clientes, gestion, resultados ni formulario_envios con servicio_id = 2.
--
-- 2. Estados status 9 (compromiso de pago), 10 (no pago) y 12 (pago parcial): se conservan
--    por decisión del negocio (banca podría necesitarlos).
