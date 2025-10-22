<?php

class Conexion extends mysqli
{

  public function __construct()
  {
    parent::__construct('p:'. DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($this->connect_errno) {
      throw new Exception('Error en la conexión a la base de datos: ' . $this->connect_error);
    }

    $this->set_charset("utf8mb4");
  }

  public function rows($result)
  {
    if ($result instanceof mysqli_result) {
      return $result->num_rows;
    }
    return 0;
  }

  public function liberar($query)
  {
    return mysqli_free_result($query);
  }

  public function recorrer($query)
  {
    return mysqli_fetch_array($query);
  }

  public function preparedQuery($query, $params = [], $types = '')
  {
    $stmt = $this->prepare($query);

    if (!$stmt) {
      throw new Exception("Error en la preparación de la consulta: " . $this->error);
    }

    if (!empty($params) && $types) {
      $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
      throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    return $stmt;
  }

  public function selectQuery($query, $params = [], $types = '')
  {
    $stmt = $this->preparedQuery($query, $params, $types);
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
      $data[] = $row;
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
  function construirQueryDebug($query, $params, $db)
  {
    // Reemplaza cada '?' con el valor correspondiente del array de parámetros.
    $partesQuery = explode('?', $query);
    $queryDebug = '';

    foreach ($params as $i => $param) {
      // Escapa el parámetro para que sea seguro en la consulta y lo rodea con comillas si es un string.
      $valor = "'" . $db->real_escape_string($param) . "'";
      $queryDebug .= $partesQuery[$i] . $valor;
    }
    // Agrega la última parte de la consulta que no tiene un '?' después.
    $queryDebug .= $partesQuery[count($params)];

    return $queryDebug;
  }
}
