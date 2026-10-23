# Convenciones de código — BulkApp

**Última actualización:** 2026-10-21

Estas son las convenciones **observadas** en el código. Al modificar el proyecto, seguí el
estilo del archivo que estés tocando para mantener consistencia.

## Routing y controladores

- El acceso es siempre por `index.php?view={modulo}` (+ `&mode={accion}`).
- Un controlador por módulo: `private/controllers/{modulo}Controller.php`.
- `index.php` valida `view` contra una **lista blanca** de módulos y devuelve 404 si no
  coincide; además exige token CSRF en las peticiones POST.
- El controlador abre con la guarda de sesión/rol y luego un `switch ($_GET['mode'])`:

  ```php
  include(PUBLIC_DIR . 'general/session.php');
  // No usar empty($_SESSION): la página de login crea el token CSRF y deja
  // la sesión no vacía aunque el usuario todavía no se haya autenticado.
  security_requerir_autenticacion(); // o security_requerir_rol([...])
  include_once(MODEL_DIR . '{modulo}Model.php');
  $conn = new database();
  if (isset($_GET['mode'])) {
    try {
      switch ($_GET['mode']) {
        case 'index': /* render de vista */ break;
        case 'registro': /* POST */ break;
        // AJAX: echo json_encode($json);
      }
    } catch (Throwable $e) {
      app_log('error', 'Error en el controlador: ' . $e->getMessage(), array(/* ... */));
      app_responder_error();
    }
  }
  ```

- **Todo controlador** envuelve su lógica en `try/catch (Throwable)` y delega el manejo
  del error a `app_log()` + `app_responder_error()`.
- **Páginas**: incluir `header.php` + `navbar.php`, luego la vista, luego `footer.php`,
  usando las constantes `PUBLIC_DIR` / `HTML_DIR`.
- **Respuestas AJAX**: construir `$json` y terminar con `echo json_encode($json);` (sin HTML).

## Modelos y acceso a datos

- Cada modelo declara una clase llamada **`database`** que envuelve `Conexion`:

  ```php
  class database {
    private $db;
    public function __construct() { $this->db = new Conexion(); }
    public function buscaCliente(string $dni, int $servicio): array {
      return $this->db->selectQuery(
        "SELECT * FROM clientes WHERE identificacion = ? AND servicio_id = ?",
        [$dni, $servicio], 'ss');
    }
  }
  ```

- **Usar SIEMPRE `selectQuery()` / `preparedQuery()` con placeholders `?`** y la cadena de
  tipos (`'s'`, `'ss'`, …). No concatenar variables en el SQL.
- Para cláusulas `IN (...)` dinámicas, generar los placeholders y pasar el array como params:

  ```php
  $in = '(' . implode(',', array_fill(0, count($ids), '?')) . ')';
  $this->db->selectQuery("... WHERE g.cliente_id IN $in ...", $ids, str_repeat('s', count($ids)));
  ```
- Los métodos de los modelos **declaran tipos** en parámetros y valor de retorno
  (`array`, `bool`, `int`, `?string`, `?int`…). Cuando el valor puede ser `false` o `null`
  según el caso, se deja el tipo sin declarar y se documenta con `@return`.

## Constantes de dominio

- No escribir literales de negocio en el código. Usar la clase `Constantes`
  (`private/config/constantes.php`):

  | Constante | Valor | Significado |
  |-----------|-------|-------------|
  | `SERVICIO_BANCARIO` | 1 | Campañas bancarias (Bancamiga, Bancaribe, Banco Activo) |
  | `SERVICIO_CASHEA` | 2 | Cobranza Cashea |
  | `SERVICIO_BANCARIO_ALIAS` | 5 | Alias histórico del servicio bancario |
  | `USUARIO_AGENTE` / `USUARIO_ADMINISTRADOR` / `USUARIO_SUPERVISOR` | 1 / 2 / 3 | Tipos de usuario |
  | `ESTADO_NO_EFECTIVO` / `ESTADO_EFECTIVO` | 3 / 4 | Estado de la gestión |
  | `ESTADO_ATENDIDO` | 7 | Cliente atendido / sin deuda |
  | `ESTADO_COMPROMISO_PAGO` / `ESTADO_PAGO_ABONADO` | 9 / 12 | Estados de Cashea |
  | `REGISTRO_ACTIVO` / `REGISTRO_INACTIVO` | 2 / 1 | Alta/baja de productos, servicios y usuarios |
  | `PLAN_PAGO_PERSONALIZADO` | 3 | Plan de pago "a medida" en Cashea |

- Helpers útiles: `Constantes::servicioBase($id)` (normaliza 5 → 1),
  `Constantes::esServicioBancario($id)`, `Constantes::esServicioCashea($id)`,
  `Constantes::admitePago($subContacto)`.

## Validación de entrada (servidor)

- Todo `$_GET` / `$_POST` se valida antes de usarse, con los helpers de
  `private/config/security.php`: `input_string()`, `input_int()`, `input_enum()`,
  `input_date()`, `input_email()`, `input_letters()`, `input_digits()`, `input_pan()`,
  `input_phone()`, `input_amount()`, `input_int_array()`.
- La validación de cliente (`public/js/fieldsValidation.js`) es solo una ayuda visual:
  **el servidor vuelve a validar**.

## Errores y logging

- `private/lib/errors.php` registra los handlers globales de PHP: los avisos van al log y
  las excepciones no controladas devuelven un mensaje genérico al usuario.
- Para registrar eventos usar `app_log($nivel, $mensaje, $contexto)` con niveles
  `debug`, `info`, `warning`, `error`, `critical`. **Nunca** usar `print_r()`, `var_dump()`
  ni `echo` con datos de depuración.
- Los errores de base de datos se registran en `Conexion` y se lanzan como
  `RuntimeException` con un mensaje genérico: el detalle real nunca llega al cliente.

## Assets y cache-busting

- Toda referencia a un CSS/JS local se hace con el helper `asset()`:

  ```php
  <script src="<?php echo asset('public/js/formulario.js'); ?>"></script>
  ```

  El helper añade `?v={hash del contenido}`: el navegador sólo descarga la versión nueva
  cuando el archivo cambia.

- Librerías frontend **locales** en `public/` (sin CDN): Bootstrap 5.3.8, Bootstrap Icons
  1.11.3, jQuery 3.7.1, DataTables. No se agregan dependencias nuevas sin bajar el
  archivo a `public/`.

## Pruebas

- Las pruebas unitarias viven en `tests/unit/` y se ejecutan sin Composer:

  ```bash
  php tests/run.php     # 40 pruebas
  php tests/lint.php    # sintaxis de todos los archivos PHP
  ```

- Cada caso se registra con `test('nombre', function () { ... })` y usa las aserciones
  `assert_igual()`, `assert_verdadero()`, `assert_falso()`, `assert_contiene()`,
  `assert_es_array()`, `assert_instancia()`, `assert_lanza()`.
- Lo que se pueda probar sin base de datos (validación, armado de datos, logger, assets)
  se prueba así; la lógica de negocio se aísla en funciones puras (por ejemplo
  `private/helpers/formularioRegistro.php`) para poder verificarla.

## Validación de formularios (cliente)

Archivo central: `public/js/fieldsValidation.js`. Se conecta por atributos inline en la vista:

| Campo | Handler | Regla |
|-------|---------|-------|
| Nombre / Apellido | `oninput="onlyLetters(this)"` | Mayúsculas, solo `[A-Z\s]`. |
| Cédula | `oninput="onlyNumbers(this)"` | Dígitos y un punto decimal `[0-9.]`. |
| Número de cuenta (PAN) | `oninput="onlyPanNumber(this)"` | Dígitos y asteriscos `[0-9*]`, máx. 20. |
| Fecha de nacimiento | `oninput="formatDate(this)"` | Formatea a `dd-mm-yyyy`. |
| Teléfono | clase `.telefono` + `onchange="validaTelf(this)"` | Formato `(0XXX)XXX.XX.XX`. |
| Correo | `oninput="upperCase(this)"` + `onchange="validateEmail(this)"` | Patrón email + dominio no duplicado. |

- El submit del formulario llama a `validateForm()` (formulario bancario) o a
  `validateForm2()` (Cashea), que delegan en `validarPagoCashea()`; la función revalida
  todo con expresiones regulares y muestra `alert()` en caso de error.
- **Convención de IDs**: los campos del formulario de captura usan sufijo `2`
  (`nombre2`, `apellido2`, `cedula2`, `correo2`); el panel de solo-lectura del cliente usa
  los nombres sin sufijo (`name`, `dni`, `account`, …). El número de cuenta es `pan`.

> Al cambiar `fieldsValidation.js` recordá que el navegador **cachea** el archivo: un cambio
> de handler sin refrescar con caché limpia puede producir `ReferenceError` (función no definida)
> y que el filtro "no funcione". Hacé hard-refresh al probar (el `?v=` del helper `asset()`
> se encarga de invalidar la caché cuando el contenido cambia).

## Nomenclatura y estilo

- Idioma **mixto español/inglés** en identificadores (`buscaCliente`, `registroResultados`,
  `paymentDate`, `fullName`). Es la realidad del código; **no se migra de forma masiva**.
  Mantené coherencia local.
- Sin namespaces. Con **tipado gradual**: todo método de clase declara tipos; las vistas y
  los controladores (capa procedural) se tipan a medida que se toquen.
- Referencia de formato: **PSR-12** (indentación de 2 espacios, una clase por archivo,
  llaves de clase en su propia línea, `<?php` sin espacio). Los archivos heredados que no
  cumplen el formato se van ajustando por módulo, no de una sola vez.
- Comentarios y documentación en español.
- Tablas/columnas de BD en español (`clientes`, `gestion`, `efectivo`, `identificacion`).

## Qué evitar (ver [technical-debt.md](technical-debt.md))

- No dejar `print_r()` / `var_dump()` / `console.log()` en el código.
- No usar `md5()` para contraseñas nuevas (el proyecto usa `password_hash()`).
- No concatenar entrada de usuario en SQL.
- No escribir literales de negocio: usar `Constantes`.
- No referenciar assets sin `asset()` (se rompe el cache-busting).
