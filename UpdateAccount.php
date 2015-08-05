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
		
        // Init variables
	$userID =               $objUser->UserID;
	$userName =             "";
	$firstName =            "";
	$lastName =             "";
	$gender =               "";
        $email =                "";
        $encryptedPassword =    "";
	$dateOfBirth =          "";
	$gamePlatforms =        null;
	$timeZone =             -1;
	$bio =                  "";
			
	$errorOnPOSTRetrieval = false;
	$exceptionMessage = "Some Fields Contained Invalid Data -- Please Review, Correct, and Try Again";
	$userExceptionMessage = $exceptionMessage;

	// Get form inputs
	try {
            $userName =         trim($_POST['userName']);
            $firstName =        trim($_POST['firstName']);
            $lastName =         trim($_POST['lastName']);
            $gender = 		$_POST['gender'];
            $email =            filter_var(trim($_POST["emailAddress"]), FILTER_SANITIZE_EMAIL);
            
            if(strlen(trim($_POST['pwd'])) > 0) {
                $encryptedPassword = $securityHandler->EncryptPassword(trim($_POST['pwd']));
            }
				
            if(strlen(trim($_POST['DOBDatePicker'])) > 0) {
		$dateOfBirth = 	date('Y-m-d', strtotime(trim($_POST['DOBDatePicker'])));
            }
            else {
		$dateOfBirth = '1900-01-01';
            }
				
            $gamePlatforms =    $_POST['platforms'];
            $timeZone =         $_POST['ddlTimeZones'];
            $bio = 		trim($_POST['message']);
	}
	catch(Exception $e) {
            $exceptionMessage = "Could not retrieve account update data from POST (Signup.php). Exception: " . $e->getMessage();
            $userExceptionMessage = "Error: Could Not Update Your Account. Please Try Again Later.";
            $errorOnPOSTRetrieval = true;
	}
		
	if($errorOnPOSTRetrieval == true) {
            $logger->LogError($exceptionMessage);
            echo $userExceptionMessage;
            exit();
	}
        
	$objUser->UserName = $userName;
        
        $emailChanged = false;
        if($email != $objUser->EmailAddress) {
            $emailChanged = true;
            $objUser->EmailAddress = $email;
        }
        
	$objUser->TimezoneID = $timeZone;
	$objUser->FirstName = $firstName;
	$objUser->LastName = $lastName;
	$objUser->Gender = $gender;
	$objUser->Birthdate = $dateOfBirth;
	$objUser->Autobiography = $bio;
	$objUser->GamePlatforms = $gamePlatforms;
	
        if(strlen(trim($email)) == 0) {
            echo "Account Update Failed: Email Address Must Not Be Empty.";
        }
        else if($emailChanged && $securityHandler->EmailAssociatedWithExistingAccount($dataAccess, $logger, $email)) {
            echo "Account Update Failed: Email Address Already Associated With Another Existing Account.";
        }
        else if(strlen(trim($userName)) == 0) {
            echo "Account Update Failed: Username Must Not Be Empty.";
        }
        else if(!$securityHandler->UsernameIsAvailable($dataAccess, $logger, $userName)) {
            echo "Account Update Failed: This Username Already Taken.";
        }
	else if(!$securityHandler->UpdateUserAccount($dataAccess, $logger, $objUser, $encryptedPassword)) {
            echo "Account Update Failed: Database Connection Error. Please Try Again Later.";
	}
	else {
            $sessionDataAccess = new DataAccess();
            $sessionHandler = new DBSessionHandler($sessionDataAccess);
            session_set_save_handler($sessionHandler, true);
            session_start();
            $_SESSION['WebUser'] = $objUser;
		
            echo "true";
	}
    }
    else {
    	echo "Unauthorized Access";
    }
?>