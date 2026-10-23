<?php
require __DIR__ . '/private/config/core.php';

security_session_start();

header_remove('X-Powered-By');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
header('X-Request-Id: ' . app_request_id());

// Lista blanca de módulos. Solo estos controladores pueden incluirse.
$controladoresPermitidos = [
  'carga_archivo',
  'configuracion',
  'csv',
  'dashboard',
  'editar',
  'formulario',
  'login',
  'reportes',
  'session',
  'usuarios',
  'xml',
];

$vista = isset($_GET['view']) ? strtolower(trim((string) $_GET['view'])) : '';

if ($vista === '') {
  include __DIR__ . '/private/controllers/sessionController.php';
  exit;
}

if (!preg_match('/^[a-z0-9_]+$/', $vista) || !in_array($vista, $controladoresPermitidos, true)) {
  http_response_code(404);
  include __DIR__ . '/private/views/error.html';
  exit;
}

$controlador = __DIR__ . '/private/controllers/' . $vista . 'Controller.php';

if (!is_file($controlador)) {
  http_response_code(404);
  include __DIR__ . '/private/views/error.html';
  exit;
}

security_require_csrf();

include $controlador;
