<?php

/**
 * Constructor de formularios dinámicos — acceso exclusivo del rol Administrador.
 *
 * Fase 1 (Builder): CRUD + versionado + regla de conflicto de servicio + preview.
 * La carga de BD por formulario (upload / BD vigente) llega en la Fase 2.
 * Respuestas AJAX en JSON; el CSRF lo adjunta public/js/csrf.js.
 */

include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_ADMINISTRADOR));

if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  include_once(MODEL_DIR . 'constructorModel.php');
  require_once dirname(__DIR__) . '/helpers/constructorDefinicion.php';

  $conexion = new database();
  $mode = isset($_GET['mode']) ? $_GET['mode'] : 'index';
  $userId = isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;

  try {
    switch ($mode) {

      case 'index':
        $nav_crumb = 'Configuración / Constructor';
        $nav_title = 'Constructor de formularios';
        $nav_subtitle = 'Crea, versiona y asigna formularios dinámicos a los servicios.';

        include(PUBLIC_DIR . 'general/nheader.php');
        include(PUBLIC_DIR . 'general/nnavbar.php');
        $servicios = $conexion->serviciosDisponibles();
        $formularios = $conexion->listarFormularios();
        include(HTML_DIR . 'constructor/index.php');
        include(PUBLIC_DIR . 'general/nfooter.php');
        break;

      case 'list':
        $estado = input_int(isset($_GET['estado']) ? $_GET['estado'] : null, 1);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('response' => 'true', 'formularios' => $conexion->listarFormularios($estado)));
        break;

      case 'detalle':
      case 'preview':
        $id = input_int(isset($_GET['id']) ? $_GET['id'] : null, 1);
        $form = ($id !== null) ? $conexion->obtenerFormulario($id) : null;
        header('Content-Type: application/json; charset=UTF-8');
        if ($form === null) {
          echo json_encode(array('response' => 'false', 'message' => 'Formulario no encontrado.'));
          break;
        }
        echo json_encode(array('response' => 'true', 'formulario' => $form));
        break;

      case 'historial':
        $id = input_int(isset($_GET['id']) ? $_GET['id'] : null, 1);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('response' => 'true', 'historial' => ($id !== null) ? $conexion->historial($id) : array()));
        break;

      case 'save':
        $id        = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
        $nombre    = input_string(isset($_POST['nombre']) ? $_POST['nombre'] : '', 150);
        $statusId  = input_enum(
          isset($_POST['estado']) ? $_POST['estado'] : null,
          array(Constantes::REGISTRO_INACTIVO, Constantes::REGISTRO_ACTIVO),
          Constantes::REGISTRO_INACTIVO
        );
        $btnTexto  = input_string(isset($_POST['btn_texto']) ? $_POST['btn_texto'] : 'Guardar', 80);
        $servicios = input_int_array(isset($_POST['servicios']) ? $_POST['servicios'] : array());
        $nota      = input_string(isset($_POST['nota']) ? $_POST['nota'] : '', 255);
        $reasignar = !empty($_POST['reasignar']);
        $defJson   = (isset($_POST['definicion']) && is_string($_POST['definicion'])) ? $_POST['definicion'] : '';

        header('Content-Type: application/json; charset=UTF-8');

        if ($nombre === '') {
          echo json_encode(array('response' => 'false', 'message' => 'El nombre del formulario es obligatorio.'));
          break;
        }

        $definicion = constructor_decodificar_definicion($defJson);
        if ($definicion === null) {
          echo json_encode(array('response' => 'false', 'message' => 'La definición del formulario es inválida.'));
          break;
        }

        $validacion = constructor_validar_definicion($definicion);
        if (!$validacion['ok']) {
          echo json_encode(array('response' => 'false', 'message' => 'Hay errores en los campos del formulario.', 'errores' => $validacion['errores']));
          break;
        }

        $resultado = $conexion->guardarFormulario(array(
          'id'         => $id,
          'nombre'     => $nombre,
          'status_id'  => $statusId,
          'btn_texto'  => $btnTexto,
          'definicion' => $validacion['definicion'],
          'servicios'  => $servicios,
          'nota'       => $nota,
          'user_id'    => $userId,
        ), $reasignar);

        if (!$resultado['ok']) {
          echo json_encode(array('response' => 'conflict', 'conflictos' => $resultado['conflictos']));
          break;
        }

        echo json_encode(array('response' => 'true', 'id' => $resultado['id'], 'version' => $resultado['version']));
        break;

      case 'delete':
        $id = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
        header('Content-Type: application/json; charset=UTF-8');
        if ($id === null) {
          echo json_encode(array('response' => 'false', 'message' => 'Formulario inválido.'));
          break;
        }
        $conexion->eliminarFormulario($id);
        echo json_encode(array('response' => 'true'));
        break;

      case 'versionVigente':
        $id = input_int(isset($_POST['id']) ? $_POST['id'] : null, 1);
        $versionId = input_int(isset($_POST['version_id']) ? $_POST['version_id'] : null, 1);
        header('Content-Type: application/json; charset=UTF-8');
        if ($id === null || $versionId === null) {
          echo json_encode(array('response' => 'false', 'message' => 'Datos inválidos.'));
          break;
        }
        $ok = $conexion->hacerVigenteVersion($id, $versionId);
        echo json_encode(array('response' => $ok ? 'true' : 'false'));
        break;

      default:
        http_response_code(404);
        include(HTML_DIR . 'error.html');
        break;
    }
  } catch (Throwable $e) {
    app_log('error', 'Error en el controlador constructor: ' . $e->getMessage(), array(
      'excepcion' => get_class($e),
      'archivo' => $e->getFile(),
      'linea' => $e->getLine(),
      'modo' => $mode,
    ));

    app_responder_error();
  }
}
