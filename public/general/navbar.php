<body style="background-color: #e9e9e9 !important">
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Menú">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbar">
        <ul class="navbar-nav ms-auto">
          <?php if (in_array((int) $_SESSION['usertype_id'], array(Constantes::USUARIO_AGENTE, Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR), true)) { ?>
            <li class="nav-item"><a class="nav-link" href="?view=formulario&mode=index">Formulario</a></li>
          <?php } ?>
          <?php if (in_array((int) $_SESSION['usertype_id'], array(Constantes::USUARIO_SUPERVISOR, Constantes::USUARIO_ADMINISTRADOR), true)) { ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="menuConfiguracion" role="button" data-bs-toggle="dropdown" aria-expanded="false">Configuración</a>
              <ul class="dropdown-menu" aria-labelledby="menuConfiguracion">
                <li><a class="dropdown-item" href="?view=configuracion&mode=index">Listar Productos</a></li>
                <li><a class="dropdown-item" href="?view=configuracion&mode=addProduct">Agregar Productos</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?view=carga_archivo&mode=index">Carga BBDD</a></li>
                <li><a class="dropdown-item" href="?view=configuracion&mode=editarResultado">Editar resultados</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?view=usuarios&mode=index">Bandeja de usuarios</a></li>
                <li><a class="dropdown-item" href="?view=usuarios&mode=new">Nuevo usuario</a></li>
                <?php if ((int) $_SESSION['usertype_id'] === Constantes::USUARIO_ADMINISTRADOR) { ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="?view=constructor&mode=index">Constructor de formularios</a></li>
                <?php } ?>
              </ul>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="menuReportes" role="button" data-bs-toggle="dropdown" aria-expanded="false">Reportes</a>
              <ul class="dropdown-menu" aria-labelledby="menuReportes">
                <li><a class="dropdown-item" href="?view=reportes&mode=index">Acumulado de la gestión</a></li>
                <li><a class="dropdown-item" href="?view=reportes&mode=contactabilidad">Motivos de contacto</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="?view=reportes&mode=ventas">Ventas por estado</a></li>
                <li><a class="dropdown-item" href="?view=reportes&mode=detalleventas">Detalle de ventas</a></li>
                <li><a class="dropdown-item" href="?view=reportes&mode=liberar">Liberar clientes Banca</a></li>
                <li><a class="dropdown-item" href="?view=xml&mode=index">Generar XML</a></li>
              </ul>
            </li>
          <?php } ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="menuUsuario" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?= htmlspecialchars((string) $_SESSION['user'], ENT_QUOTES, 'UTF-8') ?></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuUsuario">
              <li><a class="dropdown-item" href="?view=usuarios&mode=changepass">Cambiar contraseña</a></li>
              <li><a class="dropdown-item" href="?view=session&mode=disconect">Salir</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>
