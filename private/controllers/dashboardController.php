<?php
include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));
if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  include_once(MODEL_DIR . 'reportesModel.php');
  $conn = new database();

  try {
    // Mes seleccionado (YYYY-MM); por defecto el mes actual
    $mes = (isset($_GET['mes']) && preg_match('/^\d{4}-\d{2}$/', $_GET['mes'])) ? $_GET['mes'] : date('Y-m');
    $desde  = $mes . '-01';
    $finMes = date('Y-m-t', strtotime($desde));
    // En el mes actual el corte es hoy; en meses pasados, el último día del mes
    $hasta  = ($mes === date('Y-m')) ? date('Y-m-d') : $finMes;

    // Marcador de venta (gestión efectiva), igual que el Acumulado de gestión
    $idEfectivo = 1;
    $ef = $conn->efectivo([Constantes::SERVICIO_BANCARIO, Constantes::SERVICIO_BANCARIO_ALIAS]);
    if (!empty($ef) && isset($ef[0]['id'])) { $idEfectivo = $ef[0]['id']; }

    $servicios = $conn->dashboardServicios($desde, $hasta, $idEfectivo);
    $diarias   = $conn->dashboardVentasDiarias($desde, $hasta, $idEfectivo);
    $meses     = $conn->mesesConGestion();

    $nav_crumb = 'Inicio / Dashboard';
    $nav_title = 'Acumulado del mes por servicio';

    include(PUBLIC_DIR . 'general/nheader.php');
    include(PUBLIC_DIR . 'general/nnavbar.php');
    include(HTML_DIR . 'dashboard/index.php');
    include(PUBLIC_DIR . 'general/nfooter.php');
  } catch (Throwable $e) {
    app_log('error', 'Error en el controlador de dashboard: ' . $e->getMessage(), array(
      'excepcion' => get_class($e),
      'archivo' => $e->getFile(),
      'linea' => $e->getLine(),
    ));

    app_responder_error();
  }
}
?>
