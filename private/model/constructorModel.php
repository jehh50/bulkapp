<?php

/**
 * Modelo del Constructor de formularios dinámicos.
 *
 * Solo consultas preparadas (selectQuery / preparedQuery). La definición de
 * cada versión se guarda como JSON en `formulario_versiones.definicion`.
 * Regla de negocio: un solo formulario ACTIVO por servicio (se valida aquí,
 * con transacción y, si el usuario lo autoriza, reasignación).
 */
class database
{
  private $db;

  public function __construct()
  {
    $this->db = new Conexion();
  }

  /**
   * Lista los formularios no eliminados (con sus servicios y nº de campos).
   *
   * @param int|null $estado Filtra por status_id si se indica.
   * @return array
   */
  public function listarFormularios($estado = null)
  {
    $where = 'f.eliminado = 0';
    $params = array();
    $types = '';

    if ($estado !== null) {
      $where .= ' AND f.status_id = ?';
      $params[] = (int) $estado;
      $types .= 'i';
    }

    $forms = $this->db->selectQuery(
      "SELECT f.id, f.nombre, f.status_id, f.btn_texto, f.fecha, f.actualizado,
              v.version AS version_vigente, v.definicion
       FROM formularios f
       LEFT JOIN formulario_versiones v ON v.formulario_id = f.id AND v.vigente = 1
       WHERE $where
       ORDER BY f.actualizado DESC",
      $params,
      $types
    );

    foreach ($forms as &$f) {
      $f['servicios'] = $this->db->selectQuery(
        "SELECT s.id, s.descripcion
         FROM formulario_servicios fs
         JOIN servicios s ON s.id = fs.servicio_id
         WHERE fs.formulario_id = ?
         ORDER BY s.descripcion",
        array((int) $f['id']),
        'i'
      );

      $def = ($f['definicion'] !== null) ? json_decode($f['definicion'], true) : null;
      $f['num_campos'] = (is_array($def) && isset($def['campos'])) ? count($def['campos']) : 0;
      unset($f['definicion']);
    }
    unset($f);

    return $forms;
  }

  /**
   * Devuelve un formulario con su definición vigente y servicios asignados.
   *
   * @param int $id
   * @return array|null
   */
  public function obtenerFormulario($id)
  {
    $id = (int) $id;

    $rows = $this->db->selectQuery(
      "SELECT id, nombre, status_id, btn_texto, fecha, actualizado
       FROM formularios WHERE id = ? AND eliminado = 0",
      array($id),
      'i'
    );

    if (empty($rows)) {
      return null;
    }

    $form = $rows[0];

    $ver = $this->db->selectQuery(
      "SELECT id, version, definicion, nota FROM formulario_versiones
       WHERE formulario_id = ? AND vigente = 1 LIMIT 1",
      array($id),
      'i'
    );

    if (!empty($ver)) {
      $def = json_decode($ver[0]['definicion'], true);
      $form['version_id'] = (int) $ver[0]['id'];
      $form['version'] = (int) $ver[0]['version'];
      $form['definicion'] = (is_array($def) && isset($def['campos'])) ? $def : array('campos' => array());
    } else {
      $form['version_id'] = null;
      $form['version'] = 0;
      $form['definicion'] = array('campos' => array());
    }

    $serv = $this->db->selectQuery(
      "SELECT servicio_id FROM formulario_servicios WHERE formulario_id = ?",
      array($id),
      'i'
    );
    $form['servicios'] = array_map(function ($r) {
      return (int) $r['servicio_id'];
    }, $serv);

    return $form;
  }

  /**
   * Formularios ACTIVOS (distintos de $formId) que ocupan alguno de los
   * servicios indicados. Base de la regla 1-activo-por-servicio.
   *
   * @param int $formId
   * @param int[] $servicioIds
   * @return array
   */
  public function conflictosDeServicios($formId, array $servicioIds)
  {
    if (empty($servicioIds)) {
      return array();
    }

    $marcadores = implode(',', array_fill(0, count($servicioIds), '?'));
    $params = array_map('intval', $servicioIds);
    $types = str_repeat('i', count($servicioIds));

    $params[] = (int) $formId;
    $types .= 'i';
    $params[] = Constantes::REGISTRO_ACTIVO;
    $types .= 'i';

    return $this->db->selectQuery(
      "SELECT fs.servicio_id, s.descripcion AS servicio, f.id AS formulario_id, f.nombre AS formulario
       FROM formulario_servicios fs
       JOIN formularios f ON f.id = fs.formulario_id
       JOIN servicios s ON s.id = fs.servicio_id
       WHERE fs.servicio_id IN ($marcadores) AND f.id <> ? AND f.eliminado = 0 AND f.status_id = ?",
      $params,
      $types
    );
  }

  /**
   * Crea o actualiza un formulario, creando una nueva versión en cada guardado.
   *
   * @param array $datos ['id'?, 'nombre', 'status_id', 'btn_texto', 'definicion'(array normalizada),
   *                       'servicios'(int[]), 'nota'?, 'user_id'?]
   * @param bool $reasignar Si true, libera los servicios en conflicto de otros formularios.
   * @return array ['ok'=>true,'id','version']  |  ['ok'=>false,'conflictos'=>[...]]
   */
  public function guardarFormulario(array $datos, $reasignar = false)
  {
    $id        = (isset($datos['id']) && $datos['id']) ? (int) $datos['id'] : null;
    $nombre    = (string) $datos['nombre'];
    $statusId  = (int) $datos['status_id'];
    $btnTexto  = (isset($datos['btn_texto']) && $datos['btn_texto'] !== '') ? (string) $datos['btn_texto'] : 'Guardar';
    $servicios = (isset($datos['servicios']) && is_array($datos['servicios']))
      ? array_values(array_unique(array_map('intval', $datos['servicios'])))
      : array();
    $nota      = isset($datos['nota']) && $datos['nota'] !== '' ? (string) $datos['nota'] : null;
    $userId    = isset($datos['user_id']) ? (int) $datos['user_id'] : null;
    $defJson   = json_encode($datos['definicion'], JSON_UNESCAPED_UNICODE);

    // Conflicto de servicio: solo aplica cuando el formulario se guarda ACTIVO.
    if ($statusId === Constantes::REGISTRO_ACTIVO && !empty($servicios)) {
      $conflictos = $this->conflictosDeServicios($id ? $id : 0, $servicios);
      if (!empty($conflictos) && !$reasignar) {
        return array('ok' => false, 'conflictos' => $conflictos);
      }
    }

    $this->db->begin_transaction();
    try {
      if ($id === null) {
        $this->db->preparedQuery(
          "INSERT INTO formularios (nombre, status_id, btn_texto, user_id) VALUES (?,?,?,?)",
          array($nombre, $statusId, $btnTexto, $userId),
          'sisi'
        );
        $id = (int) $this->db->insert_id;
      } else {
        $this->db->preparedQuery(
          "UPDATE formularios SET nombre = ?, status_id = ?, btn_texto = ? WHERE id = ?",
          array($nombre, $statusId, $btnTexto, $id),
          'sisi'
        );
      }

      // Nueva versión (correlativa) marcada como vigente.
      $max = $this->db->selectQuery(
        "SELECT COALESCE(MAX(version),0) AS m FROM formulario_versiones WHERE formulario_id = ?",
        array($id),
        'i'
      );
      $version = (int) $max[0]['m'] + 1;

      $this->db->preparedQuery(
        "UPDATE formulario_versiones SET vigente = 0 WHERE formulario_id = ?",
        array($id),
        'i'
      );
      $this->db->preparedQuery(
        "INSERT INTO formulario_versiones (formulario_id, version, definicion, nota, vigente, user_id)
         VALUES (?,?,?,?,1,?)",
        array($id, $version, $defJson, $nota, $userId),
        'iissi'
      );

      // Reasignación: liberar los servicios de otros formularios.
      if ($reasignar && $statusId === Constantes::REGISTRO_ACTIVO && !empty($servicios)) {
        $marcadores = implode(',', array_fill(0, count($servicios), '?'));
        $params = $servicios;
        $types = str_repeat('i', count($servicios));
        $params[] = $id;
        $types .= 'i';
        $this->db->preparedQuery(
          "DELETE FROM formulario_servicios WHERE servicio_id IN ($marcadores) AND formulario_id <> ?",
          $params,
          $types
        );
      }

      // Sincronizar los servicios de este formulario.
      $this->db->preparedQuery(
        "DELETE FROM formulario_servicios WHERE formulario_id = ?",
        array($id),
        'i'
      );
      foreach ($servicios as $sid) {
        $this->db->preparedQuery(
          "INSERT INTO formulario_servicios (formulario_id, servicio_id) VALUES (?,?)",
          array($id, $sid),
          'ii'
        );
      }

      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }

    return array('ok' => true, 'id' => $id, 'version' => $version);
  }

  /**
   * Borrado lógico: conserva datos, BD e historial para reportería.
   *
   * @param int $id
   * @return bool
   */
  public function eliminarFormulario($id)
  {
    $this->db->preparedQuery(
      "UPDATE formularios SET eliminado = 1, eliminado_en = NOW(), status_id = ? WHERE id = ?",
      array(Constantes::REGISTRO_INACTIVO, (int) $id),
      'ii'
    );

    return true;
  }

  /**
   * Historial de versiones de un formulario.
   *
   * @param int $id
   * @return array
   */
  public function historial($id)
  {
    return $this->db->selectQuery(
      "SELECT id, version, nota, vigente, fecha, user_id
       FROM formulario_versiones WHERE formulario_id = ? ORDER BY version DESC",
      array((int) $id),
      'i'
    );
  }

  /**
   * Marca una versión del historial como vigente (rollback).
   *
   * @param int $formId
   * @param int $versionId
   * @return bool
   */
  public function hacerVigenteVersion($formId, $versionId)
  {
    $formId = (int) $formId;
    $versionId = (int) $versionId;

    $existe = $this->db->selectQuery(
      "SELECT id FROM formulario_versiones WHERE id = ? AND formulario_id = ?",
      array($versionId, $formId),
      'ii'
    );

    if (empty($existe)) {
      return false;
    }

    $this->db->begin_transaction();
    try {
      $this->db->preparedQuery("UPDATE formulario_versiones SET vigente = 0 WHERE formulario_id = ?", array($formId), 'i');
      $this->db->preparedQuery("UPDATE formulario_versiones SET vigente = 1 WHERE id = ?", array($versionId), 'i');
      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }

    return true;
  }

  /**
   * Servicios no bancarios (cualquier estado) que pueden recibir un formulario dinámico.
   *
   * @return array
   */
  public function serviciosDisponibles()
  {
    $servicios = $this->db->selectQuery(
      "SELECT id, descripcion, status_id FROM servicios ORDER BY descripcion",
      array(),
      ''
    );
    // Los servicios de banca usan su formulario fijo: no admiten formularios dinámicos.
    return array_values(array_filter($servicios, function ($s) {
      return !Constantes::esServicioBancario($s['id']);
    }));
  }

  /* ---------------------------------------------------------------------
   * Fase 2 — Base de datos por formulario (cargas)
   * ------------------------------------------------------------------- */

  /**
   * Persiste una carga de BD: registro de carga (vigente) + filas (JSON).
   *
   * @param int $formId
   * @param string $archivo Nombre original del archivo.
   * @param array $columnas { busqueda: k, columnas: [ {k, col, label} ] } (se guarda en `mapping`).
   * @param array[] $filas  Filas como { k => valor }.
   * @param int|null $userId
   * @return array ['carga_id'=>int, 'filas'=>int]
   */
  public function guardarCarga($formId, $archivo, array $columnas, array $filas, $userId = null)
  {
    $formId = (int) $formId;

    $this->db->begin_transaction();
    try {
      $this->db->preparedQuery(
        "UPDATE formulario_cargas SET vigente = 0 WHERE formulario_id = ?",
        array($formId),
        'i'
      );
      $this->db->preparedQuery(
        "INSERT INTO formulario_cargas (formulario_id, archivo, filas, mapping, vigente, user_id)
         VALUES (?,?,?,?,1,?)",
        array($formId, (string) $archivo, count($filas), json_encode($columnas, JSON_UNESCAPED_UNICODE), $userId !== null ? (int) $userId : null),
        'isisi'
      );
      $cargaId = (int) $this->db->insert_id;

      $kBusqueda = isset($columnas['busqueda']) ? $columnas['busqueda'] : null;
      $chunk = 500;
      $total = count($filas);
      for ($i = 0; $i < $total; $i += $chunk) {
        $slice = array_slice($filas, $i, $chunk);
        $marcadores = array();
        $params = array();
        $types = '';
        foreach ($slice as $f) {
          $marcadores[] = '(?,?,?)';
          $params[] = $cargaId;
          $params[] = ($kBusqueda !== null && isset($f[$kBusqueda])) ? mb_substr((string) $f[$kBusqueda], 0, 191, 'UTF-8') : null;
          $params[] = json_encode($f, JSON_UNESCAPED_UNICODE);
          $types .= 'iss';
        }
        $this->db->preparedQuery(
          "INSERT INTO formulario_carga_filas (carga_id, clave, fila) VALUES " . implode(',', $marcadores),
          $params,
          $types
        );
      }

      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }

    return array('carga_id' => $cargaId, 'filas' => $total);
  }

  /**
   * Historial de cargas de un formulario.
   *
   * @param int $formId
   * @return array
   */
  public function cargasDeFormulario($formId)
  {
    return $this->db->selectQuery(
      "SELECT id, archivo, filas, vigente, fecha, user_id
       FROM formulario_cargas WHERE formulario_id = ? ORDER BY fecha DESC, id DESC",
      array((int) $formId),
      'i'
    );
  }

  /**
   * Carga vigente de un formulario (o null), con la definición de la card
   * (busqueda + columnas) y una fila de muestra.
   *
   * @param int $formId
   * @return array|null
   */
  public function cargaVigente($formId)
  {
    $r = $this->db->selectQuery(
      "SELECT id, archivo, filas, fecha, mapping FROM formulario_cargas
       WHERE formulario_id = ? AND vigente = 1 LIMIT 1",
      array((int) $formId),
      'i'
    );
    if (empty($r)) {
      return null;
    }
    $carga = $r[0];
    $def = json_decode($carga['mapping'], true);
    unset($carga['mapping']);
    $carga['busqueda'] = (is_array($def) && isset($def['busqueda'])) ? $def['busqueda'] : null;
    $carga['columnas'] = (is_array($def) && isset($def['columnas'])) ? $def['columnas'] : array();
    $muestra = $this->filasDeCarga($carga['id'], 1);
    $carga['muestra'] = empty($muestra) ? null : $muestra[0];
    return $carga;
  }

  /**
   * Marca una carga como vigente (desactiva las demás del formulario).
   *
   * @param int $formId
   * @param int $cargaId
   * @return bool
   */
  public function hacerVigenteCarga($formId, $cargaId)
  {
    $formId = (int) $formId;
    $cargaId = (int) $cargaId;

    $existe = $this->db->selectQuery(
      "SELECT id FROM formulario_cargas WHERE id = ? AND formulario_id = ?",
      array($cargaId, $formId),
      'ii'
    );
    if (empty($existe)) {
      return false;
    }

    $this->db->begin_transaction();
    try {
      $this->db->preparedQuery("UPDATE formulario_cargas SET vigente = 0 WHERE formulario_id = ?", array($formId), 'i');
      $this->db->preparedQuery("UPDATE formulario_cargas SET vigente = 1 WHERE id = ?", array($cargaId), 'i');
      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }

    return true;
  }

  /**
   * Muestra de filas de una carga (decodificadas).
   *
   * @param int $cargaId
   * @param int $limite
   * @return array[]  Lista de { clave_k => valor }.
   */
  public function filasDeCarga($cargaId, $limite = 10)
  {
    $rows = $this->db->selectQuery(
      "SELECT fila FROM formulario_carga_filas WHERE carga_id = ? ORDER BY id LIMIT ?",
      array((int) $cargaId, (int) $limite),
      'ii'
    );
    return array_map(function ($r) {
      $d = json_decode($r['fila'], true);
      return is_array($d) ? $d : array();
    }, $rows);
  }
}
