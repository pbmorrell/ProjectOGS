<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    
    $dataAccess = new DataAccess();
    $logger = new Logger($dataAccess);
    $securityHandler = new SecurityHandler();

    // Get login credentials
    try {
        $userName = filter_var($_POST['loginUsername'], FILTER_SANITIZE_STRING);
        $password = filter_var($_POST['loginPassword'], FILTER_SANITIZE_STRING);
    }
    catch(Exception $e) {
        $logger->LogError("Could not retrieve login credentials from POST. Exception: " . $e->getMessage());
        echo "Error: Could Not Log In To Home Page. Please Try Again Later.";
        exit();
    }
    
    // Validate username and hashed password against database
    if((empty($userName)) || (empty($password)) || 
       ($securityHandler->AuthenticateUser($dataAccess, $logger, $userName, $password) < 0)) {
        // Tell Ajax script to redirect user to Login page if they're not authenticated, 
        // or no valid login credentials provided
        echo "Invalid Login Credentials";
    }
    else {
        // Create session
        $sessionHandler = new DBSessionHandler($dataAccess, $logger);
        session_set_save_handler($sessionHandler, true);
        session_start();

        // Set session variables
        $_SESSION['userID'] = $securityHandler->userId;
        $_SESSION['userSecurityLevel'] = $securityHandler->userSecurityLevel;
	$_SESSION['userName'] = $userName;

        echo "true";
    }
?>