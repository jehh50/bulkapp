# Constructor de formularios dinámicos — Plan de trabajo

> Estado: **Fase 0 completa (migración aplicada y verificada en BD)**. Fecha: 2026-10-29.
> Diseño origen: proyecto Claude Design *"Bulksales Resumen de gestión rediseño"* → `Constructor.dc.html` (+ `support.js`).

Guía para implementar el **Constructor de formularios dinámicos** dentro de BulkApp
(PHP MVC sin framework ni Composer). Leer junto con [`architecture.md`](architecture.md)
y [`conventions.md`](conventions.md).

> **Restricción dura (no negociable):** los **formularios de banca quedan intactos** —
> ni su **forma** ni su **modelo** se tocan. El flujo bancario (`formulario`,
> `registroGestion`/`registroResultados`, `gestion`/`resultados`, reportería) sigue tal cual.
> El Constructor sirve para **formularios dinámicos nuevos**; su eventual runtime (Fase 3,
> fuera de alcance ahora) sería una ruta de captura **separada**, nunca una modificación del
> esquema bancario fijo.

---

## 1. Qué es

Pantalla de administración para crear, versionar y asignar formularios dinámicos a los
servicios. El diseño dice *"Acceso exclusivo para el rol Supervisor"*; en BulkApp se gatea al
**nuevo rol Administrador (3)** — ver nota de rol en §3 (decisión #5). Dos vistas:

### Listado
Tabla de formularios con búsqueda, filtros por estado (Activo / Inactivo / Eliminado) y
columnas: servicios, nº de campos, BD vigente, versión, fecha y acciones
(vista previa, editar, eliminar).

### Editor
Nombre + estado (toggle Activo/Inactivo) + 4 pestañas:

| Pestaña | Contenido |
|---------|-----------|
| **Campos** | Paleta (izq) → lienzo (centro, *drag-drop* + ↑↓ + borrar) → propiedades (der). El botón "Guardar" va siempre al final y es inamovible. |
| **Servicios** | Checkboxes de servicios. Regla: **un solo formulario activo por servicio** (modal de conflicto → "reasignar y guardar"). |
| **Base de datos** | BD vigente + cargar nueva (.xlsx/.csv) con **mapeo columna→campo** + historial de cargas ("hacer vigente"). |
| **Historial** | Versiones del formulario (se crea una en cada guardado). |

Modales: eliminar (lógico — conserva datos para reportería), guardado, conflicto de
servicios y **vista previa** (renderiza el formulario real con su validación).

### Modelo de campo (según la semilla del diseño)

- **Tipos:** `text`, `textarea`, `number`, `date`, `email`, `select`, `nested`
  (select anidado de 2 niveles).
- **Props por campo:** `label`, `placeholder`, `req`, `min`, `max`, `regex`, `help`,
  `cond:{k,v}` (visibilidad condicional según un campo **previo**), `opts`
  (lista para `select`), `tree:[{p,c}]` + `lv1`/`lv2` (para `nested`, padre→hijos por coma).
- **Validación en preview (a replicar en servidor):** obligatorio; longitud mín/máx
  (texto) o valor mín/máx (`number`); `regex`; email; `nested` exige ambos niveles.

### Encaje visual
El diseño ya está alineado con el rediseño v2: paleta `#1a4391` (azul) / `#f5b83d` (ámbar),
fuente Poppins (la carga `nheader.php`) y `<dc-import name="Menu">` = `public/general/nnavbar.php`.

---

## 2. Brecha diseño → BulkApp

El `.dc.html` es un **prototipo reactivo** (framework `DCLogic` ≈ React: estado +
*drag-drop* + modales) con **datos mock en memoria**. BulkApp es **PHP server-rendered +
jQuery 3.7.1 + Bootstrap 5.3.8 vanilla, locales (sin CDN ni Composer)**. Se **reimplementa**,
no se porta:

- La UI del editor es client-side (estado, *drag-drop*, validación en vivo) → JS propio sobre
  **jQuery 3.7.1 + jQuery UI** (interacciones Sortable/Draggable/Droppable) contra endpoints JSON.
- Todo lo mock (forms, versiones, cargas) necesita **persistencia real**.
- **Lo más profundo:** para que sirva, el módulo `formulario` (captura) debe **renderizar en
  runtime el formulario activo del servicio** y guardar **valores de campos dinámicos**. Hoy
  `gestion`/`resultados` tienen columnas fijas → cambio arquitectónico mayor, en fase aparte.

---

## 3. Decisiones (cerradas 2026-10-29)

| # | Decisión | **Resuelto** | Nota |
|---|----------|--------------|------|
| 1 | **Alcance ahora** | **Builder (Fases 0–2)** | Runtime (Fase 3) queda fuera de alcance. Banca **no se toca** (restricción dura, ver encabezado). |
| 2 | **Persistencia de la definición** | **`definicion JSON` por versión** | MariaDB 10.11 (el motor real) soporta el tipo `JSON` y funciones `JSON_*`. Sin `LONGTEXT` manual. |
| 3 | **Valores capturados (runtime)** | **JSON por envío** (modelo cerrado) | Tabla `formulario_envios` con `valores JSON` + `servicio_id` denormalizado (ver §4). Esquema definido ahora; se **construye** en Fase 3. Nunca sobre el esquema bancario. |
| 4 | **.xlsx** | **CSV primero** | Parser xlsx (`ZipArchive`+`SimpleXML`, nativos) opcional más adelante. |
| 5 | **Rol** | **Administrador (3), exclusivo** | Se activa un 3.er rol. Alinear nombres + jerarquía — ver nota abajo. |
| 6 | **Menú** | **Sí** | Entrada "Constructor" en el dropdown "Configuración" de `nnavbar.php`, visible **solo** `if ($usertype == 3)`. |

### Nota sobre el rol (importante)

**Punto de partida (enredo de nombres).** Hoy las constantes están **invertidas** respecto al
negocio:

- `USUARIO_AGENTE = 1` → Operador.
- `USUARIO_ADMINISTRADOR = 2` → pero la UI lo rotula **"Supervisor"** y gatea **toda** la
  Configuración (Carga BBDD, Usuarios, Reportes, Dashboard, CSV, XML, Editar). El dropdown
  "Configuración" solo se muestra `if ($usertype == 2)`.
- `USUARIO_SUPERVISOR = 3` → **muerto**: sin opción en la UI de usuarios ni gate en ningún controller.

**Decisión (2026-10-29): tres roles, nombres alineados al negocio.**

| Valor | Constante nueva | Rol | Accede a |
|-------|-----------------|-----|----------|
| 1 | `USUARIO_AGENTE` *(sin cambio)* | Operador | Formulario (captura) |
| 2 | `USUARIO_SUPERVISOR` *(antes ADMINISTRADOR)* | Supervisor | Toda la Configuración actual |
| 3 | `USUARIO_ADMINISTRADOR` *(antes SUPERVISOR)* | Administrador | Todo lo del Supervisor **+ el Constructor** |

- **Swap de constantes:** `USUARIO_SUPERVISOR = 2` y `USUARIO_ADMINISTRADOR = 3`. Los **valores
  no cambian**, así que los usuarios rol 2 de hoy siguen siendo supervisores: sin migración de datos.
- **Administrador ⊇ Supervisor:** la config existente pasa a gatear `[USUARIO_SUPERVISOR,
  USUARIO_ADMINISTRADOR]` (2 y 3); el navbar muestra Configuración `if ($usertype == 2 || == 3)`.
- **Constructor exclusivo de Administrador:** `security_requerir_rol([Constantes::USUARIO_ADMINISTRADOR])`
  (=3), y su entrada de menú solo `if ($usertype == 3)`. Así el supervisor del día a día **no**
  puede alterar formularios (objetivo: cambios controlados).
- **Catálogo `usertypes`:** insertar fila `id=3, name='Administrador'` (los roles se leen de esa
  tabla por JOIN). Verificar que `id=2` quede como 'Supervisor'.
- **UI de usuarios:** agregar `<option value="3">Administrador</option>` en `newuser.php`,
  `edituser.php` e `index.php`, y mapear `rolid==3 → 'ADMINISTRADOR'` en `usuarios.js`.

> Este cambio de roles es parte de la **Fase 0** (ver §6) y requiere prueba manual: login como
> Operador, Supervisor y Administrador, verificando qué ve cada uno.

---

## 4. Modelo de datos (cerrado 2026-10-29)

Migración SQL en `private/config/migrations/` (seguir el formato de la existente).
Convenciones del repo: tablas en español, PK `id`, FKs `<entidad>_id`, estado vía
`status_id` reusando `REGISTRO_ACTIVO=2`/`REGISTRO_INACTIVO=1`, columna `fecha`, `utf8mb4`.

> El esquema completo queda **definido ahora**. Las tablas 1–4 se construyen en las Fases 0–2
> (Builder). La tabla 5 (`formulario_envios`, captura) es Fase 3 y se deja especificada pero
> **no se construye** en este tramo. Nada de esto toca el esquema bancario fijo.

### Tablas del Builder (Fases 0–2)

```sql
-- 1) Cabecera / identidad del formulario
CREATE TABLE formularios (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre       VARCHAR(150) NOT NULL,
  status_id    INT NOT NULL DEFAULT 1,            -- 1=Inactivo, 2=Activo (REGISTRO_*)
  btn_texto    VARCHAR(80)  NOT NULL DEFAULT 'Guardar',
  eliminado    TINYINT(1)   NOT NULL DEFAULT 0,   -- borrado lógico
  eliminado_en DATETIME     NULL,
  user_id      INT          NULL,                 -- creador
  fecha        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_estado (status_id, eliminado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Definición versionada: el JSON vive aquí (una fila por guardado)
CREATE TABLE formulario_versiones (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED NOT NULL,
  version       INT          NOT NULL,            -- correlativo por formulario: 1,2,3...
  definicion    JSON         NOT NULL,            -- documento de campos (ver §4.1)
  nota          VARCHAR(255) NULL,                -- nota de cambio opcional
  vigente       TINYINT(1)   NOT NULL DEFAULT 0,  -- 1 = versión en uso (permite rollback)
  user_id       INT          NULL,
  fecha         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_form_version (formulario_id, version),
  KEY idx_vigente (formulario_id, vigente),
  CONSTRAINT fk_ver_form FOREIGN KEY (formulario_id)
    REFERENCES formularios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Asignación formulario <-> servicio (regla: 1 ACTIVO por servicio, validada en app)
CREATE TABLE formulario_servicios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED NOT NULL,
  servicio_id   INT          NOT NULL,            -- -> servicios.id (FK blanda)
  PRIMARY KEY (id),
  UNIQUE KEY uq_form_serv (formulario_id, servicio_id),
  KEY idx_servicio (servicio_id),
  CONSTRAINT fk_fs_form FOREIGN KEY (formulario_id)
    REFERENCES formularios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Base de datos por formulario: historial de cargas CSV + mapeo + vigente (Fase 2)
CREATE TABLE formulario_cargas (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED NOT NULL,
  archivo       VARCHAR(255) NOT NULL,            -- nombre original
  filas         INT          NOT NULL DEFAULT 0,
  mapping       JSON         NOT NULL,            -- columna CSV -> clave k (ver §4.1)
  vigente       TINYINT(1)   NOT NULL DEFAULT 0,  -- 1 = BD vigente del formulario
  user_id       INT          NULL,
  fecha         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_carga_vigente (formulario_id, vigente),
  CONSTRAINT fk_carga_form FOREIGN KEY (formulario_id)
    REFERENCES formularios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabla de captura (Fase 3 — especificada, NO se construye ahora)

```sql
-- 5) Envío: un formulario dinámico completado por un operador
CREATE TABLE formulario_envios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  formulario_id INT UNSIGNED NOT NULL,
  version_id    INT UNSIGNED NOT NULL,            -- congela el esquema usado
  servicio_id   INT          NOT NULL,            -- DENORMALIZADO: reportes sin join extra
  carga_id      INT UNSIGNED NULL,                -- BD/dataset vigente contra el que se capturó
  valores       JSON         NOT NULL,            -- { "k": valor, ... }  (ver §4.1)
  user_id       INT          NULL,                -- operador que capturó
  fecha         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_env_servicio (servicio_id, fecha),      -- reportería por servicio + rango de fecha
  KEY idx_env_form (formulario_id, fecha),
  KEY idx_env_version (version_id),
  CONSTRAINT fk_env_form    FOREIGN KEY (formulario_id) REFERENCES formularios(id),
  CONSTRAINT fk_env_version FOREIGN KEY (version_id)    REFERENCES formulario_versiones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- **NO** va a `gestion`/`resultados` (esquema bancario fijo). Es un almacén propio y separado.
- `servicio_id` denormalizado por decisión del usuario: la reportería de estos formularios filtra
  y agrupa por servicio sin tocar `formulario_servicios`.
- `version_id` congela contra qué definición se llenó: editar el formulario luego no corrompe
  los envíos viejos (cada uno se lee con su propia versión).
- `valores` en **JSON** (decisión del usuario): el envío queda íntegro; reportería con
  `JSON_VALUE`/`JSON_TABLE` (MariaDB 10.11). Sin EAV.
- **Sin `ON DELETE CASCADE`** hacia `formularios`: los envíos son datos a conservar; además el
  borrado de formularios es lógico (`eliminado`), nunca físico.

### 4.1 Forma del JSON

`definicion` (en `formulario_versiones`) — refleja el modelo de campo del diseño; cada campo
tiene una clave **`k`** estable que referencian `mapping` y `valores`:

```json
{
  "campos": [
    { "k": "cedula", "type": "text", "label": "Cédula", "placeholder": "V-12345678",
      "req": true, "min": 6, "max": 10, "regex": "^[VE]-?\\d+$", "help": "Sin puntos" },
    { "k": "plan", "type": "select", "label": "Plan", "req": true,
      "opts": ["Básico", "Full", "Premium"], "cond": { "k": "tipo_cliente", "v": "nuevo" } },
    { "k": "ubicacion", "type": "nested", "label": "Estado / Ciudad",
      "lv1": "Estado", "lv2": "Ciudad",
      "tree": [ { "p": "Miranda", "c": "Los Teques, Guarenas" },
                { "p": "Carabobo", "c": "Valencia, Guacara" } ] }
  ]
}
```

- Tipos: `text, textarea, number, date, email, select, nested`.
- Props por campo: `k, type, label, placeholder, req, min, max, regex, help, cond{k,v}, opts,
  tree[{p,c}], lv1, lv2`. El **orden del array `campos`** = orden de render (lo reordena el drag&drop).

`mapping` (en `formulario_cargas`) — columna del CSV → clave `k`:

```json
{ "CEDULA": "cedula", "PLAN CONTRATADO": "plan", "CIUDAD": "ubicacion" }
```

`valores` (en `formulario_envios`) — clave `k` → valor capturado:

```json
{ "cedula": "V-12345678", "plan": "Full", "ubicacion": { "lv1": "Miranda", "lv2": "Los Teques" } }
```

### Decisiones de diseño del modelo

| Punto | Decisión | Razón |
|-------|----------|-------|
| Estado Activo/Inactivo | `status_id` reusando `REGISTRO_ACTIVO/INACTIVO` | Es el toggle del diseño; semántica ya existente |
| "Eliminado" | columna `eliminado` + `eliminado_en` | Borrado lógico ortogonal; conserva datos/BD/historial para reportería |
| Versión vigente | flag `vigente` en `formulario_versiones` | Evita FK circular; permite rollback |
| 1-activo-por-servicio | validado en **app** (transacción + modal de conflicto) | MySQL no tiene UNIQUE condicional limpio; depende de `status_id`+`eliminado` de otra tabla |
| FKs a `servicios`/`users` | índice, **no FK dura** | Tablas legacy de firma desconocida; FK dura podría romper la migración |
| Captura (`formulario_envios`) | `valores JSON` + `servicio_id` denormalizado | Consistente con JSON-everywhere; reportes por servicio sin join; `version_id` congela el esquema |

---

## 5. Mapeo al stack (convenciones obligatorias)

- **Ruta:** agregar `'constructor'` a la whitelist de `index.php`.
- **Controlador** `private/controllers/constructorController.php`:
  `security_requerir_rol([Constantes::USUARIO_ADMINISTRADOR])` (=3, exclusivo Administrador);
  `switch ($_GET['mode'])`; cada caso con `try/catch` + `app_log()` + `app_responder_error()`;
  entrada validada con helpers `input_*`; respuestas AJAX como JSON (CSRF lo adjunta `csrf.js`).
- **Modelo** `private/model/constructorModel.php`: solo `selectQuery()` / `preparedQuery()`
  (params como **array**). CRUD, versionado, regla de 1-activo-por-servicio, reasignación.
- **Vistas/assets:** `private/views/constructor/index.php` (shell con `nheader`/`nnavbar`/`nfooter`),
  `public/js/constructor.js`, `public/css/constructor.css` — referenciados con `asset()`.
- **Constantes:** estados de formulario y whitelist de tipos de campo en `Constantes`.
- **Validación en servidor:** espejo de la del preview; **nunca** confiar en el cliente.

### Stack de la UI (decisión del usuario, 2026-10-29)

- **Bootstrap 5.3.8 prevalece** como en las vistas actuales: layout, grid, cards, botones,
  modales, tabs, forms, toggles, todo con componentes/utilidades BS5. `constructor.css` es
  **solo** para lo que BS5 no alcanza (lienzo de drag&drop, estados de arrastre, paleta, etc.),
  mínimo y con la paleta v2 (`#1a4391`/`#f5b83d`).
- **Drag&drop con jQuery UI** (autorizado por el usuario; sustituye el "HTML5 nativo" previo):
  `sortable` para reordenar campos en el lienzo, `draggable`/`droppable` para paleta→lienzo.
  Traer **solo esas interacciones** (build a medida de jQuery UI), no el bundle completo.
- **Todo local en `public/`, sin CDN** (regla de oro de `AGENTS.md`): vendorizar
  `public/js/jquery-ui.min.js` (y su CSS mínimo solo si hace falta) junto a los demás.
- **El shell v2 (`nheader`/`nfooter`) no carga jQuery globalmente** — cada vista lo incluye.
  La vista del Constructor carga al pie, con `asset()`: `jquery-3.7.1.min.js` →
  `jquery-ui.min.js` → `constructor.js` (en ese orden). `csrf.js` ya viene en `nheader`.

### Endpoints (`mode=`)
`index` (shell + bootstrap JSON) · `list` · `save` (crea versión + chequeo de conflicto) ·
`delete` (lógico) · `preview` · `upload` (ingesta + mapeo) · `setVigente`.

---

## 6. Fases

### Fase 0 — Cimientos  ✅ *completa 2026-10-29 (migración aplicada y verificada)*
- **Roles (ver §3, decisión #5):**
  - `Constantes`: swap a `USUARIO_SUPERVISOR = 2` y `USUARIO_ADMINISTRADOR = 3`.
  - Config existente (carga_archivo, configuracion, csv, editar, reportes, usuarios, dashboard,
    xml): gate `[USUARIO_ADMINISTRADOR]` → `[USUARIO_SUPERVISOR, USUARIO_ADMINISTRADOR]`.
    `formularioController`: agregar `USUARIO_ADMINISTRADOR` a su gate.
  - `nnavbar.php`: Configuración/Dashboard `if ($usertype == 2 || $usertype == 3)`;
    Formulario para 1/2/3.
  - Catálogo `usertypes`: insertar `id=3 'Administrador'`.
  - UI de usuarios (`newuser.php`, `edituser.php`, `index.php`, `usuarios.js`): opción/mapa del rol 3.
  - Prueba manual: login como Operador, Supervisor y Administrador.
- Migración SQL (tablas 1–4 de §4).
- `Constantes`: estados de formulario + tipos de campo permitidos.
- Whitelist `'constructor'` en `index.php`, `constructorController.php` gateado a Administrador(3),
  entrada "Constructor" en `nnavbar.php` (`if ($usertype == 3)`).

### Fase 1 — Builder (CRUD + preview)
- Controlador (`index`, `list`, `save`, `delete`, `preview`) + modelo (CRUD, versionado,
  conflicto de servicio, reasignación).
- Vista shell (BS5) + `constructor.js` (estado del editor, drag&drop con jQuery UI, validación
  en vivo, modales BS5) + `constructor.css` (solo lo que BS5 no cubre).
- Validación de la definición en servidor (tipos, `cond`→campo previo, etc.).

### Fase 2 — Base de datos por formulario
- `upload` (ingesta CSV + mapeo columna→campo, reutilizando la lógica de `carga_archivo`),
  `setVigente`, historial de cargas. `.xlsx` opcional después (`ZipArchive`+`SimpleXML`).

### Fase 3 — Runtime (FUERA DE ALCANCE por ahora)
> **No se implementa en este tramo.** Y cuando se retome, **no toca banca**: el flujo bancario
> (`formulario`, `registroGestion`/`registroResultados`, `gestion`/`resultados`, reportería)
> queda fijo. El runtime dinámico sería una **ruta de captura separada** para formularios
> nuevos, no una modificación del esquema bancario. Esbozo para memoria futura:
> - Render del form activo del servicio desde su definición (en la ruta nueva, no en banca).
> - Guardado de valores en `formulario_envios` (`valores JSON`), separado de `gestion`/`resultados`.
> - Reportería propia de campos dinámicos (filtro por `servicio_id`, `JSON_VALUE`/`JSON_TABLE`).
> - Validación servidor espejo de la del preview.

### Cierre de cada fase
`php tests/lint.php` + `php tests/run.php`. Agregar tests para: validación de la definición,
regla de conflicto de servicio y versionado.

---

## 7. Riesgos / notas

- **Banca intacta:** el flujo bancario no se toca (forma ni modelo). El Builder (Fases 0–2) no
  lo roza; solo administra definiciones de formularios nuevos. El runtime (Fase 3) está fuera
  de alcance y, de retomarse, iría por una ruta de captura separada, no sobre el esquema fijo.
- **jQuery UI autorizado solo para drag&drop**, local en `public/` (sin CDN), interacciones a
  medida (Sortable/Draggable/Droppable). Nada de SortableJS ni otras libs; BS5 para todo lo demás.
- **.xlsx sin PhpSpreadsheet:** empezar por CSV; xlsx real requiere parser propio con extensiones
  nativas (`ZipArchive`, `SimpleXML`).
- **Refactor de roles (Fase 0):** se activa un 3.er rol (Administrador) y se **alinean los
  nombres de las constantes** (hoy invertidos: ver §3 #5). El swap no cambia valores, así que los
  usuarios actuales no se ven afectados, pero **toca ~8 controllers de config + la UI de usuarios
  + `usertypes`**; requiere prueba manual por rol. El Constructor queda **exclusivo de
  Administrador** para evitar cambios no controlados sobre los formularios.
