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

  public function datosXML($fecha, $servicio)
  {
    $sql = $this->db->query("SELECT r.id as id_resultado, p.codigo_producto, p.descripcion, p.costo_prod, p.codplan, r.nombre, r.apellido, r.nacionalidad, r.fecha_nacimiento, r.genero, r.cedula, r.telf_hab, r.telf_ofi, r.telf_celular, r.correo, r.cuenta, t.codigo as cod_cuenta, t.nombre as nombre_cuenta, e.estado, e.codigo AS codigo_estado, c.ciudad, c.codigo AS codigo_ciudad, m.nombre as municipio, m.codigo AS codigo_municipio, r.fecha_venta FROM resultados r INNER JOIN estado e ON r.estado_id = e.id INNER JOIN ciudad c ON r.ciudad_id = c.id INNER JOIN municipio m ON r.municipio_id = m.id INNER JOIN productos p ON r.producto_id = p.id INNER JOIN tipo_cuentas t ON r.tipo_cuenta_id = t.id WHERE r.fecha_venta = '$fecha' AND r.servicio_id = $servicio");

    if ($this->db->rows($sql) > 0) {
      while ($data = $this->db->recorrer($sql)) {
        $respuesta[] = $data;
      }
    } else {
      $respuesta = false;
    }
    return $respuesta;
  }

  public function maxidTransaccion()
  {
    $sql = $this->db->query("SELECT max(id_transaccion) as max_id FROM transacciones");
    return $this->db->recorrer($sql);
  }

  public function insertTransaccion($id, $fecha, $id_resultado)
  {
    $this->db->query("INSERT INTO transacciones (id_transaccion, fecha, resultado_id) VALUES ($id, '$fecha', $id_resultado)");
    return true;
  }

  public function servicio()
  {
    $sql = $this->db->query("SELECT * FROM servicios WHERE status_id = 2");
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