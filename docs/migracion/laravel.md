# Documentación para migración a Laravel — BulkApp

**Última actualización:** 2026-11-03 · **Estado del código de origen:** rama `bulkapp-v2-constructor`
**Anexo:** [esquema-actual.sql](esquema-actual.sql) (DDL real de la BD, 29 tablas)

Este documento describe la aplicación **tal como existe hoy** (PHP vainilla) para que un equipo
pueda reconstruirla en **Laravel** sin perder comportamiento de negocio. Para la versión
Node.js + React ver [nodejs-react.md](nodejs-react.md). Las dos comparten el mismo contenido de
negocio; difieren en la sección de arquitectura objetivo.

---

## 1. Contexto

**BulkApp** (título interno `BULKSALES`) es un **CRM / call center** para gestión de contactos y
ventas por campaña. Lo usan agentes telefónicos para:

- Buscar un cliente por cédula o teléfono.
- Registrar el resultado de cada llamada (contacto efectivo / no efectivo y su motivo).
- Capturar una venta (datos personales, dirección y datos bancarios) en formularios por campaña.
- Operar campañas de **formulario dinámico** definidas en un *Constructor* (sin código).
- Generar reportes de productividad y ventas, exportar a Excel y generar XML para sistemas externos.
- Importar bases de clientes masivamente desde CSV/XLSX.

### 1.1 Organización de datos

```
empresas (cliente corporativo)  1 ──── N  servicios (campañas, `servicio_id` de todo el sistema)
servicios                        N ──── M  users   (vía usuario_servicios; users.servicio_id = campaña principal)
servicios.tipo_flujo             ∈ {bancario, dinamico}
```

| `servicio_id` | Campaña | Flujo | Notas |
|---------------|---------|-------|-------|
| 1 | Banco Activo | `bancario` | Dueña de los catálogos de banca (efectivo, no efectivo, ciudades, estados…). |
| 5 | Bancaribe | `bancario` | Usa los catálogos de la 1 (`servicioBase()`). |
| 6 | Portabilidad | `dinamico` | Activa (`status_id = 2`). |

> Hay 3 campañas registradas (`servicios`): 2 bancarias activas (1 y 5) y 1 dinámica activa (6). La campaña 2 (CASHEA) fue retirada.

### 1.2 Actores y roles

| `usertypes.id` | Nombre | Permisos efectivos hoy |
|----------------|--------|------------------------|
| 1 | Operador | Captura de gestiones y ventas (`formulario`, `capturaController` index/buscar/guardar). |
| 2 | Supervisor | Lo del operador + reportes, dashboard, XML, cargas CSV, usuarios, configuración de productos, edición. |
| 3 | Administrador | Lo del supervisor + gestión de empresas/campañas y el Constructor de formularios. |

### 1.3 Estado del repositorio y deuda conocida

- Rama de trabajo `bulkapp-v2-constructor`; principal `master`.
- Módulos **legacy** que consultan un esquema anterior: `editar` (**eliminado** el 2026-11-03) y `csv` (**eliminado del alcance** el 2026-11-03). Con `editar` se va también `rechazo_ventas`, que solo usaba ese módulo.
- Tablas **legacy o vacías** (no migrar datos, decidir si se crean): `carga_temporal_clientes`,
  `rechazo_ventas`.
  `transacciones` **sí está en uso**: la escribe `xmlModel` para numerar las transacciones XML.
- Integraciones de telefonía: **Asterisk** vía AMI (`public/classes/phpAMI.php`) y **Elastix/ECCP**
  (`public/classes/ECCP.class.php`, usa `md5()` por requisito del protocolo). Módulo `grabaciones`
  referenciado pero sin controlador en el repo.

---

## 2. Convenciones de código actuales (referencia para el equipo de migración)

Estas convenciones son las del código de origen. Sirven para entender decisiones de negocio y
para mapear cada una a su equivalente Laravel.

| Convención actual | Qué hace | Equivalente Laravel sugerido |
|-------------------|----------|------------------------------|
| Routing `index.php?view=X&mode=Y` con lista blanca de módulos | Front controller propio. | Rutas con nombre (`routes/web.php`, `routes/api.php`); route groups por rol. |
| Guarda de sesión `security_requerir_rol([...])` | Valida id y tipo de usuario por módulo. | Middleware `auth` + `role:...` o *Gates*/*Policies*. |
| CSRF `security_require_csrf()` en POST | Token en sesión. | Middleware `VerifyCsrfToken` (ya incluido). |
| Modelos con clase `database` que envuelve `mysqli` y usa `?` | Consultas preparadas. | Eloquent + Query Builder (siempre con *bindings*). |
| Validación con `input_string`, `input_int`, `input_enum`, `input_date`, `input_pan`, `input_phone`… | Saneamiento de `$_GET`/`$_POST`. | *Form Requests* con reglas (`Rule::in`, `date`, `regex`) y *casts*. |
| Constantes en `Constantes` (`USUARIO_*`, `ESTADO_*`, `SERVICIO_*`) | Literales de negocio centralizados. | *PHP backed enums* (`UserType`, `ClientStatus`) + `config/bulkapp.php`. |
| `app_log($nivel, $msg, $ctx)` JSON Lines con `request_id` | Logging estructurado. | `Log::channel('json')` (Monolog) con `Log::withContext(['request_id' => ...])`. |
| Controlador con `try/catch` → `app_responder_error()` (mensaje genérico) | No filtrar errores de BD al cliente. | `bootstrap/app.php` → `withExceptions()` con respuesta genérica en producción. |
| 404 con `app_responder_no_encontrado()` | Página de error para URLs inválidas. | Manejador de `NotFoundHttpException` que renderiza la vista 404. |
| Assets con `asset()` (cache-busting por hash) | Evitar caché obsoleta. | Vite (`@vite`) con hash de contenido. |
| Frontend Bootstrap 5.3.8 + jQuery 3.7.1 **locales** (sin CDN) | Sin dependencias externas. | Mantener Bootstrap local o migrar a Blade + Bootstrap; decidir en la fase de UI. |
| Idioma mixto español/inglés en identificadores | Coherencia local. | En Laravel: modelos y tablas en español existentes (`clientes`, `gestion`); nuevo código en inglés con nombres de tabla existentes. |
| Pruebas: `php tests/run.php` (80 casos, sin Composer) | Pruebas unitarias propias. | PHPUnit / Pest; portar las pruebas de `tests/unit/` a feature tests. |

**Reglas de oro que deben sobrevivir a la migración:**

1. Nunca concatenar entrada de usuario en SQL.
2. Nunca escribir literales de negocio en el código (usar enums/config).
3. Errores: log completo en servidor, mensaje genérico al cliente.
4. Validar toda entrada en servidor (el cliente solo ayuda).
5. Contraseñas con hash moderno; migrar los hashes MD5 heredados en el login.

---

## 3. Arquitectura actual

### 3.1 Patrón y flujo

MVC simplificado con **front controller**, sin framework ni Composer.

```
GET/POST index.php?view={modulo}&mode={accion}
  ├─ core.php  → carga .env, constantes, handlers de error, helpers
  ├─ security_session_start()  (cookies seguras, timeout por inactividad)
  ├─ cabeceras de seguridad (X-Frame-Options, Referrer-Policy, X-Request-Id)
  ├─ lista blanca de view → 404 si no existe
  ├─ security_require_csrf() (obligatorio en POST)
  └─ controllers/{modulo}Controller.php
        ├─ security_requerir_rol([...])
        ├─ switch ($_GET['mode'])  → vistas HTML o JSON (AJAX)
        └─ catch (Throwable) → app_log() + app_responder_error()
```

### 3.2 Mapa de módulos → destino Laravel

| `view` | Función | Controlador origen | Destino sugerido (Laravel) |
|--------|---------|--------------------|----------------------------|
| `session` / `login` | Login, cambio de campaña, logout | `sessionController`, `loginController` | `AuthController` + `Auth` (guard de sesión). Cambio de campaña → `POST /campanas/{id}/cambiar`. |
| `formulario` | Captura bancaria: cliente, ciudad, municipio, subcontacto, registro | `formularioController` | `FormularioBancarioController` + Form Requests + `GestionService`. |
| `captura` | Captura de formularios dinámicos, reporte y exportación | `capturaController` | `CapturaController`, `ReporteFormulariosController`. |
| `constructor` | Diseño de formularios, versiones, cargas y envíos | `constructorController` | `ConstructorController` + servicios de definición y carga. |
| `empresas` | Empresas, campañas y asignación de usuarios | `empresasController` | Recursos `empresas` y `campanas` (solo Administrador). |
| `carga_archivo` | Importación CSV de clientes (lotes de 1 000) | `carga_archivoController` | Job de importación (cola) con validación previa. |
| `reportes` | Acumulado, productividad, ventas, detalle, descarga | `reportesController`, `reportesModel` | `ReportesController` + consultas con *Query Builder*; exportación en cola. |
| `dashboard` | Tablero del mes | `dashboardController` | Vista con KPIs (mismos cálculos que `reportesModel`). |
| `xml` | Generación de XML/XSD para sistemas externos | `xmlController` | Servicio `XmlExportService` + archivos en `storage/app`. |
| `usuarios` | ABM de usuarios, cambio de contraseña | `usuariosController` | `UserController` + Policies. |
| `configuracion` | Productos y parámetros | `configuracionController` | `ProductoController`, catálogos. |

### 3.3 Capa de datos

- `Conexion` extiende `mysqli` con persistencia (`p:`) y helpers `selectQuery` / `preparedQuery` y
  transacciones. Los errores se registran y se lanzan como excepción genérica.
- **Destino:** Eloquent para entidades (`Cliente`, `Gestion`, `Servicio`, `User`…) y *Query Builder*
  para reportes. Usar transacciones (`DB::transaction`) donde hoy hay `begin_transaction`.

### 3.4 Servicios transversales actuales

| Archivo | Responsabilidad | Equivalente Laravel |
|---------|-----------------|---------------------|
| `config/env.php`, `config/core.php` | `.env` y constantes | `.env` + `config/*.php` |
| `config/security.php` | Sesión, CSRF, contraseñas, `input_*`, validación de CSV | Middleware, *Form Requests*, `Hash`, validación de archivos |
| `config/constantes.php` | Dominio (tipos, estados, flujos) | Enums + `config/bulkapp.php` |
| `lib/logger.php` | Logs JSON Lines con rotación | Canal de log JSON con rotación diaria |
| `lib/errors.php` | Errores globales y respuestas genéricas | `bootstrap/app.php` |
| `helpers/formularioRegistro.php` | Armado y validación del registro de venta | `Services/RegistroVentaService` (funciones puras → clase testeable) |
| `helpers/captura.php` | Reporte dinámico: columnas, paginación, XLSX | `Services/ReporteFormularioService` |
| `helpers/constructorDefinicion.php`, `constructorCarga.php` | Definición JSON y parseo CSV/XLSX | `Services/FormularioDefinicion`, `Services/CargaParser` |
| `lib/xlsx.php` | Escritor XLSX sin librerías | `openspout` o `phpoffice/phpspreadsheet` (ver §8) |

---

## 4. Reglas de negocio

### 4.1 Gestión telefónica (formulario bancario, `servicio` 1 y 5)

Flujo al registrar un resultado de llamada (`formularioController::registro`):

1. **Campos base obligatorios:** `servicio` (dentro de las campañas válidas), `usuario` (sesión),
   `id_cliente`, `contacto` (0 o 1).
2. **Estado de la gestión** (`gestion.status_id`):
   - Si viene `noefectivo` → `3` *No efectivo*.
   - Si viene `efectivo` (sin venta) → `4` *Efectivo*.
   - Si la gestión es **venta** (efectivo = 1 con datos del cliente) → `7` *Atendido / vendido*.
3. **Actualización del cliente:** `clientes.status_id` se actualiza **solo si aún no es 7**.
   Esta es una **guardia de idempotencia**: evita registrar dos veces la venta por doble clic o
   reenvío del formulario. Si no se afectan filas, no se inserta nada más.
4. **Registro de gestión:** inserta en `gestion` (`contacto_id`, `efectivo_id`, `producto_id`,
   `noefectivo_id`, `subContacto_id`, `user_id`, `fecha`, `hora`, `cliente_id`, `status_id`,
   `servicio_id`, `tipoGestion`).
5. **Registro de venta** (solo si `efectivo = 1` y datos completos): inserta en `resultados`.

**Validación de la venta bancaria** (todas obligatorias):

| Campo | Regla |
|-------|-------|
| `nombre`, `apellido` | Solo letras (máx. 60), no vacíos. |
| `genero` | `M` o `F`. |
| `fecha_nac` | `dd-mm-yyyy` o `yyyy-mm-dd`. |
| `cedula` | Dígitos, máx. 10. |
| `correo` | Email válido, máx. 150. |
| `telf_hab`, `telf_cel` | En cliente se da formato `(0XXX)XXX.XX.XX`. En servidor: solo dígitos, espacios, `()`, `.`, `-`, `+`; máx. 20. |
| `estado`, `ciudad`, `municipio` | Ids de catálogo del servicio base (1). |
| `nacionalidad` | `1`, `2` o `3`. |
| `tipo_cuenta` | Id de `tipo_cuentas` con `status_id = '2'`. |
| `pan` (número de cuenta) | 20 caracteres, dígitos y asteriscos (enmascarado). Si no viene, se guarda `00000000000000000000`. |

- `resultados.fecha_venta` se guarda como `Ymd` (texto de 10). Se usa en reportes.
- `resultados.status_id` por defecto es `7`.

### 4.2 Campañas dinámicas (Constructor)

- Un **formulario** (`formularios`) tiene **versiones** (`formulario_versiones`). Solo una es `vigente`.
  Cada envío guarda la `version_id` con la que se capturó (versiones antiguas siguen válidas).
- Un formulario se asigna a una o más campañas (`formulario_servicios`, única por par).
- **Eliminar** un formulario es borrado lógico (`eliminado = 1`, `eliminado_en`).
- **Cargas de datos** (`formulario_cargas`): un CSV/XLSX se parsea con un **mapping** (columna
  del archivo → campo del formulario). Sus filas quedan en `formulario_carga_filas` (JSON). Solo
  una carga por formulario es `vigente` (define qué clientes se ofrecen).
- Al capturar, `formulario_envios` guarda `registro` (fila original), `valores` (lo que capturó el
  agente, `{clave: valor}`), `fila_id` (única: una fila de carga solo se captura una vez) y
  `servicio_id` (desnormalizado para reportes).
- Campos admitidos: `text`, `textarea`, `number`, `date`, `email`, `select`, `nested`.
- Un formulario de **banca no se toca** (regla de producto): los formularios bancarios siguen con su
  código fijo (`formularioController`).

### 4.3 Reportes

| Reporte | Qué calcula | Fuente |
|---------|-------------|--------|
| Acumulado / dashboard del mes | Gestiones, efectivos, no efectivos y ventas por usuario | `gestion` (+ `users`) |
| Productividad por producto | Ventas por producto y por contacto | `gestion.producto_id`, `productos` |
| Ventas / ventas por estado | Ventas y su estado | `resultados` + `estado` |
| Detalle de ventas | Registro completo de cada venta | `resultados` + catálogos |
| Reporte de formularios dinámicos | Envíos por cliente → campaña → formulario, con filtros de fecha, paginación (10/25/50/100) y exportación a Excel por tandas de 1 000 | `formulario_envios` (+ `formulario_cargas`) |

- Una **venta** se cuenta como `gestion` con `efectivo_id` y `servicio` (regla de `ventasEfectivas`).
- El reporte de formularios **no tiene ordenamiento**: la paginación es estable porque se usa solo
  `LIMIT ? OFFSET ?` sobre el filtro (decisión del producto).
- Exportación: `.xlsx` generado sin librerías (ver §8 para el reemplazo en Laravel), con dos hojas
  (`Registros` y `Filtros`). Las fechas de Excel usan serial (días desde 1899-12-30).

### 4.4 Cargas masivas de clientes (`carga_archivo`)

- Solo Supervisor y Administrador.
- Lotes de **1 000** filas.
- Solo se admiten campañas bancarias: la importación escribe en `clientes`. La desactivación previa de cargas
  (`is_active`) existía solo para Cashea y se eliminó. Para clientes bancarios no existe (está comentada en el
  código): **confirmar con negocio** si debe existir antes de migrar.
- Validación de archivos: tamaño máximo 10 MB (CSV de clientes) y 20 MB (formularios).

### 4.5 Usuarios y campañas

- Un usuario tiene una **campaña principal** (`users.servicio_id`) y puede tener campañas extra
  (`usuario_servicios`). Al iniciar sesión se carga la campaña activa; el usuario puede cambiarla
  (`sessionController::campana`).
- Desactivar usuario: `users.status_id = 1` (Inactivo). Login rechazado con mensaje de cuenta
  deshabilitada.
- Login: usuario + contraseña. Si el hash es MD5 heredado, se **rehashea** a `password_hash()` en el
  primer inicio de sesión correcto.
- Sesión con **timeout por inactividad** (`SESSION_IDLE_TIMEOUT`, por defecto 1 800 s, mínimo 300 s).

### 4.6 Estados de cliente

| `clientes.status_id` | Significado | Filas hoy |
|----------------------|-------------|-----------|
| 3 | Sin contactar | 106 787 |
| 4 | Contactado | 16 072 |
| 7 | Efectiva / vendido | 2 882 |

Catálogo `status` completo (uso general): 1 Inactivo, 2 Activo, 3 Sin contactar, 4 Contactado,
5 Rechazado, 6 Actualizado, 7 Efectiva, 8 Contactado no efectivo, 9 Compromiso de pago, 10 No pago,
11 BBDD cerrada, 12 Pago parcial. Los valores 9, 10 y 12 se conservan: no los usa hoy ninguna tabla, pero banca podría necesitarlos.

### 4.7 Catálogos de la campaña bancaria (servicio 1)

- **Contacto efectivo** (`efectivo`, servicio 1): Gestión efectiva, Llamar luego, No está interesado,
  Desempleado, Interesado pero muy costoso, Ya tengo un servicio, No tengo cómo pagar, Exclusión,
  Titular/Referido, Titular/No referido, Ya posee cobertura.
- **Contacto no efectivo** (`noefectivo`, servicio 1): Contestadora, No contesta, Ocupado, Número
  equivocado, Cliente contesta y cuelga, Mensaje con tercero, Titular no está en el país, Problemas
  con horario, Tono de fax, No conoce la empresa.
- **Subcontacto** (`subcontacto`): asociado a un `efectivo_id`.
- **Productos** (`productos`): por campaña, activos con `status_id = 2`.
- Ciudades, municipios y estados: catálogos geográficos por campaña base.

> Las tablas `efectivo` y `noefectivo` tienen filas de servicio 2 (campaña a retirar). No se usan en campañas bancarias.

---

## 5. Esquema de base de datos

**Motor actual:** MySQL/MariaDB (InnoDB). **Motor destino: PostgreSQL** (decidido el 2026-11-03; ver §5.5 y §5.6). Charset `utf8mb4`, colaciones `utf8mb4_spanish_ci` (algunas tablas
`utf8mb4_general_ci` o `latin1`). El DDL completo está en [esquema-actual.sql](esquema-actual.sql).

### 5.1 Tablas principales

| Tabla | Filas | Propósito | Claves / notas |
|-------|------:|-----------|----------------|
| `empresas` | 4 | Cliente corporativo. | `uq_empresa_nombre (nombre)`. `status_id` (2 activo). |
| `servicios` | 4 | Campaña. Es el `servicio_id` del sistema. | FK `empresa_id`. `tipo_flujo` ENUM(`bancario`,`dinamico`). `uq (empresa_id, descripcion)`. |
| `users` | 192 | Usuarios. | FK `servicio`, `status`, `usertype`. `password` VARCHAR(255). |
| `usertypes` | 3 | Roles (1 Operador, 2 Supervisor, 3 Administrador). | |
| `usuario_servicios` | 193 | Campañas extra por usuario. | PK compuesta (`user_id`, `servicio_id`). FK ON DELETE CASCADE. |
| `status` | 12 | Catálogo de estados. | |
| `clientes` | **125 741** | Base de contactos a gestionar. | **Sin FK a `servicios`** (campo `servicio_id` NOT NULL, default 1). Índices `(servicio_id, status_id)`, `status_id`. Campos de contacto en `text`. |
| `gestion` | 61 124 | Cada llamada/gestión. | **Sin FKs declaradas.** `servicio_id` es **VARCHAR(10)** (inconsistente con el resto). Índices por cliente, usuario, fecha, servicio, efectivo, no efectivo. |
| `efectivo` | 13 | Motivos de contacto efectivo. | FK `servicio_id`. |
| `noefectivo` | 17 | Motivos de contacto no efectivo. | `servicio_id` por defecto 1. |
| `subcontacto` | 21 | Subcategorías de un efectivo. | Charset `latin1` (legacy). |
| `productos` | 9 | Productos vendibles. | `status_id` (2 activo). `codigo_producto`, `costo_prod`, `codplan` como texto. |
| `resultados` | 2 912 | **Ventas bancarias** (datos completos del cliente vendido). | **Sin FKs.** `cedula` es **INT(11)** (riesgo: pierde ceros a la izquierda). `fecha_venta` y `fecha_nacimiento` son VARCHAR. |
| `ciudad` | 1 007 | Catálogo de ciudades por estado y campaña. | `servicio_id` default 1. |
| `municipio` | 338 | Catálogo de municipios. | |
| `estado` | 24 | Catálogo de estados geográficos. | |
| `tipo_cuentas` | 5 | Tipos de cuenta bancaria. | Se filtra con `status_id = '2'` (comparación con cadena). |

### 5.2 Tablas del Constructor de formularios

| Tabla | Filas | Propósito | Claves / notas |
|-------|------:|-----------|----------------|
| `formularios` | 3 | Definición lógica del formulario. | `eliminado` (borrado lógico), `btn_texto`, `status_id` (estado del formulario; valores en `constructorModel`). |
| `formulario_versiones` | 14 | Versiones inmutables con `definicion` (JSON). | UQ (`formulario_id`, `version`). Solo una `vigente`. |
| `formulario_servicios` | 2 | Asignación formulario ↔ campaña. | UQ (`formulario_id`, `servicio_id`). |
| `formulario_cargas` | 2 | Cargas de datos con `mapping` (JSON). | Una `vigente` por formulario. ON DELETE CASCADE. |
| `formulario_carga_filas` | 19 824 | Filas de cada carga (JSON en `fila`). | `clave` para búsqueda. ON DELETE CASCADE. |
| `formulario_envios` | 2 | Capturas realizadas. | **UQ `fila_id`** (una fila se captura una sola vez). `valores` y `registro` JSON. `servicio_id` desnormalizado. |

### 5.3 Tablas legacy, vacías o de uso secundario

| Tabla | Filas | Estado |
|-------|------:|--------|
| `carga_temporal_clientes` | 0 | Temporal de carga; no usada en el flujo actual. |
| `rechazo_ventas` | 0 | Sin uso desde la eliminación de `editar`. Candidata a retiro. |
| `transacciones` | 4 711 | **En uso activo**: `xmlModel` calcula `MAX(id_transaccion)` y hace INSERT por cada XML generado. Migrar con cuidado de la secuencia (`AUTO_INCREMENT` 6 104 710). |

### 5.4 Relaciones clave

```
empresas 1──N servicios 1──N clientes (por servicio_id, sin FK)
servicios N──M users        (usuario_servicios)
users 1──N gestion (user_id)        clientes 1──N gestion (cliente_id)
gestion 1──0..1 resultados (gestion_id)   // venta bancaria
formularios 1──N formulario_versiones 1──N formulario_envios
formularios 1──N formulario_cargas 1──N formulario_carga_filas
formulario_envios N──1 formulario_carga_filas (fila_id, única)
```

> **Lo que el esquema no garantiza y Laravel sí debería:** `gestion`, `resultados` y `clientes`
> no tienen claves foráneas. Al migrar conviene declararlas (con `nullOnDelete` o `restrictOnDelete`)
> después de limpiar datos huérfanos. Revisar antes de activar la restricción.

---

### 5.5 Migración a PostgreSQL

Decidido el 2026-11-03: el motor destino es **PostgreSQL**. Los tipos y comportamientos de MySQL no se trasladan tal cual:

| MySQL (actual) | PostgreSQL (destino) | Nota |
|----------------|----------------------|------|
| `INT AUTO_INCREMENT` | `integer GENERATED BY DEFAULT AS IDENTITY` | Tras migrar, ajustar la secuencia al máximo id (`setval`). |
| `BIGINT UNSIGNED` | `bigint` | PostgreSQL no tiene `unsigned`; validar que no haya negativos. |
| `TINYINT(1)` | `boolean` | Convertir `0/1` a `false/true`. Aplica a `vigente`, `eliminado`, `is_active`. |
| `DATETIME` | `timestamp without time zone` | Definir la zona horaria de almacenamiento (la app usa `America/Caracas`). |
| `DATE` / `TIME` | `date` / `time` | Sin cambios. |
| `LONGTEXT` con `CHECK (json_valid(...))` | `jsonb` | Validar que todo el contenido sea JSON antes de migrar. `jsonb` permite índices GIN sobre `valores` y `registro`. |
| `ENUM('bancario','dinamico')` | `ENUM` nativo de PG o `varchar` + `CHECK` | Igual para cualquier otro ENUM del esquema. |
| `ON UPDATE current_timestamp()` | Trigger `BEFORE UPDATE` que asigna `updated_at = now()` | No existe equivalente en `DEFAULT`. Aplica a `clientes`, `gestion`, `servicios`, `formularios`… |
| Colación `utf8mb4_spanish_ci` (insensible a mayúsculas y acentos) | Colación por defecto (sensible) | Para búsquedas sin importar mayúsculas: `ILIKE` o índice sobre `lower(x)`. Para orden en español, crear la BD con colación ICU. |
| `LIKE` (insensible en MySQL) | `ILIKE` | Revisar la búsqueda de cliente por teléfono (`telf_hab LIKE ?`). |
| `IF(cond, a, b)` | `CASE WHEN cond THEN a ELSE b END` | Revisar en reportes. |
| `DATE_ADD(?, INTERVAL 1 DAY)` | `?::date + INTERVAL '1 day'` | Filtro de fecha del reporte de formularios. |
| `affected_rows` (guardia de idempotencia) | `UPDATE ... RETURNING id` | Mantener la guardia `status_id <> 7` tal cual. |
| `insert_id` | `INSERT ... RETURNING id` | |
| Placeholders `?` | `$1, $2, …` en el driver `pg` | En Laravel, el Query Builder los gestiona. |

### 5.6 Correcciones obligatorias antes de migrar

Corrigen defectos del esquema actual que PostgreSQL rechaza o que causarían errores silenciosos:

| # | Problema | Dónde | Corrección |
|---|----------|-------|------------|
| 1 | `user` es **palabra reservada** en PostgreSQL. | `users.user` | Renombrar a `username`; actualizar consultas (login, listados). |
| 2 | Nombre de columna con espacio. | `servicios."campo 2"` | Renombrar a `campo_2`, o eliminar si no se usa (buscar usos antes). |
| 3 | Identificadores con mayúsculas: PostgreSQL los pliega a minúsculas si no se citan. | `gestion.subContacto_id`, `gestion.tipoGestion` | Renombrar a `subcontacto_id` y `tipo_gestion`. |
| 4 | Índices y tablas comparten espacio de nombres en PG. | Índice `status` de `users` vs. tabla `status` | Nombrar índices con prefijo `idx_`. |
| 5 | Fechas cero `'0000-00-00 00:00:00'`, no admitidas en PG. | `status.updated_at`, `usertypes.updated_at` | Reemplazar por `NULL` o `now()`. |
| 6 | `gestion.servicio_id` es VARCHAR(10). | `gestion` | Convertir a `integer` tras validar que todos los valores son numéricos. |
| 7 | `resultados.cedula` es INT (pierde ceros a la izquierda). | `resultados` | Convertir a `varchar(10)`. |
| 8 | Fechas guardadas como texto. | `resultados.fecha_venta` (`Ymd`), `resultados.fecha_nacimiento` | Convertir a `date` (`to_date(valor, 'YYYYMMDD')`) validando cada valor. |
| 9 | Tablas en `latin1`. | `subcontacto` | Recodificar a UTF-8 durante la carga. |
| 10 | Sin claves foráneas. | `gestion`, `resultados`, `clientes` | Limpiar huérfanos y declarar FKs después de cargar. |
| 11 | Tablas sin uso activo. | `carga_temporal_clientes`, `rechazo_ventas` | No migrar datos. Crearlas solo si el módulo que las usa sobrevive (ver §11). |

**Carga sugerida:** `pgloader` desde MySQL con reglas de conversión para los puntos 1 a 9, seguido de un
script SQL versionado con las correcciones de la tabla. **Verificación:** conteos por tabla (§5.1) y totales de
control (ventas por mes, gestiones por usuario y por día) iguales en MySQL y en PostgreSQL.

**Secuencias:** `transacciones.id_transaccion` está en `AUTO_INCREMENT` 6 104 710. Ajustar la secuencia de PG a
ese máximo antes de reactivar la generación de XML; si no, el siguiente número colisionará.

## 6. Mapeo de endpoints (modo → ruta)

| Hoy (`view` + `mode`) | Método | Ruta sugerida | Rol |
|-----------------------|--------|---------------|-----|
| `session&mode=login` | POST | `/login` | público |
| `session&mode=disconect` | POST | `/logout` | autenticado |
| `session&mode=campana` | POST | `/campanas/{servicio}/cambiar` | autenticado |
| `formulario&mode=cliente` | GET | `/api/clientes/buscar` (`cedula` o `telefono`) | Operador+ |
| `formulario&mode=ciudad` / `municipio` / `subContacto` | GET | `/api/catalogos/...` | Operador+ |
| `formulario&mode=registro` | POST | `/gestiones` | Operador+ |
| `captura&mode=buscar` / `guardar` | GET / POST | `/captura/buscar`, `/captura/envios` | Operador+ |
| `captura&mode=reporte` / `exportar` | GET | `/reportes/formularios`, `/reportes/formularios/exportar` | Supervisor+ |
| `constructor&mode=save` / `delete` | POST | `/constructor/formularios/{id}` | Administrador |
| `constructor&mode=cargaAnalizar` / `cargaConfirmar` | POST | `/constructor/formularios/{id}/cargas` | Administrador |
| `carga_archivo&mode=registro` | POST | `/cargas-clientes` (cola) | Supervisor+ |
| `reportes&mode=*` | GET | `/reportes/{tipo}` | Supervisor+ |
| `xml&mode=xml` | GET/POST | `/xml/generar` | Supervisor+ |
| `usuarios&mode=*` | varios | `/usuarios` (recurso) + `/usuarios/{id}/password` | Supervisor+ |
| `empresas&mode=*` | varios | `/empresas`, `/empresas/{id}/campanas`, `/campanas/{id}/usuarios` | Administrador |
| `configuracion&mode=*` | varios | `/productos` (recurso), `/configuracion/...` | Supervisor+ |

---

## 7. Propuesta de arquitectura en Laravel

```
app/
  Enums/          UserType, ClientStatus, FlujoCampana, Estado (backed enums)
  Models/         Empresa, Campana (servicios), User, Cliente, Gestion, Resultado,
                  Formulario, FormularioVersion, FormularioEnvio, FormularioCarga, ...
  Services/       RegistroGestionService, RegistroVentaService, ReporteFormularioService,
                  FormularioDefinicionService, CargaParserService, XmlExportService
  Http/
    Controllers/  (delgados: validan con Form Request y llaman a un servicio)
    Requests/     (una por caso de uso; reglas de §4.1)
    Middleware/   (rol, campaña activa)
  Jobs/           ImportarClientesJob (lotes de 1 000), ExportarReporteJob
  Policies/       por campaña y por rol
config/
  bulkapp.php     (tamaños, timeouts, filas por página, tandas de exportación)
database/
  migrations/     (ver §9: migración por fases, no un único `migrate:fresh`)
  seeders/        (catálogos: efectivo, noefectivo, status, usertypes)
tests/
  Feature/        (flujos de §4)
  Unit/           (validación de venta, serial de fechas, paginación)
```

**Decisiones recomendadas (a validar):**

- **Autenticación:** guard de sesión de Laravel (cookies `httpOnly`, `secure`, `SameSite=Lax`).
  Mantener el timeout por inactividad con `session.lifetime`.
- **Autorización por campaña:** las consultas de cliente, gestión y reporte deben filtrar por
  `servicio_id` de la **campaña activa** del usuario, no solo por rol.
- **Interfaces AJAX:** Blade + jQuery/Alpine para lo que hoy es AJAX, o endpoints JSON bajo `/api`.
  Las respuestas deben seguir el contrato actual (`response`, `message`) para no romper vistas.
- **Frontend:** conservar Bootstrap 5.3.8 local o migrar a Tailwind/Vite. Mantener `asset()` como
  `@vite` para cache-busting.

---

## 8. Librerías y equivalentes

| Necesidad hoy | Hoy | Laravel |
|---------------|-----|---------|
| Excel (`.xlsx`) | Escritor propio con `ZipArchive` (`private/lib/xlsx.php`). | `openspout/openspout` (streaming, bajo consumo) o `phpoffice/phpspreadsheet`. Recomendado: **openspout** por el volumen de exportación. |
| Lectura CSV/XLSX | Parser propio (`constructorCarga.php`). | `maatwebsite/excel` o `openspout` (lectura). |
| Logs | `AppLogger` JSON Lines. | Monolog + canal JSON con `daily`. |
| Pruebas | Runner propio (`tests/run.php`). | PHPUnit / Pest. |
| Hash de contraseñas | `password_hash()` + migración de MD5. | `Hash::make()`; `Hash::needsRehash()` en el login. |
| Telefonía | AMI y ECCP en `public/classes/`. | Paquete o servicio dedicado; no mezclar con controladores. |

---

## 9. Plan de migración sugerido

1. **Fase 0 — Base y datos.** Crear la BD PostgreSQL y el proyecto Laravel. Aplicar las correcciones de §5.6 en un script versionado. Cargar datos con `pgloader` y validar conteos y totales de control.
2. **Fase 1 — Dominio y seguridad.** Enums, modelos, login con migración de hash, roles y policies. Pruebas de §4.5.
3. **Fase 2 — Captura bancaria.** `formulario` completo (§4.1) con los mismos catálogos y guardia de idempotencia. Paridad verificada con casos reales (ventas duplicadas bloqueadas).
4. **Fase 3 — Constructor y captura dinámica.** Definiciones, versiones, cargas, envíos y reporte (§4.2 y §4.3). Reusar el JSON de `formulario_versiones.definicion` (ya convertido a `jsonb`).
5. **Fase 4 — Reportes, dashboard y XML.** Comparar cifras con la versión PHP en el mismo periodo. Restaurar la secuencia de `transacciones` (§5.6).
6. **Fase 5 — Cargas masivas y administración.** Jobs de importación, empresas/campañas, usuarios.
7. **Fase 6 — Retiro de PHP.** `editar` y `csv` ya quedan fuera. Retirar la aplicación PHP solo cuando la paridad esté verificada.

**Criterio de paridad:** para cada reporte, la cifra de Laravel y la de PHP en el mismo rango de fechas deben coincidir antes de apagar el módulo original.

## 10. Riesgos conocidos a resolver durante la migración

| Riesgo | Detalle | Acción |
|--------|---------|--------|
| Conversión MySQL → PostgreSQL | Tipos, fechas cero, ENUM y colaciones no se trasladan solos. | Seguir §5.5 y §5.6; `pgloader` más script de correcciones; ensayar con una copia de la BD. |
| Cambio de colación | Búsquedas de cliente por cédula o teléfono y comparaciones de texto pueden cambiar. | Definir búsqueda insensible (`lower` / `ILIKE`) y probarla con casos reales. |
| Sin FKs en `gestion`, `resultados`, `clientes` | Integridad solo por código. | Limpiar huérfanos y declarar FKs tras la carga. |
| Tipos incorrectos (`cedula` INT, `servicio_id` VARCHAR, fechas como texto) | Pérdida de datos o comparaciones erróneas. | §5.6, puntos 6 a 8. |
| Secuencia de `transacciones` | Números usados en salida XML externa. | Restaurar la secuencia a 6 104 710 antes de reactivar XML (§5.6). |
| MD5 heredado (Elastix y hashes de usuario) | Hash débil. | Rehash en el login; MD5 de Elastix solo por protocolo. |
| Exposición de `private/` en staging | Descarga de archivos internos. | Ver [`docs/pentest-sqli-2026-11-02.md`](../pentest-sqli-2026-11-02.md) (H1). En Laravel, `public/` es la única raíz web. |

## 11. Decisiones y preguntas abiertas

### Decisiones tomadas

| Fecha | Decisión |
|-------|----------|
| 2026-11-03 | Motor destino: **PostgreSQL** (reemplaza MySQL). |
| 2026-11-03 | Módulo **`csv`** eliminado del alcance: no se migra ni se reproduce. |
| 2026-11-03 | Módulo **`editar`** eliminado (controlador, modelo y vistas). Su SQL no coincidía con el esquema actual. |
| 2026-11-03 | Flujo **Cashea** retirado: código, JS y constantes eliminados. Migración `20261103_eliminar_cashea.sql` aplicada (tablas vacías). |
| 2026-11-04 | Usuarios eliminados (se conserva solo el administrador). Decisión intencional del negocio. `gestion.user_id` queda con referencias huérfanas: 61 112 de 61 124 gestiones. |
| 2026-11-04 | Migración `20261104_eliminar_campana_cashea.sql` aplicada: campaña 2 y sus catálogos eliminados. |
| 2026-11-05 | Migración `20261105_constructor_expresiones.sql` aplicada: catálogo `formulario_expresiones` (5 validaciones del Constructor: correo, solo números, solo letras, mayúsculas, teléfono). No figura en `esquema-actual.sql` (capturado el 2026-11-03). Sus patrones se escribieron sin barras invertidas, así que funcionan igual en PostgreSQL (`~`), PHP y JS. |

### Pendiente de decisión

1. ¿Se desactiva la carga anterior de clientes bancarios al importar una base nueva? Hoy está comentado en el código.
2. ¿`transacciones` (secuencia de XML) tiene consumidores externos que dependan de sus números?
3. ¿Dónde se despliega (VPS, contenedores, PaaS), con qué versión de PHP o Node y con PostgreSQL gestionado o propio?
4. ¿La integración con Asterisk/Elastix se mantiene en el mismo servidor?
5. ¿Hay requisito de SSO o se mantiene usuario y contraseña local?
6. ¿Se requiere historial de cambios (auditoría) de clientes y gestiones? Hoy no existe.
