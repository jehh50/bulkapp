-- Constructor de formularios — catálogo de expresiones de validación.
--
-- Reemplaza el input libre «Regex» del editor por un select con opciones predefinidas.
-- Cada campo guarda la clave (`expr`); al guardar el formulario el servidor copia
-- `patron` (→ regex) y `transformacion` (→ transform) en la definición de la versión,
-- de modo que una versión publicada no cambia si luego se edita el catálogo.
--
-- - patron: expresión regular SIN delimitadores; NULL si la opción solo transforma.
--   Se escribe sin barras invertidas (clases [0-9], [.], [(]) para no depender del
--   escapado de cadenas de MariaDB.
-- - transformacion: 'mayusculas' (convierte el texto) o 'telefono' (máscara al escribir).
-- - tipos: tipos de campo compatibles, separados por coma.
-- - Los ejemplos se muestran en el modal de ayuda del editor.
--
-- DEPENDE de 20261029_constructor_formularios.sql. Motor: MariaDB 10.11. utf8mb4.
-- No toca el esquema bancario. Idempotente.

CREATE TABLE IF NOT EXISTS formulario_expresiones (
  id               SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  clave            VARCHAR(30)  NOT NULL,
  nombre           VARCHAR(80)  NOT NULL,
  descripcion      VARCHAR(255) NOT NULL,
  patron           VARCHAR(255) NULL,
  transformacion   VARCHAR(20)  NULL,
  tipos            VARCHAR(60)  NOT NULL DEFAULT 'text,textarea',
  ejemplo_valido   VARCHAR(120) NOT NULL,
  ejemplo_invalido VARCHAR(120) NULL,
  orden            SMALLINT     NOT NULL DEFAULT 0,
  status_id        INT          NOT NULL DEFAULT 2,   -- Constantes::REGISTRO_ACTIVO
  PRIMARY KEY (id),
  UNIQUE KEY uq_expr_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO formulario_expresiones
  (clave, nombre, descripcion, patron, transformacion, tipos, ejemplo_valido, ejemplo_invalido, orden)
VALUES
  ('correo', 'Correo electrónico',
   'Exige un correo con usuario, @ y dominio con extensión.',
   '^[A-Za-z0-9._%+-]+@[A-Za-z0-9-]+([.][A-Za-z0-9-]+)*[.][A-Za-z]{2,}$', NULL, 'text',
   'ana.perez@empresa.com', 'ana.perez@empresa', 10),
  ('solo_numeros', 'Solo números',
   'Acepta únicamente dígitos del 0 al 9, sin espacios, puntos ni guiones.',
   '^[0-9]+$', NULL, 'text,textarea',
   '12658457', '12.658.457', 20),
  ('solo_letras', 'Solo letras',
   'Acepta letras (con acentos y ñ) y espacios; rechaza números y símbolos.',
   '^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ ]+$', NULL, 'text,textarea',
   'María José', 'María José 2', 30),
  ('mayusculas', 'Convertir a mayúsculas',
   'No valida: convierte todo el texto a mayúsculas mientras se escribe y al guardar.',
   NULL, 'mayusculas', 'text,textarea',
   'juan pérez → JUAN PÉREZ', NULL, 40),
  ('telefono', 'Teléfono (banca)',
   'Formato de teléfono de banca: (XXXX)XXX-XX-XX. El campo se va formateando al escribir.',
   '^[(][0-9]{4}[)][0-9]{3}-[0-9]{2}-[0-9]{2}$', 'telefono', 'text',
   '(0412)555-12-34', '04125551234', 50)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre), descripcion = VALUES(descripcion), patron = VALUES(patron),
  transformacion = VALUES(transformacion), tipos = VALUES(tipos),
  ejemplo_valido = VALUES(ejemplo_valido), ejemplo_invalido = VALUES(ejemplo_invalido),
  orden = VALUES(orden);
