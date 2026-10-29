# Deuda técnica — BulkApp

**Última actualización:** 2026-10-28

Inventario de deuda técnica y riesgos conocidos, con su estado actual. El detalle de
seguridad vive en [`../ANALISIS.md`](../ANALISIS.md); aquí se resume y se prioriza.

## Leyenda de estado

- 🔴 Abierto — sin mitigar.
- 🟡 Parcial — mitigado en parte / en progreso.
- 🟢 Resuelto — verificado en el código actual.
- ⚪ Cerrado por decisión — no se va a corregir; queda documentado como criterio.

## Seguridad

| # | Tema | Estado | Detalle / ubicación |
|---|------|--------|---------------------|
| 1 | SQL Injection | 🟢 | Todas las consultas usan `selectQuery()`/`preparedQuery()` con placeholders `?`. Las cláusulas `IN` dinámicas (`reportesModel::efectivo`, `reportesModel::servicio`, `formularioModel::customerHistory`) generan los placeholders y los valores viajan como parámetros. Los `IN (3,4,8,…)` que quedan dentro del SQL son literales de estado, no entrada de usuario. |
| 2 | Credenciales hardcodeadas | 🟢 | `private/config/core.php` toma `DB_*` del archivo `.env` (fuera del repo) mediante `private/config/env.php`. Plantilla en `.env.example`. |
| 3 | Inclusión de controlador sin lista blanca | 🟢 | `index.php` valida `view` contra una lista de módulos permitidos y devuelve 404 si no coincide. |
| 4 | Hashing de contraseñas con `md5()` | 🟢 | `security_hash_password()` / `security_password_verify()` usan `password_hash()`. Se mantiene un fallback que acepta hashes `md5()` legacy y los re-hashea en el login (`security_password_verify()` en `private/config/security.php`). Requiere `users.password VARCHAR(255)` para el hash bcrypt; migración en `private/config/migrations/20261021_users_password_varchar255.sql`. El `md5()` de `public/classes/ECCP.class.php` es parte del protocolo de Elastix, no un hash de usuario. |
| 5 | Contraseña por defecto `123456` | 🟢 | `sessionController.php` y `usuariosController.php` ya no la usan; el cambio de contraseña exige mínimo 8 caracteres y rechaza `123456`. |
| 6 | Sin tokens CSRF | 🟢 | `security_require_csrf()` valida el token en cada POST (`index.php` + `public/js/csrf.js` inyecta el header). Refuerzo (2026-10-28): ante token inválido o sesión expirada ya no se responde `403` en texto plano crudo; `security_responder_csrf_invalido()` devuelve JSON (`{response:'false', expired:true, message}`) a las peticiones AJAX y redirige a `index.php?expirado=1` en la navegación normal, donde `private/views/login/index.php` muestra el aviso de sesión expirada. Se conserva el log `security.csrf_failed`. |
| 7 | Sin validación/sanitización en servidor | 🟢 | Helpers `input_string()`, `input_int()`, `input_enum()`, `input_date()`, `input_email()`, `input_letters()`, `input_digits()`, `input_pan()`, `input_phone()`, `input_amount()`, `input_int_array()` en `private/config/security.php`; aplicados en controladores. |
| 8 | Sesiones sin flags seguros | 🟢 | `security_session_start()` fija `httponly`, `samesite`, `secure` (si es HTTPS), `use_strict_mode` y timeout por inactividad; el login regenera el ID de sesión. |
| 9 | Carga de archivos sin validación de MIME | 🟢 | `security_validate_csv_upload()` valida tamaño, extensión, MIME real (`finfo`) y contenido. |
| 10 | Control de acceso por rol | 🟢 | `security_requerir_rol()` exige autenticación y verifica el tipo de usuario contra la lista permitida; registra `security.access_denied` y responde `403`. Refuerzo (2026-10-28): ya no emite `403` en texto plano crudo; `security_responder_sin_permiso()` devuelve una página con modal "Acceso restringido" que redirige a `index.php?view=formulario&mode=index` (3 s) en la navegación normal, y JSON (`{response:'false', denied:true, message, redirect}`) en las peticiones AJAX. |

## Código de depuración en producción

| Archivo | Línea | Qué | Estado |
|---------|-------|-----|--------|
| `private/controllers/csvController.php` | 137 | `print_r($_POST);` | 🟢 Eliminado |
| `private/controllers/configuracionController.php` | 145 | `var_dump($registros);` | 🟢 Eliminado (incluido el comentario `// var_dump($productos);`) |

Verificación adicional (2026-10-21):

- No quedan `print_r()`, `var_dump()` ni `var_export()` en el código de aplicación; los
  únicos `error_log()` están dentro del propio logger, como respaldo.
- Se eliminó el eco de depuración de `configuracionModel::listarTemporal()`
  (`Total registros …`) y los `console.log()` de `fieldsValidation.js` y `download.js`.
- Se eliminó el código muerto `public/js/formularioRegistro.js` (era el único que
  enviaba el campo huérfano `cuenta2`).

Verificación adicional (2026-10-28):

- Se reconfirmó que no quedan `print_r()`, `var_dump()`, `var_export()` ni `console.log()`
  en el código de aplicación de `private/` ni `public/js/`. La única coincidencia de
  `console.log` está dentro de la librería vendor minificada
  `public/js/jquery.dataTables.min.js` (DataTables), no es depuración propia.

## Calidad / mantenibilidad

| Tema | Estado | Detalle |
|------|--------|---------|
| Código duplicado | 🟢 | `validateForm2()` y `validateFormCashea()` (idénticas) se unificaron en una sola implementación, `validarPagoCashea()` en `public/js/fieldsValidation.js`; ambas funciones quedan como alias. |
| Campo huérfano `cuenta2` | 🟢 | El campo real es `pan`. El único archivo que enviaba `cuenta2` (`public/js/formularioRegistro.js`) era código muerto y se eliminó; el servidor nunca leyó ese campo. |
| Métodos largos sin separación | 🟢 | El caso `registro` de `formularioController.php` se extrajo a `private/helpers/formularioRegistro.php`: funciones puras para armar/validar los datos (`formulario_registro_datos*`) y funciones de escritura (`formulario_registro_guardar*`). El controlador quedó con un `case` corto. |
| Valores mágicos | 🟢 | `private/config/constantes.php` define la clase `Constantes` (servicios, tipos de usuario, estados, productos, subcontactos con pago). Se usa en controladores, modelos y vistas en lugar de `1`, `2`, `5`, `7`, `9`, `12`… |
| Sin manejo de errores | 🟢 | `private/lib/errors.php` registra `set_error_handler()` y `set_exception_handler()`: los avisos van al log y las excepciones no controladas devuelven un mensaje genérico (nunca el detalle de mysqli). Además, los 11 controladores envuelven su lógica en `try/catch`. `Conexion` registra el error real y lanza `RuntimeException` con mensaje genérico. |
| Sin logging | 🟢 | `private/lib/logger.php` (clase `AppLogger` + helper `app_log()`): JSON Lines, niveles (`debug`…`critical`), `request_id`, rotación por tamaño y escritura en `LOG_PATH` (por defecto `storage/logs/app.log`). Configurable por `.env` (`LOG_ENABLED`, `LOG_LEVEL`, `LOG_FORMAT`, `LOG_PATH`, `LOG_MAX_BYTES`). |
| Sin tests | 🟢 | Suite propia en `tests/` (sin Composer): `php tests/run.php` (40 pruebas unitarias) y `php tests/lint.php` (sintaxis de los 85 archivos PHP). Cubre constantes, validación de entrada, armado del registro, despacho contra un modelo simulado, logger y helper de assets. |
| Nomenclatura mixta ES/EN | ⚪ | Criterio consciente: se documenta en [`conventions.md`](conventions.md) y no se va a migrar de forma masiva. La coherencia local se mantiene. |
| Sin type hints / PSR-12 | 🟡 | Toda la capa de clases declara tipos: `Conexion`, los 10 modelos, `Constantes`, `AppLogger` y los helpers. Estandarizado PSR-12 (indentación de 2 espacios, llaves de clase, `declare` sin espacio) documentado en [`conventions.md`](conventions.md). Pendiente: tipar las funciones de las vistas y de los controladores (capa procedural), que quedan fuera del alcance de la migración actual. |
| Dependencias frontend antiguas | 🟢 | jQuery 3.2.1 → **3.7.1** y Bootstrap 3.3.7 → **Bootstrap 5.3.8** (ambos locales en `public/`, sin CDN). Las 22 vistas y barras de navegación que aún usaban marcado de BS3 se migraron (panel→card, form-group→mb-3, `data-toggle`→`data-bs-toggle`, `col-xs-*`→`col-*`, `btn-default`→`btn-outline-secondary`, glyphicons→Bootstrap Icons 1.11.3). Se borraron los archivos de BS3, jQuery 1.11/3.2.1, los shims de IE y `npm.js`. |
| Sin cache-busting de assets | 🟢 | Helper `asset()` en `private/lib/assets.php`: añade `?v={hash del contenido}` a cada CSS/JS. Aplicado en los layouts (`header.php`, `nheader.php`, `footer.php`, `nfooter.php`) y en todas las vistas. El hash cambia solo cuando cambia el archivo, así que no se fuerza la recarga en cada visita. |

## Hallazgos nuevos (2026-10-21)

| Tema | Estado | Detalle |
|------|--------|---------|
| Módulos `editar` y `csv` con SQL de un esquema anterior | 🔴 | `private/model/editarModel.php` consulta columnas que ya no existen (`cod_servicio`, `id_efectivo`, tablas `resultados2`/`resultados3`). Ambos módulos no están enlazados desde la interfaz (solo son alcanzables por URL) y hoy devuelven un error 500 controlado. Hay que rehacerlos contra el esquema actual o retirarlos. |
| Avisos deprecación en el módulo `csv` | 🟡 | `csvController.php`/`csvModel.php` llaman `substr()` sobre columnas que pueden venir `NULL` (teléfonos vacíos). Ahora quedan registrados en el log (`storage/logs/app.log`) en lugar de mostrarse; conviene usar `substr((string) $valor, …)`. |

## Hallazgos nuevos (2026-10-28)

| Tema | Estado | Detalle |
|------|--------|---------|
| `is_active` de Cashea se reinicia por lote | 🔴 | En `carga_archivoModel::guardarRegistrosBatch()`, para el servicio **Cashea** el `UPDATE cashea_customers SET is_active = 0 WHERE is_active = 1` se ejecuta al inicio de CADA lote (líneas ~40 y ~68-70), y el controlador `carga_archivoController.php` llama al método por cada lote de 1000 filas. Para archivos Cashea de más de 1000 filas, cada lote desactiva (`is_active = 0`) los registros recién insertados por los lotes anteriores, dejando activo solo el último lote. El `UPDATE` debería ejecutarse UNA sola vez, antes de insertar. **Decisión (2026-10-28):** no se corrige en esta sesión porque el módulo Cashea no está en uso según el responsable; queda documentado para corregirlo si se reactiva el servicio. |
| Robustez del flujo `registro` de carga de BBDD | 🟢 | Corregido en `carga_archivoController.php` + `carga_archivoModel.php`: (1) el flujo `registro` ya no redirige siempre a `exito`; según el resultado real redirige a `mensaje=errorProceso` (si algún lote falló), `mensaje=sinDatos` (0 filas insertadas) o `mensaje=exito`; (2) se agregó guarda contra CSV vacío/sin separador (`fgetcsv` devuelve `false` o una sola columna → `mensaje=errorFormato`); (3) la codificación se detecta sobre una muestra de 1 MB (`fread($fp, 1048576)`) en vez de releer todo el archivo; (4) se eliminó el parámetro muerto `$paramTypes` de `guardarRegistrosBatch()` (ahora `guardarRegistrosBatch(array $batch, int $servicio)`). |

## Recomendaciones prioritarias (orden sugerido)

1. Resolver o retirar los módulos legacy `editar` y `csv` (SQL de un esquema anterior).
2. Tipar por completo la capa procedural (vistas y controladores) y adoptar PSR-12 de forma
   uniforme en todo el repositorio.
3. Corregir los `substr()` sobre valores `NULL` del módulo `csv`.
4. Extender la suite de `tests/` con pruebas de integración sobre la base de datos.
5. Revisar la tabla de `status` y `efectivo` para documentar el significado de cada `id`
   que sigue usándose en el SQL.
