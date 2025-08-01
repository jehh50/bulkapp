<?php
include(PUBLIC_DIR . 'general/session.php');
if (empty($_SESSION)) {
  header('location:index.php');
} else {
  include_once(MODEL_DIR . 'formularioModel.php');
  $conn = new database();

  if (isset($_GET['mode'])) {
    switch ($_GET['mode']) {
      case 'index':
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        $noefectivo = $conn->contactoNoEfectivo($_SESSION['servicio_id']);
        $efectivo = $conn->contactoEfectivo($_SESSION['servicio_id']);
        switch ($_SESSION['servicio_id']) {
          case 1:
            // bancamiga
            $estado = $conn->estado($_SESSION['servicio_id']);
            $producto = $conn->ventaProducto($_SESSION['servicio_id']);
            $cuentas = $conn->cuentasBancarias();
            include(HTML_DIR . 'formulario/index.php');
            break;
          case 2:
            // cashea
            $cliente = null;
            include(HTML_DIR . 'formulario/cashea.php');
            break;
          case 3:
            // bancaribe
            break;
        }
        include(PUBLIC_DIR . 'general/footer.php');
        break;

      case 'buscarCliente':
        $cliente = $conn->buscaCliente($_POST['dni'], $_POST['servicioId']);
        if(!$cliente){}else{if ($cliente[0]['status_id']==4){$msg = 'Cliente ya atendido';}else{$msg='';}}
        include(PUBLIC_DIR . 'general/header.php');
        include(PUBLIC_DIR . 'general/navbar.php');
        $noefectivo = $conn->contactoNoEfectivo($_SESSION['servicio_id']);
        $efectivo = $conn->contactoEfectivo($_SESSION['servicio_id']);
        include(HTML_DIR . 'formulario/cashea.php');
        include(PUBLIC_DIR . 'general/footer.php');
        break;


      case 'cliente':
        $cliente = $conn->buscaCliente($_POST['telefono'], $_POST['servicio']);
        if ($cliente) {
          foreach ($cliente as $c) {
            if ($c['status_id'] == 7) {
              $json['response'] = 'atendido';
            } else {
              $json['response'] = 'true';
              $json['id_cliente'] = $c['id'];
              $json['name'] = $c['nombre_legal'];
              $json['dni'] = $c['identificacion'];
              $json['phone1'] = $c['telf_hab'];
              $json['phone2'] = $c['telf_ofi'];
              $json['phone3'] = $c['telf_cel'];
              $json['email'] = $c['correo'];
              $json['birthday'] = $c['direccion'];
              $json['account'] = $c['cuenta'];
            }
          }
        } else {
          $json['response'] = 'false';
        }
        echo json_encode($json);
        break;

      case 'ciudad':
        $id_estado = $_POST['id_estado'];
        $ciudad = $conn->ciudad($id_estado);
        $json['ciudad'] = "";
        if ($ciudad) {
          foreach ($ciudad as $c) {
            $json['response'] = 'true';
            $json['ciudad'] = $json['ciudad'] . $c['id'] . "," . strtoupper($c['ciudad']) . "|";
          }
        } else {
          $json['response'] = 'false';
        }
        echo json_encode($json);
        break;

      case 'municipio':
        $municipio = $conn->municipio($_POST['id_estado'], $_SESSION['servicio_id']);
        $json['municipio'] = "";
        if ($municipio) {
          foreach ($municipio as $m) {
            $json['response'] = 'true';
            $json['municipio'] = $json['municipio'] . $m['id'] . "," . strtoupper($m['nombre']) . "|";
          }
        } else {
          $json['response'] = 'false';
        }
        echo json_encode($json);

        break;

      case 'registro':
        $id_cliente = $_POST['id_cliente'];
        $id_usuario = $_POST['usuario'];
        $contacto = $_POST['contacto'];
        $servicio = $_POST['servicio'];
        $date = date('Y-m-d');
        $hora = date('H:i:s');
        if (isset($_POST['efectivo'])) {
          $efectivo = $_POST['efectivo'];
          $status = 4;
        } else {
          $efectivo = null;
        }
        if (isset($_POST['noefectivo'])) {
          $noefectivo = $_POST['noefectivo'];
          $status = 3;
        }else {
          $noefectivo = null;
        }
        if (isset($_POST['venta'])) {
          $producto = $_POST['venta'];
        } else {
          $producto = null;
        }
        
        $registro = $conn->registroGestion($contacto, $efectivo, $producto, $noefectivo, $id_usuario, $date, $id_cliente, $status, $hora, $servicio);

        switch ($servicio) {
          case 1: // bancamiga       
            if (isset($_POST['nacionalidad'])) {
              $nacionalidad = $_POST['nacionalidad'];
            } else {
              $nacionalidad = null;
            }
            if (isset($_POST['cuenta2'])) {
              $cuenta = $_POST['cuenta2'];
            } else {
              $cuenta = '00000000000000000000';
            }
            if (isset($_POST['edificacion'])) {
              $edificacion = $_POST['edificacion'];
            } else {
              $edificacion = null;
            }
            if (isset($_POST['tipo_cuenta'])) {
              $tipocuenta = $_POST['tipo_cuenta'];
            } else {
              $tipocuenta = null;
            }

            if ($efectivo == 1) {
                $nombre = str_replace(',', ' ', $_POST['nombre2']);
                $apellido = str_replace(',', ' ', $_POST['apellido2']);
                $genero = $_POST['genero'];
                $fecha_nac = $_POST['fecha_nac'];
                $cedula = $_POST['cedula2'];
                $correo = $_POST['correo2'];
                $telf_hab = $_POST['telf_hab'];
                // $telf_ofi = $_POST['telf_ofi'];
                $telf_cel = $_POST['telf_cel'];
                $estado = $_POST['estado'];
                $ciudad = $_POST['ciudad'];
                $municipio = $_POST['municipio'];
                $obs = $_POST['observaciones'];
                $fecha = date('Ymd');
                $status = 7;
                $var = 0;

                $registro = $conn->registroResultados($contacto, $efectivo, $producto, $noefectivo, $id_usuario, $date, $nombre, $apellido, $genero, $fecha_nac, $nacionalidad, $cedula, $telf_hab, $telf_cel, $correo, $estado, $ciudad, $municipio, $cuenta, $tipocuenta, $obs, $fecha, $status, $id_cliente, $var, $hora, $servicio);
              } else {
                $status = 4;
                $registro = $conn->registroGestion($contacto, $efectivo, $producto, $noefectivo, $id_usuario, $date, $id_cliente, $status, $hora, $servicio);
              }

            break;
          case 2: // cashea
            var_dump($_POST);
            if($efectivo == 12 || $efectivo == 13){
              $status = 4;
              $results = $conn->registroResultadosCashea($_POST['paymentPlan'],$_POST['paymentDate'],$_POST['amount'],$_POST['fullName'],$_POST['relationship'],$_POST['observaciones'],$_POST['id_cliente'],$status);
            }
            header('location:?view=formulario&mode=index');
            break;


          // case 'nuevocliente':
          //   $telefono = $_POST['telefono'];
          //   $cliente = $conn->buscaNuevocliente($telefono);
          //   if ($cliente) {
          //     foreach ($cliente as $c) {
          //       if ($c['status'] == 1) {
          //         $json['response'] = 'atendido';
          //       } else {
          //         if ($c['genero'] == 'M') {
          //           $genero = 'Masculino';
          //         } else {
          //           $genero = 'Femenino';
          //         }
          //         $json['response'] = 'true';
          //         $json['id_cliente'] = $c['id_cliente'];
          //         $json['nombre'] = $c['nombre'] . ' ' . $c['apellido'];
          //         $json['cedula'] = $c['cedula'];
          //         $json['genero'] = $genero;
          //         $json['fecha_nac'] = $c['fecha_nacimiento'];
          //         $json['telf1'] = $c['telefono'];
          //         $json['telf2'] = $c['telefono2'];
          //         $json['telf3'] = $c['telefono3'];
          //         $json['estado'] = $c['estado'];
          //         $json['referido'] = $c['referido'];
          //       }
          //     }
          //   } else {
          //     $json['response'] = 'false';
          //   }
          //   echo json_encode($json);

          //   break;

          default:
            header('location:' . HTML_DIR . 'error.html');
            break;
        }
    }
  }
}
