<?php
  // Valores del hero (cada vista puede definir estas variables antes de incluir el navbar)
  $nav_crumb   = isset($nav_crumb)   ? $nav_crumb   : 'Reportes / Acumulado de gestión';
  $nav_title   = isset($nav_title)   ? $nav_title   : 'Resumen de gestión por operador';
  $nav_accent  = isset($nav_accent)  ? $nav_accent  : null;
  $nav_subtitle = isset($nav_subtitle) ? $nav_subtitle : null;
  $nav_service = isset($nav_service) ? $nav_service : null;
  $nav_period  = isset($nav_period)  ? $nav_period  : null;
  $nav_service_label = isset($nav_service_label) ? $nav_service_label : 'Servicio consultado';
  $nav_service_sub   = isset($nav_service_sub)   ? $nav_service_sub   : null;
  $usertype    = isset($_SESSION['usertype_id']) ? $_SESSION['usertype_id'] : 0;
  $username    = isset($_SESSION['user']) ? $_SESSION['user'] : '';

  // Campañas asignadas al usuario (N:M). Las sesiones previas a la migración no las traen:
  // se cargan una vez y quedan en sesión.
  if (!isset($_SESSION['campanas']) && isset($_SESSION['id']) && function_exists('campanas_sesion_refrescar')) {
    campanas_sesion_refrescar((int) $_SESSION['id']);
  }
  $nav_campanas = isset($_SESSION['campanas']) && is_array($_SESSION['campanas']) ? $_SESSION['campanas'] : array();
  $nav_campana_id = isset($_SESSION['servicio_id']) ? (int) $_SESSION['servicio_id'] : 0;
  $nav_campana_actual = null;
  foreach ($nav_campanas as $c) {
    if ((int) $c['id'] === $nav_campana_id) { $nav_campana_actual = $c['etiqueta']; break; }
  }

  // Resalta la opción del menú según view:mode actual
  $curKey = (isset($_GET['view']) ? $_GET['view'] : '') . ':' . (isset($_GET['mode']) ? $_GET['mode'] : '');
  if (!function_exists('nav_active')) {
    function nav_active($curKey, $keys) { return in_array($curKey, $keys) ? ' active' : ''; }
  }
?>
<nav class="navbar navbar-expand-lg app-nav" data-bs-theme="dark">
  <div class="container-fluid px-lg-4">
    <a class="navbar-brand" href="?view=dashboard&mode=index">
      <span class="brand-logo">B</span>Bulksales
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Menú">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto">
        <?php if ($usertype == Constantes::USUARIO_SUPERVISOR || $usertype == Constantes::USUARIO_ADMINISTRADOR) { ?>
          <li class="nav-item">
            <a class="nav-link<?php echo nav_active($curKey, ['dashboard:index']); ?>" href="?view=dashboard&mode=index">Dashboard</a>
          </li>
        <?php } ?>

        <?php if ($usertype == Constantes::USUARIO_AGENTE || $usertype == Constantes::USUARIO_SUPERVISOR || $usertype == Constantes::USUARIO_ADMINISTRADOR) { ?>
          <li class="nav-item">
            <a class="nav-link<?php echo nav_active($curKey, ['formulario:index', 'captura:index']); ?>" href="?view=formulario&mode=index">Formulario</a>
          </li>
        <?php } ?>

        <?php if ($usertype == Constantes::USUARIO_SUPERVISOR || $usertype == Constantes::USUARIO_ADMINISTRADOR) { ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Configuración</a>
            <ul class="dropdown-menu dropdown-menu-end" data-bs-theme="light">
              <li><a class="dropdown-item" href="?view=configuracion&mode=index">Listar Productos</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['carga_archivo:index', 'carga_archivo:registro']); ?>" href="?view=carga_archivo&mode=index">Carga BBDD</a></li>
              <li><a class="dropdown-item" href="?view=configuracion&mode=editarResultado">Editar resultados</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="?view=usuarios&mode=index">Bandeja de usuarios</a></li>
              <li><a class="dropdown-item" href="?view=usuarios&mode=new">Nuevo usuario</a></li>
              <?php if ($usertype == Constantes::USUARIO_ADMINISTRADOR) { ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item<?php echo nav_active($curKey, ['empresas:index']); ?>" href="?view=empresas&mode=index">Clientes y campañas</a></li>
                <li><a class="dropdown-item<?php echo nav_active($curKey, ['constructor:index']); ?>" href="?view=constructor&mode=index">Constructor de formularios</a></li>
              <?php } ?>
            </ul>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle<?php echo (in_array(explode(':', $curKey)[0], ['reportes', 'xml']) || $curKey === 'captura:reporte') ? ' active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reportes</a>
            <ul class="dropdown-menu dropdown-menu-end" data-bs-theme="light">
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:index', 'reportes:acumulado']); ?>" href="?view=reportes&mode=index">Acumulado de gestión</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:ventas', 'reportes:ventasxestado']); ?>" href="?view=reportes&mode=ventas">Ventas por estado</a></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:detalleventas']); ?>" href="?view=reportes&mode=detalleventas">Detalle de ventas</a></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:liberar']); ?>" href="?view=reportes&mode=liberar">Liberar clientes Banca</a></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['xml:index']); ?>" href="?view=xml&mode=index">Generar XML</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['captura:reporte']); ?>" href="?view=captura&mode=reporte">Formularios dinámicos</a></li>
              <li><hr class="dropdown-divider"></li>
            </ul>
          </li>
        <?php } ?>

        <?php if (count($nav_campanas) > 1) { ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Campaña activa">
              <i class="fi fi-rr-bullhorn"></i> <?php echo htmlspecialchars($nav_campana_actual !== null ? $nav_campana_actual : 'Campaña'); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" data-bs-theme="light">
              <li><h6 class="dropdown-header">Cambiar de campaña</h6></li>
              <?php foreach ($nav_campanas as $c) { ?>
                <li>
                  <form method="post" action="?view=session&mode=campana" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(security_csrf_token(), ENT_QUOTES); ?>">
                    <input type="hidden" name="servicio_id" value="<?php echo (int) $c['id']; ?>">
                    <button type="submit" class="dropdown-item<?php echo ((int) $c['id'] === $nav_campana_id) ? ' active' : ''; ?>"><?php echo htmlspecialchars($c['etiqueta']); ?></button>
                  </form>
                </li>
              <?php } ?>
            </ul>
          </li>
        <?php } ?>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?php echo htmlspecialchars($username); ?></a>
          <ul class="dropdown-menu dropdown-menu-end" data-bs-theme="light">
            <li><a class="dropdown-item" href="?view=usuarios&mode=changepass">Cambiar contraseña</a></li>
            <li><a class="dropdown-item" href="?view=session&mode=disconect">Salir</a></li>
          </ul>
        </li>

      </ul>
    </div>
  </div>
</nav>

<header class="hero">
  <div class="hero__in">
    <div>
      <div class="hero__crumb"><?php echo htmlspecialchars($nav_crumb); ?></div>
      <h1 class="hero__title"><?php echo htmlspecialchars($nav_title); ?><?php if ($nav_accent !== null) { ?> <span class="hero__accent"><?php echo htmlspecialchars($nav_accent); ?></span><?php } ?></h1>
      <?php if ($nav_subtitle !== null) { ?><div class="hero__sub"><?php echo htmlspecialchars($nav_subtitle); ?></div><?php } ?>
    </div>
    <?php if ($nav_service !== null) { ?>
      <div class="hero__card">
        <div class="hero__dot"></div>
        <div>
          <div class="hero__lbl"><?php echo htmlspecialchars($nav_service_label); ?></div>
          <div class="hero__val"><?php echo htmlspecialchars($nav_service); ?></div>
          <?php if ($nav_service_sub !== null) { ?><div class="hero__sub mt-0"><?php echo htmlspecialchars($nav_service_sub); ?></div><?php } ?>
        </div>
        <?php if ($nav_period !== null) { ?>
          <div class="hero__sep"></div>
          <div>
            <div class="hero__lbl">Período</div>
            <div class="hero__val hero__val--sm"><?php echo htmlspecialchars($nav_period); ?></div>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
</header>

<main class="app-main">
