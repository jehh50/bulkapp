<?php
include(PUBLIC_DIR . 'general/session.php');
security_requerir_rol(array(Constantes::USUARIO_AGENTE, Constantes::USUARIO_ADMINISTRADOR));
if (!security_usuario_autenticado()) {
  header('location:index.php');
  exit;
} else {
  include_once(MODEL_DIR . 'formularioModel.php');
  require_once dirname(__DIR__) . '/helpers/formularioRegistro.php';
  $conn = new database();

  if (isset($_GET['mode'])) {
    try {
      switch ($_GET['mode']) {
        case 'index':
          $id = Constantes::servicioBase(isset($_SESSION['servicio_id']) ? $_SESSION['servicio_id'] : 1);
          // Valores del hero (nnavbar)
          $nav_crumb = 'Inicio / Formulario';
          $nav_title = 'Formulario de gestión';
          $nav_subtitle = 'Busca al cliente por su número de teléfono para cargar sus datos.';
          include(PUBLIC_DIR . 'general/nheader.php');
          include(PUBLIC_DIR . 'general/nnavbar.php');
          $noefectivo = $conn->contactoNoEfectivo($id);
          $efectivo = $conn->contactoEfectivo($id);
          switch ($id) {
            case Constantes::SERVICIO_BANCARIO:
            case Constantes::SERVICIO_BANCARIO_ALIAS:
              // bancamiga bancaribe bancoActivo
              $estado = $conn->estado($id);
              $producto = $conn->ventaProducto($_SESSION['servicio_id']);
              $cuentas = $conn->cuentasBancarias();
              include(HTML_DIR . 'formulario/index.php');
              break;
          }
          include(PUBLIC_DIR . 'general/nfooter.php');
          break;

        case 'cliente':
          $telefono = input_string(isset($_POST['telefono']) ? $_POST['telefono'] : '', 30);
          $servicioCliente = input_enum(isset($_POST['servicio']) ? $_POST['servicio'] : null, Constantes::servicios());
          if ($telefono === '' || $servicioCliente === null) {
            $cliente = false;
          } else {
            $cliente = $conn->buscaCliente($telefono, $servicioCliente);
          }
          if ($cliente) {
            foreach ($cliente as $c) {
              if ($c['status_id'] == Constantes::ESTADO_ATENDIDO) {
                $json['response'] = 'atendido';
              } else {
                $json['response'] = 'true';
                $json['id_cliente'] = $c['id'];
                $json['name'] = $c['nombre_legal']; //NOMBRE
                $json['dni'] = $c['identificacion']; //CEDULA
                $json['phone1'] = $c['telf_hab']; //FECHA DE NACIMIENTO
                $json['phone2'] = $c['telf_ofi']; //GENERO
                $json['phone3'] = $c['telf_cel']; //TELFOO
                $json['email'] = $c['correo']; // CORREO
                $json['birthday'] = $c['direccion']; //UBICACIÓN
                $json['account'] = $c['cuenta']; //CUENTA
                $json['oferta'] = $c['oferta']; //OFERTA
              }
            }
          } else {
            $json['response'] = 'false';
          }
          echo json_encode($json);
          break;

        case 'ciudad':
          $id_estado = input_int(isset($_POST['id_estado']) ? $_POST['id_estado'] : null, 1);
          $ciudad = ($id_estado === null) ? false : $conn->ciudad($id_estado);
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
          $id = Constantes::servicioBase(isset($_SESSION['servicio_id']) ? $_SESSION['servicio_id'] : 1);
          $idEstado = input_int(isset($_POST['id_estado']) ? $_POST['id_estado'] : null, 1);

          $municipio = ($idEstado === null) ? false : $conn->municipio($idEstado, $id);
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

        case 'subContacto':
          $efectivoId = input_int(isset($_POST['efectivo_id']) ? $_POST['efectivo_id'] : null, 1);
          $subContacto = ($efectivoId === null) ? false : $conn->contactoSecundario($efectivoId);
          $json['subContacto'] = "";
          if ($subContacto) {
            foreach ($subContacto as $s) {
              $json['response'] = 'true';
              $json['subContacto'] = $json['subContacto'] . $s['id'] . "," . strtoupper($s['descripcion']) . "|";
            }
          } else {
            $json['response'] = 'false';
          }
          echo json_encode($json);
          break;

        case 'registro':
          $datos = formulario_registro_datos($_POST, $_SESSION);
          if ($datos === false) {
            header('location:?view=formulario&mode=index&mensaje=error');
            exit;
          }

          $resultado = formulario_registro_guardar($conn, $datos);
          if ($resultado === false) {
            app_log('warning', 'No se pudo registrar la gestión del cliente ' . $datos['id_cliente'], array(
              'servicio' => $datos['servicio'],
              'usuario' => $datos['usuario'],
            ));
          }

          header('location:?view=formulario&mode=index');
          exit;
          break;

        default:
          header('location:' . HTML_DIR . 'error.html');
          exit;
          break;
      }
    } catch (Throwable $e) {
      app_log('error', 'Error en el controlador de formulario: ' . $e->getMessage(), array(
        'excepcion' => get_class($e),
        'archivo' => $e->getFile(),
        'linea' => $e->getLine(),
        'modo' => isset($_GET['mode']) ? $_GET['mode'] : '',
      ));

      app_responder_error();
    }
  }
}
