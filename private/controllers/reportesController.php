<?php
include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_ADMINISTRADOR));
if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  include_once(MODEL_DIR . 'reportesModel.php');
  $conn = new database();
  if (isset($_GET['mode'])) {
    try {
      switch ($_GET['mode']) {
        case 'index':
          include(PUBLIC_DIR . 'general/nheader.php');
          include(PUBLIC_DIR . 'general/nnavbar.php');
          $id = [Constantes::SERVICIO_BANCARIO, Constantes::SERVICIO_BANCARIO_ALIAS];
          $servicio = $conn->servicio($id);
          include(HTML_DIR . 'reportes/index.php');
          include(PUBLIC_DIR . 'general/nfooter.php');
          break;

        case 'acumulado':
          $desde = input_date(isset($_POST['fecha_d']) ? $_POST['fecha_d'] : null);
          $hasta = input_date(isset($_POST['fecha_h']) ? $_POST['fecha_h'] : null);
          $productoId = (int) input_int(isset($_POST['producto']) ? $_POST['producto'] : null, 1, null, 0);
          
          if ($desde === null || $hasta === null) {
            $desde = date('Y-m-d');
            $hasta = $desde;
          }
          $servicioSel = (int) input_enum(isset($_POST['servicio']) ? $_POST['servicio'] : null, Constantes::servicios(), 1);
          $serv = [Constantes::SERVICIO_BANCARIO, Constantes::SERVICIO_BANCARIO_ALIAS];

          $servicio = $conn->servicio($serv);
          $idEfectivo = $conn->efectivo($serv);
          $idEfectivoId = !empty($idEfectivo[0]['id']) ? $idEfectivo[0]['id'] : 0;

          $gestion = $conn->clientesGestionados($desde, $hasta, $servicioSel, $idEfectivoId, $productoId);
          $ventasProducto = $conn->ventasPorProducto($desde, $hasta, $servicioSel, $idEfectivoId, $productoId);

          // Motivos de contacto (para el modal "Ver motivos de contacto")
          $motivosNC = $conn->gestionContactonoefectivo($desde, $hasta, $servicioSel);
          $motivosC  = $conn->gestionContactoefectivo($desde, $hasta, $servicioSel);

          // Datos para el hero del navbar
          $nav_service = '';
          foreach ($servicio as $s) {
            if ($s['id'] == $servicioSel) { $nav_service = $s['descripcion']; break; }
          }
          $nav_period = ($desde === $hasta)
            ? date('d/m/Y', strtotime($desde))
            : date('d/m/Y', strtotime($desde)) . ' – ' . date('d/m/Y', strtotime($hasta));

          include(PUBLIC_DIR . 'general/nheader.php');
          include(PUBLIC_DIR . 'general/nnavbar.php');
          include(HTML_DIR . 'reportes/index.php');
          include(PUBLIC_DIR . 'general/nfooter.php');
          break;

          case 'producto':
            header('Content-Type: application/json');
            $servicioProd = (int) input_enum(isset($_POST['id']) ? $_POST['id'] : null, Constantes::servicios(), 0);
            echo json_encode($servicioProd > 0 ? $conn->getProduct($servicioProd) : []);
            break;

          case 'contactabilidad':
          include(PUBLIC_DIR . 'general/header.php');
          include(PUBLIC_DIR . 'general/navbar.php');
          $desde = input_date(isset($_POST['fecha_d']) ? $_POST['fecha_d'] : null);
          $hasta = input_date(isset($_POST['fecha_h']) ? $_POST['fecha_h'] : null);
          if ($desde === null || $hasta === null) {
            $desde = date('Y-m-d');
            $hasta = $desde;
          }
          $serv = (int) input_enum(isset($_POST['servicio']) ? $_POST['servicio'] : null, Constantes::servicios(), 1);
          $id = [Constantes::SERVICIO_BANCARIO, Constantes::SERVICIO_BANCARIO_ALIAS];

          $servicio = $conn->servicio($id);
          $resultn = $conn->gestionContactonoefectivo($desde, $hasta, $serv);
          $resulte = $conn->gestionContactoefectivo($desde, $hasta, $serv);
          include(HTML_DIR . 'reportes/contactabilidad.php');
          include(PUBLIC_DIR . 'general/footer.php');
          break;

        case 'liberar':
          $nav_crumb    = 'Reportes / Desbloqueo de clientes';
          $nav_title    = 'Desbloqueo de clientes Banca';
          $nav_subtitle = 'Libera a un cliente que ya posee una venta para que pueda volver a ser gestionado.';
          include(PUBLIC_DIR . 'general/nheader.php');
          include(PUBLIC_DIR . 'general/nnavbar.php');
          include(HTML_DIR . 'reportes/liberar.php');
          include(PUBLIC_DIR . 'general/nfooter.php');
          break;

        case 'cliente':
          $clienteBuscado = input_string(isset($_POST['cliente']) ? $_POST['cliente'] : '', 30);
          $cliente = ($clienteBuscado === '') ? false : $conn->buscarCliente($clienteBuscado);
          if ($cliente == false) {
            $json['response'] = 'false';
          } else {
            $json['response'] = 'true';
            if (isset($cliente['nombre_legal'])) {
              $json['nombre'] = $cliente['nombre_legal'];
              $json['hab'] = ($cliente['telf_hab'] == null)? 'No posee' : $cliente['telf_hab'];
              $json['ofi'] = ($cliente['telf_ofi'] == null)? 'No posee' : $cliente['telf_ofi'];
              $json['cel'] = ($cliente['telf_cel'] == null)? 'No posee' : $cliente['telf_cel'];
              $json['cedula'] = $cliente['identificacion'];
              $json['servicio'] = $cliente['descripcion'];
              $json['status'] = strtoupper($cliente['name']);
            } else {
              $json['nombre'] = $cliente['nombre'] . ' ' . $cliente['apellido'];
              $json['hab'] = $json['ofi'] = $json['cel'] = $cliente['telefono'];
              $json['cedula'] = $cliente['cedula'];
            }
          }
          echo json_encode($json);
          break;

        case 'disponible':
          $clienteBuscado = input_string(isset($_POST['cliente']) ? $_POST['cliente'] : '', 30);
          $cliente = ($clienteBuscado === '') ? false : $conn->liberarCliente($clienteBuscado);
          if ($cliente == false) {
            $json['response'] = 'false';
          } else {
            $json['response'] = 'true';
          }
          echo json_encode($json);
          break;

        case 'ventas':
          $serv = null;
          if (isset($_POST['servicio']) && $_POST['servicio'] !== '') {
            $serv = input_enum($_POST['servicio'], Constantes::servicios());
            $serv = ($serv === null) ? null : (int) $serv;
          }

          $fechaD = input_date(isset($_POST['fecha_d']) ? $_POST['fecha_d'] : null);
          $fechaH = input_date(isset($_POST['fecha_h']) ? $_POST['fecha_h'] : null);
          if ($fechaD !== null && $fechaH !== null) {
            $desde = date('Ymd', strtotime($fechaD));
            $hasta = date('Ymd', strtotime($fechaH));
            $nav_period = ($fechaD === $fechaH)
              ? date('d/m/Y', strtotime($fechaD))
              : date('d/m/Y', strtotime($fechaD)) . ' – ' . date('d/m/Y', strtotime($fechaH));
          } else {
            $desde = date('Ymd');
            $hasta = $desde;
            $d = $h = date('Y-m-d');
            $nav_period = date('d/m/Y');
          }

          $id = [Constantes::SERVICIO_BANCARIO, Constantes::SERVICIO_BANCARIO_ALIAS];
          $servicio = $conn->servicio($id);
          $result = $conn->ventasEstado($desde, $hasta, $serv);

          $nav_crumb   = 'Reportes / Ventas por estado';
          $nav_title   = 'Resumen de ventas por estado';
          $nav_service = null;
          foreach ($servicio as $s) { if ($s['id'] == $serv) { $nav_service = $s['descripcion']; break; } }
          if ($nav_service === null) { $nav_period = null; }

          include(PUBLIC_DIR . 'general/nheader.php');
          include(PUBLIC_DIR . 'general/nnavbar.php');
          include(HTML_DIR . 'reportes/ventas.php');
          include(PUBLIC_DIR . 'general/nfooter.php');
          break;

        case 'ventasxestado':
          $desde = input_digits(isset($_GET['d']) ? $_GET['d'] : '', 8);
          $hasta = input_digits(isset($_GET['h']) ? $_GET['h'] : '', 8);
          if ($desde === '' || $hasta === '') {
            $desde = $hasta = date('Ymd');
          }
          $id_estado = (int) (input_int(isset($_GET['id']) ? $_GET['id'] : null, 0) ?? 0);
          $estado = input_string(isset($_GET['estado']) ? $_GET['estado'] : '', 100);
          $serv = (int) input_enum(isset($_GET['servicio']) ? $_GET['servicio'] : null, Constantes::servicios(), 1);
          $result = $conn->ventasCiudad($desde, $hasta, $id_estado, $serv);

          $id = [Constantes::SERVICIO_BANCARIO, Constantes::SERVICIO_BANCARIO_ALIAS];
          $servicio = $conn->servicio($id);
          $nav_service = null;
          foreach ($servicio as $s) { if ($s['id'] == $serv) { $nav_service = $s['descripcion']; break; } }
          $fd = DateTime::createFromFormat('Ymd', $desde);
          $fh = DateTime::createFromFormat('Ymd', $hasta);
          $nav_period = ($fd && $fh)
            ? ($desde === $hasta ? $fd->format('d/m/Y') : $fd->format('d/m/Y') . ' – ' . $fh->format('d/m/Y'))
            : null;
          // Fechas en Y-m-d para re-enviar el filtro al volver
          $backDesde = $fd ? $fd->format('Y-m-d') : '';
          $backHasta = $fh ? $fh->format('Y-m-d') : '';
          $nav_crumb  = 'Reportes / Ventas por estado / ' . htmlspecialchars($estado, ENT_QUOTES, 'UTF-8');
          $nav_title  = 'Detalle de ventas por estado';
          $nav_accent = $estado;

          include(PUBLIC_DIR . 'general/nheader.php');
          include(PUBLIC_DIR . 'general/nnavbar.php');
          include(HTML_DIR . 'reportes/ventasxciudad.php');
          include(PUBLIC_DIR . 'general/nfooter.php');
          break;

        case 'detalleventas':
          $id = [Constantes::SERVICIO_BANCARIO, Constantes::SERVICIO_BANCARIO_ALIAS];
          $servicio = $conn->servicio($id);

          $fechaD = input_date(isset($_POST['fecha_d']) ? $_POST['fecha_d'] : null);
          $fechaH = input_date(isset($_POST['fecha_h']) ? $_POST['fecha_h'] : null);
          if ($fechaD !== null && $fechaH !== null) {
            $desde = date('Ymd', strtotime($fechaD));
            $hasta = date('Ymd', strtotime($fechaH));
            $selServicio = input_enum(isset($_POST['servicio']) ? $_POST['servicio'] : null, Constantes::servicios(), '');
            $formatoVenta = Constantes::esServicioBancario($selServicio) ? 'Ymd' : 'Y-m-d';
            $ventas = ($selServicio === '') ? [] : $conn->ventaDetallada(
              date($formatoVenta, strtotime($fechaD)),
              date($formatoVenta, strtotime($fechaH)),
              $selServicio
            );

            $nav_service = null;
            foreach ($servicio as $s) { if ($s['id'] == $selServicio) { $nav_service = $s['descripcion']; break; } }
            $nav_period = ($fechaD === $fechaH)
              ? date('d/m/Y', strtotime($fechaD))
              : date('d/m/Y', strtotime($fechaD)) . ' – ' . date('d/m/Y', strtotime($fechaH));
            $backDesde = $fechaD;
            $backHasta = $fechaH;
          } else {
            $desde = date('Ymd');
            $hasta = $desde;
            $selServicio = '';
            $ventas = [];
            $backDesde = $backHasta = '';
          }

          $nav_crumb = 'Reportes / Detalle de ventas';
          $nav_title = 'Resumen de ventas';

          include(PUBLIC_DIR . 'general/nheader.php');
          include(PUBLIC_DIR . 'general/nnavbar.php');
          include(HTML_DIR . 'reportes/ventasDetalle.php');
          include(PUBLIC_DIR . 'general/nfooter.php');
          break;

        case 'download':
          header("Content-type: application/vnd.ms-excel");
          header("Content-Disposition: attachment; filename=gestionDetalladas.xls");
          $rawDesde = input_string(isset($_GET['from']) ? $_GET['from'] : '', 10);
          $rawHasta = input_string(isset($_GET['to']) ? $_GET['to'] : '', 10);
          $fechaDesde = input_date($rawDesde);
          if ($fechaDesde === null && preg_match('/^\d{8}$/', $rawDesde)) {
            $fechaDesde = DateTime::createFromFormat('!Ymd', $rawDesde);
          }
          $fechaHasta = input_date($rawHasta);
          if ($fechaHasta === null && preg_match('/^\d{8}$/', $rawHasta)) {
            $fechaHasta = DateTime::createFromFormat('!Ymd', $rawHasta);
          }
          if (!$fechaDesde || !$fechaHasta) {
            $fechaDesde = $fechaHasta = new DateTime('today');
          }
          $servicioDescarga = input_enum(isset($_GET['servicio']) ? $_GET['servicio'] : null, Constantes::servicios(), '1');
          $formatoDescarga = Constantes::esServicioBancario($servicioDescarga) ? 'Ymd' : 'Y-m-d';
          $ventas = $conn->ventaDetallada($fechaDesde->format($formatoDescarga), $fechaHasta->format($formatoDescarga), $servicioDescarga);
            echo '<table class="table table-responsive table-hover table-condensed">';
            echo '<thead>
                    <tr>
                      <th>Nombre</th>
                      <th>Apellido</th>
                      <th>Cedula</th>
                      <th>Fecha de nacimiento</th>
                      <th>Teléfono 1</th>
                      <th>Teléfono 2</th>
                      <th>Correo</th>
                     <th>Producto</th>
                      <th>Agente</th>
                      <th>Fecha</th>
                    </tr>
                  </thead>
                  <tbody>';

            if (!empty($ventas)) {
              foreach ($ventas as $venta) {
                echo '<tr>
                        <td>' . htmlspecialchars((string) $venta['nombre'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['apellido'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['cedula'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['fecha_nacimiento'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['telf_hab'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['telf_celular'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['correo'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['descripcion'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['agente'], ENT_QUOTES, 'UTF-8') . '</td>
                        <td>' . htmlspecialchars((string) $venta['fecha_venta'], ENT_QUOTES, 'UTF-8') . '</td>
                      </tr>';
              }
            }

            echo '</tbody></table>';

          break;
      
        default:
          header('location:' . HTML_DIR . 'error.html');
          break;
      }
    } catch (Throwable $e) {
      app_log('error', 'Error en el controlador reportes: ' . $e->getMessage(), array(
        'excepcion' => get_class($e),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine(),
        'modo' => isset($_GET['mode']) ? $_GET['mode'] : '',
      ));

      app_responder_error();
    }
  }
}
