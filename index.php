<?php
require('private/config/core.php');
#session_destroy();

if(isset($_GET['view'])) {
  if(file_exists('private/controllers/' . strtolower($_GET['view']) . 'Controller.php')) {
    include('private/controllers/' . strtolower($_GET['view']) . 'Controller.php');
	}
}
  else{
  	include('private/controllers/sessionController.php');
  }
?>