-- Amplía users.password para almacenar hashes de password_hash().
-- Los hashes bcrypt ocupan 60 caracteres; la columna legacy era VARCHAR(50),
-- lo que provocaba "Data too long for column 'password'" al migrar un md5
-- durante el login (HTTP 500 y sesión sin crear).

ALTER TABLE users
  MODIFY COLUMN password VARCHAR(255) NOT NULL;
