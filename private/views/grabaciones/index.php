<div class="container">
  <div class="row">
    <div class="col-sm-6 col-md-6 col-lg-12">
      <section class="container">
        <header><h1>Directorio de llamadas<h1></header>
      </section>
      <div class="panel panel-default">
        <div class="panel-body" >
        <?php
        $root = "public/llamadas/";
        if ($handler = opendir($root)) {
            while (false !== ($file = readdir($handler))) {
              if ($file == '.' && $file = '..') {
              }
              else{echo '<a href="?view=grabaciones&mode='.$file.'">?view=grabaciones&mode='.$file.'</a><br>'; echo $root.$file;}
            }
            closedir($handler);
        }
        ?>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
