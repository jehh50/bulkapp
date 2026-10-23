# Arquitectura — BulkApp

**Última actualización:** 2026-10-21

## Patrón

MVC simplificado con **Front Controller**. No usa framework ni gestor de dependencias
(sin Composer ni npm); las librerías están incluidas manualmente en `public/`.

## Flujo de una petición

```
GET/POST index.php?view={modulo}&mode={accion}
  │
  ├─ index.php                     require core.php → constantes, .env, handlers de error
  │     ├─ security_session_start()
  │     ├─ cabeceras de seguridad (X-Frame-Options, Referrer-Policy, X-Request-Id)
  │     ├─ lista blanca de módulos → 404 si 'view' no está permitido
  │     ├─ security_require_csrf()  (obligatorio en POST)
  │     └─ include controllers/{view}Controller.php
  │                                  (si no hay view → sessionController.php)
  │
  └─ controllers/{modulo}Controller.php
        ├─ include session.php
        ├─ security_requerir_autenticacion() / security_requerir_rol([...])
        │     (valida id + rol; no usa empty($_SESSION) porque el login crea el CSRF)
        ├─ $conn = new database();              // modelo del módulo
        ├─ try { switch($_GET['mode']) { ... } }
        │     ├─ vistas HTML  → include header/navbar + views/{modulo}/*.php + footer
        │     ├─ respuestas AJAX → echo json_encode($json);
        │     └─ errores → app_log() + app_responder_error() (mensaje genérico)
        └─ catch (Throwable $e) { app_log(...); app_responder_error(); }
```

## Capa de datos

Dos clases colaboran:

- **`Conexion`** (`private/config/database.php`) — extiende `mysqli`. Expone:
  - `preparedQuery($sql, $params, $types)` — `prepare` + `bind_param` + `execute`.
  - `selectQuery($sql, $params, $types)` — igual, devuelve array asociativo (`fetch_assoc`).
  - Helpers legacy: `recorrer()`, `rows()`, `liberar()`, `construirQueryDebug()`.
  - Ante un error del motor: registra el detalle en el log y lanza `RuntimeException` con
    un mensaje genérico (el cliente nunca ve el error de mysqli).
- **`database`** (una por modelo, en `private/model/{modulo}Model.php`) — **envuelve** a
  `Conexion` (`$this->db = new Conexion()`) y expone métodos de negocio (`buscaCliente`,
  `registroResultados`, …) **con declaraciones de tipo**.

> Cada `*Model.php` declara su propia clase llamada literalmente `database`. Por eso cada
> controlador incluye un solo modelo y hace `new database()`.

## Servicios transversales

| Archivo | Responsabilidad |
|---------|-----------------|
| `private/config/env.php` | Lee el `.env` (fuera del repo) y expone `env($clave, $porDefecto)`. |
| `private/config/security.php` | Sesiones seguras, CSRF, `password_hash()`/`password_verify()`, validación de entrada (`input_*`) y validación de cargas CSV. |
| `private/config/constantes.php` | Clase `Constantes`: servicios, tipos de usuario, estados y reglas de negocio. |
| `private/lib/logger.php` | `AppLogger` + `app_log()`: log en JSON Lines, niveles, `request_id` y rotación. |
| `private/lib/errors.php` | Handlers globales de error y excepción; respuesta genérica al cliente. |
| `private/lib/assets.php` | `asset($ruta)`: cache-busting por hash del contenido. |
| `private/helpers/formularioRegistro.php` | Funciones puras + de escritura del registro de gestión/venta del formulario. |

## Estructura de directorios

```
bulkapp/
├── index.php                      # Router (Front Controller) con lista blanca y CSRF
├── ANALISIS.md                    # Análisis de seguridad/calidad
├── docs/                          # Esta documentación
├── design/                        # Recursos de diseño (WIP)
├── private/
│   ├── config/
│   │   ├── core.php               # Constantes (HTML_DIR, MODEL_DIR, PUBLIC_DIR, DB_*) + carga del .env
│   │   ├── env.php                # Lectura del .env
│   │   ├── security.php           # Sesiones, CSRF, contraseñas, validación de entrada
│   │   ├── constantes.php         # Constantes de dominio (clase Constantes)
│   │   └── database.php           # Clase Conexion
│   ├── controllers/               # 11 controladores ({modulo}Controller.php)
│   ├── model/                     # 10 modelos (clase `database`)
│   ├── helpers/                   # Lógica de negocio aislada y testeable
│   ├── lib/                       # Logger, manejo de errores y helper de assets
│   └── views/                     # Vistas por módulo + error.html
├── public/
│   ├── classes/                   # phpAMI.php (Asterisk AMI), ECCP.class.php (Elastix), user.php
│   ├── css/                       # bootstrap 5.3.8, bootstrap-icons, estilos propios, datatables
│   ├── fonts/                     # (Bootstrap Icons vive en public/css/fonts)
│   ├── js/                        # jquery 3.7.1, bootstrap.bundle 5.3.8, datatables, csrf, validaciones
│   ├── general/                   # header/navbar/footer + variantes n*.php (diseño nuevo)
│   ├── images/ · archivos/{banco}/  # XML generados
├── storage/logs/                  # Salida del logger (ignorada por git)
└── tests/                         # Suite propia: runner, framework y pruebas unitarias
```

## Constantes de ruta (core.php)

| Constante | Valor |
|-----------|-------|
| `HTML_DIR` | `private/views/` |
| `MODEL_DIR` | `private/model/` |
| `PUBLIC_DIR` | `public/` |
| `LIB_DIR` | `private/lib/` |
| `APP_TITLE` | `BULKSALES` |
| `APP_VERSION` | `1.0.0` (usada como versión de fallback en `asset()`) |

## Módulos

| `view` | Controlador | Modelo | Función |
|--------|-------------|--------|---------|
| `session` / `login` | `sessionController` / `loginController` | `sessionModel` | Autenticación. |
| `formulario` | `formularioController` | `formularioModel` | Captura de gestión/venta (incluye AJAX de ciudad, municipio, subcontacto). |
| `carga_archivo` | `carga_archivoController` | `carga_archivoModel` | Importación CSV masiva (lotes de 1000). |
| `csv` | `csvController` | `csvModel` | Exportación a Excel (legacy, no enlazado en el menú). |
| `reportes` | `reportesController` | `reportesModel` | Reportes de ventas (varios tipos). |
| `xml` | `xmlController` | `xmlModel` | Exportación a XML/XSD. |
| `usuarios` | `usuariosController` | `usuariosModel` | ABM de usuarios y cambio de contraseña. |
| `configuracion` | `configuracionController` | `configuracionModel` | Productos y parámetros. |
| `editar` | `editarController` | `editarModel` | Edición de registros (legacy: SQL de un esquema anterior). |
| `dashboard` | `dashboardController` | `reportesModel` | Tablero (acumulado del mes). |
| `grabaciones` | — | `grabacionesModel` | Grabaciones de llamadas. |

## Integraciones de telefonía

- **Asterisk** vía AMI → `public/classes/phpAMI.php`.
- **Elastix / ECCP** → `public/classes/ECCP.class.php` (el `md5()` es parte del protocolo).

## Frontend

- Renderizado server-side con PHP incrustado en vistas.
- Interactividad con jQuery 3.7.1; AJAX contra los mismos controladores (`mode` devuelve JSON).
- Bootstrap 5.3.8 (local) para el diseño nuevo (`nheader`/`nnavbar`/`nfooter`) y para el
  layout legacy (`header`/`navbar`/`footer`), ya migrado de BS3 a BS5.
- Validación de formularios en cliente: `public/js/fieldsValidation.js` (ver
  [conventions.md](conventions.md)); el servidor vuelve a validar.
- Tablas con DataTables.
- Todos los assets se referencian con `asset()` para evitar caché obsoleta.
