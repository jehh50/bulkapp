<?php

/**
 * Captura de formularios dinámicos — runtime del operador (Constructor, Fase 3).
 *
 * Solo servicios NO bancarios: los de banca siguen en formularioController, intacto.
 * Layout como banca: card «Datos de la búsqueda» (BD vigente) a la izquierda y el
 * formulario a la derecha; sin BD vigente el formulario se centra y se captura directo.
 * Con BD, cada registro admite un solo envío (queda atendido).
 *
 * Reportería (Supervisor/Administrador): filtro de envíos y exportación CSV.
 */

include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_AGENTE, Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));

if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  include_once(MODEL_DIR . 'capturaModel.php');
  require_once dirname(__DIR__) . '/helpers/captura.php';

  $conexion = new database();
  $mode = isset($_GET['mode']) ? $_GET['mode'] : 'index';
  $userId = isset($_SESSION['id']) ? (int) $_SESSION['id'] : null;
  $servicioId = isset($_SESSION['servicio_id']) ? (int) $_SESSION['servicio_id'] : 0;

  try {
    switch ($mode) {

      case 'index':
        if (Constantes::esServicioBancario($servicioId)) {
          header('location:?view=formulario&mode=index');
          exit;
        }
        $form = $conexion->formularioActivoDeServicio($servicioId);
        $bd = ($form !== null) ? $conexion->cargaVigente($form['id']) : null;

        $nav_crumb = 'Inicio / Formulario';
        $nav_title = ($form !== null) ? $form['nombre'] : 'Formulario de gestión';
        $nav_subtitle = ($bd !== null)
          ? 'Busca el registro para cargar sus datos y completa el formulario.'
          : 'Completa el formulario y guarda la gestión.';

        include(PUBLIC_DIR . 'general/nheader.php');
        include(PUBLIC_DIR . 'general/nnavbar.php');
        include(HTML_DIR . 'captura/index.php');
        include(PUBLIC_DIR . 'general/nfooter.php');
        break;

      case 'buscar':
        header('Content-Type: application/json; charset=UTF-8');
        $form = $conexion->formularioActivoDeServicio($servicioId);
        $bd = ($form !== null) ? $conexion->cargaVigente($form['id']) : null;
        if ($bd === null) {
          echo json_encode(array('response' => 'false', 'message' => 'No hay base de datos para buscar.'));
          break;
        }
        $clave = input_string(isset($_POST['clave']) ? $_POST['clave'] : '', 191);
        if ($clave === '') {
          echo json_encode(array('response' => 'false', 'message' => 'Ingresa un valor para buscar.'));
          break;
        }
        $registro = $conexion->buscarRegistro($bd['id'], $clave);
        if ($registro === null) {
          echo json_encode(array('response' => 'false', 'message' => 'No se encontró el registro.'));
        } elseif ($registro['atendido']) {
          echo json_encode(array('response' => 'atendido', 'message' => 'Este registro ya fue atendido.'));
        } else {
          echo json_encode(array('response' => 'true', 'registro' => array('id' => $registro['id'], 'datos' => $registro['datos'])));
        }
        break;

      case 'guardar':
        header('Content-Type: application/json; charset=UTF-8');
        $form = $conexion->formularioActivoDeServicio($servicioId);
        if ($form === null) {
          echo json_encode(array('response' => 'false', 'message' => 'Tu servicio no tiene un formulario activo.'));
          break;
        }

        $valoresRaw = (isset($_POST['valores']) && is_string($_POST['valores'])) ? json_decode($_POST['valores'], true) : null;
        $validacion = captura_validar_valores($form['definicion'], $valoresRaw);
        if (!$validacion['ok']) {
          echo json_encode(array('response' => 'false', 'message' => 'Revisa los campos marcados.', 'errores' => $validacion['errores']));
          break;
        }

        $envio = array(
          'formulario_id' => $form['id'],
          'version_id' => $form['version_id'],
          'servicio_id' => $servicioId,
          'valores' => $validacion['valores'],
          'user_id' => $userId,
        );

        $bd = $conexion->cargaVigente($form['id']);
        if ($bd !== null) {
          $filaId = input_int(isset($_POST['fila_id']) ? $_POST['fila_id'] : null, 1);
          $registro = ($filaId !== null) ? $conexion->registro($filaId, $bd['id']) : null;
          if ($registro === null) {
            echo json_encode(array('response' => 'false', 'message' => 'Busca un registro antes de guardar.'));
            break;
          }
          if ($registro['atendido']) {
            echo json_encode(array('response' => 'atendido', 'message' => 'Este registro ya fue atendido.'));
            break;
          }
          $envio['carga_id'] = $bd['id'];
          $envio['fila_id'] = $registro['id'];
          $envio['registro'] = $registro['datos'];
        }

        $id = $conexion->guardarEnvio($envio);
        if ($id === false) {
          echo json_encode(array('response' => 'atendido', 'message' => 'Este registro ya fue atendido.'));
          break;
        }
        echo json_encode(array('response' => 'true', 'id' => $id));
        break;

      case 'reporte':
      case 'exportar':
        security_requerir_rol(array(Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));

        $formularios = $conexion->formulariosReporte();
        $servicios = $conexion->serviciosReporte();
        $idsForm = array_map('intval', array_column($formularios, 'id'));
        $idsServ = array_map('intval', array_column($servicios, 'id'));

        $fFormulario = input_enum(isset($_GET['formulario']) ? (int) $_GET['formulario'] : null, $idsForm);
        $fServicio = input_enum(isset($_GET['servicio']) ? (int) $_GET['servicio'] : null, $idsServ);
        $fDesde = input_date(isset($_GET['desde']) ? $_GET['desde'] : '');
        $fHasta = input_date(isset($_GET['hasta']) ? $_GET['hasta'] : '');
        if ($fDesde === null) {
          $fDesde = date('Y-m-01');
        }
        if ($fHasta === null) {
          $fHasta = date('Y-m-d');
        }

        if ($mode === 'exportar') {
          if ($fFormulario === null) {
            header('location:?view=captura&mode=reporte');
            exit;
          }
          $columnas = captura_columnas_reporte(
            $conexion->definicionesDeFormulario($fFormulario),
            $conexion->mappingsDeFormulario($fFormulario)
          );
          $envios = $conexion->envios($fFormulario, $fServicio, $fDesde, $fHasta);

          $nombre = 'envios_formulario_' . $fFormulario . '_' . $fDesde . '_' . $fHasta . '.csv';
          header('Content-Type: text/csv; charset=UTF-8');
          header('Content-Disposition: attachment; filename="' . $nombre . '"');
          $out = fopen('php://output', 'w');
          fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel lea UTF-8
          fputcsv($out, array_merge(
            array('ID', 'Fecha', 'Servicio', 'Operador', 'Usuario', 'Versión'),
            array_values($columnas['bd']),
            array_values($columnas['campos'])
          ), ';');
          foreach ($envios as $e) {
            $registro = json_decode((string) $e['registro'], true);
            $valores = json_decode((string) $e['valores'], true);
            fputcsv($out, array_merge(
              array($e['id'], $e['fecha'], $e['servicio'], $e['operador'], $e['usuario'], $e['version']),
              captura_celdas_reporte(is_array($registro) ? $registro : array(), is_array($valores) ? $valores : array(), $columnas)
            ), ';');
          }
          fclose($out);
          exit;
        }

        $total = 0;
        $envios = array();
        $columnas = array('bd' => array(), 'campos' => array());
        if ($fFormulario !== null) {
          $total = $conexion->contarEnvios($fFormulario, $fServicio, $fDesde, $fHasta);
          $envios = $conexion->envios($fFormulario, $fServicio, $fDesde, $fHasta, 100);
          $columnas = captura_columnas_reporte(
            $conexion->definicionesDeFormulario($fFormulario),
            $conexion->mappingsDeFormulario($fFormulario)
          );
        }

        $nav_crumb = 'Reportes / Formularios dinámicos';
        $nav_title = 'Envíos de formularios dinámicos';
        $nav_subtitle = 'Filtra los envíos por formulario, servicio y fechas, y expórtalos a CSV.';
        include(PUBLIC_DIR . 'general/nheader.php');
        include(PUBLIC_DIR . 'general/nnavbar.php');
        include(HTML_DIR . 'captura/reporte.php');
        include(PUBLIC_DIR . 'general/nfooter.php');
        break;

      default:
        http_response_code(404);
        include(HTML_DIR . 'error.html');
        break;
    }
  } catch (Throwable $e) {
    app_log('error', 'Error en el controlador captura: ' . $e->getMessage(), array(
      'excepcion' => get_class($e),
      'archivo' => $e->getFile(),
      'linea' => $e->getLine(),
      'modo' => $mode,
    ));

    app_responder_error();
  }
}
