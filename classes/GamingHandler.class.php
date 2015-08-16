<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/User.class.php';
include_once 'classes/Game.class.php';

class GamingHandler
{    
    public function LoadUserFriends($dataAccess, $logger, $userID)
    {
        $getUserFriendsQuery = "SELECT uf.`FK_User_ID_Friend` as FriendID, u.`FirstName`, u.`LastName`, u.`UserName` FROM `Gaming.UserFriends` as uf " .
                               "INNER JOIN `Security.Users` as u ON uf.`FK_User_ID_Friend` = u.`ID` " .
                               "WHERE uf.`FK_User_ID_ThisUser` = :userID " .
                               "ORDER BY CASE WHEN ((u.`FirstName` IS NOT NULL) AND (LENGTH(u.`FirstName`) > 0)) THEN u.`FirstName` ELSE u.`UserName` END, " .
                               "CASE WHEN ((u.`FirstName` IS NOT NULL) AND (LENGTH(u.`FirstName`) > 0)) THEN u.`LastName` ELSE u.`UserName` END;";
        
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
	$userFriends = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserFriendsQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
			$objUser = User::constructDefaultUser();
			$objUser->UserID = $row['FriendID'];
			$objUser->FirstName = $row['FirstName'];
			$objUser->LastName = $row['LastName'];
			$objUser->UserName = $row['UserName'];
			array_push($userFriends, $objUser);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve user friends. " . $errors);
	}
			
	return $userFriends;
    }
	
    public function LoadUserGames($dataAccess, $logger, $userID)
    {
        $getUserGamesQuery = "SELECT `ID`, `Name`, 0 AS IsGlobalGameTitle FROM `Gaming.UserGames` " .
                             "WHERE `FK_User_ID` = :userID " .
                             "UNION " .
                             "SELECT `ID`, `Name`, 1 AS IsGlobalGameTitle FROM `Configuration.Games` " .
                             "ORDER BY `Name`;";
        
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
	$userGames = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserGamesQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
			$userGame = new Game($row['ID'], $row['Name'], $row['IsGlobalGameTitle'] == 1);
			array_push($userGames, $userGame);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve user games. " . $errors);
	}
        
        return $userGames;
    }
	
    public function EventEditorLoad($dataAccess, $logger, $userID, $eventId)
    {
	// Build game-players-needed selector
	$gamePlayersNeededSelect = '<select id="gamePlayersNeeded" name="gamePlayersNeeded" >';
	$selected = 'selected="true"';
		
	for($i = 1; $i < 65; $i++) {
            if($i > 1)  $selected = '';
			
            $gamePlayersNeededSelect .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
	}
		
	$gamePlayersNeededSelect .= '</select></p>';
		
	// Build friends list selector
	$privateEventOptionDisabled = " disabled";
	$userFriendList = $this->LoadUserFriends($dataAccess, $logger, $userID);
	$friendListSelect = 'Your friends list is empty';
		
	if(count($userFriendList) > 0){
            $privateEventOptionDisabled = "";
            $friendListSelect = '';
            
            foreach($userFriendList as $userFriend) {
		$userDisplayName = (strlen($userFriend->FirstName) == 0) ? $userFriend->UserName : $userFriend->FirstName . ' ' . $userFriend->LastName;
				
		$userFriendSelected = '';
		if(($eventId > 0) /*&&(thisFriendSelected)*/) {
                    $userFriendSelected = "checked='checked'";
		}
				
		$userFriendDisabled = ' disabled';
		if(($eventId > 0) /*&& (isPrivateEvent)*/) {
                    $userFriendDisabled = '';
		}
				
		$friendListSelect .= '<input type="checkbox" name="pvtEventFriends[]" value="' . $userFriend->UserID . '" ' . $userFriendSelected . 
                                     ' ' . $userFriendDisabled . ' /> ' . $userDisplayName . '</br>';
            }
	}
		
	// Construct game selection dropdown list
	$gameSelector = '<select id="ddlGameTitles" name="ddlGameTitles">';
	$userGames = $this->LoadUserGames($dataAccess, $logger, $userID);
		
	foreach($userGames as $userGame) {
            $class = "userGameOption";
            if($userGame->$IsGlobalGameTitle) {
		$class = "globalGameOption";
            }
			
            $gameSelector .= '<option value="' . $userGame->GameID . '" class="' . $class . '">' . $userGame->Name . '</option>';
	}
	$gameSelector .= '</select>';
		
	// Return Event Scheduler HTML
	return
            '<section>'.
		'<form id="eventCreateForm" name="eventCreateForm" method="POST" action="">'.
                    '<div class="inputLine">'.
			'<p><i class="fa fa-gamepad"></i> &nbsp; What game do you wish to schedule?<br/>'.
			$gameSelector .
			'<p><i class="fa fa-calendar-o"></i> &nbsp; Tell us a date<br/>'.
                            '<input id="gameDate" name="gameDate" type="text" id ="datepicker" maxlength="50" placeholder=" Date"></p>'.
			'<p><i class="fa fa-clock-o"></i> &nbsp; Time you want to play<br/>'.
                            '<input id="gameTime" name="gameTime" type="text" maxlength="9" placeholder=" Time"></p>'.
			'<p><i class="fa fa-user"></i> &nbsp; Number of players needed<br/>'. 
                            $gamePlayersNeededSelect .
			'<p><i class="fa fa-comments-o"></i> &nbsp; Notes about your event<br/>'.
                            '<textarea name="message" id="message" placeholder=" exp: Looking for some new team mates to play through Rocket Leagues 3v3 mode. Must have a mic!" rows="6" required></textarea></p>'.
			'<p><i class="fa fa-lock"></i> &nbsp; Only allow friends to join this event<br/>'.
                            '<input type="checkbox" id="privateEvent" name="privateEvent" value="private" ' . $privateEventOptionDisabled . 
                                '>Private Event</input>&nbsp;&nbsp;'.
                            '<div class="fixedHeightScrollableContainer">'. 
                                $friendListSelect .
                            '</div><br/>'.
			'<button type="submit" class="memberHomeBtn icon fa-cogs" id="createEventBtn">Create Event!</button><br/>'.
                    '</div>'.
		'</form>'.
            '</section>';
    }
	
    public function EventEditorCreateEvent($dataAccess, $logger, $userID, $eventGame)
    {
        $gameIDCol = "`FK_UserGames_ID`";
        if($eventGame->IsGlobalGameTitle) {
            $gameIDCol = "`FK_Game_ID`";
        }
        
        $createEventQuery = "INSERT INTO `Gaming.Events` (`FK_User_ID_EventCreator`," . $gameIDCol .", `FK_Genre_ID`," .
                            "`FK_Platform_ID`, `FK_Timezone_ID`, `EventCreatedDate`, `EventModifiedDate`, `EventScheduledForDate`," .
                            "`RequiredMemberCount`, `IsActive`, `IsPublic`) " .
                            "VALUES (:FKUserEventCreator,:FKGameId,:FKGenreID,:FKPlatformID,:FKTimezoneID,:EventCreatedDate," . 
                            ":EventModifiedDate,:EventScheduledForDate,:RequiredMemberCount,:IsActive,:IsPublic);";
		
	$parmEventCreatorUserId = new QueryParameter(':FKUserEventCreator', $userID, PDO::PARAM_INT);
	$parmGameId = new QueryParameter(':FKGameId', $eventGame->GameID, PDO::PARAM_INT);
        
        
	$queryParms = array($parmEventCreatorUserId, $parmGameId);
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($createEventQuery, $queryParms)){
		$dataAccess->ExecuteNonQuery();
            }
				
            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
		return "true";
            }
	}
	
        $logger->LogError("Could not schedule event for game '". $eventGame->Name . "'. " . $errors);
	return "System Error: Could not schedule event for game '". $eventGame->Name . "'. Please try again later";
    }

    private function GetGameTitleAssociatedGenre($gameID)
    {
        
    }
}
