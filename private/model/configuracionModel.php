<?php
class database
{
  private $db;
  private $id;
  private $nombre;

  public function __construct()
  {
    $this->db = new Conexion();
  }

  public function updateStatus(int $servicio): bool
  {
    $this->db->preparedQuery("UPDATE productos SET status_id = ? WHERE status_id = ? AND servicio_id = ?", [Constantes::REGISTRO_INACTIVO, Constantes::REGISTRO_ACTIVO, $servicio], 'sss');
    return true;
  }

  public function guardarProductos(string $producto, string $codigo, string $costo, int $servicio, ?string $plan): bool
  {
    $date = date('Y-m-d');
    $this->db->preparedQuery(
      "INSERT INTO productos (descripcion, codigo_producto, costo_prod, status_id, servicio_id,fecha,codplan) VALUES (?, ?, ?, ?, ?, ?, ?)",
      [$producto, $codigo, $costo, Constantes::REGISTRO_ACTIVO, $servicio, $date, $plan], 'sssssss'
    );
    return true;
  }

  public function servicio(): array
  {
    return $this->db->selectQuery("SELECT * FROM servicios WHERE status_id = ? ORDER BY descripcion", [Constantes::REGISTRO_ACTIVO], 'i');
  }

  public function registro(string $identificacion, string $nombre_legal, string $telf_hab, string $telf_ofi, string $telf_cel, string $correo, string $direccion, string $cuenta, int $servicio_id, ?string $oferta): bool
  {
    $query = ("INSERT INTO clientes (identificacion, nombre_legal, telf_hab, telf_ofi, telf_cel, correo, direccion, cuenta, oferta, status_id,servicio_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $params = [$identificacion, $nombre_legal, $telf_hab, $telf_ofi, $telf_cel, $correo, $direccion, $cuenta, $oferta, 3, $servicio_id];
    // Tipos de datos:
    // 's' -> string, 'i' -> integer, 'd' -> double (float)
    $paramTypes = 'ssssssssiis';

    try {
      $stmt = $this->db->preparedQuery($query, $params, $paramTypes);
      $stmt->close();
      return true;
      // echo "Success";
    } catch (Exception $e) {
      app_log('error', 'Error al guardar: ' . $e->getMessage());
      return false;
    }
  }

  public function busquedaResultados(string $cedula): array
  {
    return $this->db->selectQuery("SELECT a.id as resultado_id, a.gestion_id, a.nombre, a.apellido, a.genero, a.fecha_nacimiento, a.cedula, a.telf_hab, a.telf_ofi, a.telf_celular, a.correo, a.cuenta, b.descripcion, b.id as servicio_id, a.producto_id, p.descripcion as name_product, a.fecha_venta FROM resultados a INNER JOIN servicios b  ON a.servicio_id = b.id INNER JOIN productos p ON a.producto_id = p.id WHERE a.cedula = ?", [$cedula], 's');
  }

  public function productos(int $id): array
  {
    return $this->db->selectQuery("SELECT id, descripcion FROM productos WHERE servicio_id = ? AND status_id = ?", [$id, Constantes::REGISTRO_ACTIVO], 'si');
  }

  public function updateResultados(int $id, string $nombre, string $apellido, string $cedula, string $genero, string $fecha_nac, ?string $hab, ?string $cel, ?string $correo, ?string $venta, ?int $productId, ?string $cuenta): bool
  {
    $this->db->preparedQuery(
      "UPDATE resultados SET nombre=?, apellido=?, genero=?, fecha_nacimiento=?, cedula=?, telf_hab=?, telf_celular=?, correo=?, fecha_venta=?, producto_id=?, cuenta=? WHERE id=?",
      [$nombre, $apellido, $genero, $fecha_nac, $cedula, $hab, $cel, $correo, $venta, $productId, $cuenta, $id],
      'ssssssssssss'
    );
    return true;
  }

  public function eliminarVenta(int $resultadoId): bool
  {
    try {
      $query = "DELETE FROM resultados WHERE id = ?";
      $params = [$resultadoId];
      $paramTypes = 'i';
      $stmt = $this->db->preparedQuery($query, $params, $paramTypes);
      $stmt->close();
      return true;
    } catch (Exception $e) {
      app_log('error', 'Error al eliminar resultado con ID ' . $resultadoId . ': ' . $e->getMessage());
      return false;
    }
  }

  public function contactoEfectivo(): array
  {
    return $this->db->selectQuery("SELECT * FROM efectivo");
  }

  public function ventaProducto(int $servicio): array
  {
    return $this->db->selectQuery("SELECT * FROM productos WHERE id_producto != 1 AND status=1 AND id_servicio = ?", [$servicio], 's');
  }

  public function estado(string $var): array
  {
    return $this->db->selectQuery("SELECT * FROM estado WHERE cod_servicio = ?", [$var], 's');
  }

  public function ciudad(int $var): array
  {
    return $this->db->selectQuery("SELECT * FROM ciudad where id_estado = ?", [$var], 's');
  }

  public function ciudad_(int $var): array
  {
    return $this->db->selectQuery("SELECT * FROM ciudad WHERE id_estado = ? ORDER BY ciudad", [$var], 's');
  }

  public function listarProductos(): array
  {
    return $this->db->selectQuery("SELECT p.id,p.descripcion,p.codigo_producto,p.costo_prod, p.fecha,p.codplan, s.descripcion as servicio FROM productos p JOIN servicios s ON p.servicio_id = s.id WHERE p.status_id = ?", [Constantes::REGISTRO_ACTIVO], 'i');
  }

  public function products(?int $id = null)
  {
    $data = $this->db->selectQuery("SELECT p.id,p.descripcion,p.codigo_producto,p.costo_prod, p.fecha,p.codplan, s.descripcion as servicio FROM productos p JOIN servicios s ON p.servicio_id = s.id WHERE p.id = ?", [$id], 's');
    return !empty($data) ? $data[0] : false;
  }

  public function updateProd(int $id, string $nombre, string $codigo, string $costo, ?string $plan = null): bool
  {
    if ($plan === null) {
      // Compatibilidad: flujo anterior sin edición de plan
      $this->db->preparedQuery("UPDATE productos SET descripcion = ?, codigo_producto = ?, costo_prod = ? WHERE id = ?", [$nombre, $codigo, $costo, $id], 'ssss');
    } else {
      $this->db->preparedQuery("UPDATE productos SET descripcion = ?, codigo_producto = ?, costo_prod = ?, codplan = ? WHERE id = ?", [$nombre, $codigo, $costo, $plan, $id], 'sssss');
    }
    return true;
  }
}
