<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="principal">
    <meta name="author" content="jh">

    <title><?php echo APP_TITLE; ?></title>

    <?php echo security_csrf_meta(); ?>
    <script src="<?php echo asset('public/js/csrf.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo asset('public/css/bootstrap.min.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('public/css/bootstrap-icons.min.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('public/css/estilos.css'); ?>" type="text/css">
    <link href="public/images/icon.png" rel="icon" type="image/png"/>
    <link href="<?php echo asset('public/css/dashboard.css'); ?>" rel="stylesheet">
    <script src="<?php echo asset('public/js/jquery-3.7.1.min.js'); ?>"></script>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet">

</head>
