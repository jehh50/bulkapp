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
 
  public function datosXML(string $fecha, int $servicio)
  {
    $data = $this->db->selectQuery(
      "SELECT r.id as id_resultado, p.codigo_producto, p.descripcion, p.costo_prod, p.codplan, r.nombre, r.apellido, r.nacionalidad, r.fecha_nacimiento, r.genero, r.cedula, r.telf_hab, r.telf_ofi, r.telf_celular, r.correo, r.cuenta, t.codigo as cod_cuenta, t.nombre as nombre_cuenta, e.estado, e.codigo AS codigo_estado, c.ciudad, c.codigo AS codigo_ciudad, m.nombre as municipio, m.codigo AS codigo_municipio, r.fecha_venta, s.codigo as company_code, s.descripcion as nombre_servicio FROM resultados r INNER JOIN estado e ON r.estado_id = e.id INNER JOIN ciudad c ON r.ciudad_id = c.id INNER JOIN municipio m ON r.municipio_id = m.id INNER JOIN productos p ON r.producto_id = p.id INNER JOIN tipo_cuentas t ON r.tipo_cuenta_id = t.id INNER JOIN servicios s ON r.servicio_id = s.id WHERE r.fecha_venta = ? AND r.servicio_id = ?",
      [$fecha, $servicio],
      'si'
    );

    return !empty($data) ? $data : false;
  }

  public function maxidTransaccion(): array
  {
    $data = $this->db->selectQuery("SELECT max(id_transaccion) as max_id FROM transacciones");
    return !empty($data) ? $data[0] : ['max_id' => null];
  }

  public function insertTransaccion(int $id, string $fecha, int $id_resultado): bool
  {
    $this->db->preparedQuery(
      "INSERT INTO transacciones (id_transaccion, fecha, resultado_id) VALUES (?, ?, ?)",
      [$id, $fecha, $id_resultado],
      'isi'
    );
    return true;
  }

  public function servicio(): array
  {
    return $this->db->selectQuery("SELECT id,descripcion FROM servicios WHERE status_id = 2");
  }
}