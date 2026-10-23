<?php
class database
{

  private $db;

  public function __construct()
  {
    $this->db = new Conexion();
  }

  public function clientesGestionados(string $desde, string $hasta, int $servicio, int $idEfectivo)
  {
    $sql = "SELECT CONCAT(users.nombre,' ',users.apellido) AS nombre, COUNT(gestion.contacto_id) AS gestion, users.id AS id FROM users INNER JOIN gestion ON users.id = gestion.user_id WHERE gestion.fecha BETWEEN ? AND ? AND gestion.servicio_id = ? GROUP BY gestion.user_id ORDER BY users.nombre";
    $filas = $this->db->selectQuery($sql, [$desde, $hasta, $servicio], 'sss');
    if (empty($filas)) {
      return false;
    }
    $respuesta = [];
    foreach ($filas as $data) {
      $conexion = new database();
      $id_user = $data['id'];
      $nombre = $data['nombre'];
      $gestion = $data['gestion'];

      $c = $conexion->clientesEfectivos($id_user, $desde, $hasta, $servicio);
      $efectivos = $c[0]['efectivos'];

      $v = $conexion->ventasEfectivas($id_user, $desde, $hasta, $servicio, $idEfectivo);
      $ventas = $v[0]['ventas'];

      $respuesta[] = compact("nombre", "gestion", "efectivos", "ventas");
    }
    return $respuesta;
  }

  public function efectivo(array $servicios): array
  {
    $servicios = array_values(array_map('intval', $servicios));
    if (empty($servicios)) {
      return [];
    }
    $in = '(' . implode(',', array_fill(0, count($servicios), '?')) . ')';
    $sql = "SELECT id FROM efectivo WHERE servicio_id IN $in AND descripcion = ?";
    $params = array_merge($servicios, ['gestión efectiva']);
    return $this->db->selectQuery($sql, $params, str_repeat('i', count($servicios)) . 's');
  }

  public function ventaDetallada(string $desde, string $hasta, int $servicio): array
  {
    if (Constantes::esServicioBancario($servicio)) {
      $sql = "SELECT r.nombre, r.apellido, r.cedula, r.fecha_nacimiento, r.telf_hab, r.telf_celular, r.correo, p.descripcion, CONCAT(u.nombre,' ',u.apellido) as agente, r.fecha_venta FROM `resultados` r INNER JOIN productos p ON r.producto_id = p.id INNER JOIN gestion g ON r.gestion_id = g.id INNER JOIN users u ON g.user_id = u.id WHERE r.fecha_venta BETWEEN ? AND ? AND r.servicio_id = ? ORDER BY r.fecha_venta DESC";
      return $this->db->selectQuery($sql, [$desde, $hasta, $servicio], 'sss');
    }

    $sql = "SELECT
            rc.id AS 'idResultado',
            rc.gestion_id AS 'idGestion',
            CASE rc.paymentPlan
                WHEN '1' THEN 'Monto total'
                WHEN '2' THEN 'Cuota'
                WHEN '3' THEN 'Pago personalizado'
                ELSE rc.paymentPlan
            END AS 'planDePago',
            rc.paymentDate AS 'fechaPago',
            rc.idQuote AS 'idCuota',
            rc.amount AS 'monto',
            rc.fullName AS 'nombreEncargado',
            CASE rc.relationship
                WHEN 'PADRE' THEN 'Padre'
                WHEN 'MADRE' THEN 'Madre'
                WHEN 'HIJO' THEN 'Hijo/Hija'
                WHEN 'HERMANO' THEN 'Hermano/Hermana'
                WHEN 'ESPOSA' THEN 'Esposo/Esposa'
                WHEN 'OTRO' THEN 'Otro'
                ELSE 'No especificado'
            END AS 'parentesco',
            rc.observaciones AS 'observaciones',
            CONCAT(u.nombre, ' ', u.apellido) AS 'operador',
            cc.nombre_usuario AS 'nombreCliente',
            rc.created_at AS 'fechaCreacion',
            s.name AS 'estadoGestion',
            cc.cedula AS 'cedula',
            g.tipoGestion AS 'tipo_gestion'
        FROM
            results_cashea AS rc
        LEFT JOIN
            gestion AS g ON rc.gestion_id = g.id
        LEFT JOIN
            users AS u ON g.user_id = u.id
        LEFT JOIN
            cashea_customers AS cc ON rc.cliente_id = cc.id
        LEFT JOIN
            status AS s ON rc.status_id = s.id
        WHERE
            DATE(rc.created_at) >= ? and DATE(rc.created_at) <= ?";
    return $this->db->selectQuery($sql, ["$desde 00:00:00", "$hasta 23:59:59"], 'ss');
  }

  public function clientesEfectivos(int $id_user, string $desde, string $hasta, int $servicio): array
  {
    $sql = "SELECT COUNT(gestion.contacto_id) AS efectivos FROM gestion WHERE gestion.user_id = ? AND contacto_id = 1 AND fecha BETWEEN ? AND ? AND servicio_id = ?";
    return $this->db->selectQuery($sql, [$id_user, $desde, $hasta, $servicio], 'ssss');
  }

  public function ventasEfectivas(int $id_user, string $desde, string $hasta, int $servicio, int $idEfectivo): array
  {
    $sql = "SELECT COUNT(gestion.producto_id) AS ventas FROM gestion WHERE gestion.user_id = ? AND efectivo_id = ? AND fecha BETWEEN ? AND ? AND servicio_id = ?";
    return $this->db->selectQuery($sql, [$id_user, $idEfectivo, $desde, $hasta, $servicio], 'sssss');
  }

  public function buscarCliente(string $cedula)
  {
    $sql = "SELECT a.nombre_legal,a.telf_hab,a.telf_ofi, a.telf_cel,c.name,a.identificacion,b.descripcion FROM clientes a INNER JOIN servicios b ON a.servicio_id = b.id INNER JOIN status c ON a.status_id = c.id WHERE a.identificacion = ? OR a.telf_ofi = ?";
    $data = $this->db->selectQuery($sql, [$cedula, $cedula], 'ss');
    return !empty($data) ? $data[0] : false;
  }

  public function liberarCliente(string $cedula): bool
  {
    $this->db->preparedQuery("UPDATE clientes SET status_id = 3 WHERE identificacion = ?", [$cedula], 's');
    return true;
  }

  public function gestionContactonoefectivo(string $desde, string $hasta, int $serv): array
  {
    $sql = "SELECT n.descripcion, COUNT(g.noefectivo_id) AS total FROM gestion g INNER JOIN noefectivo n ON g.noefectivo_id = n.id WHERE g.fecha BETWEEN ? AND ? AND g.servicio_id = ? GROUP BY n.descripcion";
    return $this->db->selectQuery($sql, [$desde, $hasta, $serv], 'sss');
  }

  public function gestionContactoefectivo(string $desde, string $hasta, int $serv): array
  {
    if (empty($serv)) {
      $sql = "SELECT e.descripcion, COUNT(g.efectivo_id) AS total FROM gestion g INNER JOIN efectivo e ON g.efectivo_id = e.id WHERE /*g.status_id = 4 AND */fecha BETWEEN ? AND ? GROUP BY e.descripcion";
      return $this->db->selectQuery($sql, [$desde, $hasta], 'ss');
    }
    $sql = "SELECT e.descripcion, COUNT(g.efectivo_id) AS total FROM gestion g INNER JOIN efectivo e ON g.efectivo_id = e.id WHERE /*g.status_id = 4 AND */g.fecha BETWEEN ? AND ? AND g.servicio_id = ? GROUP BY e.descripcion";
    return $this->db->selectQuery($sql, [$desde, $hasta, $serv], 'sss');
  }

  public function ventasEstado(string $desde, string $hasta, ?int $serv): array
  {
    if ($serv === null) {
      $sql = "SELECT COUNT(e.estado) AS total, e.estado, e.id FROM resultados r INNER JOIN estado e ON r.estado_id = e.id WHERE r.fecha_venta BETWEEN ? AND ? GROUP BY e.estado, e.id";
      return $this->db->selectQuery($sql, [$desde, $hasta], 'ss');
    }

    $sql = "SELECT COUNT(e.estado) AS total, e.estado, e.id FROM resultados r INNER JOIN estado e ON r.estado_id = e.id WHERE r.fecha_venta BETWEEN ? AND ? AND r.servicio_id = ? GROUP BY e.estado, e.id";
    return $this->db->selectQuery($sql, [$desde, $hasta, $serv], 'sss');
  }

  public function ventasCiudad(string $desde, string $hasta, int $id_estado, int $serv): array
  {
    $sql = "SELECT COUNT(c.ciudad) AS total, c.ciudad FROM resultados r INNER JOIN ciudad c ON r.ciudad_id = c.id WHERE r.fecha_venta BETWEEN ? AND ? AND r.estado_id = ? GROUP BY r.ciudad_id";
    return $this->db->selectQuery($sql, [$desde, $hasta, $id_estado], 'sss');
  }

  public function servicio(array $ids): array
  {
    $ids = array_values(array_map('intval', $ids));
    if (empty($ids)) {
      return [];
    }
    $in = '(' . implode(',', array_fill(0, count($ids), '?')) . ')';
    $sql = "SELECT id, descripcion FROM servicios WHERE status_id = 2 and id IN $in ORDER BY descripcion";
    return $this->db->selectQuery($sql, $ids, str_repeat('i', count($ids)));
  }

  public function gestionDetalladaCashea(string $desde, string $hasta): array
  {
    $sql = "SELECT
        IF(g.contacto_id = 0, 'No Efectivo', 'Efectivo') AS tipo_contacto,
        e.descripcion AS efectivo,
        ne.descripcion AS no_efectivo,
        sc.descripcion AS sub_contacto,
        CASE rc.paymentPlan
            WHEN '1' THEN 'Monto total'
            WHEN '2' THEN 'Cuota'
            WHEN '3' THEN 'Pago personalizado'
            ELSE rc.paymentPlan
        END AS planDePago,
        rc.paymentDate AS fechaPago,
        rc.idQuote AS idCuota,
        rc.amount AS monto,
        rc.fullName AS nombreEncargado,
        CASE rc.relationship
            WHEN 'PADRE' THEN 'Padre'
            WHEN 'MADRE' THEN 'Madre'
            WHEN 'HIJO' THEN 'Hijo/Hija'
            WHEN 'HERMANO' THEN 'Hermano/Hermana'
            WHEN 'ESPOSA' THEN 'Esposo/Esposa'
            WHEN 'OTRO' THEN 'Otro'
            ELSE 'No especificado'
        END AS parentesco,
        rc.observaciones AS observaciones,
        cc.nombre_usuario,
        cc.cedula,
        st.name AS status,
        CONCAT(u.nombre, ' ', u.apellido) AS operador,
        g.fecha AS fecha_gestion,
        g.hora AS hora_gestion
    FROM
        gestion g
    LEFT JOIN
        results_cashea rc ON g.id = rc.gestion_id
    LEFT JOIN
        efectivo e ON g.efectivo_id = e.id
    LEFT JOIN
        noefectivo ne ON g.noefectivo_id = ne.id
    LEFT JOIN
        subcontacto sc ON g.subcontacto_id = sc.id
    INNER JOIN
        cashea_customers cc ON cc.id = rc.cliente_id
    INNER JOIN
        status st ON st.id = cc.status_id
    INNER JOIN
        users u ON u.id = g.user_id
    WHERE
        g.servicio_id = 2
        AND g.fecha BETWEEN ? AND ?";
    return $this->db->selectQuery($sql, [$desde, $hasta], 'ss');
  }

  // ---------- Dashboard: acumulado del mes por servicio ----------
  public function dashboardServicios(string $desde, string $hasta, int $idEfectivo): array
  {
    $sql = "SELECT g.servicio_id AS id, s.descripcion AS nombre,
        COUNT(*) AS gestionados,
        SUM(g.contacto_id = 1) AS efectivos,
        SUM(g.efectivo_id = ?) AS ventas
      FROM gestion g
      INNER JOIN servicios s ON s.id = g.servicio_id
      WHERE s.status_id = 2 AND g.fecha BETWEEN ? AND ?
      GROUP BY g.servicio_id, s.descripcion
      ORDER BY ventas DESC, gestionados DESC";
    return $this->db->selectQuery($sql, [$idEfectivo, $desde, $hasta], 'sss');
  }

  public function dashboardVentasDiarias(string $desde, string $hasta, int $idEfectivo): array
  {
    $sql = "SELECT g.servicio_id AS id, DATE_FORMAT(g.fecha,'%Y-%m-%d') AS dia, COUNT(*) AS ventas
      FROM gestion g
      WHERE g.efectivo_id = ? AND g.fecha BETWEEN ? AND ?
      GROUP BY g.servicio_id, dia
      ORDER BY dia";
    return $this->db->selectQuery($sql, [$idEfectivo, $desde, $hasta], 'sss');
  }

  public function mesesConGestion(): array
  {
    $filas = $this->db->selectQuery("SELECT DATE_FORMAT(fecha,'%Y-%m') AS ym FROM gestion GROUP BY ym ORDER BY ym DESC");
    $respuesta = [];
    foreach ($filas as $f) { $respuesta[] = $f['ym']; }
    return $respuesta;
  }
}
?>
