-- Constructor de formularios dinámicos — Fase 0 (cimientos).
--
-- 1) Alinea el catálogo de roles con el swap de constantes:
--      1 = Operador, 2 = Supervisor, 3 = Administrador (nuevo).
-- 2) Crea las tablas base del Builder (1–4 de docs/constructor-plan.md §4).
--
-- La tabla de captura `formulario_envios` (tabla 5, Fase 3) NO se crea aquí.
-- Motor: MariaDB 10.11 (soporta el tipo JSON). Charset utf8mb4.

-- ---------------------------------------------------------------------------
-- Roles. Verificar que `usertypes` tenga (al menos) las columnas id y name;
-- ajustar la lista de columnas del INSERT si el esquema real tiene más.
-- Los valores de usertype_id de los usuarios existentes NO cambian.
-- ---------------------------------------------------------------------------
UPDATE usertypes SET name = 'Operador'   WHERE id = 1;
UPDATE usertypes SET name = 'Supervisor' WHERE id = 2;
INSERT INTO usertypes (id, name) VALUES (3, 'Administrador')
  ON DUPLICATE KEY UPDATE name = VALUES(name);

-- ---------------------------------------------------------------------------
-- 1) Cabecera / identidad del formulario
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS formularios (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre       VARCHAR(150) NOT NULL,
  status_id    INT          NOT NULL DEFAULT 1,            -- 1=Inactivo, 2=Activo (REGISTRO_*)
  btn_texto    VARCHAR(80)  NOT NULL DEFAULT 'Guardar',
  eliminado    TINYINT(1)   NOT NULL DEFAULT 0,            -- borrado lógico
  eliminado_en DATETIME     NULL,
  user_id      INT          NULL,                          -- creador
  fecha        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_estado (status_id, eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- 2) Definición versionada: el JSON de campos vive aquí (una fila por guardado)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS formulario_versiones (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED NOT NULL,
  version       INT          NOT NULL,                     -- correlativo por formulario: 1,2,3...
  definicion    JSON         NOT NULL,                     -- documento de campos (plan §4.1)
  nota          VARCHAR(255) NULL,
  vigente       TINYINT(1)   NOT NULL DEFAULT 0,           -- 1 = versión en uso (permite rollback)
  user_id       INT          NULL,
  fecha         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_form_version (formulario_id, version),
  KEY idx_vigente (formulario_id, vigente),
  CONSTRAINT fk_ver_form FOREIGN KEY (formulario_id)
    REFERENCES formularios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- 3) Asignación formulario <-> servicio (regla 1-activo-por-servicio en app)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS formulario_servicios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED NOT NULL,
  servicio_id   INT          NOT NULL,                     -- -> servicios.id (FK blanda)
  PRIMARY KEY (id),
  UNIQUE KEY uq_form_serv (formulario_id, servicio_id),
  KEY idx_servicio (servicio_id),
  CONSTRAINT fk_fs_form FOREIGN KEY (formulario_id)
    REFERENCES formularios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- 4) Base de datos por formulario: historial de cargas + mapeo + vigente
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS formulario_cargas (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED NOT NULL,
  archivo       VARCHAR(255) NOT NULL,                     -- nombre original
  filas         INT          NOT NULL DEFAULT 0,
  mapping       JSON         NOT NULL,                     -- columna CSV -> clave k (plan §4.1)
  vigente       TINYINT(1)   NOT NULL DEFAULT 0,           -- 1 = BD vigente del formulario
  user_id       INT          NULL,
  fecha         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_carga_vigente (formulario_id, vigente),
  CONSTRAINT fk_carga_form FOREIGN KEY (formulario_id)
    REFERENCES formularios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
