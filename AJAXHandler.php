<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/GamingHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    include_once 'securimage/securimage.php';
    
    $dataAccess = new DataAccess();
    $loggerDataAccess = new DataAccess();
    $securityHandler = new SecurityHandler();
    $gamingHandler = new GamingHandler();
    $logger = new Logger($loggerDataAccess);
    $objUser = User::constructDefaultUser();
	
    $action = isset($_GET['action']) ? filter_var($_GET['action'], FILTER_SANITIZE_STRING) : filter_var($_POST['action'], FILTER_SANITIZE_STRING);
    
    if(isset($action)) {
        // Only proceed if this page is accessed due to signup/login, or from a logged-in user
        if(($action != "Login") && ($action != "Signup")) {
            $sessionDataAccess = new DataAccess();
            $sessionHandler = new DBSessionHandler($sessionDataAccess);
            session_set_save_handler($sessionHandler, true);
            session_start();

            if(isset($_SESSION['WebUser'])) {
                $objUser = $_SESSION['WebUser'];
                $_SESSION['lastActivity'] = time();
                session_write_close();
            }
            else {
		// If action was intended for a jTable display, format error response appropriately
		if(strripos($action, 'ForJTable') !== false) {
                    $jTableResult = [];
                    $jTableResult['Result'] = "ERROR";
                    $jTableResult['Message'] = "Unauthorized Access - Please Log In";
                    echo json_encode($jTableResult);
		}
		else {
                    echo "Unauthorized Access - Please Log In";
		}
				
                exit();
            }
        }

        // Execute desired action
        switch($action) {
            case "Login":
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
                        $sessionDataAccess = new DataAccess();
                        $sessionHandler = new DBSessionHandler($sessionDataAccess);
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
                // or if no valid login credentials provided
                echo "Invalid Login Credentials";
                break;
            case "Logout":
		$sessionDataAccess = new DataAccess();
		$sessionHandler = new DBSessionHandler($sessionDataAccess);
		session_set_save_handler($sessionHandler, true);
		session_start();
			
		$securityHandler->LogoutUser();
                header("Location: Index.php");
                exit();
            case "Signup":
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
                    $exceptionMessage = "Could not retrieve signup credentials from POST on Signup. Exception: " . $e->getMessage();
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
                break;
            case "UpdateAccount":
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
                    $exceptionMessage = "Could not retrieve account update data from POST. Exception: " . $e->getMessage();
                    $userExceptionMessage = "Error: Could Not Update Your Account. Please Try Again Later.";
                    $errorOnPOSTRetrieval = true;
                }

                if($errorOnPOSTRetrieval == true) {
                    $logger->LogError($exceptionMessage);
                    echo $userExceptionMessage;
                    exit();
                }

                $emailChanged = false;
                if($email != $objUser->EmailAddress) {
                    $emailChanged = true;
                }
                
                $usernameChanged = false;
                if($userName != $objUser->UserName) {
                    $usernameChanged = true;
                }

                $objUser->UserName = $userName;
                $objUser->EmailAddress = $email;
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
                else if($usernameChanged && (!$securityHandler->UsernameIsAvailable($dataAccess, $logger, $userName))) {
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
                
                break;
            case "CheckUsernameAvailability":
                $userName = trim($_POST['userName']);

                if($securityHandler->UsernameIsAvailable($dataAccess, $logger, $userName)) {
                    echo "avail";
                }
                else {
                    echo "taken";
                }
                break;
            case "UpdateUsername":
                if(!$securityHandler->UpdateUsername($dataAccess, $logger, $objUser->UserID)) {
                    echo "Failed To Update Username -- Database Connection Error. Please Try Again Later.";
                }
                else {
                    $sessionDataAccess = new DataAccess();
                    $sessionHandler = new DBSessionHandler($sessionDataAccess);
                    session_set_save_handler($sessionHandler, true);
                    session_start();

                    $objUser->UserName = $objUser->EmailAddress;
                    $_SESSION['WebUser'] = $objUser;

                    echo "true";
                }
                break;
            case "EventEditorLoad":
		$eventId = '';
		if(isset($_GET['EventID'])) {
                    $eventId = filter_var(trim($_GET['EventID']), FILTER_SANITIZE_STRING);
		}             

		echo $gamingHandler->EventEditorLoad($dataAccess, $logger, $objUser->UserID, $eventId);
		break;
            case "EventManagerLoad":
		echo $gamingHandler->EventManagerLoad($dataAccess, $logger, $objUser->UserID);
                break;
            case "EventEditorCreateEvent":
                $pvtEventFriends = (isset($_POST['pvtEventFriends'])) ? ($_POST['pvtEventFriends']) : [];
                $eventGame = Game::ConstructGameForEvent(trim($_POST['ddlGameTitles']), $_POST['gameDate'], $_POST['gameTime'], 
                                                         $_POST['gamePlayersNeeded'], trim($_POST['message']), $pvtEventFriends,
                                                         $_POST['isGlobalGame'] == 'true' ? true : false, $_POST['ddlTimeZones'], 
                                                         $_POST['ddlPlatforms'], $_POST['gameTitle'], $_POST['gameDateUTC'], '', '', -1);

                echo $gamingHandler->EventEditorCreateEvent($dataAccess, $logger, $objUser->UserID, $eventGame);
                break;
            case "EventEditorUpdateEvent":
                $eventId = $_POST['eventId'];
                $gameTitleId = trim($_POST['ddlGameTitles'.$eventId]);
                $gameDate = $_POST['gameDate'.$eventId];
                $gameTime = $_POST['gameTime'.$eventId];
                $gamePlayersNeeded = $_POST['gamePlayersNeeded'.$eventId];
                $message = trim($_POST['message'.$eventId]);
                $pvtEventFriends = (isset($_POST['pvtEventFriends'.$eventId])) ? ($_POST['pvtEventFriends'.$eventId]) : [];
                $ddlTimeZonesId = $_POST['ddlTimeZones'.$eventId];
                $ddlPlatformsId = $_POST['ddlPlatforms'.$eventId];
                
                $eventGame = Game::ConstructGameForEvent($gameTitleId, $gameDate, $gameTime, $gamePlayersNeeded, $message, $pvtEventFriends,
                                                         $_POST['isGlobalGame'] == 'true' ? true : false, $ddlTimeZonesId, 
                                                         $ddlPlatformsId, $_POST['gameTitle'], $_POST['gameDateUTC'], '', '', $eventId);

                echo $gamingHandler->EventEditorUpdateEvent($dataAccess, $logger, $objUser->UserID, $eventGame);
                break;
            case "EventEditorToggleEventVisibility":				
                echo $gamingHandler->EventEditorToggleEventVisibility($dataAccess, $logger, $_POST['eventIds'], $_POST['isActive']);
                break;
            case "EventEditorDeleteEvents":
                echo $gamingHandler->EventEditorDeleteEvents($dataAccess, $logger, $_POST['eventIds']);
                break;
            case "ReloadGameTitleSelector":
		echo $gamingHandler->ConstructGameTitleSelectorHTML($dataAccess, $logger, $objUser->UserID);
		break;
            case "GetUserOwnedEventsForJTable":
		$orderBy = isset($_GET['jtSorting']) ? filter_var($_GET['jtSorting'], FILTER_SANITIZE_STRING) : "DisplayDate ASC";
		$startIndex = isset($_GET['jtStartIndex']) ? filter_var($_GET['jtStartIndex'], FILTER_SANITIZE_STRING) : "-1";
		$pageSize = isset($_GET['jtPageSize']) ? filter_var($_GET['jtPageSize'], FILTER_SANITIZE_STRING) : "-1";
		$paginationEnabled = ($startIndex === "-1") ? false : true;
                
                $showHidden = isset($_POST['showHidden']) ? filter_var($_POST['showHidden'], FILTER_SANITIZE_STRING) : "0";
                $showHiddenEvents = ($showHidden === "1") ? true : false;
			
		$showPastEventsDays = isset($_POST['showPastEventsInDays']) ? filter_var($_POST['showPastEventsInDays'], FILTER_SANITIZE_STRING) : "-1";
		echo $gamingHandler->JTableEventManagerLoad($dataAccess, $logger, $objUser->UserID, $orderBy, $paginationEnabled, 
                                                            $startIndex, $pageSize, $showHiddenEvents, $showPastEventsDays);
                break;
            case "GetCurrentEventsForJTable":
		$orderBy = isset($_GET['jtSorting']) ? filter_var($_GET['jtSorting'], FILTER_SANITIZE_STRING) : "DisplayDate ASC";
		$startIndex = isset($_GET['jtStartIndex']) ? filter_var($_GET['jtStartIndex'], FILTER_SANITIZE_STRING) : "-1";
		$pageSize = isset($_GET['jtPageSize']) ? filter_var($_GET['jtPageSize'], FILTER_SANITIZE_STRING) : "-1";
		$paginationEnabled = ($startIndex === "-1") ? false : true;
			
		echo $gamingHandler->JTableCurrentEventViewerLoad($dataAccess, $logger, $objUser->UserID, $orderBy, $paginationEnabled, $startIndex, $pageSize);
                break;
            case "EventViewerJoinEvents":
		echo $gamingHandler->AddUserToEvents($dataAccess, $logger, $objUser->UserID, $_POST['eventIds']);
                break;
            case "EventViewerLeaveEvents":
		echo $gamingHandler->RemoveUserFromEvents($dataAccess, $logger, $objUser->UserID, $_POST['eventIds']);
                break;
            case "GetJoinedPlayersForEvent":
		echo $gamingHandler->LoadJoinedPlayersForEvent($dataAccess, $logger, $_GET['eventId']);
		break;
        }
    }
    else {
        echo "Error: No action set";
    }
?>