<script type="text/javascript">
    function validateDate(){
        if(document.getElementById("date").value == ""){
            alert("Debe seleccionar una fecha para continuar.");
            return false;
        }else{
            return true;
        }
    }
</script>
    <div class="app-main__outer">
        <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-phone icon-gradient bg-premium-dark"></i>
                        </div>
                        <div>
                            Grabaciones
                            <div class="page-title-subheading">
                                Sistema para la descarga de llamadas
                            </div>
                        </div>
                    </div>    
                </div>
            </div>
            
            <?php if($_SESSION['cod_serv'] == 1){$directorio = "/llamadas/hbl/";}elseif($_SESSION['cod_serv'] == 2){$directorio = "llamadas/cmm/";}else{$directorio = "llamadas/amex/";}?>

            <div class="main-card mb-3 card">
                <div class="card-body">
                    <h5 class="card-title">Seleccione la fecha a consultar</h5>
                    <div>
                        <form name="form1" id="form1" method="POST" action="?view=llamadas&mode=result" class="form-inline">
                            <div class="mb-2 mr-sm-2 mb-sm-0 position-relative form-group">
                                <label for="exampleEmail22" class="mr-sm-2">Fecha</label>
                                <input type="date" class="form-control" id="date" name="date" max="<?=date('Y-m-d');?>" value="<?=$_POST['date'];?>">                                
                            </div>
                            <div class="mb-2 mr-sm-2 mb-sm-0 position-relative form-group">
                                <button class="btn btn-primary" id="btn-buscar" onclick="return validateDate()">Buscar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if($date === 0){}else{?>
                <div class="main-card mb-3 card">
                    <div class="card-body">
                        <div class="scroll-area-md">
                            <div class="scrollbar-container">
                                <h5 class="card-title">Grabaciones de la fecha <strong class="text-info"><?=$_POST['date'];?></strong></h5>
                                <div id="result">
                                    <?php
                                        if ($handle = opendir($directorio.$date)){
                                            while ($entry = readdir($handle)) {
                                                if($entry == "." || $entry == "..") {
                                                }
                                                else{
                                                    echo '<div class="mb-2 mr-sm-2 mb-sm-0 position-relative form-group"><div class="form-inline"><ol><a href="'.$directorio.$date.$entry.'" class="mr-2 btn btn-primary form-group" target="_blank">'.$entry.'</a></ol></div></div>';
                                                }
                                            }
                                        }
                                        else{
                                            echo '<h5 align="center" class="card-subtitle">Esta fecha no posee grabaciones registradas</h5>';
                                        }
                                        closedir($handle);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>    
    </div>

</div>