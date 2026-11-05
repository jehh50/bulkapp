-- Constructor de formularios — Fase 2 (base de datos por formulario).
--
-- Almacena las filas del dataset cargado (CSV/XLSX) por cada carga. Cada fila
-- es un documento JSON { clave_k: valor, ... } según el mapeo columna→campo.
--
-- DEPENDE de 20261029_constructor_formularios.sql (requiere formulario_cargas).
-- Aplicar DESPUÉS de esa. Motor: MariaDB 10.11. Charset utf8mb4.

CREATE TABLE IF NOT EXISTS formulario_carga_filas (
  id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  carga_id INT UNSIGNED    NOT NULL,
  fila     JSON            NOT NULL,          -- { k: valor, ... } según el mapeo
  PRIMARY KEY (id),
  KEY idx_carga (carga_id),
  CONSTRAINT fk_filas_carga FOREIGN KEY (carga_id)
    REFERENCES formulario_cargas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
