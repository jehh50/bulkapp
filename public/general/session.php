<?php

$configDir = dirname(__DIR__, 2) . '/private/config/';

if (!function_exists('env')) {
  require_once $configDir . 'env.php';
}
if (!function_exists('security_session_start')) {
  require_once $configDir . 'security.php';
}

security_session_start();
security_enforce_timeout();
