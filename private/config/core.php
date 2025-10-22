<?php

date_default_timezone_set('America/Caracas');

#Constantes de conexión a BDD

define('DB_HOST','crmapp-db.cihms2oq8bxx.us-east-1.rds.amazonaws.com');
define('DB_USER','admin');
define('DB_NAME','ddremwdt_crm');
define('DB_PORT','3306');
define('DB_PASS','C0l0mb1401+');

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