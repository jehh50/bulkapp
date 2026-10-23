# Contexto del Proyecto — BulkApp

**Última actualización:** 2026-10-21

## ¿Qué es?

**BulkApp** (título interno `BULKSALES`) es una aplicación web de **CRM / Call Center**
para gestión de contactos y ventas por campaña. La usan agentes telefónicos para:

- Buscar un cliente por cédula o teléfono.
- Registrar el resultado de cada gestión (contacto efectivo / no efectivo y su motivo).
- Capturar datos de una venta (datos personales, dirección y datos bancarios).
- Generar reportes de ventas y producir archivos XML para integración con sistemas externos.

## Dominio y actores

| Actor | `tipo` | Qué hace |
|-------|--------|----------|
| Agente | `1` | Atiende llamadas, busca clientes, registra gestiones y ventas. |
| Administrador / Supervisor | `2` | Gestiona usuarios, productos, reportes y cargas masivas. |

> Los valores están centralizados en la clase `Constantes`
> (`private/config/constantes.php`): `USUARIO_AGENTE`, `USUARIO_ADMINISTRADOR`, etc.

## Servicios / campañas (`servicio_id`)

El comportamiento del formulario y de los reportes cambia según el servicio:

| `servicio_id` | Campaña | Notas |
|---------------|---------|-------|
| `1` | Bancamiga / Bancaribe / Banco Activo | Formulario bancario estándar (`formulario/index.php`). |
| `2` | Cashea | Flujo de cobranza con compromiso/abono de pago (`formulario/cashea.php`). |
| `5` | Alias de `1` | En varios puntos se normaliza con `Constantes::servicioBase()`. |

> Los XML generados se depositan en `public/archivos/{banco}/` (bancamiga, bancaribe, bancoactivo).

## Glosario rápido

- **Gestión** — registro del resultado de una llamada (tabla `gestion`).
- **Contacto efectivo / no efectivo** — si se logró hablar con el cliente; cada caso tiene un
  **motivo** (tablas `efectivo` / `noefectivo`) y un **subcontacto** (tabla `subcontacto`).
- **PAN / Número de cuenta** — número de cuenta bancaria del cliente; 20 caracteres,
  dígitos y asteriscos (enmascarado), ej. `123456********7890`. El input se llama `pan`.
- **status_id** — estado del cliente/gestión (ej. `7` = atendido/sin deuda, `9` = compromiso de pago,
  `12` = pago abonado). Ver `Constantes::ESTADO_*`.

## Estado actual del repositorio

- Rama de trabajo: `fix/validation-form`. Rama principal: `master`.
- La capa de seguridad transversal ya está implementada: `.env` fuera del repo, lista blanca
  de módulos en `index.php`, tokens CSRF, sesiones con flags seguros, `password_hash()`,
  validación de entrada en el servidor y validación de cargas CSV (`private/config/security.php`).
- La capa de calidad también: sistema de logging (`private/lib/logger.php`), manejo global
  de errores con `try/catch` en los controladores, constantes de dominio, cache-busting de
  assets con `asset()` y suite de pruebas en `tests/`.
- Frontend unificado en **Bootstrap 5.3.8 + jQuery 3.7.1** (locales en `public/`); el diseño
  nuevo usa `nheader`/`nnavbar`/`nfooter` y el layout legacy `header`/`navbar`/`footer`.
- Pendiente conocido: los módulos legacy `editar` y `csv` consultan un esquema de base de
  datos anterior (ver [technical-debt.md](technical-debt.md)).

## Documentación relacionada

- [architecture.md](architecture.md) — cómo está construida la app.
- [conventions.md](conventions.md) — convenciones de código a respetar.
- [technical-debt.md](technical-debt.md) — deuda técnica y riesgos conocidos.
- [`../ANALISIS.md`](../ANALISIS.md) — análisis de seguridad y calidad.
