<?php

if (session_start()){
} 
else{
	session_destroy();
	header('location:index.php');
}


?>