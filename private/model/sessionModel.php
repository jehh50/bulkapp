<?php 
class database{
  
  private $db;

  public function __construct() {
    $this->db = new Conexion();
  }

 	public function sessionLogin($u){
	    $sql = $this->db->query("SELECT * FROM users WHERE user = '$u'");
	    if ($this->db->rows($sql) > 0) {
	      while($data = $this->db->recorrer($sql)){
	        $respuesta[] = $data;  
	      }
	    }
	    else{
	      $respuesta = false;
	    }
	    return  $respuesta;
  	}

  	public function sessionNew($u,$p_md5){
		$sql = $this->db->query("SELECT * FROM users WHERE user = '$u' AND password = '$p_md5'");
	    if ($this->db->rows($sql) > 0) {
	     return $this->db->recorrer($sql);
	    }
	    else{
	      return false;
	    }
  	}
  	

 }