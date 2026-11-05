<?php

/**
 * Acceso a la base de datos.
 *
 * Envuelve mysqli y expone consultas preparadas. Los errores del motor nunca
 * se muestran al cliente: se registran en el log y se lanza una excepción con
 * un mensaje genérico.
 */
class Conexion extends mysqli
{
  /**
   * @throws RuntimeException Si no se puede conectar con la base de datos.
   */
  public function __construct()
  {
    try {
      parent::__construct('p:' . DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    } catch (Throwable $e) {
      app_log('error', 'Error de conexión a la base de datos: ' . $e->getMessage(), array('event' => 'db.connection_failed'));
      throw new RuntimeException('No se pudo conectar con la base de datos.');
    }

    if ($this->connect_errno) {
      app_log('error', 'Error de conexión a la base de datos: ' . $this->connect_error, array('event' => 'db.connection_failed'));
      throw new RuntimeException('No se pudo conectar con la base de datos.');
    }

    $this->set_charset('utf8mb4');
  }

  /**
   * @param mysqli_result $result
   * @return int Número de filas del resultado.
   */
  public function rows($result)
  {
    if ($result instanceof mysqli_result) {
      return $result->num_rows;
    }

    return 0;
  }

  /**
   * @param mysqli_result $query
   * @return void
   */
  public function liberar($query)
  {
    mysqli_free_result($query);
  }

  /**
   * @param mysqli_result $query
   * @return array|null Fila actual o null si no hay más resultados.
   */
  public function recorrer($query)
  {
    $fila = mysqli_fetch_array($query);

    return ($fila === false) ? null : $fila;
  }

  /**
   * Prepara y ejecuta una consulta con placeholders.
   *
   * @param string $query Consulta SQL con marcadores '?'.
   * @param array $params Valores a vincular.
   * @param string $types Cadena de tipos de mysqli ('s', 'i', 'd', ...).
   * @return mysqli_stmt
   * @throws RuntimeException Si la consulta falla.
   */
  public function preparedQuery($query, array $params = array(), $types = '')
  {
    $stmt = $this->prepare($query);

    if (!$stmt) {
      app_log('error', 'Error al preparar la consulta: ' . $this->error, array('event' => 'db.prepare_failed', 'sql' => $query));
      throw new RuntimeException('Error al preparar la consulta.');
    }

    if (!empty($params) && $types !== '') {
      $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
      app_log('error', 'Error al ejecutar la consulta: ' . $stmt->error, array('event' => 'db.execute_failed', 'sql' => $query));
      throw new RuntimeException('Error al ejecutar la consulta.');
    }

    return $stmt;
  }

  /**
   * Ejecuta una consulta y devuelve todas las filas.
   *
   * @param string $query Consulta SQL con marcadores '?'.
   * @param array $params Valores a vincular.
   * @param string $types Cadena de tipos de mysqli ('s', 'i', 'd', ...).
   * @return array Lista de filas asociativas.
   * @throws RuntimeException Si la consulta falla.
   */
  public function selectQuery($query, array $params = array(), $types = '')
  {
    $stmt = $this->preparedQuery($query, $params, $types);
    $result = $stmt->get_result();
    $data = array();

    if ($result instanceof mysqli_result) {
      while ($row = $result->fetch_assoc()) {
        $data[] = $row;
      }
    }

    $stmt->close();

    return $data;
  }

  /**
   * Construye y devuelve una cadena de consulta SQL con los valores incrustados para depuración.
   *
   * @param string $query El string de la consulta con placeholders '?'.
   * @param array $params El array de parámetros que serán vinculados.
   * @param mysqli $db La instancia de la conexión a la base de datos para escapar strings.
   * @return string La consulta SQL completa y lista para ser copiada y probada en un cliente SQL.
   */
  public function construirQueryDebug($query, array $params, $db)
  {
    // Reemplaza cada '?' con el valor correspondiente del array de parámetros.
    $partesQuery = explode('?', $query);
    $queryDebug = '';

    foreach ($params as $i => $param) {
      // Escapa el parámetro para que sea seguro en la consulta y lo rodea de comillas si es un string.
      $valor = "'" . $db->real_escape_string($param) . "'";
      $queryDebug .= $partesQuery[$i] . $valor;
    }
    // Agrega la última parte de la consulta que no tiene un '?' después.
    $queryDebug .= $partesQuery[count($params)];

    return $queryDebug;
  }
}
