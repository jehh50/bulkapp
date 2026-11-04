<?php

/**
 * Constantes del dominio de BulkApp.
 *
 * Reemplazan los valores mágicos que se usaban de forma literal en los
 * controladores, modelos y vistas (servicio_id, usertype_id y status_id).
 */
class Constantes
{
  /* ---------------------------------------------------------------------
   * Servicios (servicio_id)
   * ------------------------------------------------------------------- */

  /** Campaña bancaria base (Banco Activo): dueña de los catálogos de banca. */
  const SERVICIO_BANCARIO = 1;

  /** Bancaribe: campaña bancaria que comparte catálogos con la 1. */
  const SERVICIO_BANCARIO_ALIAS = 5;

  /* ---------------------------------------------------------------------
   * Tipos de flujo de una campaña (servicios.tipo_flujo)
   * ------------------------------------------------------------------- */

  /** Formulario fijo de banca (formularioController). */
  const FLUJO_BANCARIO = 'bancario';

  /** Formulario dinámico del Constructor (capturaController). */
  const FLUJO_DINAMICO = 'dinamico';

  /** @var array|null Mapa id → tipo_flujo (cacheado por petición). */
  private static $flujosCampana = null;

  /* ---------------------------------------------------------------------
   * Tipos de usuario (usertype_id)
   * ------------------------------------------------------------------- */

  /** Operador: captura de formularios. */
  const USUARIO_AGENTE = 1;

  /** Supervisor: toda la configuración operativa. */
  const USUARIO_SUPERVISOR = 2;

  /** Administrador: todo lo del Supervisor + el Constructor de formularios. */
  const USUARIO_ADMINISTRADOR = 3;

  /* ---------------------------------------------------------------------
   * Estados del cliente y de la gestión (status_id)
   * ------------------------------------------------------------------- */

  /** Gestión no efectiva. */
  const ESTADO_NO_EFECTIVO = 3;

  /** Gestión efectiva. */
  const ESTADO_EFECTIVO = 4;

  /** Cliente atendido / sin deuda. */
  const ESTADO_ATENDIDO = 7;

  /* ---------------------------------------------------------------------
   * Otros valores de negocio
   * ------------------------------------------------------------------- */

  /** Registro activo del catálogo (productos, servicios, usuarios). */
  const REGISTRO_ACTIVO = 2;

  /** Registro dado de baja. */
  const REGISTRO_INACTIVO = 1;

  /**
   * Tipos de flujo admitidos para una campaña.
   *
   * @return array
   */
  public static function flujos()
  {
    return array(self::FLUJO_BANCARIO, self::FLUJO_DINAMICO);
  }

  /**
   * Mapa histórico id → flujo: se usa si la BD no está disponible o la migración
   * de empresas/campañas (20261102) aún no se aplicó.
   *
   * @return array
   */
  public static function flujosPorDefecto()
  {
    return array(
      self::SERVICIO_BANCARIO => self::FLUJO_BANCARIO,
      self::SERVICIO_BANCARIO_ALIAS => self::FLUJO_BANCARIO,
    );
  }

  /**
   * Fija el mapa id → flujo (tests) o, con null, fuerza a releerlo de la BD.
   *
   * @param array|null $mapa
   * @return void
   */
  public static function definirFlujosCampana($mapa)
  {
    self::$flujosCampana = ($mapa === null) ? null : array_map('strval', $mapa);
  }

  /**
   * Mapa id → flujo de todas las campañas (servicios.tipo_flujo).
   *
   * @return array
   */
  public static function flujosCampana()
  {
    if (self::$flujosCampana === null) {
      $mapa = function_exists('campanas_flujos_desde_bd') ? campanas_flujos_desde_bd() : null;
      self::$flujosCampana = is_array($mapa) ? $mapa : self::flujosPorDefecto();
    }

    return self::$flujosCampana;
  }

  /**
   * Lista de servicios (campañas) válidos, de cualquier estado.
   *
   * @return array
   */
  public static function servicios()
  {
    $ids = array_map('intval', array_keys(self::flujosCampana()));
    sort($ids);

    return $ids;
  }

  /**
   * Ids de las campañas de flujo bancario.
   *
   * @return array
   */
  public static function serviciosBancarios()
  {
    $ids = array();
    foreach (self::flujosCampana() as $id => $flujo) {
      if ($flujo === self::FLUJO_BANCARIO) {
        $ids[] = (int) $id;
      }
    }
    sort($ids);

    return $ids;
  }

  /**
   * Lista de tipos de usuario válidos.
   *
   * @return array
   */
  public static function tiposUsuario()
  {
    return array(self::USUARIO_AGENTE, self::USUARIO_SUPERVISOR, self::USUARIO_ADMINISTRADOR);
  }

  /** Filas por página por defecto del reporte de formularios dinámicos. */
  const REPORTE_FILAS_POR_DEFECTO = 25;

  /** Envíos que se leen de la BD por tanda al exportar el reporte a Excel. */
  const REPORTE_TANDA_EXPORTACION = 1000;

  /**
   * Opciones de «Filas por página» del reporte de formularios dinámicos.
   *
   * @return int[]
   */
  public static function filasPorPaginaReporte()
  {
    return array(10, 25, 50, 100);
  }

  /**
   * Tipos de campo admitidos por el Constructor de formularios dinámicos.
   *
   * @return array
   */
  public static function tiposCampoFormulario()
  {
    return array('text', 'textarea', 'number', 'date', 'email', 'select', 'nested');
  }

  /**
   * Normaliza el id de servicio para los catálogos: toda campaña bancaria usa los de la 1
   * (contacto efectivo/no efectivo, estados, ciudades). Las demás se quedan con su id.
   *
   * @param mixed $servicioId
   * @return int
   */
  public static function servicioBase($servicioId)
  {
    $servicioId = (int) $servicioId;

    return self::esServicioBancario($servicioId) ? self::SERVICIO_BANCARIO : $servicioId;
  }

  /**
   * Indica si la campaña usa el flujo bancario (servicios.tipo_flujo = 'bancario').
   *
   * @param mixed $servicioId
   * @return bool
   */
  public static function esServicioBancario($servicioId)
  {
    $mapa = self::flujosCampana();
    $servicioId = (int) $servicioId;

    return isset($mapa[$servicioId]) && $mapa[$servicioId] === self::FLUJO_BANCARIO;
  }

}
