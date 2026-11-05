<?php
class database{
  
  private $db;
  private $id;
  private $nombre;

  public function __construct() {
    $this->db = new Conexion();
  }

  public function gestionTotal(string $desde, string $hasta): array{
    // Portado de la antigua tabla resultados2 a la tabla real `resultados`.
    // `edificacion` no tiene equivalente en el esquema actual: se devuelve vacío.
    // $desde/$hasta deben venir en formato YYYYMMDD (coincide con resultados.fecha_venta varchar).
    $sql = "SELECT
              resultados.nombre,
              resultados.apellido,
              resultados.cedula,
              resultados.fecha_nacimiento,
              resultados.telf_hab,
              resultados.telf_ofi,
              resultados.telf_celular,
              resultados.correo,
              resultados.genero,
              resultados.cuenta,
              resultados.fecha_venta,
              municipio.codigo AS cod_municipio,
              municipio.nombre  AS municipio,
              ciudad.codigo     AS cod_ciudad,
              ciudad.ciudad,
              estado.codigo     AS cod_estado,
              estado.estado,
              NULL AS id_edificacion,
              NULL AS edificacion,
              productos.codigo_producto,
              productos.codplan
            FROM resultados
            LEFT JOIN productos ON resultados.producto_id = productos.id
            LEFT JOIN municipio ON resultados.municipio_id = municipio.id
            LEFT JOIN ciudad    ON resultados.ciudad_id    = ciudad.id
            LEFT JOIN estado    ON resultados.estado_id    = estado.id
            WHERE resultados.fecha_venta BETWEEN ? AND ?";
    return $this->db->selectQuery($sql, [$desde, $hasta], 'ss');
  }
}

?>