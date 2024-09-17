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

  public function clientesGestionados($desde, $hasta, $servicio, $idEfectivo)
  {
    $sql = $this->db->query("SELECT CONCAT(users.nombre,' ',users.apellido) AS nombre, COUNT(gestion.contacto_id) AS gestion, users.id AS id FROM users INNER JOIN gestion ON users.id = gestion.user_id WHERE gestion.fecha BETWEEN '$desde' AND '$hasta' AND gestion.servicio_id = '$servicio' GROUP BY gestion.user_id ORDER BY users.nombre");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $conexion = new database();

        $id_user = $data['id'];
        $nombre = $data['nombre'];
        $gestion = $data['gestion'];
        $c = $conexion->clientesEfectivos($id_user, $desde, $hasta, $servicio);
        $efectivos = $c[0]['efectivos'];

        $v = $conexion->ventasEfectivas($id_user, $desde, $hasta, $servicio, $idEfectivo);
        $ventas = $v[0]['ventas'];

        $respuesta[] = compact("nombre", "gestion", "efectivos", "ventas");
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function efectivo($serv)
  {
    $sql = $this->db->query("SELECT id FROM efectivo WHERE servicio_id = '$serv' and descripcion = 'gestión efectiva'");
    if ($this->db->rows($sql) > 0) {
      $data = $this->db->recorrer($sql);
      $respuesta[] = $data;
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function ventaDetallada($desde,$hasta,$servicio)
  {
    $sql = $this->db->query("SELECT r.nombre, r.apellido, r.cedula, r.telf_hab, r.telf_celular, p.descripcion, CONCAT(u.nombre,' ',u.apellido) as agente, r.fecha_venta FROM `resultados` r INNER JOIN productos p ON r.producto_id = p.id INNER JOIN gestion g ON r.gestion_id = g.id INNER JOIN users u ON g.user_id = u.id WHERE r.fecha_venta BETWEEN $desde AND $hasta AND r.servicio_id = $servicio ORDER BY r.fecha_venta DESC");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function clientesEfectivos($id_user, $desde, $hasta, $servicio)
  {
    $sql = $this->db->query("SELECT COUNT(gestion.contacto_id) AS efectivos FROM gestion WHERE gestion.user_id = " . $id_user . " AND contacto_id = 1 AND fecha BETWEEN '$desde' AND '$hasta' AND servicio_id = $servicio");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function ventasEfectivas($id_user, $desde, $hasta, $servicio, $idEfectivo)
  {
    $sql = $this->db->query("SELECT COUNT(gestion.producto_id) AS ventas FROM gestion WHERE gestion.user_id = " . $id_user . " AND efectivo_id = $idEfectivo AND fecha BETWEEN '$desde' AND '$hasta' AND servicio_id = $servicio");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function buscarCliente($cedula)
  {
    $sql = $this->db->query("SELECT a.nombre_legal,a.telf_hab,a.telf_ofi, a.telf_cel,c.name,a.identificacion,b.descripcion FROM clientes a INNER JOIN servicios b ON a.servicio_id = b.id INNER JOIN status c ON a.status_id = c.id WHERE a.identificacion = '$cedula'");
    if ($this->db->rows($sql) > 0) {
      $respuesta = $this->db->recorrer($sql);
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function liberarCliente($cedula)
  {
    $this->db->query("UPDATE clientes SET status_id = 3 WHERE identificacion = '$cedula'");
    return true;
  }

  public function gestionContactonoefectivo($desde, $hasta, $serv)
  {
    // if (empty($serv)) {
    //   $sql = $this->db->query("SELECT n.descripcion, COUNT(g.noefectivo_id) AS total FROM gestion g INNER JOIN noefectivo n ON g.noefectivo_id = n.id WHERE g.fecha BETWEEN '$desde' AND '$hasta' GROUP BY n.descripcion");
    // } else {
      $sql = $this->db->query("SELECT n.descripcion, COUNT(g.noefectivo_id) AS total FROM gestion g INNER JOIN noefectivo n ON g.noefectivo_id = n.id WHERE g.fecha BETWEEN '$desde' AND '$hasta' AND g.servicio_id = '$serv' GROUP BY n.descripcion");
    // }

    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;

  }

  public function gestionContactoefectivo($desde, $hasta, $serv)
  {
    if (empty($serv)) {
      $sql = $this->db->query("SELECT e.descripcion, COUNT(g.efectivo_id) AS total FROM gestion g INNER JOIN efectivo e ON g.efectivo_id = e.id WHERE /*g.status_id = 4 AND */fecha BETWEEN '$desde' AND '$hasta' GROUP BY e.descripcion");
    } else {
      $sql = $this->db->query("SELECT e.descripcion, COUNT(g.efectivo_id) AS total FROM gestion g INNER JOIN efectivo e ON g.efectivo_id = e.id WHERE /*g.status_id = 4 AND */g.fecha BETWEEN '$desde' AND '$hasta' AND g.servicio_id = '$serv' GROUP BY e.descripcion");
    }
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function ventasEstado($desde, $hasta, $serv)
  {
    $sql = $this->db->query("SELECT COUNT(e.estado) AS total, e.estado, e.id FROM resultados r INNER JOIN estado e ON r.estado_id = e.id WHERE r.fecha_venta BETWEEN '$desde' AND '$hasta' AND r.servicio_id = '$serv' GROUP BY e.estado, e.id");

    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function ventasCiudad($desde, $hasta, $id_estado, $serv)
  {
    $sql = $this->db->query("SELECT COUNT(c.ciudad) AS total, c.ciudad FROM resultados r INNER JOIN ciudad c ON r.ciudad_id = c.id WHERE r.fecha_venta BETWEEN '$desde' AND '$hasta' AND r.estado_id = $id_estado GROUP BY r.ciudad_id");

    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function servicio()
  {
    $sql = $this->db->query("SELECT * FROM servicios WHERE status_id = 2 ORDER BY descripcion");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function gestionTotal($desde, $hasta)
  {
    $sql = $this->db->query("SELECT resultados2.nombre, resultados2.apellido, resultados2.cedula, resultados2.fecha_nacimiento, resultados2.telf_hab, resultados2.telf_ofi, resultados2.telf_celular, resultados2.correo, resultados2.genero, resultados2.cuenta, resultados2.fecha_venta, municipio.codigo AS cod_municipio,municipio.municipio,ciudad.codigo AS cod_ciudad,ciudad.ciudad,estado.codigo AS cod_estado,estado.estado,edificaciones.id_edificacion,edificaciones.edificacion,productos.codigo_producto,productos.codplan FROM resultados2 LEFT JOIN productos ON resultados2.id_producto = productos.id_producto LEFT JOIN municipio ON resultados2.id_municipio = municipio.id_municipio LEFT JOIN ciudad ON resultados2.id_ciudad = ciudad.id_ciudad LEFT JOIN estado ON resultados2.id_estado = estado.id_estado LEFT JOIN edificaciones ON resultados2.id_edificacion = edificaciones.id_edificacion WHERE resultados2.fecha_venta BETWEEN '$desde'  AND '$hasta'");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function gestionTotal2($desde, $hasta)
  {
    $sql = $this->db->query("SELECT a.nombre, a.apellido, a.edad, a.telf_hab, a.telf_cel, a.correo, a.trabaja, a.banco, a.ingresos, a.cobertura, a.zipcode, a.fecha_cita, a.hora_cita, a.observaciones, b.descripcion AS seguro, c.estado, d.ciudad FROM resultados3 a LEFT JOIN seguros b ON a.seguro = b.id_seguro LEFT JOIN estado c ON a.id_estado = c.id_estado LEFT JOIN ciudad d ON a.id_ciudad = d.id_ciudad WHERE a.fecha_gestion BETWEEN '$desde' AND '$hasta' AND a.status = 0 and a.id_servicio = 'oc'");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function gestionTotal3($desde, $hasta)
  {
    $sql = $this->db->query("SELECT a.nombre, a.apellido, a.genero, a.nacionalidad, a.cedula, a.habitacion, a.oficina, a.celular, a.correo, b.descripcion AS producto, a.observaciones FROM resultadosfdu a LEFT JOIN productos b ON a.id_producto = b.id_producto WHERE a.fecha_gestion BETWEEN '$desde' AND '$hasta' AND a.status = 0");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function gestionTotal4($desde, $hasta)
  {
    $sql = $this->db->query("SELECT CONCAT(a.nombre, ' ', a.apellido) AS nombre, a.telf_cel, a.telf_hab, a.correo, a.observaciones, a.fecha_cita, a.zipcode, b.estado, c.ciudad, d.nombre  AS centromedico, e.descripcion companiaseguro, a.ciudadano AS direccion FROM resultados3 a LEFT JOIN estado b ON a.id_estado = b.id_estado LEFT JOIN ciudad c ON a.id_ciudad = c.id_ciudad LEFT JOIN centromedico d ON a.ingresos = d.id_centro LEFT JOIN seguros e ON a.seguro = e.id_seguro WHERE a.fecha_gestion BETWEEN '$desde' AND '$hasta' AND a.status = 0 and a.id_servicio = 'fe'");
    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

}