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

  /** Campañas bancarias: Bancamiga, Bancaribe y Banco Activo. */
  const SERVICIO_BANCARIO = 1;

  /** Flujo de cobranza Cashea. */
  const SERVICIO_CASHEA = 2;

  /** Alias histórico del servicio bancario. */
  const SERVICIO_BANCARIO_ALIAS = 5;

  /* ---------------------------------------------------------------------
   * Tipos de usuario (usertype_id)
   * ------------------------------------------------------------------- */

  const USUARIO_AGENTE = 1;
  const USUARIO_ADMINISTRADOR = 2;
  const USUARIO_SUPERVISOR = 3;

  /* ---------------------------------------------------------------------
   * Estados del cliente y de la gestión (status_id)
   * ------------------------------------------------------------------- */

  /** Gestión no efectiva. */
  const ESTADO_NO_EFECTIVO = 3;

  /** Gestión efectiva. */
  const ESTADO_EFECTIVO = 4;

  /** Cliente atendido / sin deuda. */
  const ESTADO_ATENDIDO = 7;

  /** Compromiso de pago (Cashea). */
  const ESTADO_COMPROMISO_PAGO = 9;

  /** Pago abonado (Cashea). */
  const ESTADO_PAGO_ABONADO = 12;

  /* ---------------------------------------------------------------------
   * Otros valores de negocio
   * ------------------------------------------------------------------- */

  /** Registro activo del catálogo (productos, servicios, usuarios). */
  const REGISTRO_ACTIVO = 2;

  /** Registro dado de baja. */
  const REGISTRO_INACTIVO = 1;

  /** Subcontactos de Cashea que admiten compromiso o abono de pago. */
  const SUBCONTACTO_CON_PAGO = array(1, 10, 20, 21);

  /** Plan de pago "pago personalizado" en Cashea. */
  const PLAN_PAGO_PERSONALIZADO = 3;

  /**
   * Lista de servicios válidos.
   *
   * @return array
   */
  public static function servicios()
  {
    return array(self::SERVICIO_BANCARIO, self::SERVICIO_CASHEA, self::SERVICIO_BANCARIO_ALIAS);
  }

  /**
   * Lista de tipos de usuario válidos.
   *
   * @return array
   */
  public static function tiposUsuario()
  {
    return array(self::USUARIO_AGENTE, self::USUARIO_ADMINISTRADOR, self::USUARIO_SUPERVISOR);
  }

  /**
   * Normaliza el id de servicio: el alias 5 se trabaja como el servicio 1.
   *
   * @param mixed $servicioId
   * @return int
   */
  public static function servicioBase($servicioId)
  {
    $servicioId = (int) $servicioId;

    return ($servicioId === self::SERVICIO_BANCARIO_ALIAS) ? self::SERVICIO_BANCARIO : $servicioId;
  }

  /**
   * Indica si el servicio corresponde a una campaña bancaria (1 o su alias 5).
   *
   * @param mixed $servicioId
   * @return bool
   */
  public static function esServicioBancario($servicioId)
  {
    $servicioId = (int) $servicioId;

    return $servicioId === self::SERVICIO_BANCARIO || $servicioId === self::SERVICIO_BANCARIO_ALIAS;
  }

  /**
   * Indica si el servicio es Cashea.
   *
   * @param mixed $servicioId
   * @return bool
   */
  public static function esServicioCashea($servicioId)
  {
    return (int) $servicioId === self::SERVICIO_CASHEA;
  }

  /**
   * Indica si un subcontacto de Cashea admite compromiso o abono de pago.
   *
   * @param mixed $subContacto
   * @return bool
   */
  public static function admitePago($subContacto)
  {
    if ($subContacto === null || $subContacto === '') {
      return false;
    }

    return in_array((int) $subContacto, self::SUBCONTACTO_CON_PAGO, true);
  }
}
