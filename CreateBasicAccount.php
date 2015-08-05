<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    include_once 'securimage/securimage.php';
    
	//session_start();
    $dataAccess = new DataAccess();
    $logger = new Logger($dataAccess);
	
    $securImage = new Securimage();
    $captchaValidated = false;
	
    $captchaCode = trim($_POST['captcha_code']);
	
    try {
        if($securImage->check($captchaCode) == true) {
            $captchaValidated = true;
	}
    }
    catch(Exception $e) {
	$logger->LogError("Exception occurred on captcha validation: " . $e->getMessage());
    }
	
    if(!$captchaValidated) {
        echo "Incorrect Code Entered...Please Try Again";
	exit();
    }
	
    // Remove Securimage session
    session_unset();
    session_destroy();
	
    $securityHandler = new SecurityHandler();
    $userEmail = "";
    $encryptedPassword = "";
    $errorOnPOSTRetrieval = false;
    $exceptionMessage = "Email Address and Password must be filled out to create an account";
    $userExceptionMessage = $exceptionMessage;
    
    // Get login credentials
    try {
	$password = trim($_POST["signupPW"]);
        $userEmail = filter_var(trim($_POST["signupEmail"]), FILTER_SANITIZE_EMAIL);
		
	if((strlen($password) == 0) || (strlen($userEmail) == 0)) {
            $errorOnPOSTRetrieval = true;
	}
    }
    catch(Exception $e) {
	$exceptionMessage = "Could not retrieve signup credentials from POST (Login.php). Exception: " . $e->getMessage();
	$userExceptionMessage = "Error: Could Not create Basic Account. Please Try Again Later.";
	$errorOnPOSTRetrieval = true;
    }
	
    if($errorOnPOSTRetrieval == true) {
        $logger->LogError($exceptionMessage);
        echo $userExceptionMessage;
        exit();		
    }
    
    $encryptedPassword = $securityHandler->EncryptPassword($password);
	
    // Get security level for "Basic" users
    $basicRoleId = -1;
    $basicSecLevel = -1;
    $secLevelQuery = "select `ID`, `SecurityLevel` from `Security.Roles` WHERE `Name` = 'BasicMember'";
	
    $errors = $dataAccess->CheckErrors();
    $echoResponse = "Account Creation Failed: Database Connection Error. Please Try Again Later.";

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
            // Check that selected email is not already associated with an existing account
            if(!$securityHandler->EmailAssociatedWithExistingAccount($dataAccess, $logger, $userEmail)) {
                // Create account
		$objUser = $securityHandler->InsertBasicUser($dataAccess, $logger, $encryptedPassword, $userEmail, $basicRoleId, $basicSecLevel);

		if($objUser->UserID > 0) {
                    // Create session
                    $sessionDataAccess = new DataAccess();
                    $sessionHandler = new DBSessionHandler($sessionDataAccess);
                    session_set_save_handler($sessionHandler, true);
                    session_start();

                    // Set session variables
                    $_SESSION['WebUser'] = $objUser;
                    $_SESSION['lastActivity'] = time();
                    $_SESSION['JustCreatedAccount'] = true;
                    echo "true";
                    exit();
                }
            }
            else {
                $echoResponse = "Account Creation Failed: This Email Already Registered With An Existing Account.";
                $errors = "Account Creation Failed -- This Email Already Registered With An Existing Account.";
            }
        }
    }
    
    $logger->LogError("Could not insert new user (email = '" . $userEmail . "') into Users table. Exception: " . $errors);
    echo $echoResponse;
?>