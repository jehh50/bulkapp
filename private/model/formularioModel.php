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
    if ($servicio == 2) {
      $sql = "SELECT cc.is_active, cc.fecha_creacion_orden as mora, (CASE WHEN s.id = '3' THEN 'Pendiente por cobrar' WHEN s.id = 4 THEN 'Pendiente por cobrar' WHEN s.id = 8 THEN 'Pendiente por cobrar' WHEN s.id = 9 THEN 'Compromiso de pago' ELSE s.name END) AS status, cc.status_id, cc.id, cc.id_cuota, cc.local_origen, cc.plata_por_cobrar, cc.fecha_pagar, cc.numero_cuota, cc.tramo_inicial, cc.segmento, cc.nombre_usuario, cc.email, cc.telefono, cc.cedula, (SELECT SUM(CAST(REPLACE(REPLACE(plata_por_cobrar, '.', ''), ',', '.') AS DECIMAL(20, 10))) AS total_plata_por_cobrar FROM cashea_customers WHERE (cedula = ? OR telefono = ?) AND status_id IN (3,4,8,9,10,12,13) and is_active = 1) AS deuda_total FROM cashea_customers cc  INNER JOIN status s ON cc.status_id = s.id WHERE (cc.cedula = ? OR cc.telefono = ?) AND cc.status_id IN (3,4,7,8,9,10,12,13) AND cc.is_active = 1 ORDER BY cc.id_cuota ASC";

      //$sql = $this->db->query("SELECT * FROM vista_cashea_clientes vcc WHERE vcc.cedula = '$telefono' OR vcc.telefono = '$telefono' ORDER BY vcc.id_cuota ASC");

      return $this->db->selectQuery($sql, [$telefono, $telefono, $telefono, $telefono], 'ssss');
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
    $whereParams = [];
    if (Constantes::esServicioBancario($servicio)) {
      $table = 'clientes';
      $where = 'id = ?';
      $whereParams[] = $id_cliente;
    } else {
      $table = 'cashea_customers cc';
      if (!$idQuote) {
        $where = 'cc.cedula = ? AND cc.status_id = 3';
        $whereParams[] = $dni;
      } else {
        $where = 'cc.cedula = ? AND cc.status_id NOT IN (7,11) AND cc.id_cuota in ';
        $whereParams[] = $dni;
        if (count($idQuote) == 1) {
          $idQuote = explode("|", $idQuote[0]);
          $where .= "(?)";
          $whereParams[] = $idQuote[0];
        } else {
          $placeholders = [];
          for ($i = 0; $i < count($idQuote); $i++) {
            $idQuote[$i] = explode("|", $idQuote[$i]);
            $idQuote[$i] = $idQuote[$i][0];
            $placeholders[] = '?';
            $whereParams[] = $idQuote[$i];
          }
          $where .= '(' . implode(',', $placeholders) . ')';
        }
      }
    }

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

  public function registroResultadosCashea(?int $paymentPlan, ?string $paymentDate, ?array $idQuote, ?string $amount, ?string $fullName, ?string $relationship, ?string $observaciones, int $id_cliente, ?int $status, ?int $gestionId)
  {
    if (!is_array($idQuote) || empty($idQuote)) {
      return false;
    }
    for ($i = 0; $i < count($idQuote); $i++) {
      if (count($idQuote) == 1) {
        $value = explode('|', $idQuote[$i]);
        if (empty($amount)) {
          $amount = $value[1];
        }

        $this->db->preparedQuery(
          "INSERT INTO results_cashea (gestion_id,paymentPlan, paymentDate, idQuote, amount, fullName, relationship, observaciones, cliente_id, status_id) VALUES (?,?,?,?,?,?,?,?,?,?)",
          [$gestionId, $paymentPlan, $paymentDate, $value[0], $amount, $fullName, $relationship, $observaciones, $id_cliente, $status],
          'ssssssssss'
        );
      } else {
        $value = explode('|', $idQuote[$i]);
        $this->db->preparedQuery(
          "INSERT INTO results_cashea (gestion_id,paymentPlan, paymentDate, idQuote, amount, fullName, relationship, observaciones, cliente_id, status_id) VALUES (?,?,?,?,?,?,?,?,?,?)",
          [$gestionId, $paymentPlan, $paymentDate, $value[0], $value[1], $fullName, $relationship, $observaciones, $id_cliente, $status],
          'ssssssssss'
        );
      }
    }
  }

  public function codigosProductos(int $servicio): array
  {
    return $this->db->selectQuery("SELECT id FROM productos WHERE servicio_id = ?", [$servicio], 's');
  }

  public function referidos(int $cod_servicio, string $fecha, int $id_user, int $tipo): array
  {
    if ($tipo == 2) {
      $sql = "SELECT DISTINCT(clientes.nombre_legal), clientes.identificacion, clientes.telf_hab, clientes.telf_ofi, clientes.telf_cel, clientes.identificacion, CONCAT(gestion.fecha,' ',gestion.hora) AS fecha FROM clientes inner join gestion on clientes.id = gestion.cliente_id WHERE gestion.efectivo_id = 2 AND gestion.servicio_id = ? AND gestion.fecha = ? AND gestion.user_id = ? ORDER BY fecha desc";
      return $this->db->selectQuery($sql, [$cod_servicio, $fecha, $id_user], 'sss');
    } else {
      $sql = "SELECT DISTINCT(clientes.nombre_legal), clientes.identificacion, clientes.telf_hab, clientes.telf_ofi, clientes.telf_cel, clientes.identificacion, CONCAT(gestion.fecha,' ',gestion.hora) AS fecha, CONCAT(users.nombre,' ',users.apellido) AS operador FROM clientes INNER JOIN gestion on clientes.id = gestion.cliente_id INNER JOIN users ON gestion.user_id = users.id WHERE gestion.efectivo_id = 2 AND gestion.servicio_id = ? AND gestion.fecha = ? ORDER BY fecha desc";
      return $this->db->selectQuery($sql, [$cod_servicio, $fecha], 'ss');
    }
  }

  public function cuentasBancarias(): array
  {
    return $this->db->selectQuery("SELECT * FROM tipo_cuentas where status_id = '2'");
  }

  public function customerHistory(array $idCliente): array
  {
    $placeholders = [];
    for ($i = 0; $i < count($idCliente); $i++) {
      $placeholders[] = '?';
    }
    $in = '(' . implode(',', $placeholders) . ')';

    $sql = "SELECT IF(g.contacto_id = 0, 'No efectivo','Efectivo') AS contacto, e.descripcion AS efectivo, ne.descripcion AS noefectivo, s.descripcion AS subcontacto, g.fecha, st.name as status, CONCAT(u.nombre,' ',u.apellido) as asesor FROM gestion g LEFT JOIN efectivo e ON g.efectivo_id = e.id LEFT JOIN noefectivo ne ON g.noefectivo_id = ne.id LEFT JOIN subcontacto s ON g.subContacto_id = s.id LEFT JOIN status st ON g.status_id = st.id LEFT JOIN users u ON g.user_id = u.id WHERE g.cliente_id IN $in ORDER BY g.id DESC";
    return $this->db->selectQuery($sql, $idCliente, str_repeat('s', count($idCliente)));
  }
}
