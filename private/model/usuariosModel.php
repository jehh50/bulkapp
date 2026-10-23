<?php
class database{

  private $db;
  private $id;
  private $nombre;

  public function __construct() {
    $this->db = new Conexion();
  }

  public function newUser(string $nombre, string $apellido, string $user, string $pass, int $tipo, int $cliente): bool{
	$existe = $this->db->selectQuery("SELECT id FROM users WHERE user = ?", [$user], 's');
	if (empty($existe)) {
		$this->db->preparedQuery(
			"INSERT INTO users (nombre,apellido,user,password,usertype_id,status_id,servicio_id) VALUES (?,?,?,?,?,?,?)",
			[$nombre,$apellido,$user,$pass,$tipo,Constantes::REGISTRO_ACTIVO,$cliente], 'sssssss'
		);
		return true;
	}
	return false;
  }

  public function listUser(): array{
    // Activos primero, luego orden natural por id
    return $this->db->selectQuery("SELECT u.id,u.nombre,u.apellido,u.user,ut.name, u.usertype_id, u.status_id, st.name as status, s.descripcion, s.id as id_servicio FROM users u inner JOIN servicios s on u.servicio_id = s.id INNER JOIN usertypes ut ON u.usertype_id = ut.id INNER JOIN status st ON u.status_id = st.id ORDER BY CASE WHEN u.status_id = 2 THEN 0 ELSE 1 END, u.id ASC");
  }

  public function deleteUser(int $id_user): bool{
    // Borrado lógico: el usuario queda INACTIVO y conserva su historial de gestiones
    $this->db->preparedQuery("UPDATE users SET status_id = 1 WHERE id = ?", [$id_user], 'i');
    return true;
  }


  public function editUser(int $id_user): array{
    return $this->db->selectQuery("SELECT u.id,u.nombre,u.apellido,u.user,ut.name,st.name as status, u.status_id, s.descripcion, s.id as id_servicio FROM users u inner JOIN servicios s on u.servicio_id = s.id INNER JOIN usertypes ut ON u.usertype_id = ut.id INNER JOIN status st ON u.status_id = st.id WHERE u.id = ?", [$id_user], 's');
  }

  public function updateUser(int $id, string $nombre, string $apellido, int $rol, int $status, int $servicio): bool{
    $this->db->preparedQuery("UPDATE users SET nombre=?,apellido=?,usertype_id=?,status_id=?,servicio_id=? WHERE id=?", [$nombre,$apellido,$rol,$status,$servicio,$id], 'ssssii');
    return true;
  }

  public function existsUser(string $user, ?int $excludeId = null): bool{
    if ($excludeId === null) {
      $data = $this->db->selectQuery("SELECT id FROM users WHERE user = ?", [$user], 's');
    } else {
      $data = $this->db->selectQuery("SELECT id FROM users WHERE user = ? AND id <> ?", [$user, $excludeId], 'ss');
    }
    return !empty($data);
  }

  public function updatePass(int $id, string $newpass): bool{
    $this->db->preparedQuery("UPDATE users SET password = ? WHERE id = ?", [$newpass,$id], 'ss');
    return true;
  }

  public function servicio(): array{
    return $this->db->selectQuery("SELECT * FROM servicios where status_id = 2 ORDER BY descripcion");
  }

}

?>
