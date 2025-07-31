<?php
class database {
  
  private $db;

 
  public function __construct() {
    $this->db = new Conexion();
  }


  public function contactoEfectivo($e){
    $sql = $this->db->query("SELECT * FROM efectivo WHERE servicio_id = '$e'");
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

  public function contactoNoEfectivo($e){
    $sql = $this->db->query("SELECT * FROM noefectivo where servicio_id = '$e'");
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
    $sql = $this->db->query("SELECT * FROM productos WHERE status_id = 2 AND servicio_id = '$servicio'");
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

  public function buscaCliente($telefono,$servicio){
    if($servicio == 1){
      $sql = $this->db->query("SELECT * FROM clientes WHERE (identificacion = '$telefono' OR telf_hab LIKE '%" . $telefono . "' or telf_ofi LIKE '%" . $telefono . "' OR telf_cel LIKE '%" . $telefono."') and servicio_id = '$servicio'");
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
    if($servicio == 2){
      $sql = $this->db->query("SELECT * FROM cashea_customers WHERE cedula = '$telefono'");
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

  public function estado($servicio_id){
    $sql = $this->db->query("SELECT * FROM estado WHERE servicio_id = '$servicio_id'");
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

  public function ciudadContar($id_estado){
    $sql = $this->db->query("SELECT count(id_estado) FROM ciudad WHERE id_estado = '".$id_estado."'");
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

  public function ciudad($id_estado){ 
    $sql = $this->db->query("SELECT * FROM ciudad WHERE estado_id = $id_estado AND servicio_id = 1 ORDER BY ciudad");
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

  public function municipio($estado_id,$servicio_id){
    $sql = $this->db->query("SELECT * FROM municipio WHERE estado_id = '".$estado_id."' and servicio_id = ".$servicio_id);
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

  public function registroResultados($contacto,$efectivo,$producto,$noefectivo,$id_usuario,$date,$nombre,$apellido,$genero,$fecha_nac,$nacionalidad,$cedula,$telf_hab,$telf_cel,$correo,$estado,$ciudad,$municipio,$cuenta,$tipocuenta,$obs,$fecha,$status,$id_cliente,$var,$hora,$servicio){
    $this->db->query("INSERT INTO gestion (contacto_id, efectivo_id, producto_id, user_id, fecha, cliente_id, hora, servicio_id) VALUES ($contacto,$efectivo,$producto,$id_usuario,'$date',$id_cliente,'$hora','$servicio')");

    $id_gestion = $this->db->insert_id;

    $this->db->query("UPDATE clientes SET status_id = $status WHERE id = $id_cliente");
      // echo ("UPDATE clientes SET status_id = $status WHERE id = $id_cliente");
    $sql = $this->db->query("SELECT MAX(id) as id_gestion FROM gestion WHERE cliente_id = $id_cliente");
    $data = $this->db->recorrer($sql);
    $id_gestion = $data['id_gestion'];
        
    $this->db->query("INSERT INTO resultados (gestion_id,nombre,apellido,genero,fecha_nacimiento,nacionalidad,cedula,telf_hab,telf_celular,correo,estado_id,ciudad_id,municipio_id,cuenta,tipo_cuenta_id,producto_id,observaciones,fecha_venta,servicio_id) VALUES ($id_gestion,'$nombre','$apellido','$genero','$fecha_nac','$nacionalidad',$cedula,'$telf_hab','$telf_cel','$correo',$estado,$ciudad,$municipio,'$cuenta','$tipocuenta',$producto,'$obs','$fecha','$servicio')");
    
  }
 
  public function registroGestion($contacto, $efectivo, $producto, $noefectivo, $id_usuario, $date, $id_cliente, $status, $hora, $servicio){
    if($servicio == 2){
      $table = 'cashea_customers';
      $field = 'cedula';
    }else{
      $table = 'clientes';
      $field = 'id';
    }
    
    if($efectivo == null && $producto == null){
      $this->db->query("INSERT INTO gestion (contacto_id, noefectivo_id, user_id, fecha, cliente_id, hora, servicio_id) VALUES ($contacto, $noefectivo, $id_usuario, '$date', $id_cliente, '$hora', '$servicio')");
      $this->db->query("UPDATE $table SET status_id = 8 WHERE $field = $id_cliente");

    }else if($noefectivo == null && $producto == null){
      $this->db->query("INSERT INTO gestion (contacto_id, efectivo_id, producto_id, user_id, fecha, cliente_id, hora, servicio_id) VALUES ($contacto, $efectivo, null, $id_usuario, '$date', $id_cliente, '$hora', '$servicio')");
      $this->db->query("UPDATE $table SET status_id = 4 WHERE $field = $id_cliente");

    }else{
      $this->db->query("INSERT INTO gestion (contacto_id, efectivo_id, producto_id, user_id, fecha, cliente_id, hora, servicio_id) VALUES ($contacto, $efectivo, $producto, $id_usuario, '$date', $id_cliente, '$hora', '$servicio')");
      $this->db->query("UPDATE $table SET status_id = 4 WHERE $field = $id_cliente"); 
    }
  }

  public function registroResultadosCashea($paymentPlan,$paymentDate,$amount,$fullName,$relationship,$observaciones,$id_cliente,$status){
    $this->db->query("INSERT INTO results_cashea (paymentPlan, paymentDate, amount, fullName, relationship, observaciones, cliente_id, status_id) VALUES ('$paymentPlan', '$paymentDate', $amount, '$fullName', '$relationship', '$observaciones', $id_cliente, $status)");
  }

  public function codigosProductos($servicio){
    $sql = $this->db->query("SELECT id_producto FROM productos WHERE id_servicio = '$servicio'");
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

  public function referidos($cod_servicio,$fecha,$id_user,$tipo){
    if($tipo == 2){
      $sql = $this->db->query("SELECT DISTINCT(clientes.nombre_legal), clientes.identificacion, clientes.telf_hab, clientes.telf_ofi, clientes.telf_cel, clientes.identificacion, CONCAT(gestion.fecha,' ',gestion.hora) AS fecha FROM clientes inner join gestion on clientes.id_cliente = gestion.id_cliente WHERE gestion.id_efectivo = 2 AND gestion.id_servicio = '$cod_servicio' AND gestion.fecha = '$fecha' AND gestion.id_user = $id_user ORDER BY fecha desc");
    }else{
      $sql = $this->db->query("SELECT DISTINCT(clientes.nombre_legal), clientes.identificacion, clientes.telf_hab, clientes.telf_ofi, clientes.telf_cel, clientes.identificacion, CONCAT(gestion.fecha,' ',gestion.hora) AS fecha, CONCAT(users.nombre,' ',users.apellido) AS operador FROM clientes INNER JOIN gestion on clientes.id_cliente = gestion.id_cliente INNER JOIN users ON gestion.id_user = users.id_user WHERE gestion.id_efectivo = 2 AND gestion.id_servicio = '$cod_servicio' AND gestion.fecha = '$fecha' ORDER BY fecha desc");
    }
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

  public function cuentasBancarias(){
    $sql = $this->db->query("SELECT * FROM tipo_cuentas");
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