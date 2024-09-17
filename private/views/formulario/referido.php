<script>
  function mayus(e) {
    e.value = e.value.toUpperCase();
  }

  function solonumeros(e){
    key=e.keyCode || e.which;
    teclado=String.fromCharCode(key).toLowerCase();
    numero="0123456789";
    if(numero.indexOf(teclado)==-1){
      alert('Solo se permiten números.')
      return false;
    }
  }

  function sololetras(e) {
    key=e.keyCode || e.which;
    teclado=String.fromCharCode(key).toLowerCase();
    letras="qwertyuiopasdfghjklñzxcvbnm";

    especiales="32";

    if(letras.indexOf(teclado)==-1 && especiales!=key){
      alert("Solo se permite letras.")
      return false;
    }
  }
</script>

<div class="container">
  <div class="row">
    <div class="col-sm-offset-3 col-sm-6 col-md-offset-3 col-md-6 col-lg-offset-3 col-lg-6" id="formularioCliente">
      <section class="container">
        <header><h1><h1></header>
      </section>
      <div class="panel panel-default">
        <div class="panel-body">
          <form name="form1" enctype="multipart/form-data" method="POST" onsubmit="return validaform(this);" action="?view=formulario&mode=registroreferido" autocomplete="off">        
          <div id="formulario">
            <section>
              <h2><span class="form-group-addon"><strong>Datos del referido</strong></span></h2>
            </section>

            <div class="form-group">
              <span class="form-group-addon">Nombre del cliente</span>
              <input type="text" class="form-control" onkeypress="return sololetras(event)" placeholder="Carolina" aria-describedby="nombre" name="nombre" id="nombre" onkeyup="mayus(this);"  />
            </div>

            <div class="form-group">
              <span class="form-group-addon">Apellido del cliente</span>
              <input type="text" class="form-control" onkeypress="return sololetras(event)" placeholder="Perez" aria-describedby="apellido" name="apellido" id="apellido" onkeyup="mayus(this);"  />
            </div>
            
            <div class="form-group">
              <span class="form-group-addon">Cedula del cliente</span>
              <input type="text" class="form-control" onkeypress="return solonumeros(event)" placeholder="12456345" aria-describedby="cedula" name="cedula" id="cedula" maxlength="8"/>
            </div>

            <div class="form-group">
              <span class="form-group-addon">Genero del cliente</span>
              <select class="selectpicker show-menu-arrow show-tick form-control" name="gender" id="gender">
                   <option value='' disabled selected style='display:none;'>Seleccione...</option>
                   <option value='F' style='display:enable;'>Femenino</option>
                   <option value='M' style='display:enable;'>Masculino</option>
              </select>
            </div>

            <div class="form-group">
              <span class="form-group-addon">Teléfono Habitación</span>
              <input type="text" class="form-control" placeholder="(0212)345.67.89" aria-describedby="telf_hab" name="telf_hab" id="telf_hab" onchange="validaTelf(this);" maxlength="15" />
            </div>
            
            <div class="form-group">
              <span class="form-group-addon">Teléfono Oficina</span>
              <input type="text" class="form-control" placeholder="(0212)345.67.89" aria-describedby="telf_ofi" name="telf_ofi" id="telf_ofi" maxlength="15" onchange="validaTelf(this);"/>
            </div>

            <div class="form-group">
              <span class="form-group-addon">Teléfono Celular</span>
              <input type="text" class="form-control" placeholder="(0424)234.56.78" aria-describedby="telf_cel" name="telf_cel" id="telf_cel" maxlength="15" onchange="validaTelf(this);"/>
            </div>

            <div class="form-group">
              <section>
                <span class="form-group-addon">Estado</span>
              </section>
              <select class="form-control" name="estado" id="estado" />
                <option value='' style='display:enable;'>Seleccione...</option>
                <option value="NO OTORGADO" style='display:enable;'>NO OTORGADO</option>
                <?php foreach ($estado as $es) { ?>
                <option value='<?php echo $es['estado'];?>' style='display:enable;'><?php echo $es['estado'];}?></option>
              </select>
            </div>               
          </div>
          <button type="submit" class="btn btn-success btn-md">Guardar</button>
          <a href="?view=formulario&mode=index" class="btn btn-medium btn-info">Volver</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
