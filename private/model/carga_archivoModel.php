<?php
class database{
  
  private $db;
  private $id;
  private $nombre;

  public function __construct() {
    $this->db = new Conexion();
  }


  	public function registro($tipo_persona, $tipo_documento, $identificacion, $nombre_legal, $telf_hab, $telf_ofi, $telf_cel,$correo,$direccion, $cuenta,$fecha,$cod_servicio){
	  $sql = $this->db->query("INSERT INTO clientes (tipo_persona, tipo_de_documento, identificacion, nombre_legal, telf_hab, telf_ofi, telf_cel, correo, direccion, cuenta,fecha_carga,cod_servicio) VALUES ('$tipo_persona', '$tipo_documento', '$identificacion', '$nombre_legal', '$telf_hab', '$telf_ofi', '$telf_cel', '$correo', '$direccion', '$cuenta','$fecha','$cod_servicio')"); 
    }
  	
	public function servicio(){
	  $sql = $this->db->query("SELECT * FROM servicios where cod_servicio != 'all' order by descripcion");
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