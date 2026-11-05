<?php

/**
 * Micro-framework de pruebas para BulkApp (sin Composer).
 *
 * Cada archivo de test registra sus casos con `test('nombre', function () { ... })`
 * y el runner (`tests/run.php`) los ejecuta y devuelve el código de salida.
 */

class AssertionFailed extends Exception
{
}

class TestSuite
{
  private static $pruebas = array();
  private static $fallos = array();
  private static $total = 0;
  private static $actual = '';

  /**
   * @param string $nombre
   * @param callable $callback
   */
  public static function registrar($nombre, $callback)
  {
    self::$pruebas[] = array($nombre, $callback);
  }

  /**
   * @return int Código de salida (0 = todo ok).
   */
  public static function ejecutar()
  {
    foreach (self::$pruebas as $prueba) {
      list($nombre, $callback) = $prueba;
      self::$actual = $nombre;
      self::$total++;

      try {
        call_user_func($callback);
        echo "  ok   " . $nombre . PHP_EOL;
      } catch (AssertionFailed $e) {
        self::$fallos[] = array($nombre, $e->getMessage());
        echo "  FAIL " . $nombre . " -> " . $e->getMessage() . PHP_EOL;
      } catch (Throwable $e) {
        self::$fallos[] = array($nombre, get_class($e) . ': ' . $e->getMessage());
        echo "  ERROR " . $nombre . " -> " . get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
      }
    }

    echo PHP_EOL;
    echo 'Pruebas ejecutadas: ' . self::$total . ', fallos: ' . count(self::$fallos) . PHP_EOL;

    if (self::$fallos) {
      echo PHP_EOL . 'Detalle de los fallos:' . PHP_EOL;
      foreach (self::$fallos as $fallo) {
        echo '  - ' . $fallo[0] . ': ' . $fallo[1] . PHP_EOL;
      }
      return 1;
    }

    return 0;
  }

  /**
   * @param string $mensaje
   * @throws AssertionFailed
   */
  public static function fallar($mensaje)
  {
    throw new AssertionFailed($mensaje);
  }
}

/**
 * Registra un caso de prueba.
 *
 * @param string $nombre
 * @param callable $callback
 */
function test($nombre, $callback)
{
  TestSuite::registrar($nombre, $callback);
}

/**
 * @param mixed $esperado
 * @param mixed $actual
 * @param string $mensaje
 */
function assert_igual($esperado, $actual, $mensaje = '')
{
  if ($esperado !== $actual) {
    TestSuite::fallar(
      ($mensaje !== '' ? $mensaje . ' | ' : '')
      . 'se esperaba ' . var_export($esperado, true) . ' y se obtuvo ' . var_export($actual, true)
    );
  }
}

/**
 * @param mixed $actual
 * @param string $mensaje
 */
function assert_distinto($actual, $otro, $mensaje = '')
{
  if ($actual === $otro) {
    TestSuite::fallar(($mensaje !== '' ? $mensaje . ' | ' : '') . 'los valores no deberían ser iguales');
  }
}

/**
 * @param mixed $valor
 * @param string $mensaje
 */
function assert_verdadero($valor, $mensaje = '')
{
  if ($valor !== true) {
    TestSuite::fallar(($mensaje !== '' ? $mensaje . ' | ' : '') . 'se esperaba true y se obtuvo ' . var_export($valor, true));
  }
}

/**
 * @param mixed $valor
 * @param string $mensaje
 */
function assert_falso($valor, $mensaje = '')
{
  if ($valor !== false) {
    TestSuite::fallar(($mensaje !== '' ? $mensaje . ' | ' : '') . 'se esperaba false y se obtuvo ' . var_export($valor, true));
  }
}

/**
 * @param string $aguja
 * @param string $pajar
 * @param string $mensaje
 */
function assert_contiene($aguja, $pajar, $mensaje = '')
{
  if (strpos($pajar, $aguja) === false) {
    TestSuite::fallar(($mensaje !== '' ? $mensaje . ' | ' : '') . 'no se encontró "' . $aguja . '"');
  }
}

/**
 * @param mixed $valor
 * @param string $mensaje
 */
function assert_es_array($valor, $mensaje = '')
{
  if (!is_array($valor)) {
    TestSuite::fallar(($mensaje !== '' ? $mensaje . ' | ' : '') . 'se esperaba un arreglo y se obtuvo ' . gettype($valor));
  }
}

/**
 * @param string $clase
 * @param mixed $valor
 * @param string $mensaje
 */
function assert_instancia($clase, $valor, $mensaje = '')
{
  if (!($valor instanceof $clase)) {
    TestSuite::fallar(($mensaje !== '' ? $mensaje . ' | ' : '') . 'no es una instancia de ' . $clase);
  }
}

/**
 * @param callable $callback
 * @param string $mensaje
 */
function assert_lanza($callback, $mensaje = '')
{
  try {
    call_user_func($callback);
  } catch (Throwable $e) {
    return $e;
  }

  TestSuite::fallar(($mensaje !== '' ? $mensaje . ' | ' : '') . 'se esperaba una excepción');
  return null;
}
