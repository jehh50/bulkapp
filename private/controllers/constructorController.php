<?php

/**
 * Constructor de formularios dinámicos — acceso exclusivo del rol Administrador.
 *
 * Fase 1 (Builder): CRUD + versionado + regla de conflicto de servicio + preview.
 * Fase 2 (BD por formulario): carga CSV/XLSX con columnas propias (card «Datos de la
 * búsqueda», independiente de los campos del formulario), vigente e historial.
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
  require_once dirname(__DIR__) . '/helpers/constructorCarga.php';

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
        echo json_encode(array('response' => 'true', 'formulario' => $form, 'bd' => $conexion->cargaVigente($id)));
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
        // Banca conserva su formulario fijo: se descartan servicios bancarios.
        $servicios = array_values(array_filter($servicios, function ($s) {
          return !Constantes::esServicioBancario($s);
        }));
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

      case 'cargaAnalizar':
        header('Content-Type: application/json; charset=UTF-8');
        $val = constructor_validar_subida(isset($_FILES['archivo']) ? $_FILES['archivo'] : null);
        if (!$val[0]) {
          echo json_encode(array('response' => 'false', 'message' => $val[1]));
          break;
        }
        $parseado = constructor_parsear_archivo($_FILES['archivo']['tmp_name'], $val[1]);
        if ($parseado === null) {
          echo json_encode(array('response' => 'false', 'message' => 'No se pudo leer el archivo.'));
          break;
        }
        if (empty($parseado['headers'])) {
          echo json_encode(array('response' => 'false', 'message' => 'El archivo no tiene encabezados.'));
          break;
        }
        echo json_encode(array(
          'response' => 'true',
          'headers' => $parseado['headers'],
          'filas_total' => count($parseado['filas']),
          'muestra' => array_slice($parseado['filas'], 0, 5),
        ));
        break;

      case 'cargaConfirmar':
        header('Content-Type: application/json; charset=UTF-8');
        $formId = input_int(isset($_POST['form_id']) ? $_POST['form_id'] : null, 1);
        if ($formId === null || $conexion->obtenerFormulario($formId) === null) {
          echo json_encode(array('response' => 'false', 'message' => 'Formulario inválido.'));
          break;
        }
        $val = constructor_validar_subida(isset($_FILES['archivo']) ? $_FILES['archivo'] : null);
        if (!$val[0]) {
          echo json_encode(array('response' => 'false', 'message' => $val[1]));
          break;
        }
        $parseado = constructor_parsear_archivo($_FILES['archivo']['tmp_name'], $val[1]);
        if ($parseado === null) {
          echo json_encode(array('response' => 'false', 'message' => 'No se pudo leer el archivo.'));
          break;
        }
        $defRaw = (isset($_POST['columnas']) && is_string($_POST['columnas'])) ? json_decode($_POST['columnas'], true) : null;
        $norm = constructor_normalizar_columnas($defRaw, $parseado['headers']);
        if (!$norm['ok']) {
          echo json_encode(array('response' => 'false', 'message' => implode(' ', $norm['errores'])));
          break;
        }
        $filas = constructor_aplicar_columnas($parseado['headers'], $parseado['filas'], $norm['columnas']);
        if (empty($filas)) {
          echo json_encode(array('response' => 'false', 'message' => 'Ninguna fila tiene valor en la columna de búsqueda.'));
          break;
        }
        $nombreArchivo = input_string(isset($_FILES['archivo']['name']) ? $_FILES['archivo']['name'] : 'carga', 255);
        $res = $conexion->guardarCarga($formId, $nombreArchivo, $norm['columnas'], $filas, $userId);
        echo json_encode(array(
          'response' => 'true',
          'carga_id' => $res['carga_id'],
          'filas' => $res['filas'],
          'omitidas' => count($parseado['filas']) - $res['filas'],
        ));
        break;

      case 'cargas':
        $id = input_int(isset($_GET['id']) ? $_GET['id'] : null, 1);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array(
          'response' => 'true',
          'cargas' => ($id !== null) ? $conexion->cargasDeFormulario($id) : array(),
          'vigente' => ($id !== null) ? $conexion->cargaVigente($id) : null,
        ));
        break;

      case 'cargaFilas':
        $cargaId = input_int(isset($_GET['carga_id']) ? $_GET['carga_id'] : null, 1);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('response' => 'true', 'filas' => ($cargaId !== null) ? $conexion->filasDeCarga($cargaId, 10) : array()));
        break;

      case 'cargaSetVigente':
        $formId = input_int(isset($_POST['form_id']) ? $_POST['form_id'] : null, 1);
        $cargaId = input_int(isset($_POST['carga_id']) ? $_POST['carga_id'] : null, 1);
        header('Content-Type: application/json; charset=UTF-8');
        if ($formId === null || $cargaId === null) {
          echo json_encode(array('response' => 'false', 'message' => 'Datos inválidos.'));
          break;
        }
        $ok = $conexion->hacerVigenteCarga($formId, $cargaId);
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
