<?php
class database{

  private $db;

  public function __construct() {
    $this->db = new Conexion();
  }

	public function sessionLogin(string $u){
	    $sql = "SELECT * FROM users WHERE user = ?";
	    $data = $this->db->selectQuery($sql, [$u], 's');
	    return !empty($data) ? $data[0] : false;
	}

	public function updatePass(int $id, string $hash): bool
	{
		$this->db->preparedQuery("UPDATE users SET password = ? WHERE id = ?", [$hash, $id], 'ss');
		return true;
	}


 }
