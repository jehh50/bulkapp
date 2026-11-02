# Clientes (empresas) y campañas — plan y decisiones

**Fecha:** 2026-11-02 · **Rama:** `bulkapp-v2-constructor` · **Estado:** Fases 0–2 implementadas
(más el punto 1 de la F3: el flujo lo define la campaña).

## Problema

`servicios` se usaba como el cliente (Banco Activo, Bancaribe, Cashea) y la campaña no existía.
`servicio_id` aparece en 16 tablas (`gestion`, `resultados`, `clientes` = BBDD de contactos,
`productos`, catálogos, `users`…) y en la sesión del agente, y el flujo bancario estaba fijado en
código (ids 1 y 5).

## Decisión central

**`servicios` pasa a ser la CAMPAÑA y se agrega `empresas` como padre (1:N).** Los ids no cambian,
así que no se migra ninguna fila histórica y banca conserva su formulario y su modelo.

```
empresas (UI «Clientes»)  1 ── N  servicios (UI «Campañas»)  N ── M  users
                                        └── productos.servicio_id (sin cambios)
```

Decisiones del usuario (2026-11-02):

| Tema | Decisión |
|------|----------|
| Nombre de la tabla | `empresas` (`clientes` ya es la BBDD de contactos). |
| Quién gestiona | **Administrador (3)**, exclusivo. |
| Productos | Siguen colgando de la campaña: planes con el mismo nombre en campañas distintas tienen código/precio propios. |
| Usuarios | N:M: una campaña tiene N usuarios y un usuario puede estar en varias. |
| Campaña | Nombre, descripción, fecha de inicio, meta (opcional). |
| Flujo | Lo define la campaña (`tipo_flujo`): un cliente puede abrir campañas nuevas sin tocar código. |
| `servicios."campo 2"` | No se toca (sin uso conocido). |

## Modelo (migración `20261102_empresas_campanas.sql`)

- **`empresas`**: `id`, `nombre` UNIQUE, `identificacion` (RIF, opcional), `status_id`, timestamps.
- **`servicios`** (+ columnas): `empresa_id` (FK, obligatoria), `detalle` (descripción),
  `fecha_inicio`, `meta` (NULL), `tipo_flujo` ENUM(`bancario`,`dinamico`).
  El **nombre** de la campaña sigue en `descripcion` (lo usan vistas, reportería y la carpeta del
  XML). UNIQUE (`empresa_id`, `descripcion`).
- **`usuario_servicios`** (`user_id`, `servicio_id`) PK compuesta. `users.servicio_id` se conserva
  como **campaña principal** (con la que arranca la sesión) y siempre está asignada.
- Datos: una empresa por servicio existente; 1 y 5 → `bancario`, 2 (Cashea) → `dinamico`;
  `fecha_inicio` = `created_at`; cada usuario asignado a su principal.

## Reglas de negocio

- Inactivar una empresa inactiva sus campañas; no se activa una campaña de una empresa inactiva.
- `tipo_flujo` se fija al crear la campaña (cambiarlo re-enrutaría su historial).
- Campaña bancaria nueva → formulario de banca con los catálogos de la 1
  (`Constantes::servicioBase()`); productos, BBDD y XML por su propio id. Requiere `codigo`
  (company code del XML).
- Campaña dinámica nueva → captura del Constructor (asignarle formulario en el Constructor).
- Quitar a un usuario de su campaña principal la mueve a otra asignada; si no tiene otra, no se
  le quita (se informa como «bloqueado»).
- Sin borrado: empresas y campañas se activan/inactivan (tienen historial).

## Fases

**F0 — Modelo y constantes.** Migración + `private/helpers/campanas.php` (validación, etiqueta
«Empresa › Campaña», mapa de flujos). `Constantes::esServicioBancario()`, `servicios()`,
`servicioBase()` y el nuevo `serviciosBancarios()` leen `servicios.tipo_flujo` (cache por
petición); si la BD/migración no está, usan el mapa histórico (1 y 5 bancarios). Los `[1, 5]`
fijos de reportes/dashboard pasan a `serviciosBancarios()`.

**F1 — Módulo «Clientes y campañas»** (`?view=empresas`, solo Administrador):
`empresasController` + `empresasModel` + `views/empresas/index.php` + `public/js/empresas.js` +
`public/css/empresas.css`. Listado de clientes → ficha (datos + campañas) → modales de campaña y
de usuarios asignados. Menú: Configuración → «Clientes y campañas».

**F1b — Sesión.** Al iniciar sesión se cargan las campañas asignadas activas en
`$_SESSION['campanas']`; si hay más de una, la navbar muestra un selector que cambia
`$_SESSION['servicio_id']` (`?view=session&mode=campana`, POST + CSRF, valida la asignación).
Los cambios de asignación se ven en el siguiente inicio de sesión o cambio de campaña.

**F2 — Selectores.** Etiqueta «Empresa › Campaña» (sin repetir cuando coinciden) en usuarios
(campaña principal; la bandeja muestra «+N» otras campañas), Constructor, reporte de formularios
dinámicos, Carga BBDD, Reportes y XML. En los módulos de banca solo cambia el **texto** de la
opción (`etiqueta`); `descripcion` y los ids enviados quedan iguales.

## Despliegue

1. Aplicar `private/config/migrations/20261102_empresas_campanas.sql` **antes** de publicar el
   código (las consultas de los selectores ya hacen JOIN con `empresas`). `Constantes` tolera la
   ausencia de la migración; los selectores no.
2. Renombrar «CASHEA» como empresa si se desea; crear campañas nuevas desde el módulo.

## Pendientes / riesgos conocidos

- La carpeta del XML se calcula con el nombre de la campaña (`xmlController`/`views/xml`):
  renombrar una campaña bancaria cambia su carpeta, y una campaña bancaria nueva genera su
  propia carpeta. Valorar mover el nombre de carpeta a la empresa o a un campo propio.
- Los catálogos de banca (efectivo/no efectivo/estados/ciudades) son únicos (servicio 1) para
  todas las campañas bancarias.
- El Supervisor sigue editando solo la campaña principal del usuario (módulo Usuarios); las
  asignaciones adicionales son del Administrador.
