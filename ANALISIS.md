# Análisis del Proyecto: BulkApp

**Fecha de análisis:** 2026-08-18  
**Última revisión:** 2026-10-21 (rama `fix/validation-form`)  
**Ruta:** `/var/www/html/bulkapp/`

> Documentación complementaria en [`docs/`](docs/): [context.md](docs/context.md),
> [architecture.md](docs/architecture.md), [conventions.md](docs/conventions.md),
> [technical-debt.md](docs/technical-debt.md).

---

## 1. Descripción General

**BulkApp** es una aplicación CRM/Call Center para la gestión de contactos y ventas en masa. Permite registrar resultados de llamadas, cargar bases de datos de clientes desde archivos CSV, generar reportes y producir archivos XML para integración con sistemas externos. Integra sistemas VoIP (Asterisk) y plataformas de call center (Elastix).

---

## 2. Tecnologías

| Capa | Tecnología |
|------|------------|
| Backend | PHP (MVC sin framework) |
| Base de datos | MySQL / MariaDB (UTF-8mb4) |
| Frontend | Bootstrap 5.3.8 + Bootstrap Icons 1.11.3 + jQuery 3.7.1 + DataTables (todo local en `public/`)
| Telefonía | Asterisk (AMI) + Elastix (ECCP) |
| Generación de docs | XML con schema XSD |

**Sin gestor de dependencias** (ni Composer, ni npm). Las librerías están incluidas manualmente.

---

## 3. Arquitectura

### Patrón: MVC simplificado con Front Controller

```
GET index.php?view={modulo}
    └─> private/controllers/{modulo}Controller.php
            └─> private/model/{modulo}Model.php
                    └─> private/views/{modulo}/
```

### Estructura de directorios

```
bulkapp/
├── index.php                  # Router (Front Controller)
├── private/
│   ├── config/
│   │   ├── core.php           # Constantes globales y credenciales
│   │   └── database.php       # Clase Conexion (extends mysqli)
│   ├── controllers/           # 8 controladores
│   ├── model/                 # 8 modelos
│   └── views/                 # 33 vistas HTML/PHP
└── public/
    ├── classes/               # phpAMI.php, ECCP.class.php, user.php
    ├── css/                   # Bootstrap 5.3.8, Bootstrap Icons, estilos propios
    ├── js/                    # jQuery 3.7.1, DataTables, Bootstrap 5.3.8 bundle
    └── general/               # header.php, navbar.php, footer.php, session.php
```

### Módulos principales

| Módulo | Controlador | Descripción |
|--------|-------------|-------------|
| Formulario | `formularioController.php` | Captura de gestión de clientes |
| Carga CSV | `carga_archivoController.php` | Importación masiva (lotes de 1000) |
| Reportes | `reportesController.php` | 8 tipos de reportes de ventas |
| Usuarios | `usuariosController.php` | ABM de usuarios con roles |
| XML | `xmlController.php` | Exportación de ventas a XML/XSD |
| Configuración | `configuracionController.php` | Productos y parámetros |

### Roles de usuario

- `tipo = 1` → Agente
- `tipo = 2` → Administrador / Supervisor

---

## 4. Vulnerabilidades de Seguridad

### Estado actual (revisión 2026-10-20)

Los hallazgos críticos y altos del análisis original quedaron mitigados en el código:

| Punto original | Estado | Evidencia |
|----------------|--------|-----------|
| SQL Injection | 🟢 Mitigado | Modelos migrados a `preparedQuery()`/`selectQuery()`; cláusulas `IN` con placeholders y arrays de enteros; `updateQuotes` sin interpolación. |
| Credenciales hardcodeadas | 🟢 Mitigado | DB en `.env` vía `private/config/core.php`; `user.php` y `phpAMI.php` leen variables de entorno; `.env` ignorado por Git. |
| Inclusión de controlador sin lista blanca | 🟢 Mitigado | `index.php` valida `view` contra allowlist y responde 404. |
| Hashing débil | 🟢 Mitigado | `password_hash()`/`password_verify()`; migración transparente de md5 legacy en login. |
| Contraseña por defecto `123456` | 🟢 Mitigado | Eliminada; reset administrativo genera contraseña temporal aleatoria. |
| Sin CSRF | 🟢 Mitigado | Token global para POST + `csrf.js`; acciones de configuración migradas a POST. |
| Sin validación de entrada | 🟢 Mitigado | Helpers `input_*` aplicados en controladores principales. |
| Autorización insuficiente | 🟡 Parcial | `usuariosController` exige `usertype_id = 2`; falta replicar el guard en otros módulos administrativos. |
| Carga de archivos insegura | 🟢 Mitigado | Validación de error, tamaño, extensión, MIME con `finfo` y bytes NUL. |
| Sesiones sin flags seguros | 🟢 Mitigado | `HttpOnly`, `SameSite=Lax`, `Secure` bajo HTTPS, strict mode, timeout y regeneración de ID. |
| Debug en producción | 🟢 Mitigado | `print_r`/`var_dump` eliminados. |
| XSS en exportaciones | 🟢 Mitigado | Datos de clientes escapados en las tablas de descarga; XML generado con valores escapados. |
| Exposición de errores | 🟢 Mitigado | `database.php` registra detalle con `error_log` y devuelve mensaje genérico. |

**Excepción documentada:** `ECCP.class.php` conserva `md5()` porque forma parte del protocolo
de autenticación de Elastix/ECCP, no del almacenamiento de contraseñas de BulkApp.

**Pendiente:** guard de autorización uniforme por módulo (la parte de pruebas automatizadas y
el logging estructurado se cerraron el 2026-10-21, ver secciones 5 y 6).

## 5. Calidad de Código

### Positivo

- Separación clara en capas MVC.
- Uso consistente de `preparedQuery()` y `selectQuery()` para consultas seguras.
- Componentes de vista reutilizables (`header.php`, `navbar.php`).
- Capa de infraestructura común: `security.php`, `constantes.php`, `lib/logger.php`,
  `lib/errors.php`, `lib/assets.php` y `helpers/formularioRegistro.php`.
- Suite de pruebas propia (`tests/`) y verificación de sintaxis de todo el proyecto.

### Estado tras la revisión 2026-10-21

| Categoría | Estado | Detalle |
|-----------|--------|---------|
| **Duplicación** | 🟢 | `validateForm2()` y `validateFormCashea()` se unificaron en `validarPagoCashea()` (`public/js/fieldsValidation.js`). El patrón `while($data = $db->recorrer($sql))` quedó obsoleto: los modelos usan `selectQuery()`. |
| **Métodos largos** | 🟢 | El caso `'registro'` de `formularioController.php` (87 líneas) se extrajo a `private/helpers/formularioRegistro.php`, con funciones puras de armado/validación y funciones de escritura separadas. |
| **Valores mágicos** | 🟢 | Clase `Constantes` (`private/config/constantes.php`) para servicios, tipos de usuario, estados, productos y subcontactos; aplicada en controladores, modelos y vistas. |
| **Manejo de errores** | 🟢 | Handlers globales (`private/lib/errors.php`) + `try/catch` en los 11 controladores + `app_responder_error()` (mensaje genérico). `Conexion` registra el detalle y lanza `RuntimeException` genérica. |
| **Logging** | 🟢 | `AppLogger` (`private/lib/logger.php`): JSON Lines, niveles, `request_id`, rotación y salida en `storage/logs/app.log` (`LOG_*` en `.env`). |
| **Tests** | 🟢 | `php tests/run.php` → 40 pruebas unitarias; `php tests/lint.php` → sintaxis de 85 archivos PHP. |
| **Type hints / PSR-12** | 🟡 | Toda la capa de clases (modelos, `Conexion`, `Constantes`, logger, helpers) declara tipos. Queda tipar la capa procedural (vistas y controladores). |
| **Nomenclatura mixta** | ⚪ | Criterio documentado: no se migra de forma masiva (ver `docs/conventions.md`). |
| **Dependencias frontend** | 🟢 | jQuery 3.7.1 y Bootstrap 5.3.8 locales; vistas migradas de BS3 a BS5; Bootstrap Icons 1.11.3. |
| **Cache-busting** | 🟢 | Helper `asset()` (hash del contenido) aplicado en layouts y vistas. |

---

## 6. Deuda Técnica

- **~3.400 líneas** de controllers + models + 33 vistas PHP.
- Sin namespaces, interfaces ni traits.
- Logging estructurado implementado (JSON Lines con `request_id`).
- Variables de entorno implementadas con un cargador propio (sin Composer).
- Documentación vigente en `docs/` (`context.md`, `architecture.md`, `conventions.md`,
  `technical-debt.md`).
- Módulos legacy `editar` y `csv` con SQL de un esquema de base de datos anterior:
  no están enlazados en el menú y fallan con un error 500 controlado.

---

## 7. Recomendaciones Prioritarias

1. Rehacer o retirar los módulos legacy `editar` y `csv` (SQL de un esquema anterior).
2. Extender el guard de autorización (`usertype_id`) a configuración, reportes, XML y carga de archivos.
3. Mantener documentada la excepción de md5 en ECCP/Elastix.
4. Tipar la capa procedural (vistas y controladores) y unificar el formato en PSR-12.
5. Agregar pruebas de integración sobre la base de datos (hoy la suite es unitaria).


## 8. Trabajo de validación del formulario (rama `fix/validation-form`)

Endurecimiento de la validación en el formulario de captura (`private/views/formulario/index.php`
+ `public/js/fieldsValidation.js`):

- Nombre/Apellido: `onlyLetters` (mayúsculas, solo letras y espacios).
- Cédula: `onlyNumbers` (dígitos).
- **Número de cuenta (PAN)**: nueva función `onlyPan` (solo dígitos y asteriscos, máx. 20) y
  validación de envío `/^[0-9*]{20}$/`. Antes usaba `onlyNumbers`.
- Fecha de nacimiento: `formatDate` a `dd-mm-yyyy` + cálculo de edad (18–64).
- Teléfono: formateo `(0XXX)XXX.XX.XX` vía clase `.telefono`.
- Correo: patrón de email + detección de dominio duplicado (`dominioDuplicado`).

> **Nota operativa:** `fieldsValidation.js` se cachea en el navegador. Tras cambiar un handler,
> probar con caché limpia; de lo contrario puede verse un `ReferenceError` (función no definida)
> y aparentar que el filtro de un campo "no funciona". Se evaluó versionar los `<script>` con
> `?v=` y se descartó para no forzar recarga en cada visita.

### Deuda detectada en esta revisión (cerrada el 2026-10-21)

- `validateForm2()` y `validateFormCashea()` en `fieldsValidation.js` eran idénticas → ahora
  comparten la implementación `validarPagoCashea()`.
- Campo `cuenta2` referenciado en `formulario.js` / `formularioRegistro.js` pero inexistente en
  la vista (el campo real es `pan`) → `formularioRegistro.js` era código muerto y se eliminó.
- `print_r($_POST)` y `var_dump` eliminados.
- Capa de calidad cerrada: logging estructurado, manejo uniforme de errores, constantes de
  dominio, cache-busting de assets, suite de pruebas y actualización de jQuery/Bootstrap.