<?php
  // Valores del hero (cada vista puede definir estas variables antes de incluir el navbar)
  $nav_crumb   = isset($nav_crumb)   ? $nav_crumb   : 'Reportes / Acumulado de gestión';
  $nav_title   = isset($nav_title)   ? $nav_title   : 'Resumen de gestión por operador';
  $nav_accent  = isset($nav_accent)  ? $nav_accent  : null;
  $nav_subtitle = isset($nav_subtitle) ? $nav_subtitle : null;
  $nav_service = isset($nav_service) ? $nav_service : null;
  $nav_period  = isset($nav_period)  ? $nav_period  : null;
  $usertype    = isset($_SESSION['usertype_id']) ? $_SESSION['usertype_id'] : 0;
  $username    = isset($_SESSION['user']) ? $_SESSION['user'] : '';

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
            <a class="nav-link<?php echo nav_active($curKey, ['formulario:index']); ?>" href="?view=formulario&mode=index">Formulario</a>
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
                <li><a class="dropdown-item<?php echo nav_active($curKey, ['constructor:index']); ?>" href="?view=constructor&mode=index">Constructor de formularios</a></li>
              <?php } ?>
            </ul>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle<?php echo in_array(explode(':', $curKey)[0], ['reportes', 'xml']) ? ' active' : ''; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reportes</a>
            <ul class="dropdown-menu dropdown-menu-end" data-bs-theme="light">
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:index', 'reportes:acumulado']); ?>" href="?view=reportes&mode=index">Acumulado de gestión</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:ventas', 'reportes:ventasxestado']); ?>" href="?view=reportes&mode=ventas">Ventas por estado</a></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:detalleventas']); ?>" href="?view=reportes&mode=detalleventas">Detalle de ventas</a></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['reportes:liberar']); ?>" href="?view=reportes&mode=liberar">Liberar clientes Banca</a></li>
              <li><a class="dropdown-item<?php echo nav_active($curKey, ['xml:index']); ?>" href="?view=xml&mode=index">Generar XML</a></li>
              <li><hr class="dropdown-divider"></li>
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
          <div class="hero__lbl">Servicio consultado</div>
          <div class="hero__val"><?php echo htmlspecialchars($nav_service); ?></div>
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
