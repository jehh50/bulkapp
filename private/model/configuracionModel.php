<?php
class database
{
  private $db;
  private $id;
  private $nombre;

  public function __construct()
  {
    $this->db = new Conexion();
  }

  public function updateStatus($servicio)
  {
    $this->db->query("UPDATE productos SET status_id = 1 WHERE status_id = 2 AND servicio_id = '$servicio'");
    return true;
  }

  public function guardarProductos($producto, $codigo, $costo, $servicio, $plan)
  {
    $date = date('Y-m-d');
    $this->db->query("INSERT INTO productos (descripcion, codigo_producto, costo_prod, status_id, servicio_id,fecha,codplan) VALUES ('$producto', '$codigo', '$costo', 2, '$servicio','$date','$plan')");
    return true;
  }

  public function servicio()
  {
    $sql = $this->db->query("SELECT * FROM servicios ORDER BY descripcion");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }
  public function registro($identificacion, $nombre_legal, $telf_hab, $telf_ofi, $telf_cel, $correo, $direccion, $cuenta, $servicio_id, $oferta)
  {
    // $identificacion = iconv(mb_detect_encoding($identificacion, mb_detect_order(), true), "UTF-8", $identificacion);
    // $nombre_legal = iconv(mb_detect_encoding($nombre_legal, mb_detect_order(), true), "UTF-8", $nombre_legal);
    // $telf_hab = iconv(mb_detect_encoding($telf_hab, mb_detect_order(), true), "UTF-8", $telf_hab);
    // $telf_ofi = iconv(mb_detect_encoding($telf_ofi, mb_detect_order(), true), "UTF-8", $telf_ofi);
    // $telf_cel = iconv(mb_detect_encoding($telf_cel, mb_detect_order(), true), "UTF-8", $telf_cel);
    // $correo = iconv(mb_detect_encoding($correo, mb_detect_order(), true), "UTF-8", $correo);
    // $direccion = iconv(mb_detect_encoding($direccion, mb_detect_order(), true), "UTF-8", $direccion);
    // $cuenta = iconv(mb_detect_encoding($cuenta, mb_detect_order(), true), "UTF-8", $cuenta);
    // $servicio_id = iconv(mb_detect_encoding($servicio_id, mb_detect_order(), true), "UTF-8", $servicio_id);
    // $oferta = iconv(mb_detect_encoding($oferta, mb_detect_order(), true), "UTF-8", $oferta);

    $query = ("INSERT INTO clientes (identificacion, nombre_legal, telf_hab, telf_ofi, telf_cel, correo, direccion, cuenta, oferta, status_id,servicio_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $params = [$identificacion, $nombre_legal, $telf_hab, $telf_ofi, $telf_cel, $correo, $direccion, $cuenta, $oferta, 3, $servicio_id];
    // Tipos de datos: 
    // 's' -> string, 'i' -> integer, 'd' -> double (float)
    $paramTypes = 'ssssssssiis';

    try {
      $stmt = $this->db->preparedQuery($query, $params, $paramTypes);
      $stmt->close();
      return true;
      // echo "Success";
    } catch (Exception $e) {
      echo "Error al guardar => " . $e->getMessage();
    }
  }

  public function guardarRegistrosTemporalBatch($batch, $types, $servicio)
  {
    // CORRECCIÓN 1: Verificar la variable correcta ($batch)
    if (empty($batch)) {
      return false;
    }

    if ($servicio == 1) {
      $query = "INSERT INTO clientes (identificacion, nombre_legal, telf_hab, telf_ofi, telf_cel, correo, direccion, cuenta, oferta) VALUES ";
    } else {
      $query = "INSERT INTO cashea (cedula, id_cuota, nombre_grupo, fecha_pagar, monto_cuota, numero_cuota, fee, plata_por_cobrar, capital_asignado, id_orden, identificacion_orden, fecha_creacion_orden, email, telefono, nombre_usuario, local_origen, estado_deuda, tramo_inicial, tramo_actual, segmento) VALUES ";
    }

    $placeHolders = [];
    $flatennedParams = [];

    foreach ($batch as $row) {
      // CORRECCIÓN 2: Usar count($row) para obtener el número de columnas
      $placeHolders[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';
      $flatennedParams = array_merge($flatennedParams, $row);
    }

    try {
      $query .= implode(',', $placeHolders);
      $stmt = $this->db->preparedQuery($query, $flatennedParams, str_repeat($types, count($batch)));

      if ($stmt) {
        $stmt->close();
      }

      return true;
    } catch (Exception $e) {
      error_log("Error en guardarRegistrosTemporalBatch: " . $e->getMessage());
      return false;
    }
  }

  public function listarTemporal($servicio, $paginaActual, $registrosPorPagina)
  {
    if ($servicio == 1) {
      $tabla = "clientes";
    } else {
      $tabla = "cashea_customers";
    }

    $offset = ($paginaActual - 1) * $registrosPorPagina;

    $totalRegistrosQuery = "SELECT COUNT(*) as total FROM $tabla";
    $totalRegistrosResult = $this->db->query($totalRegistrosQuery);

    $filaTotal = $this->db->recorrer($totalRegistrosResult);
    $totalRegistros = $filaTotal['total'];

    $registrosQuery = "SELECT * FROM $tabla LIMIT $registrosPorPagina OFFSET $offset";
    $registrosResult = $this->db->query($registrosQuery);

    $registros = [];
    if ($this->db->rows($registrosResult) > 0) {
      while ($data = $this->db->recorrer($registrosResult)) {
        $registros[] = $data;
      }
    }

    $totalPaginas = ceil($totalRegistros / $registrosPorPagina);
    echo 'Total registros ' . $totalRegistros;
    echo '<br>Total páginas ' . $totalPaginas;
    echo '<br>Pagina actual ' . $paginaActual;
    echo '<br>registros por página: ' . $registrosPorPagina . '<br>';
    return [
      'registros' => $registros,
      'totalRegistros' => $totalRegistros,
      'totalPaginas' => $totalPaginas,
      'paginaActual' => $paginaActual,
      'porPagina' => $registrosPorPagina
    ];
  }

  public function busquedaResultados($cedula)
  {
    $sql = $this->db->query("SELECT a.id as resultado_id, a.gestion_id, a.nombre, a.apellido, a.genero, a.fecha_nacimiento, a.cedula, a.telf_hab, a.telf_ofi, a.telf_celular, a.correo, a.cuenta, b.descripcion, b.id as servicio_id, a.producto_id, p.descripcion as name_product, a.fecha_venta FROM resultados a INNER JOIN servicios b  ON a.servicio_id = b.id INNER JOIN productos p ON a.producto_id = p.id WHERE a.cedula = '$cedula'");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      return false;
    }
    return $respuesta;
  }

  public function productos($id)
  {
    $sql = $this->db->query("SELECT id, descripcion FROM productos WHERE servicio_id = $id AND status_id = 2");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      return false;
    }
    return $respuesta;
  }
  public function updateResultados($id, $nombre, $apellido, $cedula, $genero, $fecha_nac, $hab, $cel, $correo, $venta, $productId)
  {
    //echo ("UPDATE resultados SET nombre='$nombre', apellido='$apellido', genero='$genero', fecha_nacimiento='$fecha_nac', cedula=$cedula, telf_hab='$hab', telf_celular='$cel', correo='$correo', fecha_venta='$venta' WHERE  id=$id");

    $this->db->query("UPDATE resultados SET nombre='$nombre', apellido='$apellido', genero='$genero', fecha_nacimiento='$fecha_nac', cedula=$cedula, telf_hab='$hab', telf_celular='$cel', correo='$correo', fecha_venta='$venta', producto_id=$productId WHERE  id=$id");

    return true;
  }

  public function eliminarVenta($resultadoId)
  {
    try {
      $query = "DELETE FROM resultados WHERE id = ?";
      $params = [$resultadoId];
      $paramTypes = 'i';
      $stmt = $this->db->preparedQuery($query, $params, $paramTypes);
      $stmt->close();
      return true;
    } catch (Exception $e) {
      return "Error al eliminar resultado con ID $resultadoId: " . $e->getMessage();
    }
  }

  public function contactoEfectivo()
  {
    $sql = $this->db->query("SELECT * FROM efectivo");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function ventaProducto($servicio)
  {
    $sql = $this->db->query("SELECT * FROM productos WHERE id_producto != 1 AND status=1 AND id_servicio = '$servicio'");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function estado($var)
  {
    $sql = $this->db->query("SELECT * FROM estado WHERE cod_servicio = '$var'");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function ciudad($var)
  {
    $sql = $this->db->query("SELECT * FROM ciudad where id_estado = $var");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function ciudad_($var)
  {
    $sql = $this->db->query("SELECT * FROM ciudad WHERE id_estado = $var ORDER BY ciudad");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function listarProductos()
  {
    $sql = $this->db->query("SELECT * FROM productos WHERE status_id = 2");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function products($id = null)
  {
    $sql = $this->db->query("SELECT * FROM productos WHERE id = " . $id);
    if ($this->db->rows($sql) > 0) {
      return $this->db->recorrer($sql);
    } else {
      return false;
    }
  }

  public function updateProd($id, $nombre, $codigo, $costo)
  {
    $this->db->query("UPDATE productos SET descripcion = '$nombre', codigo_producto = '$codigo', costo_prod = $costo WHERE id = " . $id);
    return true;
  }
}
