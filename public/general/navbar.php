<body style="background-color: #252525 !important">
  <nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <!--iframe src="public/iframe/index.php" id="iframe" name="iframe" style="width:565px;height:50px;margin-top:5px;" frameborder="0" allowtransparency="true"></iframe-->
      </div>
      <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav navbar-right">

            <?php if($_SESSION['usertype_id'] == 1 || $_SESSION['usertype_id'] == 2){?>
              <!-- <li><a href="" data-toggle="modal" data-target="#staticBackdrop">Acumulado</a></li> -->
              <li><a href="?view=formulario&mode=index">Formulario</a></li>
            <?php } if($_SESSION['usertype_id'] == 2){?>   
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Configuración <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="?view=configuracion&mode=agregarProducto">Agregar Productos</a></li>
                  <li><a href="?view=configuracion&mode=consultarProducto">Listar Productos</a></li>
                  <li><a href="?view=carga_archivo&mode=index">Carga BBDD</a></li>
                  <!-- <li><a href="?view=configuracion&mode=cargaArchivo">Carga BBDD</a></li> -->
                  <li><a href="?view=configuracion&mode=editarResultado">Editar resultados</a></li>
                  <li><a href="?view=usuarios&mode=index">Bandeja de usuarios</a></li>
                  <li><a href="?view=usuarios&mode=new">Nuevo usuario</a></li>
                </ul>
              </li>
            <?php } ?>
            <?php if($_SESSION['usertype_id'] == 2){?>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reportes <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="?view=reportes&mode=index">Acumulado de la gestión</a></li>
                  <li><a href="?view=reportes&mode=contactabilidad">Motivos de contacto</a></li>
                  <li><a href="?view=reportes&mode=ventas">Ventas por estado</a></li>
                  <li><a href="?view=reportes&mode=detalleventas">Detalle de ventas</a></li>
                  <li><a href="?view=reportes&mode=gestionCashea">Gestión Cashea</a></li>
                  <li><a href="?view=reportes&mode=liberar">Liberar cliente</a></li>
                  <li><a href="?view=xml&mode=index">XML</a></li>
                </ul>
              </li>
            <?php } ?>
            <?php if($_SESSION['usertype_id'] == 4){?>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reportes <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="?view=reportes&mode=index">Acumulado de la gestión</a></li>
                  <li><a href="?view=reportes&mode=contactabilidad">Motivos de contacto</a></li>
                  <li><a href="?view=reportes&mode=ventas">Ventas por estado</a></li>
                  <li><a href="?view=reportes&mode=detallada">Gestión detallada</a></li>
                  <!-- <li><a href="?view=reportes&mode=gestionCashea">Gestión Cashea</a></li> -->
                </ul>
              </li>
            <?php } ?>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <?= $_SESSION['user'] ?> <span class="glyphicon glyphicon-user"></span> <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="?view=usuarios&mode=changepass">Cambiar contraseña</a></li>
                <li><a href="?view=session&mode=disconect">Salir</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
