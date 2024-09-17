<?php
class database{
  
  private $db;
  private $id;
  private $nombre;

  public function __construct() {
    $this->db = new Conexion();
  }

  public function gestionTotal($desde,$hasta){
    $sql = $this->db->query("SELECT
                                      resultados2.nombre,
                                      resultados2.apellido,
                                      resultados2.cedula,
                                      resultados2.fecha_nacimiento,
                                      resultados2.telf_hab,
                                      resultados2.telf_ofi,
                                      resultados2.telf_celular,
                                      resultados2.correo,
                                      resultados2.genero,
                                      resultados2.cuenta,
                                      resultados2.fecha_venta,
                                      municipio.codigo AS cod_municipio,
                                      municipio.municipio,
                                      ciudad.codigo AS cod_ciudad,
                                      ciudad.ciudad,
                                      estado.codigo AS cod_estado,
                                      estado.estado,
                                      edificaciones.id_edificacion,
                                      edificaciones.edificacion,
                                      productos.codigo_producto,
                                      productos.codplan
                                    FROM
                                      resultados2
                                    LEFT JOIN productos ON resultados2.id_producto = productos.id_producto
                                    LEFT JOIN municipio ON resultados2.id_municipio = municipio.id_municipio
                                    LEFT JOIN ciudad ON resultados2.id_ciudad = ciudad.id_ciudad
                                    LEFT JOIN estado ON resultados2.id_estado = estado.id_estado
                                    LEFT JOIN edificaciones ON resultados2.id_edificacion = edificaciones.id_edificacion
                                  WHERE resultados2.fecha_venta BETWEEN '$desde'  AND '$hasta'");
    
    if($this->db->rows($sql) > 0 ){
      while($data = $this->db->recorrer($sql)){
        $respuesta[] = $data;
      }
    }
    else{
      $respuesta = false;
    }
    return $respuesta;
  }
}

?>