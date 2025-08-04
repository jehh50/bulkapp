<?php
class database{
  
  private $db;
  private $id;
  private $nombre;

  public function __construct() {
    $this->db = new Conexion();
  }

  public function newUser($nombre,$apellido,$user,$pass,$tipo,$cliente){
  	$sql = $this->db->query("SELECT * FROM users WHERE user = '$user'");
  	if($this->db->rows($sql) == 0 ){
  		$sql = $this->db->query("INSERT INTO users (nombre,apellido,user,password,usertype_id,status_id,servicio_id) VALUES ('$nombre','$apellido','$user','$pass','$tipo',2,'$cliente')");
  		$respuesta = true;
  	}
  	else{
  		$respuesta = false;
  	}
  	return $respuesta;
  }

  public function listUser(){
    $sql = $this->db->query("SELECT u.id,u.nombre,u.apellido,u.user,ut.name,st.name as status, s.descripcion FROM users u inner JOIN servicios s on u.servicio_id = s.id INNER JOIN usertypes ut ON u.usertype_id = ut.id INNER JOIN status st ON u.status_id = st.id");
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


  public function editUser($id_user){
    $sql = $this->db->query("SELECT u.id,u.nombre,u.apellido,u.user,ut.name,st.name as status, u.status_id, s.descripcion, s.id as id_servicio FROM users u inner JOIN servicios s on u.servicio_id = s.id INNER JOIN usertypes ut ON u.usertype_id = ut.id INNER JOIN status st ON u.status_id = st.id WHERE u.id = $id_user");
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

  public function updateUser($id,$nombre,$apellido,$rol,$status,$servicio){
    $this->db->query("UPDATE users SET nombre='$nombre',apellido='$apellido',usertype_id=".$rol.",status_id='$status',servicio_id='$servicio' WHERE id=".$id);
    $respuesta = true;

    return $respuesta;
  }

  public function updatePass($id,$newpass){
    $sql = $this->db->query("UPDATE users SET password = '$newpass' WHERE id_user = ".$id);
    $respuesta = true;

    return $respuesta;
  }

  public function servicio(){
    $sql = $this->db->query("SELECT * FROM servicios where status_id = 2 ORDER BY descripcion");
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