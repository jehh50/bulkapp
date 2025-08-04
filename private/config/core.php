<?php

date_default_timezone_set('America/Caracas');

#Constantes de conexión a BDD

define('DB_HOST','localhost');
define('DB_USER','jehh');
define('DB_PASS','12345678');
define('DB_NAME','bulk2');
define('DB_PORT','3306');

#cPanel Credentials
#Constantes de conexion a BDD
// define('DB_HOST','localhost');
// define('DB_USER','ddremwdt_pringles');
// define('DB_PASS','g%P2Ckvm&lT&');
// define('DB_NAME','ddremwdt_crm');
// define('DB_PORT','3306');

#Constantes de la APP
define('HTML_DIR','private/views/');
define('MODEL_DIR','private/model/');
define('PUBLIC_DIR','public/');
define('APP_TITLE','BULKSALES');
define('APP_COPY','Copyright &copy; ' . date('Y',time()));
define('APP_URL','http://localhost/'); 

require('database.php');

?>
