<?php
include(PUBLIC_DIR . 'general/session.php');
if (empty($_SESSION)) {
  header('location:index.php');
} else {
  include_once(MODEL_DIR . 'reportesModel.php');
  $conn = new database();
  if (isset($_GET['mode'])) {
    switch ($_GET['mode']) {
      case 'index':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        $servicio = $conn->servicio();
        include(HTML_DIR . 'reportes/index.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;
      #-------------------------------------------------------------------------------------------
      case 'acumulado':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        if (isset($_POST['fecha_d']) || isset($_POST['fecha_h'])) {
          $desde = $_POST['fecha_d'];
          $hasta = $_POST['fecha_h'];
        } else {
          $desde = date('Y-m-d');
          $hasta = $desde;
        }
        if (!empty($_POST['servicio'])) {
          $serv = $_POST['servicio'];
        }

        $servicio = $conn->servicio();
        $idEfectivo = $conn->efectivo($serv);

        $gestion = $conn->clientesGestionados($desde, $hasta, $serv, $idEfectivo[0]['id']);
        include(HTML_DIR . 'reportes/index.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;
      #-------------------------------------------------------------------------------------------
      case 'contactabilidad':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        if (isset($_POST['fecha_d']) || isset($_POST['fecha_h'])) {
          $desde = $_POST['fecha_d'];
          $hasta = $_POST['fecha_h'];
        } else {
          $desde = date('Y-m-d');
          $hasta = $desde;
        }
        if (empty($_POST['servicio'])) {
          $serv = 1;
        } else {
          $serv = $_POST['servicio'];
        }
        $servicio = $conn->servicio();
        $resultn = $conn->gestionContactonoefectivo($desde, $hasta, $serv);
        $resulte = $conn->gestionContactoefectivo($desde, $hasta, $serv);
        include(HTML_DIR . 'reportes/contactabilidad.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;
      #-------------------------------------------------------------------------------------------
      case 'liberar':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        include(HTML_DIR . 'reportes/liberar.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;

      case 'cliente':
        $cliente = $_POST['cliente'];
        $cliente = $conn->buscarCliente($cliente);
        //print_r($cliente);
        if ($cliente == false) {
          $json['response'] = 'false';
        } else {
          $json['response'] = 'true';
          if (isset($cliente['nombre_legal'])) {
            $json['nombre'] = $cliente['nombre_legal'];
            $json['hab'] = $cliente['telf_hab'];
            $json['ofi'] = $cliente['telf_ofi'];
            $json['cel'] = $cliente['telf_cel'];
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
        $cliente = $_POST['cliente'];
        $cliente = $conn->liberarCliente($cliente);
        if ($cliente == false) {
          $json['response'] = 'false';
        } else {
          $json['response'] = 'true';
        }
        echo json_encode($json);
        break;

      case 'ventas':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');

        if (empty($_POST['servicio'])) {
          $serv = "";
        } else {
          $serv = $_POST['servicio'];
        }
        if (isset($_POST['fecha_d']) || isset($_POST['fecha_h'])) {
          $desde = date('Ymd', strtotime($_POST['fecha_d']));
          $hasta = date('Ymd', strtotime($_POST['fecha_h']));

        } else {
          $desde = date('Ymd');
          $hasta = $desde;
          $d = $h = date('Y-m-d');
        }
        $servicio = $conn->servicio();
        $result = $conn->ventasEstado($desde, $hasta, $serv);
        include(HTML_DIR . 'reportes/ventas.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;

      case 'ventasxestado':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        $desde = $_GET['d'];
        $hasta = $_GET['h'];
        $id_estado = $_GET['id'];
        $estado = $_GET['estado'];
        $serv = $_GET['servicio'];
        $result = $conn->ventasCiudad($desde, $hasta, $id_estado, $serv);
        include(HTML_DIR . 'reportes/ventasxciudad.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;

      case 'detalleventas':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        $servicio = $conn->servicio();

        if (isset($_POST['fecha_d']) || isset($_POST['fecha_h'])) {
          $desde = date('Ymd', strtotime($_POST['fecha_d']));
          $hasta = date('Ymd', strtotime($_POST['fecha_h']));
          $ventas = $conn->ventaDetallada($desde, $hasta, $servicio[0]['id']);
        }

        include(HTML_DIR . 'reportes/ventasDetalle.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;

      case 'download':
        $ventas = $conn->ventaDetallada($_GET['from'], $_GET['to'], $_GET['servicio']);
        header("Content-type: application/vnd.ms-excel");
				header("Content-Disposition: attachment; filename=ventasDetalladas.xls");

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
                    <td>'.$venta['nombre'].'</td>
                    <td>'.$venta['apellido'].'</td>
                    <td>'.$venta['cedula'].'</td>
                    <td>'.$venta['fecha_nacimiento'].'</td>
                    <td>'.$venta['telf_hab'].'</td>
                    <td>'.$venta['telf_celular'].'</td>
                    <td>'.$venta['correo'].'</td>
                    <td>'.$venta['descripcion'].'</td>
                    <td>'.$venta['agente'].'</td>
                    <td>'.$venta['fecha_venta'].'</td>
                  </tr>';
          }
        }

        echo '</tbody></table>';

        exit;

      default:
        header('location:' . HTML_DIR . 'error.html');
        break;
    }
  }
}
?>