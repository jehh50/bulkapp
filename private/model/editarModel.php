<?php
class database{

  private $db;
  private $id;
  private $nombre;

  public function __construct() {
    $this->db = new Conexion();
  }

  public function busquedaResultados(string $cedula){
    $sql_a = "SELECT a.id_resultado, a.id_gestion, a.nombre, a.apellido, a.genero, a.fecha_nacimiento, a.cedula, a.telf_hab, a.telf_ofi, a.telf_celular, a.correo, a.cuenta, a.status, b.descripcion, b.cod_servicio FROM resultados a LEFT JOIN servicios b  ON a.cod_servicio = b.cod_servicio WHERE a.cedula = ?";
    $a = $this->db->selectQuery($sql_a, [$cedula], 's');
    if (!empty($a)) {
      return $a;
    }

    $sql_b = "SELECT id_resultado2 as a.id_resultado, a.id_gestion, a.nombre, a.apellido, a.genero, a.fecha_nacimiento, a.cedula, a.telf_hab, a.telf_ofi, a.telf_celular, a.correo, a.cuenta, a.status, b.cod_servicio, b.cod_servicio FROM resultados2 a LEFT JOIN servicios b  ON a.cod_servicio = b.cod_servicio WHERE a.cedula = ?";
    $b = $this->db->selectQuery($sql_b, [$cedula], 's');
    return !empty($b) ? $b : false;
  }

  public function updateResultados(int $idresultado, string $nombre, string $apellido, string $cedula, ?string $tlf_hab, ?string $tlf_ofic, ?string $tlf_celu, ?string $correo, ?string $cuenta, string $servicio, string $genero, string $fecha_nac): bool{
   if($servicio == 'pcus'){
        $this->db->preparedQuery("UPDATE resultados2 SET nombre=?, apellido=?, genero=?, fecha_nacimiento=?, cedula=?, telf_hab=?, telf_ofi=?, telf_celular=?, correo=?, cuenta=? WHERE  cedula=?",
          [$nombre, $apellido, $genero, $fecha_nac, $cedula, $tlf_hab, $tlf_ofic, $tlf_celu, $correo, $cuenta, $cedula], 'sssssssssss');
   }else{
        $this->db->preparedQuery("UPDATE resultados SET nombre=?, apellido=?, genero=?, fecha_nacimiento=?, cedula=?, telf_hab=?, telf_ofi=?, telf_celular=?, correo=?, cuenta=? WHERE  id_resultado=?",
          [$nombre, $apellido, $genero, $fecha_nac, $cedula, $tlf_hab, $tlf_ofic, $tlf_celu, $correo, $cuenta, $idresultado], 'sssssssssss');
   }
   return true;
  }

  public function servicio(): array{
    return $this->db->selectQuery("SELECT * FROM servicios where cod_servicio != 'all' ORDER BY descripcion");
  }

  public function eliminarVenta(int $motivo, string $cedula, string $servicio, int $id_resultado, int $user, string $date, int $id_gestion){
    if($servicio == 'pcus'){
      $this->db->preparedQuery("UPDATE resultados2 SET status = 1 WHERE cedula = ?", [$cedula], 's');
    }elseif($servicio == 'oc'){
      $this->db->preparedQuery("UPDATE resultados3 SET status = 1 WHERE id_resultado = ?", [$id_resultado], 's');
    }else{
      $this->db->preparedQuery("UPDATE resultados SET status = 1 WHERE id_resultado = ?", [$id_resultado], 's');
    }

    $this->db->preparedQuery("UPDATE gestion SET status = 1 WHERE id_gestion = ?", [$id_gestion], 's');

    if($servicio == 'oc'){
      $this->db->preparedQuery("UPDATE clientes SET status = 2 WHERE telf_hab = ?", [$cedula], 's');
    }else{
      $this->db->preparedQuery("UPDATE clientes SET status = 2 WHERE identificacion = ?", [$cedula], 's');
    }
    $this->db->preparedQuery("INSERT INTO rechazo_ventas (motivo,fecha,id_resultado,cod_servicio,id_usuario) VALUES (?,?,?,?,?)",
      [$motivo, $date, $id_resultado, $servicio, $user], 'sssss');

    $respuesta = 1;
    return $respuesta;
  }


  public function busquedaResultadosInv(string $telefono): array{
    return $this->db->selectQuery("SELECT a.id_gestion, a.id_resultado, a.nombre,a.apellido,a.edad, a.telf_hab, a.telf_cel, a.correo, a.banco, a.cobertura, a.seguro, a.trabaja, a.ciudadano, a.zipcode, a.fecha_cita, a.hora_cita, a.status, b.estado, c.ciudad, a.id_estado, a.id_ciudad  FROM resultados3 a join estado b on a.id_estado = b.id_estado JOIN ciudad c on a.id_ciudad = c.id_ciudad WHERE a.telf_hab = ?", [$telefono], 's');
  }

  public function updateResulta2(int $contacto, int $resultado, int $gestion): bool{
    $this->db->preparedQuery("UPDATE resultados3 SET status = 1 WHERE id_resultado = ?", [$resultado], 's');
    $this->db->preparedQuery("UPDATE gestion SET id_efectivo = ?, id_producto = 0, status = 1 WHERE id_gestion = ?", [$contacto, $gestion], 'ss');
    return true;
  }

  public function updateResulta3(int $resultado, string $nombre, string $apellido, int $edad, ?string $telf_hab, ?string $telf_cel, ?string $correo, ?string $cobertura, ?string $compania, ?string $trabaja, ?string $banco, ?string $ciudadano, int $estado, int $ciudad, ?string $zipcode, ?string $fecha_cita, ?string $hora_cita): bool{
    $this->db->preparedQuery("UPDATE resultados3 SET nombre = ?, apellido = ?, edad = ?, telf_hab = ?, telf_cel = ?, correo = ?, cobertura = ?, seguro = ?, trabaja = ?, banco = ?, ciudadano = ?, id_estado = ?, id_ciudad = ?, zipcode = ?, fecha_cita = ?, hora_cita = ? WHERE id_resultado = ?",
      [$nombre, $apellido, $edad, $telf_hab, $telf_cel, $correo, $cobertura, $compania, $trabaja, $banco, $ciudadano, $estado, $ciudad, $zipcode, $fecha_cita, $hora_cita, $resultado], str_repeat('s', 17));
    return true;
  }


  public function contactoEfectivo(int $a): array{
    return $this->db->selectQuery("SELECT * FROM efectivo WHERE id_efectivo != 0 ");
  }

  public function ventaProducto(?int $servicio): array{
    return $this->db->selectQuery("SELECT * FROM productos WHERE id_producto != 1 AND status=1 AND id_servicio = ?", [$servicio], 's');
  }

  public function estado(string $var): array{
    return $this->db->selectQuery("SELECT * FROM estado WHERE cod_servicio = ?", [$var], 's');
  }

  public function ciudad(int $var): array{
    return $this->db->selectQuery("SELECT * FROM ciudad where id_estado = ?", [$var], 's');
  }

    public function ciudad_(int $var): array{
    return $this->db->selectQuery("SELECT * FROM ciudad WHERE id_estado = ? ORDER BY ciudad", [$var], 's');
  }

public function seguro(): array{
    return $this->db->selectQuery("SELECT * FROM seguros");
  }

}

?>
