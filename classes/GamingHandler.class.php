<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/User.class.php';
include_once 'classes/Game.class.php';
include_once 'classes/Utils.class.php';

class GamingHandler
{    
    public function LoadUserFriends($dataAccess, $logger, $userID, $eventId = -1)
    {
	$eventWhereClause = "";
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
	$parmEventId = null;
	$queryParms = array($parmUserId);
		
	if($eventId > -1) {
            $eventWhereClause = "AND ((COALESCE(em.`FK_Event_ID`, -1)) = :eventId) ";
            $parmEventId = new QueryParameter(':eventId', $eventId, PDO::PARAM_INT);
            array_push($queryParms, $parmEventId);
	}
		
        $getUserFriendsQuery = "SELECT uf.`FK_User_ID_Friend` as FriendID, u.`FirstName`, u.`LastName`, u.`UserName` FROM `Gaming.UserFriends` as uf " .
                               "INNER JOIN `Security.Users` as u ON uf.`FK_User_ID_Friend` = u.`ID` " .
                               "LEFT JOIN `Gaming.EventAllowedMembers` as em ON u.`ID` = em.`FK_User_ID` " .
                               "WHERE (uf.`FK_User_ID_ThisUser` = :userID) " . $eventWhereClause .
                               "ORDER BY CASE WHEN ((u.`FirstName` IS NOT NULL) AND (LENGTH(u.`FirstName`) > 0)) THEN u.`FirstName` ELSE u.`UserName` END, " .
                               "CASE WHEN ((u.`FirstName` IS NOT NULL) AND (LENGTH(u.`FirstName`) > 0)) THEN u.`LastName` ELSE u.`UserName` END;";
        
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
        // If this is called for existing event, apply styles and call JS for modal dialog instance of this editor
        $additionalCSS = "";
        $formName = "eventCreateForm";
        $formButtonName = "createEventBtnMobile";
        $formButtonText = "Create Event!";
        $gameDateValue = "";
        $gameTimeValue = "";
        $eventInfo = null;
	$eventEditorLineBreak = "";
        
        if(strlen($eventId) > 0) {
            $additionalCSS = "class='box style1'";
            $formName = "eventEditForm"  . $eventId;
            $formButtonName = "editEventBtn" . $eventId;
            $formButtonText = "Update Event";
            $eventEditorLineBreak = "<br />";
            
            // Load information about this event
            $eventArray = $this->GetUserScheduledGames($dataAccess, $logger, $userID, "DisplayDate ASC", 
                                                       false, "0", "10", $eventId, true);
            if(count($eventArray) > 0) {
                $eventInfo = $eventArray[0];
                $gameDateValue = 'value="' . $eventInfo->ScheduledDate . '" ';
                $gameTimeValue = 'value="' . $eventInfo->ScheduledTime . '" ';
            }
            else {
                $logger->LogError("Event " . $eventId . " could not be loaded for editing");
                return "ERROR: Event not found";
            }
        }
        
	// Build game-players-needed selector
	$gamePlayersNeededSelect = '<select id="gamePlayersNeeded' . $eventId . '" name="gamePlayersNeeded' . $eventId . '" >';
	$selected = '';
		
	for($i = 1; $i < 65; $i++) {
            $selected = '';
            if($eventInfo != null) {
                if($i == $eventInfo->RequiredPlayersCount)  $selected = 'selected="true"';
            }
            else if($i == 2) {
                $selected = 'selected="true"';
            }
			
            $gamePlayersNeededSelect .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
	}
		
	$gamePlayersNeededSelect .= '</select></p>';
		
	// Build friends list selector
	$privateEventOptionDisabled = " disabled";
        $privateEventOptionChecked = "";
	$userFriendList = $this->LoadUserFriends($dataAccess, $logger, $userID);
	$friendListSelect = 'Your friends list is empty';
		
	if(count($userFriendList) > 0){
            $privateEventOptionDisabled = "";
            $friendListSelect = '';
            if(($eventInfo != null) && (!$eventInfo->IsPublicGame)) {
                $privateEventOptionChecked = "checked='checked'";
            }
            
            foreach($userFriendList as $userFriend) {
		$userDisplayName = (strlen($userFriend->FirstName) == 0) ? $userFriend->UserName : $userFriend->FirstName . ' ' . $userFriend->LastName;
				
		$userFriendSelected = '';
		if(($eventInfo != null) && (Utils::SearchUserArrayByID($eventInfo->FriendsAllowed, $userFriend->UserID) !== null)) {
                    $userFriendSelected = "checked='checked'";
		}
				
		$userFriendDisabled = ' disabled';
		if(($eventInfo != null) && (!$eventInfo->IsPublicGame)) {
                    $userFriendDisabled = '';
		}
				
		$friendListSelect .= '<input type="checkbox" name="pvtEventFriends' . $eventId . '[]" value="' . $userFriend->UserID . '" ' . $userFriendSelected . 
                                     ' ' . $userFriendDisabled . ' /> ' . $userDisplayName . '</br>';
            }
	}
		
	// Return Event Scheduler HTML
	return
            '<section ' . $additionalCSS . '>'.
		'<form id="' . $formName . '" name="' . $formName . '" method="POST" action="">'.
                    '<div class="inputLine">'.
			'<p><i class="fa fa-gamepad"></i> &nbsp; What game do you wish to schedule?<br/>'.
			'<div id="gameSelectorDiv' . $eventId . '">'.
                            $this->ConstructGameTitleSelectorHTML($dataAccess, $logger, $userID, $eventId, (($eventInfo != null) ? $eventInfo->Name : '')) .
			'</div><br />'.
			'<p><i class="fa fa-calendar-o"></i> &nbsp; Tell us a date<br/>'.
                            '<input id="gameDate' . $eventId . '" name="gameDate' . $eventId . '" ' . $gameDateValue .
                            'type="text" maxlength="50" placeholder=" Date"></p>'.
			'<p><i class="fa fa-clock-o"></i> &nbsp; Time you want to play<br/>'.
                            '<input id="gameTime' . $eventId . '" name="gameTime' . $eventId . '" ' . $gameTimeValue .
                            'type="text" maxlength="9" placeholder=" Time">&nbsp;&nbsp;'. $eventEditorLineBreak .
                            $this->GetTimezoneList($dataAccess, (($eventInfo != null) ? $eventInfo->ScheduledTimeZoneID : -1), $eventId) .
                        '</p>'.
			'<p><i class="fa fa-user"></i> &nbsp; Total number of players needed<br/>'. 
                            $gamePlayersNeededSelect .
                        '<p><i class="fa fa-gamepad"></i> &nbsp; Choose a platform for this game<br/>'. 
                            $this->GetPlatformDropdownList($dataAccess, (($eventInfo != null) ? $eventInfo->SelectedPlatformID : -1), $eventId) .
                        '</p>'.
			'<p><i class="fa fa-comments-o"></i> &nbsp; Notes about your event<br/>'.
                            '<textarea name="message' . $eventId . '" id="message' . $eventId .
                            '" placeholder=" exp: Looking for some new team mates to play through Rocket Leagues 3v3 mode. Must have a mic!" rows="6" required>'.
                            (($eventInfo != null) ? ($eventInfo->Notes) : '') . '</textarea>'.
                        '</p>'.
			'<p><i class="fa fa-lock"></i> &nbsp; Only allow friends to join this event</p>'.
                        '<input type="checkbox" id="privateEvent' . $eventId . '" name="privateEvent' . $eventId . '" value="private" ' . 
                            $privateEventOptionChecked . ' ' . $privateEventOptionDisabled . 
                        '>Private Event</input>&nbsp;<input class="selectAllCheckbox" type="checkbox" id="selectAllFriends' . $eventId . '" value="all" ' . 
                            ((($eventId === 0) || (!$privateEventOptionChecked)) ? 'disabled ' : '') . '>Select all</input>'.
                        '<div class="fixedHeightScrollableContainer">'. 
                            $friendListSelect .
                        '</div>'.
                        '<br /><br /><button type="submit" class="memberHomeBtn icon fa-cogs" id="' . $formButtonName . '">' . $formButtonText . '</button><br/>'.
                    '</div>'.
		'</form>'.
            '</section>';
    }
	
    public function ConstructGameTitleSelectorHTML($dataAccess, $logger, $userID, $eventId, $selectedGameTitle = "")
    {
        $gameSelector = '<select id="ddlGameTitles' . $eventId . '" name="ddlGameTitles' . $eventId . '">';
        $userGames = $this->LoadUserGames($dataAccess, $logger, $userID);

        foreach($userGames as $userGame) {
            $class = "userGameOption";
            $selected = "";
            if($userGame->IsGlobalGameTitle) {
                $class = "globalGameOption";
            }
            if((strlen($selectedGameTitle) > 0) && ($userGame->Name === $selectedGameTitle)) {
                $selected = '" selected="true';
            }

            $gameSelector .= '<option value="' . $userGame->GameID . $selected . '" class="' . $class . '">' . $userGame->Name . '</option>';
        }
        $gameSelector .= '</select>';

        return $gameSelector;
    }
	
    public function JTableEventManagerLoad($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, 
                                           $pageSize, $showHiddenEvents)
    {
        $scheduledGames = $this->GetUserScheduledGames($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, 
                                                       $startIndex, $pageSize, -1, $showHiddenEvents);

        $rows = [];
        foreach($scheduledGames as $game) {
            $playersSignedUp = $this->GetEventAttendeeNames($dataAccess, $logger, $game->EventID);

            $row = array (
                "ID" => $game->EventID,
                "GameTitle" => $game->Name,
                "Platform" => $game->SelectedPlatformText,
                "DisplayDate" => $game->ScheduledDate,
                "DisplayTime" => $game->ScheduledTime . ' ' . $game->ScheduledTimeZoneText,
                "Notes" => $game->Notes,
                "PlayersSignedUp" => sprintf("%d (of %d)", count($playersSignedUp), $game->RequiredPlayersCount),
                "Edit" => '',
		"Hidden" => !$game->Visible ? "hidden" : ""
            );

            array_push($rows, $row);
        }

        $jTableResult = [];
        $jTableResult['Result'] = 'OK';
        $jTableResult['TotalRecordCount'] = $this->GetTotalCountUserScheduledGames($dataAccess, $logger, $userID);
        $jTableResult['Records'] = $rows;
        return json_encode($jTableResult);
    }
	
    public function EventEditorCreateEvent($dataAccess, $logger, $userID, $eventGame)
    {
        $gameIDCol = "`FK_UserGames_ID`";
        $genreID = NULL;
        $genreParamType = PDO::PARAM_NULL;
		
	// If user entered a game title that doesn't currently exist in the system,
	// add it to the UserGames table and retrieve the inserted ID
        if(!$eventGame->IsExistingTitle) {
            // Wrap UserGames, Events, and EventMembers inserts in transaction
            if($dataAccess->BeginTransaction()) {
		$eventGame->GameID = $this->AddNewGameToUserGameList($dataAccess, $logger, $userID, $eventGame->Name);
            }
            else {
		$logger->LogError("Could not begin transaction to add user game title to UserGames. Exception: " . $dataAccess->CheckErrors());
            }
        }
        else if($eventGame->IsGlobalGameTitle) {
            $gameIDCol = "`FK_Game_ID`";
            
            // If user selected a system game title, get the genre associated with this title (if available)
            $genreID = $this->GetGameTitleAssociatedGenre($dataAccess, $logger, $eventGame->GameID);
        }
        
        if(($genreID != NULL) && ($genreID >= 0)) {
            $genreParamType = PDO::PARAM_INT;
        }
		
	// Format display time: convert to military time (24-hr clock) for DB storage
	$eventGame->ScheduledTime = Utils::ConvertStandardTimeToMilitaryTime($eventGame->ScheduledTime);
        
        // Build query to create event
        $createEventQuery = "INSERT INTO `Gaming.Events` (`FK_User_ID_EventCreator`," . $gameIDCol .", `FK_Genre_ID`," .
                            "`FK_Platform_ID`, `FK_Timezone_ID`, `EventCreatedDate`, `EventModifiedDate`, `EventScheduledForDate`," .
                            "`RequiredMemberCount`, `IsActive`, `IsPublic`,`Notes`, `DisplayDate`, `DisplayTime`) " .
                            "VALUES (:FKUserEventCreator,:FKGameId,:FKGenreID,:FKPlatformID,:FKTimezoneID,SYSDATE()," . 
                            "SYSDATE(),:EventScheduledForDate,:RequiredMemberCount,1,:IsPublic,:notes, :displayDate, :displayTime);";
		
	$parmEventCreatorUserId = new QueryParameter(':FKUserEventCreator', $userID, PDO::PARAM_INT);
	$parmGameId = new QueryParameter(':FKGameId', $eventGame->GameID, PDO::PARAM_INT);
        $parmGenreId = new QueryParameter(':FKGenreID', $genreID, $genreParamType);
        $parmPlatformId = new QueryParameter(':FKPlatformID', $eventGame->SelectedPlatformID, PDO::PARAM_INT);
        $parmTimezoneId = new QueryParameter(':FKTimezoneID', $eventGame->ScheduledTimeZoneID, PDO::PARAM_INT);
        $parmEventScheduledForDate = new QueryParameter(':EventScheduledForDate', $eventGame->ScheduledDateUTC, PDO::PARAM_STR);
        $parmRequiredMemberCount = new QueryParameter(':RequiredMemberCount', $eventGame->RequiredPlayersCount, PDO::PARAM_INT);
        $parmIsPublicEvent = new QueryParameter(':IsPublic', $eventGame->IsPublicGame ? 1 : 0, PDO::PARAM_INT);
        $parmNotes = new QueryParameter(':notes', $eventGame->Notes, PDO::PARAM_STR);
	$parmDisplayDate = new QueryParameter(':displayDate', $eventGame->ScheduledDate, PDO::PARAM_STR);
	$parmDisplayTime = new QueryParameter(':displayTime', $eventGame->ScheduledTime, PDO::PARAM_STR);
        
	$queryParms = array($parmEventCreatorUserId, $parmGameId, $parmGenreId, $parmPlatformId, $parmTimezoneId, $parmEventScheduledForDate,
                            $parmRequiredMemberCount, $parmIsPublicEvent, $parmNotes, $parmDisplayDate, $parmDisplayTime);
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) == 0) {
            if(!$dataAccess->CheckIfInTransaction())
            {
		if(!$dataAccess->BeginTransaction()) {
                    $errors .= "Could not begin transaction...unable to create event";
		}
            }
				
            $errors .= $dataAccess->CheckErrors();
			
            if(strlen($errors) == 0) {
                if($dataAccess->BuildQuery($createEventQuery, $queryParms)){
                    $dataAccess->ExecuteNonQuery();

                    // Insert current user as first joined member for the new event
                    $eventID = $dataAccess->GetLastInsertId();
                    $joinedUsers = [ $userID ];

                    if($this->AddUsersToEvent($dataAccess, $logger, $eventID, $joinedUsers))
                    {
			// Add allowed users for this event, if a private event
			$addedAllowedUsersToEvent = true;
			if(!$eventGame->IsPublicGame) {
                            $addedAllowedUsersToEvent = $this->AddAllowedUsersToEvent($dataAccess, $logger, $eventID, $eventGame->FriendsAllowed);
			}
						
			if($addedAllowedUsersToEvent) {
                            if($dataAccess->CommitTransaction())    return "true";
                            else                                    $errors .= "Could not commit transaction...rolling back";
			}
                    }
                }
            }
        }
	
        // Failed to add new game title, create event, add initial event member, or add allowed event members -- roll back everything
        if($dataAccess->CheckIfInTransaction())
        {
            $dataAccess->RollbackTransaction();
        }
	
        $logger->LogError("Could not schedule event for game '" . $eventGame->Name . "'. " . $errors);
	return "System Error: Could not schedule event for game '" . $eventGame->Name . "'. Please try again later";
    }

    private function GetGameTitleAssociatedGenre($dataAccess, $logger, $gameID)
    {
        $getGameGenreQuery = "SELECT `FK_Genre_ID` FROM `Configuration.GameGenres` " .
                             "WHERE `FK_Game_ID` = :gameID;";
        
        $parmGameId = new QueryParameter(':gameID', $gameID, PDO::PARAM_INT);
        $queryParms = array($parmGameId);
        $genreID = -1;
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getGameGenreQuery, $queryParms)){
		$results = $dataAccess->GetSingleResult();
					
		if($results != null){
                    $genreID = $results['FK_Genre_ID'];
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve genre associated with game ID " . $gameID . ". " . $errors);
	}
        
        return $genreID;
    }

    public function EventEditorUpdateEvent($dataAccess, $logger, $userID, $eventGame)
    {
        $gameIDCol = "`FK_UserGames_ID`";
        $genreID = NULL;
        $genreParamType = PDO::PARAM_NULL;
		
	// If user entered a game title that doesn't currently exist in the system,
	// add it to the UserGames table and retrieve the inserted ID
        if(!$eventGame->IsExistingTitle) {
            // Wrap UserGames, Events, and EventMembers inserts in transaction
            if($dataAccess->BeginTransaction()) {
		$eventGame->GameID = $this->AddNewGameToUserGameList($dataAccess, $logger, $userID, $eventGame->Name);
            }
            else {
		$logger->LogError("Could not begin transaction to add user game title to UserGames. Exception: " . $dataAccess->CheckErrors());
            }
        }
        else if($eventGame->IsGlobalGameTitle) {
            $gameIDCol = "`FK_Game_ID`";
            
            // If user selected a system game title, get the genre associated with this title (if available)
            $genreID = $this->GetGameTitleAssociatedGenre($dataAccess, $logger, $eventGame->GameID);
        }
        
        if(($genreID != NULL) && ($genreID >= 0)) {
            $genreParamType = PDO::PARAM_INT;
        }
		
	// Format display time: convert to military time (24-hr clock) for DB storage
	$eventGame->ScheduledTime = Utils::ConvertStandardTimeToMilitaryTime($eventGame->ScheduledTime);
        
        // Build query to update event
        $updateEventQuery = "UPDATE `Gaming.Events` SET " .
                            "`FK_User_ID_EventCreator`=:FKUserEventCreator," . $gameIDCol . "=:FKGameId," .
                            "`FK_Genre_ID`=:FKGenreID,`FK_Platform_ID`=:FKPlatformID,`FK_Timezone_ID`=:FKTimezoneID," .
                            "`EventModifiedDate`=SYSDATE(),`EventScheduledForDate`=:EventScheduledForDate," .
                            "`RequiredMemberCount`=:RequiredMemberCount,`IsActive`=1,`IsPublic`=:IsPublic," .
                            "`Notes`=:notes,`DisplayDate`=:displayDate,`DisplayTime`=:displayTime " .
                            "WHERE `ID`=:eventId;";
		
	$parmEventCreatorUserId = new QueryParameter(':FKUserEventCreator', $userID, PDO::PARAM_INT);
	$parmGameId = new QueryParameter(':FKGameId', $eventGame->GameID, PDO::PARAM_INT);
        $parmGenreId = new QueryParameter(':FKGenreID', $genreID, $genreParamType);
        $parmPlatformId = new QueryParameter(':FKPlatformID', $eventGame->SelectedPlatformID, PDO::PARAM_INT);
        $parmTimezoneId = new QueryParameter(':FKTimezoneID', $eventGame->ScheduledTimeZoneID, PDO::PARAM_INT);
        $parmEventScheduledForDate = new QueryParameter(':EventScheduledForDate', $eventGame->ScheduledDateUTC, PDO::PARAM_STR);
        $parmRequiredMemberCount = new QueryParameter(':RequiredMemberCount', $eventGame->RequiredPlayersCount, PDO::PARAM_INT);
        $parmIsPublicEvent = new QueryParameter(':IsPublic', $eventGame->IsPublicGame ? 1 : 0, PDO::PARAM_INT);
        $parmNotes = new QueryParameter(':notes', $eventGame->Notes, PDO::PARAM_STR);
	$parmDisplayDate = new QueryParameter(':displayDate', $eventGame->ScheduledDate, PDO::PARAM_STR);
	$parmDisplayTime = new QueryParameter(':displayTime', $eventGame->ScheduledTime, PDO::PARAM_STR);
        $parmEventId = new QueryParameter(':eventId', $eventGame->EventID, PDO::PARAM_INT);
        
	$queryParms = array($parmEventCreatorUserId, $parmGameId, $parmGenreId, $parmPlatformId, $parmTimezoneId, $parmEventScheduledForDate,
                            $parmRequiredMemberCount, $parmIsPublicEvent, $parmNotes, $parmDisplayDate, $parmDisplayTime, $parmEventId);
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) == 0) {
            if(!$dataAccess->CheckIfInTransaction())
            {
		if(!$dataAccess->BeginTransaction()) {
                    $errors .= "Could not begin transaction...unable to create event";
		}
            }
				
            $errors .= $dataAccess->CheckErrors();
			
            if(strlen($errors) == 0) {
                if($dataAccess->BuildQuery($updateEventQuery, $queryParms)){
                    $dataAccess->ExecuteNonQuery();

                    // Update allowed users for this event, if a private event
                    $updatedAllowedUsersForEvent = true;
                    if(!$eventGame->IsPublicGame) {
                        $replaceWithCurrentSet = true;
                        $updatedAllowedUsersForEvent = $this->AddAllowedUsersToEvent($dataAccess, $logger, $eventGame->EventID, 
                                                                                     $eventGame->FriendsAllowed, $replaceWithCurrentSet);
                    }

                    if($updatedAllowedUsersForEvent) {
                        if($dataAccess->CommitTransaction())    return "true";
                        else                                    $errors .= "Could not commit transaction...rolling back";
                    }
                }
            }
        }
	
        // Failed to add new game title, update event, and/or update allowed event members -- roll back everything
        if($dataAccess->CheckIfInTransaction())
        {
            $dataAccess->RollbackTransaction();
        }
	
        $logger->LogError("Could not update event for game '" . $eventGame->Name . "'. " . $errors);
	return "System Error: Could not update event for game '" . $eventGame->Name . "'. Please try again later";
    }
    
    private function AddNewGameToUserGameList($dataAccess, $logger, $userID, $gameName)
    {
        $addNewUserGameQuery = "INSERT INTO `Gaming.UserGames` (`FK_User_ID`,`Name`) " .
                               "VALUES (:FKUserId, :gameName);";
		
	$parmUserId = new QueryParameter(':FKUserId', $userID, PDO::PARAM_INT);
	$parmGameName = new QueryParameter(':gameName', $gameName, PDO::PARAM_STR);
	$queryParms = array($parmUserId, $parmGameName);
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($addNewUserGameQuery, $queryParms)){
		$dataAccess->ExecuteNonQuery();
            }
				
            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
		return $dataAccess->GetLastInsertId();
            }
	}
	
        $logger->LogError("Could not add new user game title '". $gameName . "' for userID " . $userID . ". " . $errors);
	return -1;
    }
    
    public function AddUsersToEvent($dataAccess, $logger, $eventID, $joinedUsers)
    {
        $addUsersToEventQuery = "INSERT INTO `Gaming.EventMembers` (`FK_Event_ID`,`FK_User_ID`) VALUES ";
		
        $valuesClauseFormat = "(%s, %s)";
        $eventParmNameFormat = ":eventId%d";
        $userParmNameFormat = ":FKUserId%d";
        $queryParms = [];

        // Build insert statement
        for($i = 0; $i < count($joinedUsers); $i++) {
            $valuesClauseSuffix = ", ";
            if($i === (count($joinedUsers) - 1)) {
                $valuesClauseSuffix = ";";
            }

            $eventParmName = sprintf($eventParmNameFormat, $i);
            $userParmName = sprintf($userParmNameFormat, $i);
            $addUsersToEventQuery .= (sprintf($valuesClauseFormat, $eventParmName, $userParmName) . $valuesClauseSuffix);

            $parmEventId = new QueryParameter($eventParmName, $eventID, PDO::PARAM_INT);
            $parmUserId = new QueryParameter($userParmName, $joinedUsers[$i], PDO::PARAM_INT);
            array_push($queryParms, $parmEventId, $parmUserId);
        }
		
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($addUsersToEventQuery, $queryParms)){
		$dataAccess->ExecuteNonQuery();
            }
				
            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
		return true;
            }
	}
	
        $logger->LogError("Could not add users to new event [ID = " . $eventID . "]. " . $errors);
	return false;
    }
	
    public function AddAllowedUsersToEvent($dataAccess, $logger, $eventID, $allowedUsers, $replaceWithCurrentSet = false)
    {
        $errors = "";
        if($replaceWithCurrentSet) {
            $deleteQuery = "DELETE FROM `Gaming.EventAllowedMembers` WHERE `FK_Event_ID`=:eventId;";
            $parmEventId = new QueryParameter(':eventId', $eventID, PDO::PARAM_INT);
            $deleteQueryParms = array($parmEventId);
            
            $errors = $dataAccess->CheckErrors();
            if(strlen($errors) == 0) {
                if($dataAccess->BuildQuery($deleteQuery, $deleteQueryParms)){
                    $dataAccess->ExecuteNonQuery();
                }

                $errors = $dataAccess->CheckErrors();

                if(strlen($errors) > 0) {
                    $logger->LogError("Could not update allowed users for event [ID = " . $eventID . "]. " . $errors);
                    return false;
                }
            }
        }
        
        $addAllowedUsersToEventQuery = "INSERT INTO `Gaming.EventAllowedMembers` (`FK_Event_ID`,`FK_User_ID`) VALUES ";
		
        $valuesClauseFormat = "(%s, %s)";
        $eventParmNameFormat = ":eventId%d";
        $userParmNameFormat = ":FKUserId%d";
        $queryParms = [];

        // Build insert statement
        for($i = 0; $i < count($allowedUsers); $i++) {
            $valuesClauseSuffix = ", ";
            if($i === (count($allowedUsers) - 1)) {
                $valuesClauseSuffix = ";";
            }

            $eventParmName = sprintf($eventParmNameFormat, $i);
            $userParmName = sprintf($userParmNameFormat, $i);
            $addAllowedUsersToEventQuery .= (sprintf($valuesClauseFormat, $eventParmName, $userParmName) . $valuesClauseSuffix);

            $parmEventId = new QueryParameter($eventParmName, $eventID, PDO::PARAM_INT);
            $parmUserId = new QueryParameter($userParmName, $allowedUsers[$i], PDO::PARAM_INT);
            array_push($queryParms, $parmEventId, $parmUserId);
        }
		
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($addAllowedUsersToEventQuery, $queryParms)){
		$dataAccess->ExecuteNonQuery();
            }
				
            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
		return true;
            }
	}
	
        $logger->LogError("Could not add allowed users to new event [ID = " . $eventID . "]. " . $errors);
	return false;
    }
	
    public function EventEditorToggleEventVisibility($dataAccess, $logger, $eventID, $isActive)
    {
	$updateSuccess = false;
	$eventToggleName = "hidden";
	if($isActive === "1") {
            $eventToggleName = "visible";
	}
		
	$updateEventQuery = "UPDATE `Gaming.Events` SET `IsActive` = :isActive WHERE `ID` = :eventId;";
				
	$parmIsActive = new QueryParameter(':isActive', $isActive, PDO::PARAM_INT);
	$parmEventId = new QueryParameter(':eventId', $eventID, PDO::PARAM_INT);
	$queryParms = array($parmIsActive, $parmEventId);
			
	$errors = $dataAccess->CheckErrors();
			
	if(strlen($errors) == 0) {
            try {				
		if($dataAccess->BuildQuery($updateEventQuery, $queryParms)){
                    $updateSuccess = $dataAccess->ExecuteNonQuery();
		}

		$errors = $dataAccess->CheckErrors();
            }
            catch(Exception $e) {
		$logger->LogError("Could not update visibility for event ID '" . $eventID . "'. Exception: " . $e->getMessage());
            }
	}
			
	if(!$updateSuccess) {
            $logger->LogError("Could not update visibility for event ID '" . $eventID . "'. " . $errors);
	}
				
	return $updateSuccess ? "SUCCESS: Updated event to be " . $eventToggleName : "SYSTEM ERROR: Could not make event " . eventToggleName . ". Please try again later.";
    }
	
    public function GetTimezoneList($dataAccess, $selectedTimeZoneID, $eventId = '')
    {
        $timeZoneListId = ((strlen($eventId) == 0) ? 'ddlTimeZones' : ('ddlTimeZones' . $eventId));
        
        $timeZoneQuery = "SELECT `ID`, `Description` FROM `Configuration.TimeZones` ORDER BY `SortOrder`;";
        $ddlTimeZonesHTML = "";
        $ddlTimeZonesErrorHTML = "<select id='" . $timeZoneListId . "' name='" . $timeZoneListId . 
                                 "'><option value='-1'>Cannot load time zones, please try later</option></select><br/><br/>";

        $errors = $dataAccess->CheckErrors();
        
        if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($timeZoneQuery)){
                $results = $dataAccess->GetResultSet();
                if($results != null){
                    $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<select id='" . $timeZoneListId . "' name='" . $timeZoneListId . "'>";
                    foreach($results as $row){
                        if($row['ID'] == $selectedTimeZoneID) {
                            $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<option value='" . $row['ID'] . "' selected='true'>" . $row['Description'] . "</option>";
                        }
                        else {
                            $ddlTimeZonesHTML = $ddlTimeZonesHTML . "<option value='" . $row['ID'] . "'>" . $row['Description'] . "</option>";
                        }
                    }
                    $ddlTimeZonesHTML = $ddlTimeZonesHTML . "</select>";
                }
            }
        }

        $errors = $dataAccess->CheckErrors();
        if(strlen($errors) == 0) {
            return $ddlTimeZonesHTML;
        }
        else { 
            return $ddlTimeZonesErrorHTML;
        }
    }
    
    public function GetPlatformCheckboxList($dataAccess, $selectedPlatforms)
    {
        $platformQuery = "SELECT `ID`, `Name` FROM `Configuration.Platforms` ORDER BY `Name`;";
        $ddlPlatformsHTML = "";
        $ddlPlatformsErrorHTML = "<p>Cannot load console list, please try later</p>";

        $errors = $dataAccess->CheckErrors();
        
        if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($platformQuery)){
                $results = $dataAccess->GetResultSet();
                if($results != null){
                    foreach($results as $row){
                        $selected = "";
                        if(in_array($row['ID'], $selectedPlatforms)) {
                            $selected = "checked='checked'";
                        }

                        $ddlPlatformsHTML = $ddlPlatformsHTML . "<input type='checkbox' name='platforms[]' " . 
                                            $selected . " value='" . $row['ID'] . "'>" . $row['Name'] . "</input><br/>";
                    }
                }
            }
        }

        $errors = $dataAccess->CheckErrors();
        if(strlen($errors) == 0) {
            return $ddlPlatformsHTML;
        }
        else { 
            return $ddlPlatformsErrorHTML;
        }
    }
    
    public function GetPlatformDropdownList($dataAccess, $selectedPlatform, $eventId = '')
    {
        $platformListId = ((strlen($eventId) == 0) ? 'ddlPlatforms' : ('ddlPlatforms' . $eventId));
        
        $platformQuery = "SELECT `ID`, `Name` FROM `Configuration.Platforms` ORDER BY `Name`;";
        $ddlPlatformsHTML = "";
        $ddlPlatformsErrorHTML = "<select id='" . $platformListId . "' name='" . $platformListId . 
                                 "'><option value='-1'>Cannot load console list, please try later</option></select><br/><br/>";

        $errors = $dataAccess->CheckErrors();
        
        if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($platformQuery)){
                $results = $dataAccess->GetResultSet();
                if($results != null){
                    $ddlPlatformsHTML .= "<select id='" . $platformListId . "' name='" . $platformListId . "'>";
                    foreach($results as $row){
                        if($row['ID'] == $selectedPlatform) {
                            $ddlPlatformsHTML .= "<option value='" . $row['ID'] . "' selected='true'>" . $row['Name'] . "</option>";
                        }
                        else {
                            $ddlPlatformsHTML .= "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
                        }
                    }
                    $ddlPlatformsHTML .= "</select>";
                }
            }
        }

        $errors = $dataAccess->CheckErrors();
        if(strlen($errors) == 0) {
            return $ddlPlatformsHTML;
        }
        else { 
            return $ddlPlatformsErrorHTML;
        }
    }
    
    private function TranslateOrderByToQueryOrderByClause($orderBy)
    {
        $queryOrderByClause = "";
        $orderByColumn = "";
        $orderByDirection = "";

        sscanf($orderBy, "%s %s", $orderByColumn, $orderByDirection);

        // Always sort by event scheduled date at minimum, or after game name or platform 
        //  when there are games of same name or same platform
        switch($orderByColumn) {
            case "GameTitle":
                $queryOrderByClause = "(COALESCE(cg.`Name`, ug.`Name`)) " . $orderByDirection . ", e.`EventScheduledForDate`";
                break;
            case "Platform":
                $queryOrderByClause = "p.`Name` " . $orderByDirection . ", e.`EventScheduledForDate`";
                break;
            case "DisplayDate":
                $queryOrderByClause = "e.`EventScheduledForDate` " . $orderByDirection;
                break;
        }

        return $queryOrderByClause;
    }
	
    public function GetUserScheduledGames($dataAccess, $logger, $userID, $orderBy = "DisplayDate ASC", 
                                          $paginationEnabled = false, $startIndex = "0", $pageSize = "10", $eventId = -1, $showHiddenEvents = false)
    {
	$limitClause = "";
	if($paginationEnabled) {
            $limitClause = "LIMIT " . $startIndex . "," . $pageSize;
	}
        
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
        
        $eventWhereClause = "";
        $parmEventId = null;
	if($eventId > -1) {
            $eventWhereClause .= "AND (e.`ID` = :eventId) ";
            $parmEventId = new QueryParameter(':eventId', $eventId, PDO::PARAM_INT);
            array_push($queryParms, $parmEventId);
        }
        
        $parmIsActive = null;
        if(!$showHiddenEvents) {
            $eventWhereClause .= "AND (e.`IsActive` = :isActive) ";
            $parmIsActive = new QueryParameter(':isActive', 1, PDO::PARAM_INT);
            array_push($queryParms, $parmIsActive);
        }
        
	$orderByClause = "ORDER BY " . $this->TranslateOrderByToQueryOrderByClause($orderBy);
		
        $getUserGamesQuery = "SELECT e.`ID`, COALESCE(cg.`Name`, ug.`Name`) AS GameTitle, COALESCE(tz.`Abbreviation`, tz.`Description`) AS TimeZone, " .
                             "e.`EventScheduledForDate`, e.`DisplayDate`, e.`DisplayTime`, e.`RequiredMemberCount`, p.`Name` AS Platform, e.`Notes`, " . 
                             "e.`FK_Game_ID` AS GameID, tz.`ID` AS TimezoneID, p.`ID` AS PlatformID, e.`IsPublic`, e.`IsActive` " .
                             "FROM `Gaming.Events` AS e ".
                             "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
                             "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
                             "LEFT JOIN `Configuration.Games` AS cg ON cg.`ID` = e.`FK_Game_ID` ".
                             "LEFT JOIN `Gaming.UserGames` AS ug ON ug.`ID` = e.`FK_UserGames_ID` ".
                             "WHERE (e.`FK_User_ID_EventCreator` = :userID) " . $eventWhereClause .
                             "AND (e.`EventScheduledForDate` > UTC_TIMESTAMP()) " . // Only show future events
                             $orderByClause . " " . $limitClause . ";";
        
	$userGames = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserGamesQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
			// Get user friends allowed to sign up for this event, if it's private
			$eventAllowedFriends = [];
			if($row['IsPublic'] == 0) {
                            $eventAllowedFriends = $this->LoadUserFriends($dataAccess, $logger, $userID, $row['ID']);
			}
						
			// Show event scheduled date to user in standard time format, with no seconds shown, and AM/PM indicator
			$displayTime = Utils::ConvertMilitaryTimeToStandardTime($row['DisplayTime']);
						
                        $userGame = Game::ConstructGameForEvent($row['GameID'], $row['DisplayDate'], $displayTime, $row['RequiredMemberCount'], $row['Notes'], $eventAllowedFriends, 
                                                                false, $row['TimezoneID'], $row['PlatformID'], $row['GameTitle'], $row['EventScheduledForDate'], $row['Platform'], 
                                                                $row['TimeZone'], $row['ID'], $row['IsActive'] == '1' ? true : false);
			array_push($userGames, $userGame);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve user scheduled games. " . $errors);
	}
        
        return $userGames;
    }
	
    private function GetTotalCountUserScheduledGames($dataAccess, $logger, $userID)
    {
	$userTotalScheduledGamesCnt = 0;
        $getUserGamesQuery = "SELECT COUNT(e.`ID`) AS Cnt " .
                             "FROM `Gaming.Events` AS e ".
                             "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
                             "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
                             "WHERE (e.`FK_User_ID_EventCreator` = :userID) " .
                             "AND (e.`IsActive` = 1) " .
                             "AND (e.`EventScheduledForDate` > UTC_TIMESTAMP());";
        
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
	$userGames = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserGamesQuery, $queryParms)){
		$results = $dataAccess->GetSingleResult();
					
		if($results != null){
                    $userTotalScheduledGamesCnt = $results['Cnt'];
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve count of user scheduled games. " . $errors);
	}
        
        return $userTotalScheduledGamesCnt;
    }
    
    public function GetEventAttendeeNames($dataAccess, $logger, $eventID)
    {
        $getEventMembersQuery = "SELECT u.`UserName`, CASE WHEN e.`FK_User_ID_EventCreator` = em.`FK_User_ID` THEN ' (Creator)' ELSE '' END AS EventCreator " .
				"FROM `Gaming.EventMembers` AS em " .
                                "INNER JOIN `Security.Users` AS u ON em.`FK_User_ID` = u.`ID` " .
				"INNER JOIN `Gaming.Events` AS e ON e.`ID` = em.`FK_Event_ID` " .
                                "WHERE em.`FK_Event_ID` = :eventID " .
				"ORDER BY u.`UserName`;";
        
        $parmEventId = new QueryParameter(':eventID', $eventID, PDO::PARAM_INT);
        $queryParms = array($parmEventId);
	$userNames = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getEventMembersQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
			array_push($userNames, $row['UserName'] . $row['EventCreator']);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve attendees for event " . $eventID . ". " . $errors);
	}
        
        return $userNames;
    }
}
