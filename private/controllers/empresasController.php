<?php

/**
 * Clientes (empresas) y campañas — acceso exclusivo del rol Administrador.
 *
 * Una empresa tiene N campañas (registros de `servicios`); una campaña tiene N usuarios
 * asignados y un usuario puede estar en varias (usuario_servicios).
 * Respuestas AJAX en JSON; el CSRF lo adjunta public/js/csrf.js.
 */

include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_ADMINISTRADOR));

if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  include_once(MODEL_DIR . 'empresasModel.php');

  $conexion = new database();
  $mode = isset($_GET['mode']) ? $_GET['mode'] : 'index';
  $userId = isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;
  $esPost = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') === 'POST';

  $responderJson = function (array $datos) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
  };

  try {
    if (in_array($mode, array('saveEmpresa', 'saveCampana', 'asignar'), true) && !$esPost) {
      $responderJson(array('response' => 'false', 'message' => 'Método no permitido.'));
      exit;
    }

    switch ($mode) {

      case 'index':
        $nav_crumb = 'Configuración / Clientes y campañas';
        $nav_title = 'Clientes y campañas';
        $nav_subtitle = 'Registra clientes, sus campañas y los usuarios asignados a cada una.';

        $empresas = $conexion->listarEmpresas();
        include(PUBLIC_DIR . 'general/nheader.php');
        include(PUBLIC_DIR . 'general/nnavbar.php');
        include(HTML_DIR . 'empresas/index.php');
        include(PUBLIC_DIR . 'general/nfooter.php');
        break;

      case 'list':
        $responderJson(array('response' => 'true', 'empresas' => $conexion->listarEmpresas()));
        break;

      case 'detalle':
        $id = input_int(isset($_GET['id']) ? $_GET['id'] : null, 1);
        $empresa = ($id !== null) ? $conexion->obtenerEmpresa($id) : null;
        if ($empresa === null) {
          $responderJson(array('response' => 'false', 'message' => 'Cliente no encontrado.'));
          break;
        }
        $responderJson(array('response' => 'true', 'empresa' => $empresa, 'campanas' => $conexion->listarCampanas($id)));
        break;

      case 'saveEmpresa':
        $id = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
        $val = campanas_validar_empresa(array(
          'nombre' => input_string(isset($_POST['nombre']) ? $_POST['nombre'] : '', 150),
          'identificacion' => input_string(isset($_POST['identificacion']) ? $_POST['identificacion'] : '', 30),
          'status_id' => isset($_POST['status_id']) ? $_POST['status_id'] : null,
        ));
        if (!$val['ok']) {
          $responderJson(array('response' => 'false', 'message' => implode(' ', $val['errores'])));
          break;
        }
        if ($id !== null && $conexion->obtenerEmpresa($id) === null) {
          $responderJson(array('response' => 'false', 'message' => 'Cliente no encontrado.'));
          break;
        }

        $res = $conexion->guardarEmpresa($val['datos'], $id);
        if (!$res['ok']) {
          $responderJson(array('response' => 'false', 'message' => $res['message']));
          break;
        }
        app_log('info', $id === null ? 'Cliente creado' : 'Cliente actualizado', array(
          'event' => $id === null ? 'empresas.creada' : 'empresas.actualizada',
          'empresa_id' => $res['id'],
          'status_id' => $val['datos']['status_id'],
          'campanas_inactivadas' => $res['campanas_inactivadas'],
          'user_id' => $userId,
        ));
        $responderJson(array('response' => 'true', 'id' => $res['id'], 'campanas_inactivadas' => $res['campanas_inactivadas']));
        break;

      case 'saveCampana':
        $id = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
        $empresaId = input_int(isset($_POST['empresa_id']) ? $_POST['empresa_id'] : null, 1);
        if ($empresaId === null) {
          $responderJson(array('response' => 'false', 'message' => 'Cliente inválido.'));
          break;
        }
        $val = campanas_validar_campana(array(
          'nombre' => input_string(isset($_POST['nombre']) ? $_POST['nombre'] : '', 100),
          'detalle' => input_string(isset($_POST['detalle']) ? $_POST['detalle'] : '', 1000),
          'fecha_inicio' => isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
          'meta' => input_string(isset($_POST['meta']) ? $_POST['meta'] : '', 20),
          'tipo_flujo' => isset($_POST['tipo_flujo']) ? $_POST['tipo_flujo'] : null,
          'codigo' => input_string(isset($_POST['codigo']) ? $_POST['codigo'] : '', 20),
          'status_id' => isset($_POST['status_id']) ? $_POST['status_id'] : null,
        ), $id === null);
        if (!$val['ok']) {
          $responderJson(array('response' => 'false', 'message' => implode(' ', $val['errores'])));
          break;
        }

        $res = $conexion->guardarCampana($empresaId, $val['datos'], $id);
        if (!$res['ok']) {
          $responderJson(array('response' => 'false', 'message' => $res['message']));
          break;
        }
        app_log('info', $id === null ? 'Campaña creada' : 'Campaña actualizada', array(
          'event' => $id === null ? 'campanas.creada' : 'campanas.actualizada',
          'servicio_id' => $res['id'],
          'empresa_id' => $empresaId,
          'tipo_flujo' => isset($val['datos']['tipo_flujo']) ? $val['datos']['tipo_flujo'] : null,
          'status_id' => $val['datos']['status_id'],
          'user_id' => $userId,
        ));
        $responderJson(array('response' => 'true', 'id' => $res['id']));
        break;

      case 'usuarios':
        $servicioId = input_int(isset($_GET['servicio_id']) ? $_GET['servicio_id'] : null, 1);
        $campana = ($servicioId !== null) ? $conexion->obtenerCampana($servicioId) : null;
        if ($campana === null) {
          $responderJson(array('response' => 'false', 'message' => 'Campaña no encontrada.'));
          break;
        }
        $responderJson(array('response' => 'true', 'campana' => $campana, 'usuarios' => $conexion->usuariosDeCampana($servicioId)));
        break;

      case 'asignar':
        $servicioId = input_int(isset($_POST['servicio_id']) ? $_POST['servicio_id'] : null, 1);
        if ($servicioId === null || $conexion->obtenerCampana($servicioId) === null) {
          $responderJson(array('response' => 'false', 'message' => 'Campaña no encontrada.'));
          break;
        }
        $usuarios = input_int_array(isset($_POST['usuarios']) ? $_POST['usuarios'] : array(), 5000);
        $res = $conexion->asignarUsuarios($servicioId, $usuarios);
        app_log('info', 'Usuarios asignados a campaña', array(
          'event' => 'campanas.usuarios_asignados',
          'servicio_id' => $servicioId,
          'agregados' => $res['agregados'],
          'quitados' => $res['quitados'],
          'principal_movida' => $res['principal_movida'],
          'bloqueados' => count($res['bloqueados']),
          'user_id' => $userId,
        ));
        // El propio administrador puede haberse (des)asignado: refresca su selector.
        if ($userId !== null) {
          campanas_sesion_refrescar($userId);
        }
        $responderJson(array_merge(array('response' => 'true'), $res));
        break;

      default:
        app_responder_no_encontrado();
    }
  } catch (Throwable $e) {
    app_log('error', 'Error en el controlador de empresas: ' . $e->getMessage(), array(
      'event' => 'empresas.error',
      'excepcion' => get_class($e),
      'archivo' => $e->getFile(),
      'linea' => $e->getLine(),
      'modo' => $mode,
    ));

    app_responder_error();
  }
}
