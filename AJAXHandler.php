<?php
    include_once 'classes/DataAccess.class.php';
    include_once 'classes/SecurityHandler.class.php';
    include_once 'classes/GamingHandler.class.php';
    include_once 'classes/DBSessionHandler.class.php';
    include_once 'classes/Logger.class.php';
    include_once 'classes/User.class.php';
    
    $dataAccess = new DataAccess();
    $loggerDataAccess = new DataAccess();
    $securityHandler = new SecurityHandler();
    $gamingHandler = new GamingHandler();
    $logger = new Logger($loggerDataAccess);
    $objUser = User::constructDefaultUser();
	
    if(isset($_POST['action'])) {
        $action = $_POST['action'];

        //$logger->LogInfo("action = " . $action);

        // Only proceed if this page is accessed due to signup/login, or from a logged-in user
        if(($action != "Login") && ($action != "Signup")) {
            $sessionDataAccess = new DataAccess();
            $sessionHandler = new DBSessionHandler($sessionDataAccess);
            session_set_save_handler($sessionHandler, true);
            session_start();

            if(isset($_SESSION['WebUser'])) {
                    $objUser = $_SESSION['WebUser'];
                    session_write_close();
            }
            else {
                echo "Unauthorized Access";
                exit();
            }
        }

        // Execute desired action
        switch($_POST['action']) {
            case "Login":
                ;
                break;
            case "Signup":
                ;
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
            case "EventEditorLoad":
                if($objUser->IsPremiumMember) {
                    $eventId = '0';
                    if(isset($_POST['EventID'])) {
                        $eventId = $_POST['EventID'];
                    }

                    echo $gamingHandler->EventEditorLoad($dataAccess, $logger, $objUser->UserID, $eventId);
                }
                break;
            case "EventEditorCreateEvent":
                if($objUser->IsPremiumMember) {
                    $eventGame = Game::ConstructGameForEvent(trim($_POST['ddlGameTitles']), $_POST['gameDate'], $_POST['gameTime'], 
                                                             $_POST['gamePlayersNeeded'], trim($_POST['message']), $_POST['pvtEventFriends']);
                    
                    echo $gamingHandler->EventEditorCreateEvent($dataAccess, $logger, $objUser->UserID, $eventGame);
                }
                break;
        }
    }
    else {
        echo "Error: No action set";
    }
?>