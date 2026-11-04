-- Clientes (empresas) y campañas — Fase 0.
--
-- Modelo: una EMPRESA (cliente corporativo: Banco Activo, Bancaribe…) tiene N
-- CAMPAÑAS. La campaña es la tabla `servicios` de siempre: se conservan sus ids, así que
-- gestion, resultados, clientes (BBDD de contactos), productos, catálogos, users y el
-- Constructor siguen apuntando a `servicio_id` sin migrar una sola fila histórica.
--
-- 1) empresas: catálogo nuevo de clientes corporativos.
-- 2) servicios (= campaña):
--      empresa_id   FK a empresas (obligatoria).
--      detalle      descripción larga de la campaña (el NOMBRE sigue en `descripcion`,
--                   que usan las vistas, la reportería y el XML — no se renombra).
--      fecha_inicio fecha de inicio de la campaña (se rellena con created_at).
--      meta         meta opcional (entero).
--      tipo_flujo   'bancario' = formulario fijo de banca; 'dinamico' = Constructor.
--                   Lo leen Constantes::esServicioBancario()/servicios()/servicioBase().
-- 3) usuario_servicios: N:M usuario ↔ campaña. users.servicio_id se conserva como la
--    campaña PRINCIPAL (con la que arranca la sesión) y siempre está asignada.
--
-- Datos: Banco Activo (1) y Bancaribe (5) → flujo bancario; servicio 2 → dinámico. `servicios."campo 2"` no se toca.
-- El TRIM de "Bancaribe " no altera la carpeta XML (se calcula quitando espacios).
--
-- Motor: MariaDB 10.11. Charset utf8mb4. Idempotente donde MariaDB lo permite.

CREATE TABLE IF NOT EXISTS empresas (
  id             INT          NOT NULL AUTO_INCREMENT,
  nombre         VARCHAR(100) NOT NULL,
  identificacion VARCHAR(20)  NULL,                -- RIF
  status_id      INT          NOT NULL DEFAULT 2,  -- Constantes::REGISTRO_*
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_empresa_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Una empresa por cada servicio existente (mismo nombre, sin espacios sobrantes).
-- (CHAR_LENGTH: la collation PAD SPACE considera iguales "x" y "x ".)
UPDATE servicios SET descripcion = TRIM(descripcion) WHERE CHAR_LENGTH(descripcion) <> CHAR_LENGTH(TRIM(descripcion));

INSERT IGNORE INTO empresas (nombre, status_id)
  SELECT TRIM(descripcion), status_id FROM servicios ORDER BY id;

ALTER TABLE servicios
  ADD COLUMN IF NOT EXISTS empresa_id   INT          NULL AFTER id,
  ADD COLUMN IF NOT EXISTS detalle      VARCHAR(500) NULL AFTER descripcion,
  ADD COLUMN IF NOT EXISTS fecha_inicio DATE         NULL AFTER detalle,
  ADD COLUMN IF NOT EXISTS meta         INT UNSIGNED NULL AFTER fecha_inicio,
  ADD COLUMN IF NOT EXISTS tipo_flujo   ENUM('bancario','dinamico') NOT NULL DEFAULT 'dinamico' AFTER meta;

UPDATE servicios s
  JOIN empresas e ON e.nombre = TRIM(s.descripcion)
   SET s.empresa_id = e.id
 WHERE s.empresa_id IS NULL;

UPDATE servicios SET fecha_inicio = DATE(created_at) WHERE fecha_inicio IS NULL;

-- Flujo de las campañas existentes (antes fijado en Constantes: 1 y 5 bancarios).
UPDATE servicios SET tipo_flujo = 'bancario' WHERE id IN (1, 5);

ALTER TABLE servicios
  MODIFY empresa_id   INT  NOT NULL,
  MODIFY fecha_inicio DATE NOT NULL,
  ADD KEY IF NOT EXISTS idx_servicio_empresa (empresa_id),
  ADD UNIQUE KEY IF NOT EXISTS uq_servicio_empresa_nombre (empresa_id, descripcion);

ALTER TABLE servicios
  ADD CONSTRAINT fk_servicio_empresa FOREIGN KEY IF NOT EXISTS (empresa_id) REFERENCES empresas (id);

-- Asignación N:M usuario ↔ campaña.
CREATE TABLE IF NOT EXISTS usuario_servicios (
  user_id     INT      NOT NULL,
  servicio_id INT      NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, servicio_id),
  KEY idx_us_servicio (servicio_id),
  CONSTRAINT fk_us_user     FOREIGN KEY (user_id)     REFERENCES users (id)     ON DELETE CASCADE,
  CONSTRAINT fk_us_servicio FOREIGN KEY (servicio_id) REFERENCES servicios (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- Cada usuario queda asignado a su campaña principal actual.
INSERT IGNORE INTO usuario_servicios (user_id, servicio_id)
  SELECT id, servicio_id FROM users;
