<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    
    $dataAccess = new DataAccess();
    $logger = new Logger($dataAccess);
    $securityHandler = new SecurityHandler();
    $userName = "";
    $password = "";

    // Get login credentials
    try {
        $userName = trim($_POST['loginUsername']);
        $password = trim($_POST['loginPassword']);
    }
    catch(Exception $e) {
        $logger->LogError("Could not retrieve login credentials from POST. Exception: " . $e->getMessage());
        echo "Error: Could Not Log In To Home Page. Please Try Again Later.";
        exit();
    }
    
    // Validate username and hashed password against database
    if((!empty($userName)) && (!empty($password))) {
        $objUser = $securityHandler->AuthenticateUser($dataAccess, $logger, $userName, $password);

        if($objUser->UserID > 0) {
            // Create session
            $sessionHandler = new DBSessionHandler($dataAccess);
            session_set_save_handler($sessionHandler, true);
            session_start();

            // Set session variables
            $_SESSION['WebUser'] = $objUser;
            $_SESSION['lastActivity'] = time();

            echo "true";
            exit();
        }
    }
    
    // Tell Ajax script to redirect user to Login page if they're not authenticated, 
    // or no valid login credentials provided
    echo "Invalid Login Credentials";
?>