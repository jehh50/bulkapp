<?php
class database
{
  private $db;
  public function __construct()
  {
    $this->db = new Conexion();
  }

  public function contactoEfectivo(int $id): array
  {
    return $this->db->selectQuery("SELECT * FROM efectivo WHERE servicio_id = ?", [$id], 's');
  }

  public function contactoNoEfectivo(int $id): array
  {
    return $this->db->selectQuery("SELECT * FROM noefectivo WHERE servicio_id = ?", [$id], 's');
  }

  public function contactoSecundario(int $e): array
  {
    return $this->db->selectQuery("SELECT * FROM subcontacto where efectivo_id = ? ORDER BY descripcion", [$e], 's');
  }

  public function ventaProducto(int $servicio): array
  {
    return $this->db->selectQuery("SELECT * FROM productos WHERE status_id = ? AND servicio_id = ?", [Constantes::REGISTRO_ACTIVO, $servicio], 'si');
  }

  public function buscaCliente(string $telefono, int $servicio): ?array
  {
    if (Constantes::esServicioBancario($servicio)) {
      $sql = "SELECT * FROM clientes WHERE (identificacion = ? OR telf_hab LIKE ? or telf_ofi LIKE ? OR telf_cel LIKE ?) and servicio_id = ?";
      return $this->db->selectQuery($sql, [$telefono, '%' . $telefono, '%' . $telefono, '%' . $telefono, $servicio], 'sssss');
    }
  }

  public function estado(int $servicio_id): array
  {
    return $this->db->selectQuery("SELECT * FROM estado WHERE servicio_id = ?", [$servicio_id], 's');
  }

  public function ciudadContar(int $id_estado): array
  {
    return $this->db->selectQuery("SELECT count(estado_id) FROM ciudad WHERE estado_id = ?", [$id_estado], 's');
  }

  public function ciudad(int $id_estado): array
  {
    return $this->db->selectQuery("SELECT * FROM ciudad WHERE estado_id = ? AND servicio_id = 1 ORDER BY ciudad", [$id_estado], 's');
  }

  public function municipio(int $estado_id, int $servicio_id): array
  {
    return $this->db->selectQuery("SELECT * FROM municipio WHERE estado_id = ? and servicio_id = ?", [$estado_id, $servicio_id], 'ss');
  }

  public function registroResultados(?int $contacto, ?int $efectivo, ?int $producto, ?int $noefectivo, int $id_usuario, string $date, ?string $nombre, ?string $apellido, ?string $genero, ?string $fecha_nac, ?int $nacionalidad, ?string $cedula, ?string $telf_hab, ?string $telf_cel, ?string $correo, ?int $estado, ?int $ciudad, ?int $municipio, ?string $cuenta, ?int $tipocuenta, ?string $obs, string $fecha, ?int $status, int $id_cliente, int $var, string $hora, int $servicio): bool
  {
    // Guardia de idempotencia: marca al cliente como vendido SOLO si aún no lo está.
    // Si no se afecta ninguna fila, ya estaba vendido (status 7) o no existe:
    // no se vuelve a registrar la venta (evita duplicados por reenvío del POST o doble clic).
    $stmt = $this->db->preparedQuery(
      "UPDATE clientes SET status_id = ? WHERE id = ? AND status_id <> ?",
      [$status, $id_cliente, Constantes::ESTADO_ATENDIDO],
      'sss'
    );
    $afectadas = $stmt->affected_rows;
    $stmt->close();
    if ($afectadas < 1) {
      return false;
    }

    $this->db->preparedQuery(
      "INSERT INTO gestion (contacto_id, efectivo_id, producto_id, user_id, fecha, cliente_id, status_id, hora, servicio_id) VALUES (?,?,?,?,?,?,?,?,?)",
      [$contacto, $efectivo, $producto, $id_usuario, $date, $id_cliente, $status, $hora, $servicio],
      'sssssssss'
    );
    $id_gestion = $this->db->insert_id;

    $this->db->preparedQuery(
      "INSERT INTO resultados (gestion_id,nombre,apellido,genero,fecha_nacimiento,nacionalidad,cedula,telf_hab,telf_celular,correo,estado_id,ciudad_id,municipio_id,cuenta,tipo_cuenta_id,producto_id,observaciones,fecha_venta,servicio_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
      [$id_gestion, $nombre, $apellido, $genero, $fecha_nac, $nacionalidad, $cedula, $telf_hab, $telf_cel, $correo, $estado, $ciudad, $municipio, $cuenta, $tipocuenta, $producto, $obs, $fecha, $servicio],
      'sssssssssssssssssss'
    );

    return true;
  }

  public function registroGestion(?int $contacto, ?int $efectivo, ?int $producto, ?int $noefectivo, ?int $subcontacto, int $id_usuario, string $date, int $id_cliente, ?int $status, string $hora, int $servicio, ?string $dni, ?array $idQuote, ?string $gestion): int
  {
    $table = 'clientes';
    $where = 'id = ?';
    $whereParams = [$id_cliente];

    $efectivo = ($efectivo === null) ? null : $efectivo;
    $noefectivo = ($noefectivo === null) ? null : $noefectivo;
    $producto = ($producto === null) ? null : $producto;
    $subcontacto = ($subcontacto === '' || $subcontacto === null) ? null : $subcontacto;
    // tipoGestion es NOT NULL: el formulario de servicio 1/5 no envía 'gestion',
    // así que null se convierte en cadena vacía (como hacía el INSERT interpolado anterior).
    $gestion = ($gestion === null) ? '' : $gestion;


    $this->db->preparedQuery(
      "INSERT INTO gestion (contacto_id, efectivo_id, producto_id, noefectivo_id, subContacto_id, user_id, fecha, cliente_id, status_id, hora, servicio_id, tipoGestion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
      [$contacto, $efectivo, $producto, $noefectivo, $subcontacto, $id_usuario, $date, $id_cliente, $status, $hora, $servicio, $gestion],
      'ssssssssssss'
    );

    $id_gestion = $this->db->insert_id;

    $updateParams = array_merge([$status], $whereParams);
    $this->db->preparedQuery("UPDATE $table SET status_id = ? WHERE $where", $updateParams, str_repeat('s', count($updateParams)));

    return $id_gestion;
  }

  public function codigosProductos(int $servicio): array
  {
    return $this->db->selectQuery("SELECT id FROM productos WHERE servicio_id = ?", [$servicio], 's');
  }

  public function cuentasBancarias(): array
  {
    return $this->db->selectQuery("SELECT * FROM tipo_cuentas where status_id = '2'");
  }
}
