<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/GamingHandler.class.php';
    include_once 'classes/PayPalMsgHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    include_once 'classes/EventSearchParameters.class.php';
    include_once 'classes/UserSearchParameters.class.php';
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

		echo $gamingHandler->EventEditorLoad($dataAccess, $logger, $objUser, $eventId);
		break;
            case "EventManagerLoad":
		echo $gamingHandler->EventManagerLoad($dataAccess, $logger, $objUser->UserID);
                break;
            case "EventEditorCreateEvent":
                $pvtEventFriends = (isset($_POST['pvtEventFriends'])) ? ($_POST['pvtEventFriends']) : [];
                $gameTitleID = -1;
                if(isset($_POST['ddlGameTitles'])) {
                    $gameTitleID = trim($_POST['ddlGameTitles']);
                }
                
                $eventGame = Game::ConstructGameForEvent($gameTitleID, $_POST['gameDate'], $_POST['gameTime'], 
                                                         $_POST['gamePlayersNeeded'], trim($_POST['message']), $pvtEventFriends,
                                                         $_POST['isGlobalGame'] == 'true' ? true : false, $_POST['ddlTimeZones'], 
                                                         $_POST['ddlPlatforms'], $_POST['gameTitle'], $_POST['gameDateUTC'], '', '', -1);

                echo $gamingHandler->EventEditorCreateEvent($dataAccess, $logger, $objUser->UserID, $eventGame);
                break;
            case "EventEditorUpdateEvent":
                $eventId = $_POST['eventId'];
                $gameTitleId = (isset($_POST['ddlGameTitles'.$eventId])) ? trim($_POST['ddlGameTitles'.$eventId]) : -1;
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
		echo $gamingHandler->ConstructGameTitleSelectorHTML($dataAccess, $logger, $objUser->UserID, '');
		break;
            case "GetUserOwnedEventsForJTable":
		$orderBy = isset($_GET['jtSorting']) ? filter_var($_GET['jtSorting'], FILTER_SANITIZE_STRING) : "DisplayDate ASC";
		$startIndex = isset($_GET['jtStartIndex']) ? filter_var($_GET['jtStartIndex'], FILTER_SANITIZE_STRING) : "-1";
		$pageSize = isset($_GET['jtPageSize']) ? filter_var($_GET['jtPageSize'], FILTER_SANITIZE_STRING) : "-1";
		$paginationEnabled = ($startIndex === "-1") ? false : true;

                $startDateTime = isset($_POST['gameFilterStartDateTime']) ? filter_var($_POST['gameFilterStartDateTime'], FILTER_SANITIZE_STRING) : "";
                $endDateTime = isset($_POST['gameFilterEndDateTime']) ? filter_var($_POST['gameFilterEndDateTime'], FILTER_SANITIZE_STRING) : "";
		$startDateRangeInDays = isset($_POST['gameFilterDateRangeStart']) ? filter_var($_POST['gameFilterDateRangeStart'], FILTER_SANITIZE_STRING) : "";
		$endDateRangeInDays = isset($_POST['gameFilterDateRangeEnd']) ? filter_var($_POST['gameFilterDateRangeEnd'], FILTER_SANITIZE_STRING) : "";
				
		if(strlen($startDateRangeInDays) > 0) {
                    $curUTCStartDate = new DateTime(null, new DateTimeZone("UTC"));
                    $curUTCEndDate = new DateTime(null, new DateTimeZone("UTC"));
					
                    // Add value of selected start and end dates, each of which will be the difference from today in days, to current date
                    $startDateTime = ($startDateRangeInDays == "0") ? ($curUTCStartDate->format(DateTime::ATOM)) : 
                                                                      ($curUTCStartDate->sub(new DateInterval(sprintf("P%sD", $startDateRangeInDays)))->format(DateTime::ATOM));
                    $endDateTime = ($endDateRangeInDays == "0") ? ($curUTCEndDate->format(DateTime::ATOM)) : 
								  ($curUTCEndDate->add(new DateInterval(sprintf("P%sD", $endDateRangeInDays)))->format(DateTime::ATOM));
		}
				
                $existingGameTitles = (isset($_POST['filterGameTitles'])) ? ($_POST['filterGameTitles']) : [];
                $customGameTitle = isset($_POST['gameCustomTitleFilter']) ? trim(filter_var($_POST['gameCustomTitleFilter'], FILTER_SANITIZE_STRING)) : "";
                if(strlen($customGameTitle) > 0) {
                    array_push($existingGameTitles, $customGameTitle);
                }
				
		$activeJoinedUsers = (isset($_POST['filterActiveJoinedUsers'])) ? ($_POST['filterActiveJoinedUsers']) : [];
                $customJoinedUserEntry = isset($_POST['gameCustomJoinedUserFilter']) ? trim(filter_var($_POST['gameCustomJoinedUserFilter'], FILTER_SANITIZE_STRING)) : "";
                
                $platforms = (isset($_POST['filterPlatforms'])) ? ($_POST['filterPlatforms']) : [];
                $customPlatformEntry = isset($_POST['customPlatformFilter']) ? trim(filter_var($_POST['customPlatformFilter'], FILTER_SANITIZE_STRING)) : "";
                
                $evtStatusFilters = (isset($_POST['evtStatus'])) ? ($_POST['evtStatus']) : [];
                $showFullEventsOnly = in_array('showFull', $evtStatusFilters);
                $showOpenEventsOnly = false;
                $showHiddenEvents = in_array('showHidden', $evtStatusFilters);
                
		$searchParms = new EventSearchParameters($showHiddenEvents, $startDateTime, $endDateTime, $existingGameTitles, [], $activeJoinedUsers, $platforms, 
                                                         true, true, $showFullEventsOnly, false, "", $customJoinedUserEntry, $customPlatformEntry, $showOpenEventsOnly);
		echo $gamingHandler->JTableEventManagerLoad($dataAccess, $logger, $objUser->UserID, $orderBy, $paginationEnabled, 
                                                            $startIndex, $pageSize, $searchParms);
                break;
            case "GetCurrentEventsForJTable":
		$orderBy = isset($_GET['jtSorting']) ? filter_var($_GET['jtSorting'], FILTER_SANITIZE_STRING) : "DisplayDate ASC";
		$startIndex = isset($_GET['jtStartIndex']) ? filter_var($_GET['jtStartIndex'], FILTER_SANITIZE_STRING) : "-1";
		$pageSize = isset($_GET['jtPageSize']) ? filter_var($_GET['jtPageSize'], FILTER_SANITIZE_STRING) : "-1";
		$paginationEnabled = ($startIndex === "-1") ? false : true;

		$showHiddenEvents = false;
                $startDateTime = isset($_POST['gameFilterStartDateTime']) ? filter_var($_POST['gameFilterStartDateTime'], FILTER_SANITIZE_STRING) : "";
                $endDateTime = isset($_POST['gameFilterEndDateTime']) ? filter_var($_POST['gameFilterEndDateTime'], FILTER_SANITIZE_STRING) : "";
		$startDateRangeInDays = isset($_POST['gameFilterDateRangeStart']) ? filter_var($_POST['gameFilterDateRangeStart'], FILTER_SANITIZE_STRING) : "";
		$endDateRangeInDays = isset($_POST['gameFilterDateRangeEnd']) ? filter_var($_POST['gameFilterDateRangeEnd'], FILTER_SANITIZE_STRING) : "";
						
		if(strlen($startDateRangeInDays) > 0) {
                    $curUTCStartDate = new DateTime(null, new DateTimeZone("UTC"));
                    $curUTCEndDate = new DateTime(null, new DateTimeZone("UTC"));
					
                    // Add value of selected start and end dates, each of which will be the difference from today in days, to current date
                    $startDateTime = ($startDateRangeInDays == "0") ? ($curUTCStartDate->format(DateTime::ATOM)) : 
								      ($curUTCStartDate->sub(new DateInterval(sprintf("P%sD", $startDateRangeInDays)))->format(DateTime::ATOM));
                    $endDateTime = ($endDateRangeInDays == "0") ? ($curUTCEndDate->format(DateTime::ATOM)) : 
								  ($curUTCEndDate->add(new DateInterval(sprintf("P%sD", $endDateRangeInDays)))->format(DateTime::ATOM));
		}
				
                $existingGameTitles = (isset($_POST['filterGameTitles'])) ? ($_POST['filterGameTitles']) : [];
                $customGameTitle = isset($_POST['gameCustomTitleFilter']) ? trim(filter_var($_POST['gameCustomTitleFilter'], FILTER_SANITIZE_STRING)) : "";
                if(strlen($customGameTitle) > 0) {
                    array_push($existingGameTitles, $customGameTitle);
                }
				
		$activeUsers = (isset($_POST['filterActiveUsers'])) ? ($_POST['filterActiveUsers']) : [];
                $customUserEntry = isset($_POST['gameCustomUserFilter']) ? trim(filter_var($_POST['gameCustomUserFilter'], FILTER_SANITIZE_STRING)) : "";
				
		$activeJoinedUsers = (isset($_POST['filterActiveJoinedUsers'])) ? ($_POST['filterActiveJoinedUsers']) : [];
                $customJoinedUserEntry = isset($_POST['gameCustomJoinedUserFilter']) ? trim(filter_var($_POST['gameCustomJoinedUserFilter'], FILTER_SANITIZE_STRING)) : "";
                
                $platforms = (isset($_POST['filterPlatforms'])) ? ($_POST['filterPlatforms']) : [];
                $customPlatformEntry = isset($_POST['customPlatformFilter']) ? trim(filter_var($_POST['customPlatformFilter'], FILTER_SANITIZE_STRING)) : "";
                
                $evtStatusFilters = (isset($_POST['evtStatus'])) ? ($_POST['evtStatus']) : [];
                $showJoinedEvents = in_array('showJoined', $evtStatusFilters);
                $showUnjoinedEvents = in_array('showUnjoined', $evtStatusFilters);
                $showFullEventsOnly = false;
                $showOpenEventsOnly = in_array('openOnly', $evtStatusFilters);
				
		$searchParms = new EventSearchParameters($showHiddenEvents, $startDateTime, $endDateTime, $existingGameTitles, $activeUsers, $activeJoinedUsers, 
                                                         $platforms, $showJoinedEvents, $showUnjoinedEvents, $showFullEventsOnly, false, $customUserEntry, 
                                                         $customJoinedUserEntry, $customPlatformEntry, $showOpenEventsOnly);
		echo $gamingHandler->JTableCurrentEventViewerLoad($dataAccess, $logger, $objUser->UserID, $orderBy, $paginationEnabled, $startIndex, 
                                                                  $pageSize, $searchParms);
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
            case "GetPlatformDropdownListForEditor":
                $selectorFieldName = isset($_POST['selectorFieldName']) ? filter_var($_POST['selectorFieldName'], FILTER_SANITIZE_STRING) : "";
                echo $gamingHandler->GetPlatformDropdownList($dataAccess, -1, '', $selectorFieldName);
                break;
            case "GetCurrentGamerTagsForUser":
		$orderBy = isset($_GET['jtSorting']) ? filter_var($_GET['jtSorting'], FILTER_SANITIZE_STRING) : "GamerTagName ASC";
		$startIndex = isset($_GET['jtStartIndex']) ? filter_var($_GET['jtStartIndex'], FILTER_SANITIZE_STRING) : "-1";
		$pageSize = isset($_GET['jtPageSize']) ? filter_var($_GET['jtPageSize'], FILTER_SANITIZE_STRING) : "-1";
		$paginationEnabled = ($startIndex === "-1") ? false : true;
                
                // If retrieving gamer tags for the current user, userID param will be set to -1...otherwise, use the value of the
                // userID param to retrieve gamer tag list
                $userId = intval((isset($_GET['userID'])) ? (filter_var($_GET['userID'], FILTER_SANITIZE_STRING)) : "-1");
                if($userId == -1)  $userId = $objUser->UserID;
			
                echo $securityHandler->LoadGamerTagsForUser($dataAccess, $logger, $userId, -1, $orderBy, $paginationEnabled, $startIndex, $pageSize);
                break;
            case "AddGamerTagForUser":
		$platformID = isset($_POST['PlatformName']) ? filter_var($_POST['PlatformName'], FILTER_SANITIZE_STRING) : "";
		$tagName = isset($_POST['GamerTagName']) ? filter_var($_POST['GamerTagName'], FILTER_SANITIZE_STRING) : "";
				
                echo $securityHandler->AddGamerTagForUser($dataAccess, $logger, $objUser->UserID, $platformID, $tagName);
                break;
            case "UpdateGamerTagsForUser":
		$gamerTagID = isset($_POST['ID']) ? filter_var($_POST['ID'], FILTER_SANITIZE_STRING) : "";
		$platformID = isset($_POST['PlatformName']) ? filter_var($_POST['PlatformName'], FILTER_SANITIZE_STRING) : "";
		$tagName = isset($_POST['GamerTagName']) ? filter_var($_POST['GamerTagName'], FILTER_SANITIZE_STRING) : "";
				
		echo $securityHandler->UpdateGamerTagForUser($dataAccess, $logger, $objUser->UserID, $gamerTagID, $platformID, $tagName);
                break;
            case "DeleteGamerTagsForUser":
		$gamerTagID = isset($_POST['ID']) ? filter_var($_POST['ID'], FILTER_SANITIZE_STRING) : "";
			
                echo $securityHandler->DeleteGamerTagsForUser($dataAccess, $logger, $objUser->UserID, $gamerTagID);
                break;
            case "CancelPayPalSubscription":
                $payPalMsgHandler = new PayPalMsgHandler();
                echo $payPalMsgHandler->CancelSubscriptionForUser($dataAccess, $logger, $objUser->UserID);
                break;
            case "GetFriendInviteAvailUsersForJTable":
                $orderBy = isset($_GET['jtSorting']) ? filter_var($_GET['jtSorting'], FILTER_SANITIZE_STRING) : "UserName ASC";
                $startIndex = isset($_GET['jtStartIndex']) ? filter_var($_GET['jtStartIndex'], FILTER_SANITIZE_STRING) : "-1";
                $pageSize = isset($_GET['jtPageSize']) ? filter_var($_GET['jtPageSize'], FILTER_SANITIZE_STRING) : "-1";
                $paginationEnabled = ($startIndex === "-1") ? false : true;

                $userName = isset($_POST['usernameFilter']) ? filter_var($_POST['usernameFilter'], FILTER_SANITIZE_STRING) : "";
                $gamerTag = isset($_POST['gamerTagFilter']) ? filter_var($_POST['gamerTagFilter'], FILTER_SANITIZE_STRING) : "";
                $firstName = isset($_POST['firstnameFilter']) ? filter_var($_POST['firstnameFilter'], FILTER_SANITIZE_STRING) : "";
                $lastName = isset($_POST['lastnameFilter']) ? filter_var($_POST['lastnameFilter'], FILTER_SANITIZE_STRING) : "";
                $platforms = (isset($_POST['filterPlatforms'])) ? ($_POST['filterPlatforms']) : [];
                $gender = isset($_POST['genderFilter']) ? filter_var($_POST['genderFilter'], FILTER_SANITIZE_STRING) : "";

		$searchParms = new UserSearchParameters($gamerTag, $userName, $firstName, $lastName, $platforms, $gender);

		echo $gamingHandler->JTableAvailableUsersViewerLoad($dataAccess, $logger, $objUser->UserID, $orderBy, $paginationEnabled, $startIndex, 
                                                                    $pageSize, $searchParms);
                break;
            case "GetCurrentFriendsListForJTable":
                $orderBy = isset($_GET['jtSorting']) ? filter_var($_GET['jtSorting'], FILTER_SANITIZE_STRING) : "UserName ASC";
                $startIndex = isset($_GET['jtStartIndex']) ? filter_var($_GET['jtStartIndex'], FILTER_SANITIZE_STRING) : "-1";
                $pageSize = isset($_GET['jtPageSize']) ? filter_var($_GET['jtPageSize'], FILTER_SANITIZE_STRING) : "-1";
                $paginationEnabled = ($startIndex === "-1") ? false : true;

                $userName = isset($_POST['usernameFilter']) ? filter_var($_POST['usernameFilter'], FILTER_SANITIZE_STRING) : "";
                $gamerTag = isset($_POST['gamerTagFilter']) ? filter_var($_POST['gamerTagFilter'], FILTER_SANITIZE_STRING) : "";
                $firstName = isset($_POST['firstnameFilter']) ? filter_var($_POST['firstnameFilter'], FILTER_SANITIZE_STRING) : "";
                $lastName = isset($_POST['lastnameFilter']) ? filter_var($_POST['lastnameFilter'], FILTER_SANITIZE_STRING) : "";
                $platforms = (isset($_POST['filterPlatforms'])) ? ($_POST['filterPlatforms']) : [];
                
                $friendTypeFilters = (isset($_POST['friendTypes'])) ? ($_POST['friendTypes']) : [];
                $showInvitationsToMe = in_array('showInvToMe', $friendTypeFilters);
                $showInvitationsFromMe = in_array('showInvFromMe', $friendTypeFilters);
                $showRejectedInvitations = in_array('showRejectedInv', $friendTypeFilters);
                $showCurrentFriends = in_array('showCurFriends', $friendTypeFilters);
                
                $searchParms = new UserSearchParameters($gamerTag, $userName, $firstName, $lastName, $platforms, "", $showInvitationsToMe, 
                                                        $showInvitationsFromMe, $showRejectedInvitations, $showCurrentFriends);

		echo $gamingHandler->JTableCurrentFriendsListViewerLoad($dataAccess, $logger, $objUser->UserID, $orderBy, $paginationEnabled, $startIndex, 
                                                                        $pageSize, $searchParms);
                break;
            case "SendFriendInviteToUsers":				
                echo $gamingHandler->SendFriendInviteToUsers($dataAccess, $logger, $objUser->UserID, $_POST['userIds']);
                break;
            case "AcceptUserFriendInvites":
                echo $gamingHandler->AcceptUserFriendInvites($dataAccess, $logger, $objUser->UserID, $_POST['userIds']);
                break;
            case "RemoveUserFromFriendList":
                $targetUserId = isset($_POST['ID']) ? filter_var($_POST['ID'], FILTER_SANITIZE_STRING) : "";
                
                $userIds = [$targetUserId];
                $resultMsg = $gamingHandler->RemoveUsersFromFriendList($dataAccess, $logger, $objUser->UserID, $userIds);
                $isError = stripos($resultMsg, "SYSTEM ERROR") !== FALSE;
                
                $jTableResult = [];
                $jTableResult['Result'] = $isError ? 'ERROR' : 'OK';
                if($isError)  $jTableResult['Message'] = $resultMsg;
                echo json_encode($jTableResult);
                break;
            case "RemoveUsersFromFriendList":
                echo $gamingHandler->RemoveUsersFromFriendList($dataAccess, $logger, $objUser->UserID, $_POST['userIds']);
                break;
            case "ShowUserProfileDetails":
		$userId = '';
		if(isset($_GET['userId'])) {
                    $userId = filter_var(trim($_GET['userId']), FILTER_SANITIZE_STRING);
		}

		echo $securityHandler->ShowUserProfileDetails($dataAccess, $logger, $userId);
		break;
        }
    }
    else {
        echo "Error: No action set";
    }
?>