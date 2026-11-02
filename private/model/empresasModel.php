<?php

/**
 * Modelo de Clientes (empresas) y campañas — acceso exclusivo del Administrador.
 *
 * Una campaña es un registro de `servicios` (su id es el `servicio_id` de todo el sistema;
 * su nombre vive en `servicios.descripcion`). Reglas:
 * - Inactivar una empresa inactiva también sus campañas; una campaña no puede activarse
 *   si su empresa está inactiva.
 * - El tipo de flujo se fija al crear la campaña y no se modifica.
 * - users.servicio_id es la campaña PRINCIPAL y siempre está en usuario_servicios: al quitar
 *   a un usuario de su principal se le asigna otra; si no tiene otra, no se le quita.
 * Solo consultas preparadas.
 */
class database
{
  private $db;

  public function __construct()
  {
    $this->db = new Conexion();
  }

  /* ---------------------------------------------------------------------
   * Empresas
   * ------------------------------------------------------------------- */

  /**
   * Empresas con su conteo de campañas (total / activas) y usuarios asignados.
   *
   * @return array
   */
  public function listarEmpresas()
  {
    return $this->db->selectQuery(
      "SELECT e.id, e.nombre, e.identificacion, e.status_id, e.updated_at,
              COUNT(DISTINCT s.id) AS campanas,
              COUNT(DISTINCT IF(s.status_id = ?, s.id, NULL)) AS campanas_activas,
              COUNT(DISTINCT us.user_id) AS usuarios
         FROM empresas e
         LEFT JOIN servicios s ON s.empresa_id = e.id
         LEFT JOIN usuario_servicios us ON us.servicio_id = s.id
        GROUP BY e.id, e.nombre, e.identificacion, e.status_id, e.updated_at
        ORDER BY e.status_id = ? DESC, e.nombre",
      array(Constantes::REGISTRO_ACTIVO, Constantes::REGISTRO_ACTIVO),
      'ii'
    );
  }

  /**
   * @param int $id
   * @return array|null
   */
  public function obtenerEmpresa($id)
  {
    $filas = $this->db->selectQuery(
      "SELECT id, nombre, identificacion, status_id FROM empresas WHERE id = ?",
      array((int) $id),
      'i'
    );

    return empty($filas) ? null : $filas[0];
  }

  /**
   * Crea o actualiza una empresa. Inactivarla inactiva también sus campañas.
   *
   * @param array $datos Salida de campanas_validar_empresa()['datos'].
   * @param int|null $id
   * @return array { ok: bool, id?: int, message?: string, campanas_inactivadas?: int }
   */
  public function guardarEmpresa(array $datos, $id = null)
  {
    $dup = $this->db->selectQuery(
      "SELECT id FROM empresas WHERE nombre = ? AND id <> ?",
      array($datos['nombre'], (int) $id),
      'si'
    );
    if (!empty($dup)) {
      return array('ok' => false, 'message' => 'Ya existe un cliente con ese nombre.');
    }

    $inactivadas = 0;
    $this->db->begin_transaction();
    try {
      if ($id === null) {
        $this->db->preparedQuery(
          "INSERT INTO empresas (nombre, identificacion, status_id) VALUES (?,?,?)",
          array($datos['nombre'], $datos['identificacion'], $datos['status_id']),
          'ssi'
        );
        $id = (int) $this->db->insert_id;
      } else {
        $this->db->preparedQuery(
          "UPDATE empresas SET nombre = ?, identificacion = ?, status_id = ? WHERE id = ?",
          array($datos['nombre'], $datos['identificacion'], $datos['status_id'], (int) $id),
          'ssii'
        );
        if ($datos['status_id'] === Constantes::REGISTRO_INACTIVO) {
          $stmt = $this->db->preparedQuery(
            "UPDATE servicios SET status_id = ? WHERE empresa_id = ? AND status_id = ?",
            array(Constantes::REGISTRO_INACTIVO, (int) $id, Constantes::REGISTRO_ACTIVO),
            'iii'
          );
          $inactivadas = (int) $stmt->affected_rows;
        }
      }
      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }

    return array('ok' => true, 'id' => (int) $id, 'campanas_inactivadas' => $inactivadas);
  }

  /* ---------------------------------------------------------------------
   * Campañas (servicios)
   * ------------------------------------------------------------------- */

  /**
   * Campañas de una empresa con su nº de usuarios asignados y productos activos.
   *
   * @param int $empresaId
   * @return array
   */
  public function listarCampanas($empresaId)
  {
    return $this->db->selectQuery(
      "SELECT s.id, s.descripcion AS nombre, s.detalle, s.fecha_inicio, s.meta, s.tipo_flujo,
              s.codigo, s.status_id,
              (SELECT COUNT(*) FROM usuario_servicios us WHERE us.servicio_id = s.id) AS usuarios,
              (SELECT COUNT(*) FROM productos p WHERE p.servicio_id = s.id AND p.status_id = ?) AS productos
         FROM servicios s
        WHERE s.empresa_id = ?
        ORDER BY s.status_id = ? DESC, s.fecha_inicio DESC, s.descripcion",
      array(Constantes::REGISTRO_ACTIVO, (int) $empresaId, Constantes::REGISTRO_ACTIVO),
      'iii'
    );
  }

  /**
   * @param int $id
   * @return array|null
   */
  public function obtenerCampana($id)
  {
    $filas = $this->db->selectQuery(
      "SELECT s.id, s.empresa_id, s.descripcion AS nombre, s.tipo_flujo, s.status_id,
              e.nombre AS empresa
         FROM servicios s JOIN empresas e ON e.id = s.empresa_id
        WHERE s.id = ?",
      array((int) $id),
      'i'
    );

    return empty($filas) ? null : $filas[0];
  }

  /**
   * Crea o actualiza una campaña de una empresa.
   *
   * @param int $empresaId
   * @param array $datos Salida de campanas_validar_campana()['datos'].
   * @param int|null $id
   * @return array { ok: bool, id?: int, message?: string }
   */
  public function guardarCampana($empresaId, array $datos, $id = null)
  {
    $empresa = $this->obtenerEmpresa($empresaId);
    if ($empresa === null) {
      return array('ok' => false, 'message' => 'Cliente no encontrado.');
    }
    if ($datos['status_id'] === Constantes::REGISTRO_ACTIVO && (int) $empresa['status_id'] !== Constantes::REGISTRO_ACTIVO) {
      return array('ok' => false, 'message' => 'No se puede activar una campaña de un cliente inactivo.');
    }

    if ($id !== null) {
      $actual = $this->obtenerCampana($id);
      if ($actual === null || (int) $actual['empresa_id'] !== (int) $empresaId) {
        return array('ok' => false, 'message' => 'Campaña no encontrada.');
      }
    }

    $dup = $this->db->selectQuery(
      "SELECT id FROM servicios WHERE empresa_id = ? AND descripcion = ? AND id <> ?",
      array((int) $empresaId, $datos['nombre'], (int) $id),
      'isi'
    );
    if (!empty($dup)) {
      return array('ok' => false, 'message' => 'El cliente ya tiene una campaña con ese nombre.');
    }

    if ($id === null) {
      $this->db->preparedQuery(
        "INSERT INTO servicios (empresa_id, descripcion, detalle, fecha_inicio, meta, tipo_flujo, codigo, status_id)
         VALUES (?,?,?,?,?,?,?,?)",
        array((int) $empresaId, $datos['nombre'], $datos['detalle'], $datos['fecha_inicio'], $datos['meta'],
          $datos['tipo_flujo'], $datos['codigo'], $datos['status_id']),
        'isssissi'
      );
      $id = (int) $this->db->insert_id;
    } else {
      // tipo_flujo no se toca: cambiarlo re-enrutaría el historial de la campaña.
      $this->db->preparedQuery(
        "UPDATE servicios SET descripcion = ?, detalle = ?, fecha_inicio = ?, meta = ?, codigo = ?, status_id = ?
          WHERE id = ?",
        array($datos['nombre'], $datos['detalle'], $datos['fecha_inicio'], $datos['meta'], $datos['codigo'],
          $datos['status_id'], (int) $id),
        'sssisii'
      );
    }

    return array('ok' => true, 'id' => (int) $id);
  }

  /* ---------------------------------------------------------------------
   * Usuarios asignados (N:M)
   * ------------------------------------------------------------------- */

  /**
   * Usuarios activos con su marca de asignación a la campaña y si es su principal.
   *
   * @param int $servicioId
   * @return array
   */
  public function usuariosDeCampana($servicioId)
  {
    return $this->db->selectQuery(
      "SELECT u.id, u.user, u.nombre, u.apellido, u.usertype_id, ut.name AS rol,
              (us.user_id IS NOT NULL) AS asignado,
              (u.servicio_id = ?) AS principal
         FROM users u
         JOIN usertypes ut ON ut.id = u.usertype_id
         LEFT JOIN usuario_servicios us ON us.user_id = u.id AND us.servicio_id = ?
        WHERE u.status_id = ?
        ORDER BY asignado DESC, u.usertype_id DESC, u.nombre, u.apellido",
      array((int) $servicioId, (int) $servicioId, Constantes::REGISTRO_ACTIVO),
      'iii'
    );
  }

  /**
   * Fija los usuarios (activos) asignados a una campaña.
   * Los usuarios inactivos conservan sus asignaciones tal cual.
   *
   * @param int $servicioId
   * @param int[] $userIds Usuarios que deben quedar asignados.
   * @return array { agregados: int, quitados: int, principal_movida: int, bloqueados: array }
   */
  public function asignarUsuarios($servicioId, array $userIds)
  {
    $servicioId = (int) $servicioId;
    $deseados = array_values(array_unique(array_map('intval', $userIds)));

    $activos = array_map('intval', array_column($this->db->selectQuery(
      "SELECT id FROM users WHERE status_id = ?",
      array(Constantes::REGISTRO_ACTIVO),
      'i'
    ), 'id'));
    $deseados = array_values(array_intersect($deseados, $activos));

    $actuales = $this->db->selectQuery(
      "SELECT u.id, u.user, u.servicio_id
         FROM usuario_servicios us JOIN users u ON u.id = us.user_id
        WHERE us.servicio_id = ? AND u.status_id = ?",
      array($servicioId, Constantes::REGISTRO_ACTIVO),
      'ii'
    );
    $idsActuales = array_map('intval', array_column($actuales, 'id'));

    $agregar = array_diff($deseados, $idsActuales);
    $resultado = array('agregados' => 0, 'quitados' => 0, 'principal_movida' => 0, 'bloqueados' => array());

    $this->db->begin_transaction();
    try {
      foreach ($agregar as $uid) {
        $this->db->preparedQuery(
          "INSERT IGNORE INTO usuario_servicios (user_id, servicio_id) VALUES (?,?)",
          array((int) $uid, $servicioId),
          'ii'
        );
        $resultado['agregados']++;
      }

      foreach ($actuales as $u) {
        $uid = (int) $u['id'];
        if (in_array($uid, $deseados, true)) {
          continue;
        }

        if ((int) $u['servicio_id'] === $servicioId) {
          // Es su campaña principal: se mueve a otra asignada (preferible activa).
          $otra = $this->db->selectQuery(
            "SELECT us.servicio_id FROM usuario_servicios us JOIN servicios s ON s.id = us.servicio_id
              WHERE us.user_id = ? AND us.servicio_id <> ?
              ORDER BY s.status_id = ? DESC, s.id LIMIT 1",
            array($uid, $servicioId, Constantes::REGISTRO_ACTIVO),
            'iii'
          );
          if (empty($otra)) {
            $resultado['bloqueados'][] = array('id' => $uid, 'user' => $u['user']);
            continue;
          }
          $this->db->preparedQuery(
            "UPDATE users SET servicio_id = ? WHERE id = ?",
            array((int) $otra[0]['servicio_id'], $uid),
            'ii'
          );
          $resultado['principal_movida']++;
        }

        $this->db->preparedQuery(
          "DELETE FROM usuario_servicios WHERE user_id = ? AND servicio_id = ?",
          array($uid, $servicioId),
          'ii'
        );
        $resultado['quitados']++;
      }

      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }

    return $resultado;
  }
}
