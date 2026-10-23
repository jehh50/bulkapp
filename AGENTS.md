# AGENTS.md — BulkApp

Guía rápida para trabajar en este repositorio (MVC en PHP sin framework ni Composer).

## Documentación (leer antes de tocar código)

| Documento | Contenido |
|-----------|-----------|
| [`docs/context.md`](docs/context.md) | Qué es la app, dominio, actores y estado del repositorio. |
| [`docs/architecture.md`](docs/architecture.md) | Estructura, flujo de una petición y servicios transversales. |
| [`docs/conventions.md`](docs/conventions.md) | **Convenciones de código a respetar** (tipado, constantes, logging, assets, tests). |
| [`docs/technical-debt.md`](docs/technical-debt.md) | Deuda técnica known y prioridades. |
| [`ANALISIS.md`](ANALISIS.md) | Análisis de seguridad y calidad. |

## Comandos

```bash
php tests/run.php     # pruebas unitarias (40 casos)
php tests/lint.php    # sintaxis de todos los archivos PHP
```

## Reglas de oro

- **No** concatenar entrada de usuario en SQL: usar `selectQuery()` / `preparedQuery()`.
- **No** dejar `print_r()`, `var_dump()` ni `console.log()`: usar `app_log($nivel, $msg, $ctx)`.
- **No** escribir literales de negocio: usar la clase `Constantes` (`private/config/constantes.php`).
- **No** referenciar CSS/JS sin el helper `asset()` (cache-busting).
- Los controladores manejan errores con `try/catch` + `app_log()` + `app_responder_error()`.
- Los datos que llegan de `$_GET`/`$_POST` se validan con los helpers `input_*`.
- Bootstrap 5.3.8 y jQuery 3.7.1, **locales** en `public/` (no CDN, no jQuery/Bootstrap nuevos).
