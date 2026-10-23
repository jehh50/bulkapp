<?php
$configDir = dirname(__DIR__, 2) . '/private/config/';
if (!function_exists('env') || !function_exists('load_env')) {
  require_once $configDir . 'env.php';
}
load_env(dirname($configDir) . '/.env');

$host  = env('AGENT_HOST', '192.168.5.100');
$user  = env('AGENT_USER', '');
$pass  = env('AGENT_PASS', '');
$agent = env('AGENT_EXTENSION', 'Agent/100');
$passAgent = env('AGENT_DEFAULT_PASSWORD', '');
$logAgent  = env('AGENT_LOG_AGENT', '6001');
?>