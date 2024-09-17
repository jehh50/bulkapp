<div class="container">
  <div class="row">
    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-12">
      <section class="container">
        <header><h1></h1></header>
      </section>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <section>
              <label><h1>Descarga de gestión <strong>CASLO USD</strong></h1></label>
            </section>
                <form name="resultados" method="POST" action="?view=csv&mode=descargar">
                    <div class="form-group">              
                      <div class="form-group col-lg-3">  
                        <label>Desde</label><input type="date" class="form-control" aria-describedby="fecha_d" value="<?=date('Y-m-d')?>" name="fecha_d" id="fecha_d" autofocus />
                      </div>
                      <div class="form-group col-lg-3">  
                        <label>Hasta</label><input type="date" class="form-control" aria-describedby="fecha_h" value="<?=date('Y-m-d')?>" name="fecha_h" id="fecha_h" autofocus />
                      </div>
                    </div>
                    <div class="form-group col-lg-12">
                        <input type="submit" class="btn btn-medium btn-success" value="Descargar" id="btn-buscar">
                    </div>
                </form> 

                <div class="table-responsive col-lg-12">
               </div>


          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>