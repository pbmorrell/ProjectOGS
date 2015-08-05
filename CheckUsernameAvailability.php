<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    
    $dataAccess = new DataAccess();
    $loggerDataAccess = new DataAccess();
    $securityHandler = new SecurityHandler();
    $logger = new Logger($loggerDataAccess);
	
    $sessionDataAccess = new DataAccess();
    $sessionHandler = new DBSessionHandler($sessionDataAccess);
    session_set_save_handler($sessionHandler, true);
    session_start();
	
    // Only proceed if this page is accessed from a logged-in user
    if(isset($_SESSION['WebUser'])) {
        $objUser = $_SESSION['WebUser'];
		session_write_close();
		
		$userName = trim($_POST['userName']);
		
		if($securityHandler->UsernameIsAvailable($dataAccess, $logger, $userName)) {
				echo "avail";
		}
		else {
			echo "taken";
		}
    }
    else {
    	echo "Unauthorized Access";
    }
?>