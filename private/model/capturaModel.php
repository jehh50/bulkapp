<?php

/**
 * Modelo del runtime de formularios dinámicos (Constructor, Fase 3).
 *
 * Captura del operador (formulario activo del servicio, búsqueda en la BD vigente,
 * envíos) y reportería de envíos. Tablas propias: no toca el esquema bancario.
 */
class database
{
  private $db;

  public function __construct()
  {
    $this->db = new Conexion();
  }

  /**
   * Formulario activo asignado a un servicio, con su versión vigente.
   *
   * @param int $servicioId
   * @return array|null ['id','nombre','btn_texto','version_id','definicion'(array)]
   */
  public function formularioActivoDeServicio($servicioId)
  {
    $r = $this->db->selectQuery(
      "SELECT f.id, f.nombre, f.btn_texto, v.id AS version_id, v.definicion
       FROM formulario_servicios fs
       JOIN formularios f ON f.id = fs.formulario_id
       JOIN formulario_versiones v ON v.formulario_id = f.id AND v.vigente = 1
       WHERE fs.servicio_id = ? AND f.status_id = ? AND f.eliminado = 0
       LIMIT 1",
      array((int) $servicioId, Constantes::REGISTRO_ACTIVO),
      'ii'
    );
    if (empty($r)) {
      return null;
    }
    $form = $r[0];
    $def = json_decode($form['definicion'], true);
    $form['definicion'] = is_array($def) ? $def : array('campos' => array());
    return $form;
  }

  /**
   * BD vigente del formulario con la definición de la card, o null si no hay
   * (o si la carga no define columna de búsqueda).
   *
   * @param int $formId
   * @return array|null ['id', 'busqueda', 'columnas']
   */
  public function cargaVigente($formId)
  {
    $r = $this->db->selectQuery(
      "SELECT id, mapping FROM formulario_cargas WHERE formulario_id = ? AND vigente = 1 LIMIT 1",
      array((int) $formId),
      'i'
    );
    if (empty($r)) {
      return null;
    }
    $def = json_decode($r[0]['mapping'], true);
    if (!is_array($def) || empty($def['busqueda']) || empty($def['columnas'])) {
      return null;
    }
    return array('id' => (int) $r[0]['id'], 'busqueda' => $def['busqueda'], 'columnas' => $def['columnas']);
  }

  /**
   * Busca un registro por su clave en una carga. Si hay varios con la misma
   * clave, devuelve primero uno no atendido.
   *
   * @param int $cargaId
   * @param string $clave
   * @return array|null ['id', 'datos'(array), 'atendido'(bool)]
   */
  public function buscarRegistro($cargaId, $clave)
  {
    $r = $this->db->selectQuery(
      "SELECT r.id, r.fila, (e.id IS NOT NULL) AS atendido
       FROM formulario_carga_filas r
       LEFT JOIN formulario_envios e ON e.fila_id = r.id
       WHERE r.carga_id = ? AND r.clave = ?
       ORDER BY atendido ASC, r.id ASC
       LIMIT 1",
      array((int) $cargaId, (string) $clave),
      'is'
    );
    return empty($r) ? null : $this->registroDesdeFila($r[0]);
  }

  /**
   * Registro por id, solo si pertenece a la carga indicada.
   *
   * @param int $filaId
   * @param int $cargaId
   * @return array|null ['id', 'datos'(array), 'atendido'(bool)]
   */
  public function registro($filaId, $cargaId)
  {
    $r = $this->db->selectQuery(
      "SELECT r.id, r.fila, (e.id IS NOT NULL) AS atendido
       FROM formulario_carga_filas r
       LEFT JOIN formulario_envios e ON e.fila_id = r.id
       WHERE r.id = ? AND r.carga_id = ?",
      array((int) $filaId, (int) $cargaId),
      'ii'
    );
    return empty($r) ? null : $this->registroDesdeFila($r[0]);
  }

  /**
   * @param array $row
   * @return array
   */
  private function registroDesdeFila(array $row)
  {
    $datos = json_decode($row['fila'], true);
    return array(
      'id' => (int) $row['id'],
      'datos' => is_array($datos) ? $datos : array(),
      'atendido' => (bool) $row['atendido'],
    );
  }

  /**
   * Inserta un envío. Devuelve false si el registro ya fue atendido (clave única
   * sobre fila_id), lo que cubre dos operadores guardando a la vez.
   *
   * @param array $d ['formulario_id','version_id','servicio_id','carga_id'?,'fila_id'?,
   *                  'registro'?(array),'valores'(array),'user_id'?]
   * @return int|false Id del envío.
   */
  public function guardarEnvio(array $d)
  {
    try {
      $this->db->preparedQuery(
        "INSERT INTO formulario_envios
           (formulario_id, version_id, servicio_id, carga_id, fila_id, registro, valores, user_id)
         VALUES (?,?,?,?,?,?,?,?)",
        array(
          (int) $d['formulario_id'],
          (int) $d['version_id'],
          (int) $d['servicio_id'],
          isset($d['carga_id']) ? (int) $d['carga_id'] : null,
          isset($d['fila_id']) ? (int) $d['fila_id'] : null,
          isset($d['registro']) ? json_encode($d['registro'], JSON_UNESCAPED_UNICODE) : null,
          json_encode((object) $d['valores'], JSON_UNESCAPED_UNICODE),
          isset($d['user_id']) ? (int) $d['user_id'] : null,
        ),
        'iiiiissi'
      );
    } catch (mysqli_sql_exception $e) {
      if ((int) $e->getCode() === 1062) {
        return false;
      }
      throw $e;
    }
    return (int) $this->db->insert_id;
  }

  /* ---------------------------------------------------------------------
   * Reportería
   * ------------------------------------------------------------------- */

  /**
   * Combinaciones cliente → campaña → formulario para los filtros del reporte: las
   * asignaciones vigentes del Constructor más las que ya tienen envíos (un formulario
   * desasignado o eliminado conserva su historial). Solo campañas no bancarias.
   *
   * @return array[] empresa_id, empresa, servicio_id, campana, formulario_id, formulario, eliminado
   */
  public function combinacionesReporte()
  {
    $filas = $this->db->selectQuery(
      "SELECT em.id AS empresa_id, em.nombre AS empresa, s.id AS servicio_id, s.descripcion AS campana,
              f.id AS formulario_id, f.nombre AS formulario, f.eliminado
         FROM (SELECT formulario_id, servicio_id FROM formulario_servicios
               UNION
               SELECT DISTINCT formulario_id, servicio_id FROM formulario_envios) p
         JOIN formularios f ON f.id = p.formulario_id
         JOIN servicios s ON s.id = p.servicio_id
         JOIN empresas em ON em.id = s.empresa_id
        ORDER BY em.nombre, s.descripcion, f.eliminado, f.nombre",
      array(),
      ''
    );
    return array_values(array_filter($filas, function ($f) {
      return !Constantes::esServicioBancario($f['servicio_id']);
    }));
  }

  /**
   * Número de la versión vigente del formulario (null si no tiene).
   *
   * @param int $formId
   * @return int|null
   */
  public function versionVigente($formId)
  {
    $r = $this->db->selectQuery(
      "SELECT version FROM formulario_versiones WHERE formulario_id = ? AND vigente = 1 LIMIT 1",
      array((int) $formId),
      'i'
    );
    return empty($r) ? null : (int) $r[0]['version'];
  }

  /**
   * Envíos filtrados por formulario, servicio (opcional) y rango de fechas (inclusivo).
   *
   * @param int $formId
   * @param int|null $servicioId
   * @param string $desde Y-m-d
   * @param string $hasta Y-m-d
   * @param int|null $limite
   * @param int $desplazamiento Filas a saltar (paginación).
   * @return array
   */
  public function envios($formId, $servicioId, $desde, $hasta, $limite = null, $desplazamiento = 0)
  {
    list($where, $params, $types) = $this->filtroEnvios($formId, $servicioId, $desde, $hasta);
    $sql = "SELECT e.id, e.fecha, e.version_id, v.version, " . campanas_sql_etiqueta('s', 'em') . " AS servicio,
                   e.registro, e.valores, u.user AS usuario,
                   TRIM(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))) AS operador
            FROM formulario_envios e
            JOIN formulario_versiones v ON v.id = e.version_id
            JOIN servicios s ON s.id = e.servicio_id
            LEFT JOIN empresas em ON em.id = s.empresa_id
            LEFT JOIN users u ON u.id = e.user_id
            WHERE $where
            ORDER BY e.fecha DESC, e.id DESC";
    if ($limite !== null) {
      $sql .= " LIMIT ? OFFSET ?";
      $params[] = (int) $limite;
      $params[] = max(0, (int) $desplazamiento);
      $types .= 'ii';
    }
    return $this->db->selectQuery($sql, $params, $types);
  }

  /**
   * Total de envíos con los mismos filtros.
   *
   * @return int
   */
  public function contarEnvios($formId, $servicioId, $desde, $hasta)
  {
    list($where, $params, $types) = $this->filtroEnvios($formId, $servicioId, $desde, $hasta);
    $r = $this->db->selectQuery("SELECT COUNT(*) AS n FROM formulario_envios e WHERE $where", $params, $types);
    return (int) $r[0]['n'];
  }

  /**
   * @return array [string $where, array $params, string $types]
   */
  private function filtroEnvios($formId, $servicioId, $desde, $hasta)
  {
    $where = "e.formulario_id = ? AND e.fecha >= ? AND e.fecha < DATE_ADD(?, INTERVAL 1 DAY)";
    $params = array((int) $formId, $desde, $hasta);
    $types = 'iss';
    if ($servicioId !== null) {
      $where .= " AND e.servicio_id = ?";
      $params[] = (int) $servicioId;
      $types .= 'i';
    }
    return array($where, $params, $types);
  }

  /**
   * Definiciones de todas las versiones, de la más reciente a la más antigua.
   *
   * @param int $formId
   * @return array[]
   */
  public function definicionesDeFormulario($formId)
  {
    $rows = $this->db->selectQuery(
      "SELECT definicion FROM formulario_versiones WHERE formulario_id = ? ORDER BY version DESC",
      array((int) $formId),
      'i'
    );
    return array_map(function ($r) {
      $d = json_decode($r['definicion'], true);
      return is_array($d) ? $d : array();
    }, $rows);
  }

  /**
   * Definiciones de card de todas las cargas, de la más reciente a la más antigua.
   *
   * @param int $formId
   * @return array[]
   */
  public function mappingsDeFormulario($formId)
  {
    $rows = $this->db->selectQuery(
      "SELECT mapping FROM formulario_cargas WHERE formulario_id = ? ORDER BY fecha DESC, id DESC",
      array((int) $formId),
      'i'
    );
    return array_map(function ($r) {
      $d = json_decode($r['mapping'], true);
      return is_array($d) ? $d : array();
    }, $rows);
  }
}
