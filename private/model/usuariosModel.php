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
		// La campaña principal siempre forma parte de sus campañas asignadas (N:M).
		$this->asegurarAsignacion((int) $this->db->insert_id, $cliente);
		return true;
	}
	return false;
  }

  public function listUser(): array{
    // Activos primero, luego orden natural por id
    // descripcion = etiqueta «Empresa › Campaña» de la campaña principal; campanas_extra = otras asignadas.
    return $this->db->selectQuery("SELECT u.id,u.nombre,u.apellido,u.user,ut.name, u.usertype_id, u.status_id, st.name as status, " . campanas_sql_etiqueta() . " AS descripcion, s.id as id_servicio, (SELECT COUNT(*) FROM usuario_servicios us WHERE us.user_id = u.id AND us.servicio_id <> u.servicio_id) AS campanas_extra FROM users u inner JOIN servicios s on u.servicio_id = s.id LEFT JOIN empresas e ON e.id = s.empresa_id INNER JOIN usertypes ut ON u.usertype_id = ut.id INNER JOIN status st ON u.status_id = st.id ORDER BY CASE WHEN u.status_id = 2 THEN 0 ELSE 1 END, u.id ASC");
  }

  public function deleteUser(int $id_user): bool{
    // Borrado lógico: el usuario queda INACTIVO y conserva su historial de gestiones
    $this->db->preparedQuery("UPDATE users SET status_id = 1 WHERE id = ?", [$id_user], 'i');
    return true;
  }


  public function editUser(int $id_user): array{
    return $this->db->selectQuery("SELECT u.id,u.nombre,u.apellido,u.user,ut.name,st.name as status, u.status_id, " . campanas_sql_etiqueta() . " AS descripcion, s.id as id_servicio FROM users u inner JOIN servicios s on u.servicio_id = s.id LEFT JOIN empresas e ON e.id = s.empresa_id INNER JOIN usertypes ut ON u.usertype_id = ut.id INNER JOIN status st ON u.status_id = st.id WHERE u.id = ?", [$id_user], 's');
  }

  public function updateUser(int $id, string $nombre, string $apellido, int $rol, int $status, int $servicio): bool{
    $this->db->preparedQuery("UPDATE users SET nombre=?,apellido=?,usertype_id=?,status_id=?,servicio_id=? WHERE id=?", [$nombre,$apellido,$rol,$status,$servicio,$id], 'ssssii');
    $this->asegurarAsignacion($id, $servicio);
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

  /**
   * Campañas activas de clientes activos; descripcion = etiqueta «Empresa › Campaña».
   */
  public function servicio(): array{
    return $this->db->selectQuery(
      "SELECT s.id, " . campanas_sql_etiqueta() . " AS descripcion FROM servicios s JOIN empresas e ON e.id = s.empresa_id WHERE s.status_id = ? AND e.status_id = ? ORDER BY descripcion",
      [Constantes::REGISTRO_ACTIVO, Constantes::REGISTRO_ACTIVO], 'ii'
    );
  }

  /**
   * Garantiza que la campaña principal del usuario esté en usuario_servicios.
   * Las demás asignaciones las gestiona el Administrador en Clientes y campañas.
   */
  private function asegurarAsignacion(int $userId, int $servicioId): void{
    if ($userId <= 0 || $servicioId <= 0) {
      return;
    }
    $this->db->preparedQuery("INSERT IGNORE INTO usuario_servicios (user_id, servicio_id) VALUES (?,?)", [$userId, $servicioId], 'ii');
  }

}

?>
