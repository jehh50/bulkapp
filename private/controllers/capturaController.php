<?php

/**
 * Captura de formularios dinámicos — runtime del operador (Constructor, Fase 3).
 *
 * Solo servicios NO bancarios: los de banca siguen en formularioController, intacto.
 * Layout como banca: card «Datos de la búsqueda» (BD vigente) a la izquierda y el
 * formulario a la derecha; sin BD vigente el formulario se centra y se captura directo.
 * Con BD, cada registro admite un solo envío (queda atendido).
 *
 * Reportería (Supervisor/Administrador): cliente → campaña → formulario, paginada,
 * con exportación a .xlsx (todas las filas del filtro).
 */

include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_AGENTE, Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR));

if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  include_once(MODEL_DIR . 'capturaModel.php');
  require_once dirname(__DIR__) . '/helpers/captura.php';
  require_once dirname(__DIR__) . '/lib/xlsx.php';

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

        $arbol = captura_arbol_reporte($conexion->combinacionesReporte());

        $fCliente = input_int(isset($_GET['cliente']) ? $_GET['cliente'] : null, 1);
        $fCampana = input_int(isset($_GET['campana']) ? $_GET['campana'] : null, 1);
        $fFormulario = input_int(isset($_GET['formulario']) ? $_GET['formulario'] : null, 1);
        $fDesde = input_date(isset($_GET['desde']) ? $_GET['desde'] : '');
        $fHasta = input_date(isset($_GET['hasta']) ? $_GET['hasta'] : '');
        if ($fDesde === null) {
          $fDesde = date('Y-m-01');
        }
        if ($fHasta === null) {
          $fHasta = date('Y-m-d');
        }
        if ($fDesde > $fHasta) {
          list($fDesde, $fHasta) = array($fHasta, $fDesde);
        }
        $porPagina = (int) input_enum(
          isset($_GET['n']) ? $_GET['n'] : null,
          Constantes::filasPorPaginaReporte(),
          Constantes::REPORTE_FILAS_POR_DEFECTO
        );

        // Solo combinaciones existentes en el árbol (ids manipulados → sin selección).
        $seleccion = ($fCliente !== null && $fCampana !== null && $fFormulario !== null)
          ? captura_seleccion_reporte($arbol, $fCliente, $fCampana, $fFormulario)
          : null;

        $columnas = array('bd' => array(), 'campos' => array());
        $definiciones = array();
        $version = null;
        if ($seleccion !== null) {
          $definiciones = $conexion->definicionesDeFormulario($fFormulario);
          $columnas = captura_columnas_reporte($definiciones, $conexion->mappingsDeFormulario($fFormulario));
          $version = $conexion->versionVigente($fFormulario);
        }

        if ($mode === 'exportar') {
          if ($seleccion === null) {
            header('location:?view=captura&mode=reporte');
            exit;
          }
          @set_time_limit(300);
          $tipos = captura_tipos_campos($definiciones);
          $etiquetaGrupo = array(XLSX_ESTILO_ENCABEZADO_1, XLSX_ESTILO_ENCABEZADO_2, XLSX_ESTILO_ENCABEZADO_3);

          $encabezado = array();
          $anchos = array();
          foreach (array('ID' => 8, 'Fecha' => 17, 'Operador' => 24, 'Usuario' => 14) as $t => $w) {
            $encabezado[] = xlsx_celda($t, $etiquetaGrupo[0]);
            $anchos[] = $w;
          }
          foreach ($columnas['bd'] as $label) {
            $encabezado[] = xlsx_celda($label, $etiquetaGrupo[1]);
            $anchos[] = max(12, min(40, mb_strlen($label) + 4));
          }
          foreach ($columnas['campos'] as $label) {
            $encabezado[] = xlsx_celda($label, $etiquetaGrupo[2]);
            $anchos[] = max(12, min(40, mb_strlen($label) + 4));
          }
          $encabezado[] = xlsx_celda('Versión', $etiquetaGrupo[0]);
          $anchos[] = 9;

          $hoja = xlsx_hoja_abrir('Registros', $anchos, true);
          xlsx_hoja_fila($hoja, $encabezado);
          $total = 0;
          $desplazamiento = 0;
          $tanda = Constantes::REPORTE_TANDA_EXPORTACION;
          do {
            $envios = $conexion->envios($fFormulario, $fCampana, $fDesde, $fHasta, $tanda, $desplazamiento);
            foreach ($envios as $e) {
              $registro = json_decode((string) $e['registro'], true);
              $valores = json_decode((string) $e['valores'], true);
              xlsx_hoja_fila($hoja, array_merge(
                array(
                  xlsx_numero($e['id']),
                  xlsx_fecha($e['fecha']),
                  xlsx_celda($e['operador'] !== '' ? $e['operador'] : (string) $e['usuario']),
                  xlsx_celda((string) $e['usuario']),
                ),
                captura_celdas_xlsx(is_array($registro) ? $registro : array(), is_array($valores) ? $valores : array(), $columnas, $tipos),
                array(xlsx_numero($e['version']))
              ));
            }
            $total += count($envios);
            $desplazamiento += $tanda;
          } while (count($envios) === $tanda);
          xlsx_hoja_cerrar($hoja);

          $usuario = isset($_SESSION['user']) ? (string) $_SESSION['user'] : '';
          $filtros = xlsx_hoja_abrir('Filtros', array(22, 60));
          foreach (array(
            'Cliente' => $seleccion['cliente']['nombre'],
            'Campaña' => $seleccion['campana']['nombre'],
            'Formulario' => $seleccion['formulario']['nombre'],
            'Versión vigente' => $version !== null ? 'v' . $version : '—',
            'Período' => date('d/m/Y', strtotime($fDesde)) . ' – ' . date('d/m/Y', strtotime($fHasta)),
            'Registros' => (string) $total,
            'Generado por' => $usuario,
            'Fecha de generación' => date('d/m/Y H:i'),
          ) as $k => $v) {
            xlsx_hoja_fila($filtros, array(xlsx_celda($k, XLSX_ESTILO_NEGRITA), xlsx_celda($v)));
          }
          xlsx_hoja_cerrar($filtros);

          $archivo = tempnam(sys_get_temp_dir(), 'rep_');
          xlsx_empaquetar($archivo, array($hoja, $filtros));

          $nombre = 'reporte_' . captura_slug($seleccion['cliente']['nombre'])
            . '_' . captura_slug($seleccion['campana']['nombre'])
            . '_' . captura_slug($seleccion['formulario']['nombre'])
            . '_' . $fDesde . '_' . $fHasta . '.xlsx';

          app_log('info', 'Reporte de formulario dinámico exportado', array(
            'evento' => 'reporte.formulario_exportado',
            'formulario_id' => $fFormulario,
            'servicio_id' => $fCampana,
            'desde' => $fDesde,
            'hasta' => $fHasta,
            'registros' => $total,
            'user_id' => $userId,
          ));

          header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header('Content-Disposition: attachment; filename="' . $nombre . '"');
          header('Content-Length: ' . filesize($archivo));
          header('Cache-Control: no-store');
          readfile($archivo);
          @unlink($archivo);
          exit;
        }

        $total = 0;
        $envios = array();
        $pagina = 1;
        $totalPaginas = 1;
        if ($seleccion !== null) {
          $total = $conexion->contarEnvios($fFormulario, $fCampana, $fDesde, $fHasta);
          $totalPaginas = max(1, (int) ceil($total / $porPagina));
          $pagina = (int) input_int(isset($_GET['p']) ? $_GET['p'] : null, 1, null, 1);
          $pagina = min($pagina, $totalPaginas);
          $envios = $conexion->envios($fFormulario, $fCampana, $fDesde, $fHasta, $porPagina, ($pagina - 1) * $porPagina);
        }

        $nav_crumb = 'Reportes / Formularios dinámicos';
        $nav_title = 'Reporte de formularios';
        $nav_subtitle = 'Elige cliente, campaña y formulario para ver sus registros y exportarlos a Excel.';
        if ($seleccion !== null) {
          $nav_service_label = 'Formulario consultado';
          $nav_service = $seleccion['formulario']['nombre'];
          $nav_service_sub = campanas_etiqueta($seleccion['cliente']['nombre'], $seleccion['campana']['nombre']);
          $nav_period = date('d/m/Y', strtotime($fDesde)) . ' – ' . date('d/m/Y', strtotime($fHasta));
        }
        include(PUBLIC_DIR . 'general/nheader.php');
        include(PUBLIC_DIR . 'general/nnavbar.php');
        include(HTML_DIR . 'captura/reporte.php');
        include(PUBLIC_DIR . 'general/nfooter.php');
        break;

      default:
        app_responder_no_encontrado();
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
