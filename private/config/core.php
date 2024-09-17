<?php

date_default_timezone_set('America/Caracas');

#Constantes de conexión a BDD
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','bulk2');
define('DB_PORT','3306');

#Constantes de conexión a BDD ISSABEL
// define('DB_AHOST','192.168.5.100');
// define('DB_AUSER','bulksales');
// define('DB_APASS','123456');
// define('DB_ANAME','call_center');
// define('DB_APORT','3306');


#Constantes de la APP
define('HTML_DIR','private/views/');
define('MODEL_DIR','private/model/');
define('PUBLIC_DIR','public/');
define('APP_TITLE','BULKSALES');
define('APP_COPY','Copyright &copy; ' . date('Y',time()));
define('APP_URL','http://localhost/'); 

require('database.php');

?>