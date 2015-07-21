<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    
    $dataAccess = new DataAccess();
    $logger = new Logger($dataAccess);
    $securityHandler = new SecurityHandler();
    $userName = "";
    $userEmail = "";
    $encryptedPassword = "";
    
    // Get login credentials
    try {
        $userName = filter_var($_POST["signupUsername"], FILTER_SANITIZE_STRING);
        $encryptedPassword = $securityHandler->EncryptPassword(filter_var($_POST["signupPW"], FILTER_SANITIZE_STRING));
        $userEmail = filter_var($_POST["signupEmail"], FILTER_SANITIZE_STRING);
    }
    catch(Exception $e) {
        $logger->LogError("Could not retrieve signup credentials from POST. Exception: " . $e->getMessage());
        echo "Error: Could Not create Basic Account. Please Try Again Later.";
        exit();
    }
    
    // Get security level for "Basic" users
    $basicRoleId = -1;
    $basicSecLevel = -1;
    $secLevelQuery = "select `ID`, `SecurityLevel` from `Security.Roles` WHERE `Name` = 'BasicMember'";
	
    $errors = $dataAccess->CheckErrors();

    if(strlen($errors) == 0) {
        if($dataAccess->BuildQuery($secLevelQuery)){
            $results = $dataAccess->GetSingleResult();
            if($results != null){
                $basicRoleId = $results['ID'];
		$basicSecLevel = $results['SecurityLevel'];
            }
	}
		
	$errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            // Create account
            $createdUserId = $securityHandler->InsertBasicUser($dataAccess, $logger, $userName, $encryptedPassword, $userEmail, $basicRoleId);

            if($createdUserId > 0) {
                // Create session
                $sessionHandler = new DBSessionHandler($dataAccess, $logger);
                session_set_save_handler($sessionHandler, true);
                session_start();

                // Set session variables
                $_SESSION['userID'] = $createdUserId;
                $_SESSION['userSecurityLevel'] = $basicSecLevel;
                $_SESSION['userName'] = $userName;

                echo "true";
                exit();
            }
        }
    }
    
    $logger->LogError("Could not insert '" . $userName . "' into Users table. Exception: " . $errors);
    echo "Account Creation Failed: Database Connection Error. Please Try Again Later.";
    //echo $errors;
?>