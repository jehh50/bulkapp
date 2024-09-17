<?php

class Conexion extends mysqli {

  public function __construct() {
    parent::__construct(DB_HOST,DB_USER,DB_PASS,DB_NAME,DB_PORT);

    if ($this->connect_errno) {
      throw new Exception('Error en la conexión a la base de datos: ' . $this->connect_error);
    }

    // $this->connect_errno ? die('Error en la conexión a la base de datos') : null;
    $this->set_charset("utf8mb4");
  }

   public function rows($result) {
    // return mysqli_num_rows($query);
    if($result instanceof mysqli_result){
      return $result->num_rows;
    }
    return 0;
  }

  public function liberar($query) {
    return mysqli_free_result($query);
  }

  public function recorrer($query) {
    return mysqli_fetch_array($query);
  }

  public function preparedQuery($query, $params = [], $types = '') {
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
  


}
