<?php
class database{
  
  private $db;
  private $id;
  private $nombre;

  public function __construct() {
    $this->db = new Conexion();
  }

  public function busquedaResultados($cedula){
    $sql_a = $this->db->query("SELECT a.id_resultado, a.id_gestion, a.nombre, a.apellido, a.genero, a.fecha_nacimiento, a.cedula, a.telf_hab, a.telf_ofi, a.telf_celular, a.correo, a.cuenta, a.status, b.descripcion, b.cod_servicio FROM resultados a LEFT JOIN servicios b  ON a.cod_servicio = b.cod_servicio WHERE a.cedula = $cedula"); 

    $sql_b = $this->db->query("SELECT id_resultado2 as a.id_resultado, a.id_gestion, a.nombre, a.apellido, a.genero, a.fecha_nacimiento, a.cedula, a.telf_hab, a.telf_ofi, a.telf_celular, a.correo, a.cuenta, a.status, b.cod_servicio, b.cod_servicio FROM resultados2 a LEFT JOIN servicios b  ON a.cod_servicio = b.cod_servicio WHERE a.cedula = $cedula");

    if($this->db->rows($sql_a) > 0 ){
      while($data = $this->db->recorrer($sql_a)){
        $respuesta[] = $data;
      }
    }
    elseif($this->db->rows($sql_b) > 0 ){
      while($data = $this->db->recorrer($sql_b)){
        $respuesta[] = $data;
      }
    }
    else{
      $respuesta = false;      
    } 
    return $respuesta;

  }

  public function updateResultados($idresultado, $nombre, $apellido, $cedula, $tlf_hab, $tlf_ofic, $tlf_celu, $correo, $cuenta, $servicio, $genero, $fecha_nac){
   if($servicio == 'pcus'){
        $sql = $this->db->query("UPDATE resultados2 SET nombre='$nombre', apellido='$apellido', genero='$genero', fecha_nacimiento='$fecha_nac', cedula=$cedula, telf_hab='$tlf_hab', telf_ofi='$tlf_ofic', telf_celular='$tlf_celu', correo='$correo', cuenta='$cuenta' WHERE  cedula=$cedula");
   }else{
    $sql = $this->db->query("UPDATE resultados SET nombre='$nombre', apellido='$apellido', genero='$genero', fecha_nacimiento='$fecha_nac', cedula=$cedula, telf_hab='$tlf_hab', telf_ofi='$tlf_ofic', telf_celular='$tlf_celu', correo='$correo', cuenta='$cuenta' WHERE  id_resultado=$idresultado");
   }
   return true;
  }

  public function servicio(){
    $sql = $this->db->query("SELECT * FROM servicios where cod_servicio != 'all' ORDER BY descripcion");
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

  public function eliminarVenta($motivo,$cedula,$servicio,$id_resultado,$user,$date,$id_gestion){
    if($servicio == 'pcus'){
      $sql = $this->db->query("UPDATE resultados2 SET status = 1 WHERE cedula = $cedula");
    }elseif($servicio == 'oc'){
      $sql = $this->db->query("UPDATE resultados3 SET status = 1 WHERE id_resultado = $id_resultado");
    }else{
      $sql = $this->db->query("UPDATE resultados SET status = 1 WHERE id_resultado = $id_resultado");
    }

    $sql = $this->db->query("UPDATE gestion SET status = 1 WHERE id_gestion = $id_gestion");

    if($servicio == 'oc'){
      $sql = $this->db->query("UPDATE clientes SET status = 2 WHERE telf_hab = $cedula");
    }else{
      $sql = $this->db->query("UPDATE clientes SET status = 2 WHERE identificacion = $cedula");
    }
    $sql = $this->db->query("INSERT INTO rechazo_ventas (motivo,fecha,id_resultado,cod_servicio,id_usuario) VALUES ('$motivo','$date',$id_resultado,'$servicio',$user)");

    $respuesta = 1;
    return $respuesta;
  }


  public function busquedaResultadosInv($telefono){
    $sql = $this->db->query("SELECT a.id_gestion, a.id_resultado, a.nombre,a.apellido,a.edad, a.telf_hab, a.telf_cel, a.correo, a.banco, a.cobertura, a.seguro, a.trabaja, a.ciudadano, a.zipcode, a.fecha_cita, a.hora_cita, a.status, b.estado, c.ciudad, a.id_estado, a.id_ciudad  FROM resultados3 a join estado b on a.id_estado = b.id_estado JOIN ciudad c on a.id_ciudad = c.id_ciudad WHERE a.telf_hab = $telefono"); 
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

  public function updateResulta2($contacto,$resultado,$gestion){
    $sql = $this->db->query("UPDATE resultados3 SET status = 1 WHERE id_resultado = $resultado");
    $sql = $this->db->query("UPDATE gestion SET id_efectivo = $contacto, id_producto = 0, status = 1 WHERE id_gestion = $gestion");
    return true;
  }

  public function updateResulta3($resultado,$nombre,$apellido,$edad,$telf_hab,$telf_cel,$correo,$cobertura,$compania,$trabaja,$banco,$ciudadano,$estado,$ciudad,$zipcode,$fecha_cita,$hora_cita){
    $sql = $this->db->query("UPDATE resultados3 SET nombre = '$nombre', apellido = '$apellido', edad = $edad, telf_hab = $telf_hab, telf_cel = $telf_cel, correo = '$correo', cobertura = '$cobertura', seguro = $compania, trabaja = '$trabaja', banco = '$banco', ciudadano = '$ciudadano', id_estado = $estado, id_ciudad = $ciudad, zipcode = '$zipcode', fecha_cita = '$fecha_cita', hora_cita = '$hora_cita' WHERE id_resultado = $resultado");
    return true; 
  }


  public function contactoEfectivo($a){
    $sql = $this->db->query("SELECT * FROM efectivo WHERE id_efectivo != 0 ");
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

  public function ventaProducto($servicio){
    $sql = $this->db->query("SELECT * FROM productos WHERE id_producto != 1 AND status=1 AND id_servicio = '$servicio'");
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

  public function estado($var){
    $sql = $this->db->query("SELECT * FROM estado WHERE cod_servicio = '$var'");
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

  public function ciudad($var){
    $sql = $this->db->query("SELECT * FROM ciudad where id_estado = $var");
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

    public function ciudad_($var){
    $sql = $this->db->query("SELECT * FROM ciudad WHERE id_estado = $var ORDER BY ciudad");
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

public function seguro(){
    $sql = $this->db->query("SELECT * FROM seguros");
    if($this->db->rows($sql) > 0 ){
      while($data = $this->db->recorrer($sql)){
        $respuesta[] = $data;
      }
    }else{
      $respuesta = false;
    }
    return $respuesta;
  } 

}

?>