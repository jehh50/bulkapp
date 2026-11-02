-- Constructor de formularios — Fase 3 (runtime de captura).
--
-- 1) formulario_carga_filas.clave: valor de la columna de búsqueda de cada fila,
--    indexado por carga, para que la búsqueda del operador no recorra el JSON.
--    Se rellena para las cargas existentes a partir de mapping.busqueda.
-- 2) formulario_envios: un formulario dinámico completado por un operador.
--    - fila_id UNIQUE: un envío por registro de la BD (el registro queda "atendido").
--      Los envíos sin BD llevan fila_id NULL (no cuentan para la unicidad).
--    - registro: foto de los datos de la card al capturar (la reportería no depende
--      de que la carga siga existiendo).
--    - Sin cascade hacia formularios/versiones: los envíos son datos a conservar.
--
-- DEPENDE de 20261029_constructor_formularios.sql y 20261030_constructor_carga_filas.sql.
-- Motor: MariaDB 10.11. Charset utf8mb4. No toca el esquema bancario.

ALTER TABLE formulario_carga_filas
  ADD COLUMN IF NOT EXISTS clave VARCHAR(191) NULL AFTER carga_id,
  ADD KEY IF NOT EXISTS idx_carga_clave (carga_id, clave);

UPDATE formulario_carga_filas f
  JOIN formulario_cargas c ON c.id = f.carga_id
   SET f.clave = LEFT(JSON_UNQUOTE(JSON_EXTRACT(f.fila, CONCAT('$.', JSON_UNQUOTE(JSON_EXTRACT(c.mapping, '$.busqueda'))))), 191)
 WHERE f.clave IS NULL
   AND JSON_EXTRACT(c.mapping, '$.busqueda') IS NOT NULL;

CREATE TABLE IF NOT EXISTS formulario_envios (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED    NOT NULL,
  version_id    INT UNSIGNED    NOT NULL,          -- congela el esquema usado
  servicio_id   INT             NOT NULL,          -- DENORMALIZADO: reportes sin join extra
  carga_id      INT UNSIGNED    NULL,              -- BD vigente contra la que se capturó
  fila_id       BIGINT UNSIGNED NULL,              -- registro de la BD (NULL si no hay BD)
  registro      JSON            NULL,              -- foto de la card { k: valor }
  valores       JSON            NOT NULL,          -- { k: valor } del formulario
  user_id       INT             NULL,              -- operador que capturó
  fecha         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_env_fila (fila_id),
  KEY idx_env_servicio (servicio_id, fecha),
  KEY idx_env_form (formulario_id, fecha),
  KEY idx_env_version (version_id),
  CONSTRAINT fk_env_form    FOREIGN KEY (formulario_id) REFERENCES formularios(id),
  CONSTRAINT fk_env_version FOREIGN KEY (version_id)    REFERENCES formulario_versiones(id),
  CONSTRAINT fk_env_carga   FOREIGN KEY (carga_id)      REFERENCES formulario_cargas(id) ON DELETE SET NULL,
  CONSTRAINT fk_env_fila    FOREIGN KEY (fila_id)       REFERENCES formulario_carga_filas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
