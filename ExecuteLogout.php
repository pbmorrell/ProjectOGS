<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/User.class.php';
    
    $dataAccess = new DataAccess();
    
    $sessionHandler = new DBSessionHandler($dataAccess);
    session_set_save_handler($sessionHandler, true);
    session_start();
    
    $securityHandler = new SecurityHandler();
    $securityHandler->LogoutUser("Login.php");
?>