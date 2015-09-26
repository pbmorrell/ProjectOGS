<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/User.class.php';
include_once 'classes/Game.class.php';
include_once 'classes/EventMember.class.php';
include_once 'classes/Utils.class.php';

class GamingHandler
{    
    public function LoadUserFriends($dataAccess, $logger, $userID, $eventId = -1)
    {
	$eventWhereClause = "";
        $eventLeftJoinClause = "";
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
	$parmEventId = null;
	$queryParms = array($parmUserId);
		
	if($eventId > -1) {
            $eventLeftJoinClause = "LEFT JOIN `Gaming.EventAllowedMembers` as em ON u.`ID` = em.`FK_User_ID` ";
            $eventWhereClause = "AND ((COALESCE(em.`FK_Event_ID`, -1)) = :eventId) ";
            $parmEventId = new QueryParameter(':eventId', $eventId, PDO::PARAM_INT);
            array_push($queryParms, $parmEventId);
	}
		
        $getUserFriendsQuery = "SELECT uf.`FK_User_ID_Friend` as FriendID, u.`FirstName`, u.`LastName`, u.`UserName` FROM `Gaming.UserFriends` as uf " .
                               "INNER JOIN `Security.Users` as u ON uf.`FK_User_ID_Friend` = u.`ID` " . $eventLeftJoinClause .
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
        // If this is called for existing event, append eventID to each control ID for uniqueness
        $formName = "eventCreateForm";
        $formButtonName = "createEventBtn";
        $formButtonText = "Create Event!";
        $editEventButtons = "";
        $gameDateValue = "";
        $gameTimeValue = "";
        $eventInfo = null;
        
        if(strlen($eventId) > 0) {
            $formName = "eventEditForm"  . $eventId;
            $formButtonName = "editEventBtn" . $eventId;
            $formButtonText = "Update Event";
            
            // Load information about this event
            $eventArray = $this->GetScheduledGames($dataAccess, $logger, $userID, "DisplayDate ASC", 
                                                   false, "0", "10", $eventId, true, 30);
            if(count($eventArray) > 0) {
                $eventInfo = $eventArray[0];
                $gameDateValue = 'value="' . $eventInfo->ScheduledDate . '" ';
                $gameTimeValue = 'value="' . $eventInfo->ScheduledTime . '" ';
                
                $btnVisibilityActionAttr = "1";
                $btnVisibilityText = "Enable Event";
                if($eventInfo->Visible) {
                    $btnVisibilityActionAttr = "0";
                    $btnVisibilityText = "Hide Event";
                }
                
                $editEventButtons = '<button class="memberHomeBtn" myAction="' . $btnVisibilityActionAttr . 
                                        '" id="toggleEventVisibilityBtn' . $eventId . '">' . $btnVisibilityText . '<br /><span class="icon fa-close" /></button>'.
                                    '<button class="memberHomeBtn" id="deleteEventBtn' . $eventId . '">Delete<br /><span class="icon fa-trash" /></button>';
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
            '<section class="box style1">'.
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
                            'type="text" maxlength="9" placeholder=" Time"><br />' .
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
                        '</div><br /><br />'.
                        '<div id="eventDialogToolbar' . $eventId . '">'.
                            '<button type="submit" class="memberHomeBtn" id="' . $formButtonName . '">' . $formButtonText . 
                                '<br /><span class="icon fa-cogs" /></button>'.
                            '<button class="memberHomeBtn" id="cancelEventBtn' . $eventId . '">Cancel<br /><span class="icon fa-thumbs-o-down" /></button>'.
                            $editEventButtons.
                        '</div>'.
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
                                           $pageSize, $showHiddenEvents, $showPastEventsDays)
    {
        $scheduledGames = $this->GetScheduledGames($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, 
                                                   $startIndex, $pageSize, -1, $showHiddenEvents, $showPastEventsDays);

        $rows = [];
        foreach($scheduledGames as $game) {
            $playersSignedUp = $game->EventMembers;
			
            $playersSignedUpData = "";
            foreach($playersSignedUp as $player) {
		$playersSignedUpData .= ($player->EventMemberId . "|" . $player->UserDisplayName . ",");
            }
            $playersSignedUpData = rtrim($playersSignedUpData, ",");

            $row = array (
                "ID" => $game->EventID,
                "GameTitle" => $game->Name,
                "Platform" => $game->SelectedPlatformText,
                "DisplayDate" => $game->ScheduledDate,
                "DisplayTime" => $game->ScheduledTime . ' ' . $game->ScheduledTimeZoneText,
                "Notes" => $game->Notes,
                "PlayersSignedUp" => sprintf("%d (of %d)", count($playersSignedUp), $game->RequiredPlayersCount),
		"PlayersSignedUpData" => $playersSignedUpData,
                "Edit" => '',
		"Hidden" => !$game->Visible ? 'Yes' : 'No'
            );

            array_push($rows, $row);
        }

        $jTableResult = [];
        $jTableResult['Result'] = 'OK';
        $jTableResult['TotalRecordCount'] = $this->GetTotalCountScheduledGames($dataAccess, $logger, $userID, $showHiddenEvents, $showPastEventsDays);
        $jTableResult['Records'] = $rows;
        return json_encode($jTableResult);
    }
	
    public function JTableCurrentEventViewerLoad($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, $pageSize)
    {
        $scheduledGames = $this->GetScheduledGames($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, 
                                                   $startIndex, $pageSize, -1, false, "-1", true);
	$totalScheduledGameCnt = $this->GetTotalCountScheduledGames($dataAccess, $logger, $userID, false, "-1", true);
	$totalGameCntNotJoined = $this->GetTotalCountUnjoinedScheduledGames($dataAccess, $logger, $userID);

        $rows = [];
        foreach($scheduledGames as $game) {
            $playersSignedUp = $game->EventMembers;
			
            $playersSignedUpData = "";
            foreach($playersSignedUp as $player) {
		$playersSignedUpData .= ($player->EventMemberId . "|" . $player->UserDisplayName . ",");
            }
            $playersSignedUpData = rtrim($playersSignedUpData, ",");

            $row = array (
                "ID" => $game->EventID,
		"TotalGamesToJoinCount" => $totalGameCntNotJoined,
		"UserName" => $game->EventCreatorUserName,
                "GameTitle" => $game->Name,
                "Platform" => $game->SelectedPlatformText,
                "DisplayDate" => $game->ScheduledDate,
                "DisplayTime" => $game->ScheduledTime . ' ' . $game->ScheduledTimeZoneText,
                "Notes" => $game->Notes,
                "PlayersSignedUp" => sprintf("%d (of %d)", count($playersSignedUp), $game->RequiredPlayersCount),
		"PlayersSignedUpData" => $playersSignedUpData,
		"Joined" => $game->JoinStatus
            );

            array_push($rows, $row);
        }

        $jTableResult = [];
        $jTableResult['Result'] = 'OK';
        $jTableResult['TotalRecordCount'] = $totalScheduledGameCnt;
        $jTableResult['Records'] = $rows;
        return json_encode($jTableResult);
    }
	
    public function LoadJoinedPlayersForEvent($dataAccess, $logger, $eventId)
    {
        $rows = [];
        $playersSignedUp = $this->GetEventMembers($dataAccess, $logger, $eventId);

        foreach($playersSignedUp as $player) {
            $row = array (
                "ID" => $player->EventMemberId,
                "PlayerName" => $player->UserDisplayName
            );

            array_push($rows, $row);
        }

        $jTableResult = [];
        $jTableResult['Result'] = 'OK';
        $jTableResult['TotalRecordCount'] = count($rows);
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
	
    public function AddUserToEvents($dataAccess, $logger, $userID, $eventIds)
    {
	$joinSuccess = false;
        $addUserToEventsQuery = "INSERT INTO `Gaming.EventMembers` (`FK_Event_ID`,`FK_User_ID`) VALUES ";
		
        $valuesClauseFormat = "(%s, %s)";
        $eventParmNameFormat = ":eventId%d";
        $userParmNameFormat = ":FKUserId%d";
        $queryParms = [];

        // Build insert statement
        for($i = 0; $i < count($eventIds); $i++) {
            $valuesClauseSuffix = ", ";
            if($i === (count($eventIds) - 1)) {
                $valuesClauseSuffix = ";";
            }

            $eventParmName = sprintf($eventParmNameFormat, $i);
            $userParmName = sprintf($userParmNameFormat, $i);
            $addUserToEventsQuery .= (sprintf($valuesClauseFormat, $eventParmName, $userParmName) . $valuesClauseSuffix);

            $parmEventId = new QueryParameter($eventParmName, $eventIds[$i], PDO::PARAM_INT);
            $parmUserId = new QueryParameter($userParmName, $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmEventId, $parmUserId);
        }
		
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($addUserToEventsQuery, $queryParms)){
		$dataAccess->ExecuteNonQuery();
            }
				
            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
		$joinSuccess = true;
            }
	}
		
	if(!$joinSuccess) {
            $logger->LogError("Could not add user [ID = " . $userID . "] to requested events. " . $errors);
	}
		
	return ($joinSuccess === true) ? ("SUCCESS: Joined requested events") : ("SYSTEM ERROR: Could not join requested events. Please try again later.");
    }
    
    public function RemoveUserFromEvents($dataAccess, $logger, $userID, $eventIDs)
    {		
	$deleteSuccess = false;
	$eventIdList = "";
	$removeUserFromEventsQuery = "";
	$errors = "";
		
	try {
            if(is_array($eventIDs)) {
		$eventIdList = implode(",", $eventIDs);
		$eventIdListForQuery = (str_repeat("?,", count($eventIDs) - 1)) . "?";
		$removeUserFromEventsQuery = "DELETE FROM `Gaming.EventMembers` WHERE (`FK_User_ID` = ?) AND (`FK_Event_ID` IN (" . $eventIdListForQuery . "));";
            }
            else {
		$errors = "Error when preparing remove-user-from-events query: event list is not an array";
            }
	}
	catch(Exception $e) {
            $errors = "Exception when preparing remove-user-from-events query: " . $e->getMessage();
	}
			
	if(strlen($errors) == 0) {
            try {				
		if($dataAccess->BuildQuery($removeUserFromEventsQuery)){
                    $positionalParms = [$userID];
                    $positionalParms = array_unique(array_merge($positionalParms, $eventIDs));
                    $deleteSuccess = $dataAccess->ExecuteNonQueryWithPositionalParms($positionalParms);
		}

		$errors = $dataAccess->CheckErrors();
            }
            catch(Exception $e) {
		$logger->LogError("Could not remove user " . $userID . " from events (" . $eventIdList . "). Exception: " . $e->getMessage());
            }
	}
			
	if(!$deleteSuccess) {
            $logger->LogError("Could not remove user " . $userID . " from events (" . $eventIdList . "). " . $errors);
	}
				
	return ($deleteSuccess === true) ? ("SUCCESS: Left requested events") : ("SYSTEM ERROR: Could not leave requested events. Please try again later.");
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
	
    public function EventEditorToggleEventVisibility($dataAccess, $logger, $eventIDs, $isActive)
    {
	$eventToggleName = "hidden";
	if($isActive === "1") {
            $eventToggleName = "visible";
	}
		
	$updateSuccess = false;
	$eventIdList = "";
	$updateEventQuery = "";
	$errors = "";
		
	try {
            if(is_array($eventIDs)) {
		$eventIdList = implode(",", $eventIDs);
		$eventIdListForQuery = (str_repeat("?,", count($eventIDs) - 1)) . "?";
		$updateEventQuery = "UPDATE `Gaming.Events` SET `IsActive` = ? WHERE `ID` IN (" . $eventIdListForQuery . ");";
            }
            else {
		$errors = "Error when preparing toggle event visibility query: event list is not an array";
            }
	}
	catch(Exception $e) {
            $errors = "Exception when preparing toggle event visibility query: " . $e->getMessage();
	}
			
	if(strlen($errors) == 0) {
            try {				
		if($dataAccess->BuildQuery($updateEventQuery)){
                    $positionalParms = [$isActive];
                    $positionalParms = array_unique(array_merge($positionalParms, $eventIDs));
                    $updateSuccess = $dataAccess->ExecuteNonQueryWithPositionalParms($positionalParms);
		}

		$errors = $dataAccess->CheckErrors();
            }
            catch(Exception $e) {
		$logger->LogError("Could not update visibility for event IDs (" . $eventIdList . "). Exception: " . $e->getMessage());
            }
	}
			
	if(!$updateSuccess) {
            $logger->LogError("Could not update visibility for event IDs (" . $eventIdList . "). " . $errors);
	}
				
	return ($updateSuccess === true) ? ("SUCCESS: Updated requested events to be " . $eventToggleName) : ("SYSTEM ERROR: Could not make requested events " . $eventToggleName . ". Please try again later.");
    }
	
    public function EventEditorDeleteEvents($dataAccess, $logger, $eventIDs)
    {		
	$deleteSuccess = false;
	$eventIdList = "";
	$deleteEventQuery = "";
	$errors = "";
		
	try {
            if(is_array($eventIDs)) {
		$eventIdList = implode(",", $eventIDs);
		$eventIdListForQuery = (str_repeat("?,", count($eventIDs) - 1)) . "?";
		$deleteEventQuery = "DELETE FROM `Gaming.Events` WHERE `ID` IN (" . $eventIdListForQuery . ");";
            }
            else {
		$errors = "Error when preparing delete events query: event list is not an array";
            }
	}
	catch(Exception $e) {
            $errors = "Exception when preparing delete events query: " . $e->getMessage();
	}
			
	if(strlen($errors) == 0) {
            try {				
		if($dataAccess->BuildQuery($deleteEventQuery)){
                    $deleteSuccess = $dataAccess->ExecuteNonQueryWithPositionalParms($eventIDs);
		}

		$errors = $dataAccess->CheckErrors();
            }
            catch(Exception $e) {
		$logger->LogError("Could not delete events (" . $eventIdList . "). Exception: " . $e->getMessage());
            }
	}
			
	if(!$deleteSuccess) {
            $logger->LogError("Could not delete events (" . $eventIdList . "). " . $errors);
	}
				
	return ($deleteSuccess === true) ? ("SUCCESS: Deleted requested events") : ("SYSTEM ERROR: Could not delete requested events. Please try again later.");
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
    
    private function ApplyUserRequestedOrderingToResults($orderBy, &$gameArray)
    {
        $orderByColumn = "";
        $orderByDirection = "";

        sscanf($orderBy, "%s %s", $orderByColumn, $orderByDirection);

        // Always sort by event scheduled date at minimum, or after other specified 
        //  columns when they have equal values
        switch($orderByColumn) {
            case "GameTitle":
                $props = ["Name", "ScheduledDateUTC"];
                $propSortDirections = ["Name" => $orderByDirection, "ScheduledDateUTC" => "ASC"];
                Utils::SortObjectArrayByMultiMemberProp($gameArray, $props, $propSortDirections);
                break;
            case "Platform":
                $props = ["SelectedPlatformText", "ScheduledDateUTC"];
                $propSortDirections = ["SelectedPlatformText" => $orderByDirection, "ScheduledDateUTC" => "ASC"];
                Utils::SortObjectArrayByMultiMemberProp($gameArray, $props, $propSortDirections);
                break;
            case "Hidden":
		// If sorting by "Hidden", need to reverse requested sort direction because "IsActive" is the opposite of "Hidden"
                $props = ["Visible", "ScheduledDateUTC"];
                $propSortDirections = ["Visible" => (($orderByDirection == "ASC") ? "DESC" : "ASC"), "ScheduledDateUTC" => "ASC"];
                Utils::SortObjectArrayByMultiMemberProp($gameArray, $props, $propSortDirections);
                break;
            case "DisplayDate":
                $props = ["ScheduledDateUTC"];
                $propSortDirections = ["ScheduledDateUTC" => $orderByDirection];
                Utils::SortObjectArrayByMultiMemberProp($gameArray, $props, $propSortDirections);
                break;
            case "User":
                $props = ["EventCreatorUserName", "ScheduledDateUTC"];
                $propSortDirections = ["EventCreatorUserName" => $orderByDirection, "ScheduledDateUTC" => "ASC"];
                Utils::SortObjectArrayByMultiMemberProp($gameArray, $props, $propSortDirections);
                break;
            case "Joined":
                $props = ["JoinStatus", "ScheduledDateUTC"];
                $propSortDirections = ["JoinStatus" => $orderByDirection, "ScheduledDateUTC" => "ASC"];
                Utils::SortObjectArrayByMultiMemberProp($gameArray, $props, $propSortDirections);
                break;
        }
    }
	
    public function GetScheduledGames($dataAccess, $logger, $userID, $orderBy = "DisplayDate ASC", 
                                      $paginationEnabled = false, $startIndex = "0", $pageSize = "10", 
                                      $eventId = -1, $showHiddenEvents = false, $showPastEventsDays = "-1", $excludeEventsForUser = false)
    {
	$limitClause = "";
	if($paginationEnabled) {
            $limitClause = "LIMIT " . $startIndex . "," . $pageSize;
	}
        
        $queryParms = [];
        $eventWhereClause = "";
		
	// Determine if current user is member of the given event
	$eventMemberLeftJoinClause = "LEFT JOIN `Gaming.EventMembers` AS em2 ON (em2.`FK_Event_ID` = e.`ID`) AND (em2.`FK_User_ID` = :userID1) ";
	$parmUserId1 = new QueryParameter(':userID1', $userID, PDO::PARAM_INT);
	array_push($queryParms, $parmUserId1);
        
	if(($userID > -1) && (!$excludeEventsForUser)) {
            $eventWhereClause .= "AND (e.`FK_User_ID_EventCreator` = :userID2) ";
            $parmUserId2 = new QueryParameter(':userID2', $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmUserId2);
        }
        else if(($userID > -1) && $excludeEventsForUser) {            
            $eventWhereClause .= "AND (e.`FK_User_ID_EventCreator` <> :userID2) ";
            $parmUserId2 = new QueryParameter(':userID2', $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmUserId2);
			
            // If excluding events for current user, also filter out any private events for which the current user is not an allowed member
            $eventWhereClause .= "AND ((e.`IsPublic` = 1) OR (e.`ID` IN ".
                                 "(SELECT `FK_Event_ID` FROM `Gaming.EventAllowedMembers`".
                                 " WHERE (`FK_User_ID` = :userID3) AND (`FK_Event_ID` = e.`ID`))".
                                 ")) ";
            $parmUserId3 = new QueryParameter(':userID3', $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmUserId3);
        }
		
	if($eventId > -1) {
            $eventWhereClause .= "AND (e.`ID` = :eventId) ";
            $parmEventId = new QueryParameter(':eventId', $eventId, PDO::PARAM_INT);
            array_push($queryParms, $parmEventId);
        }
        
        if(!$showHiddenEvents) {
            $eventWhereClause .= "AND (e.`IsActive` = :isActive) ";
            $parmIsActive = new QueryParameter(':isActive', 1, PDO::PARAM_INT);
            array_push($queryParms, $parmIsActive);
        }
		
        if($showPastEventsDays !== "-1") {
            $eventWhereClause .= "AND (e.`EventScheduledForDate` > (DATE_SUB(UTC_TIMESTAMP(), INTERVAL :daysOld DAY))) ";
            $parmShowPastEventsDays = new QueryParameter(':daysOld', $showPastEventsDays, PDO::PARAM_INT);
            array_push($queryParms, $parmShowPastEventsDays);
        }
	else {
            $eventWhereClause .= "AND (e.`EventScheduledForDate` > UTC_TIMESTAMP()) "; // Only show future events
	}
		
	// Replace first "AND" with a "WHERE"
	$firstAndPos = stripos($eventWhereClause, "and");
	if($firstAndPos !== false) {
            $eventWhereClause = "WHERE " . (substr($eventWhereClause, ($firstAndPos + 4)));
	}
		
        $getUserGamesQuery = "SELECT e.`ID`, COALESCE(cg.`Name`, ug.`Name`) AS GameTitle, COALESCE(tz.`Abbreviation`, tz.`Description`) AS TimeZone, " .
                             "e.`EventScheduledForDate`, e.`DisplayDate`, e.`DisplayTime`, e.`RequiredMemberCount`, p.`Name` AS Platform, e.`Notes`, " . 
                             "e.`FK_Game_ID` AS GameID, tz.`ID` AS TimezoneID, p.`ID` AS PlatformID, e.`IsPublic`, e.`IsActive`, " .
                             "u2.`UserName`, CASE WHEN e.`FK_User_ID_EventCreator` = em.`FK_User_ID` THEN ' (Creator)' ELSE '' END AS EventCreator, " .
                             "u.`UserName` AS EventCreatorUserName, em.`ID` AS EventMemberID, u2.`ID` AS UserID, ".
                             "(CASE WHEN em2.`ID` IS NULL THEN 0 ELSE 1 END) AS thisUserIsJoined " .
                             "FROM `Gaming.Events` AS e ".
                             "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
                             "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
                             "INNER JOIN `Security.Users` AS u ON u.`ID` = e.`FK_User_ID_EventCreator` ".
                             "LEFT JOIN `Gaming.EventMembers` AS em ON (em.`FK_Event_ID` = e.`ID`) ". 
                             "LEFT JOIN `Security.Users` AS u2 ON em.`FK_User_ID` = u2.`ID` ".
                             $eventMemberLeftJoinClause.
                             "LEFT JOIN `Configuration.Games` AS cg ON cg.`ID` = e.`FK_Game_ID` ".
                             "LEFT JOIN `Gaming.UserGames` AS ug ON ug.`ID` = e.`FK_UserGames_ID` ".
                             $eventWhereClause . 
                             "ORDER BY e.`ID`, u2.`UserName` " . $limitClause . ";";
        
	$userGames = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserGamesQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    $lastEvtId = 0;
                    $lastUserGame = null;
                    $lastRequiredMemberCount = -1;
                    $lastThisUserIsJoined = 0;
                    $evtMembers = [];
                    foreach($results as $row) {
			if($lastEvtId !== $row['ID']) {
                            // If transitioning to new game from old one, set event member array and joined status of last game
                            $this->FinalizeLastUserGame($lastUserGame, $evtMembers, $lastRequiredMemberCount, $lastThisUserIsJoined);
							
                            // Get user friends allowed to sign up for this event, if it's private
                            $eventAllowedFriends = [];
                            if($row['IsPublic'] == 0) {
				$eventAllowedFriends = $this->LoadUserFriends($dataAccess, $logger, $userID, $row['ID']);
                            }
							
                            // Show event scheduled date to user in standard time format, with no seconds shown, and AM/PM indicator
                            $displayTime = Utils::ConvertMilitaryTimeToStandardTime($row['DisplayTime']);
							
                            $userGame = Game::ConstructGameForEvent($row['GameID'], $row['DisplayDate'], $displayTime, $row['RequiredMemberCount'], $row['Notes'], $eventAllowedFriends, 
                                                                    false, $row['TimezoneID'], $row['PlatformID'], $row['GameTitle'], $row['EventScheduledForDate'], $row['Platform'], 
                                                                    $row['TimeZone'], $row['ID'], $row['IsActive'] == '1' ? true : false, $row['EventCreatorUserName']);
                            array_push($userGames, $userGame);
                            $lastUserGame = $userGame;
                            $lastRequiredMemberCount = $row['RequiredMemberCount'];
                            $lastThisUserIsJoined = $row['thisUserIsJoined'];
                            $evtMembers = [];
			}
						
			$evtMember = new EventMember($row['EventMemberID'], $row['ID'], $row['UserID'], $row['UserName'], $row['UserName'] . $row['EventCreator']);
                        $lastEvtId = $row['ID'];
			array_push($evtMembers, $evtMember);
                    }
					
                    // Set event member array of last game
                    $this->FinalizeLastUserGame($lastUserGame, $evtMembers, $lastRequiredMemberCount, $lastThisUserIsJoined);
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve user scheduled games. " . $errors);
	}
        
        // Apply requested sort order from client side to table, via array sort
        $this->ApplyUserRequestedOrderingToResults($orderBy, $userGames);
        return $userGames;
    }
	
    private function FinalizeLastUserGame($lastUserGame, $evtMembers, $requiredMemberCount, $thisUserIsJoined)
    {
	if($lastUserGame != null) {
            $lastUserGame->EventMembers = $evtMembers;
			
            // Calculate join status of this game: full (all required members signed up), join (open for current user to join), leave (current user is joined already)
            $joinStatus = "JOIN";
            if($thisUserIsJoined == 1) {
		$joinStatus = "LEAVE";
            }
            else if(count($evtMembers) >= $requiredMemberCount) {
		$joinStatus = "FULL";
            }
			
            $lastUserGame->JoinStatus = $joinStatus;
	}
    }
	
    private function GetTotalCountScheduledGames($dataAccess, $logger, $userID, $showHiddenEvents, $showPastEventsDays = "-1", $excludeEventsForUser = false)
    {
	$userTotalScheduledGamesCnt = 0;
		
        $queryParms = [];
	$eventWhereClause = "";
		
	if(($userID > -1) && (!$excludeEventsForUser)) {
            $eventWhereClause .= "AND (e.`FK_User_ID_EventCreator` = :userID) ";
            $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmUserId);
        }
        else if(($userID > -1) && $excludeEventsForUser) {
            $eventWhereClause .= "AND (e.`FK_User_ID_EventCreator` <> :userID) ";
            $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmUserId);
			
	// If excluding events for current user, also filter out any private events for which the current user is not an allowed member
	$eventWhereClause .= "AND ((e.`IsPublic` = 1) OR (e.`ID` IN ".
                             "(SELECT `FK_Event_ID` FROM `Gaming.EventAllowedMembers`".
                             " WHERE (`FK_User_ID` = :userID2) AND (`FK_Event_ID` = e.`ID`))".
                             ")) ";
            $parmUserId2 = new QueryParameter(':userID2', $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmUserId2);
        }
        
        if(!$showHiddenEvents) {
            $eventWhereClause .= "AND (e.`IsActive` = :isActive) ";
            $parmIsActive = new QueryParameter(':isActive', 1, PDO::PARAM_INT);
            array_push($queryParms, $parmIsActive);
        }
		
        if($showPastEventsDays !== "-1") {
            $eventWhereClause .= "AND (e.`EventScheduledForDate` > (DATE_SUB(UTC_TIMESTAMP(), INTERVAL :daysOld DAY))) ";
            $parmShowPastEventsDays = new QueryParameter(':daysOld', $showPastEventsDays, PDO::PARAM_INT);
            array_push($queryParms, $parmShowPastEventsDays);
        }
	else {
            $eventWhereClause .= "AND (e.`EventScheduledForDate` > UTC_TIMESTAMP()) "; // Only show future events
	}
		
	// Replace first "AND" with a "WHERE"
	$firstAndPos = stripos($eventWhereClause, "and");
	if($firstAndPos !== false) {
            $eventWhereClause = "WHERE " . (substr($eventWhereClause, ($firstAndPos + 4)));
	}
		
        $getUserGamesQuery = "SELECT COUNT(e.`ID`) AS Cnt " .
                             "FROM `Gaming.Events` AS e ".
                             "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
                             "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
                             $eventWhereClause . ";";
        
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
	
    public function GetTotalCountUnjoinedScheduledGames($dataAccess, $logger, $userID)
    {
	$userTotalUnjoinedGamesCnt = 0;
		
        $getUserGamesQuery = "SELECT COUNT(e.`ID`) AS Cnt " .
                             "FROM `Gaming.Events` AS e ".
                             "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
                             "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
                             "WHERE (e.`FK_User_ID_EventCreator` <> :userID) ".
                             "AND (e.`IsActive` = 1) ".
                             "AND (e.`EventScheduledForDate` > UTC_TIMESTAMP()) ". // Only show future events
                             "AND (e.`RequiredMemberCount` > (SELECT COUNT(`ID`) FROM `Gaming.EventMembers` WHERE `FK_Event_ID` = e.`ID`)) ".
                             "AND ((e.`IsPublic` = 1) OR (e.`ID` IN ".
				"(SELECT `FK_Event_ID` FROM `Gaming.EventAllowedMembers`".
				" WHERE (`FK_User_ID` = :userID2) AND (`FK_Event_ID` = e.`ID`))".
				")) ".
                             "AND (e.`ID` NOT IN ".
				"(SELECT `FK_Event_ID` FROM `Gaming.EventMembers`".
				" WHERE (`FK_User_ID` = :userID3))".
				");";
		
	$parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
	$parmUserId2 = new QueryParameter(':userID2', $userID, PDO::PARAM_INT);
	$parmUserId3 = new QueryParameter(':userID3', $userID, PDO::PARAM_INT);
	$queryParms = array($parmUserId, $parmUserId2, $parmUserId3);
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserGamesQuery, $queryParms)){
		$results = $dataAccess->GetSingleResult();
					
		if($results != null){
		   $userTotalUnjoinedGamesCnt = $results['Cnt'];
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve count of user unjoined games. " . $errors);
	}
        
        return $userTotalUnjoinedGamesCnt;
    }
    
    public function GetEventMembers($dataAccess, $logger, $eventID)
    {
        $getEventMembersQuery = "SELECT u.`UserName`, CASE WHEN e.`FK_User_ID_EventCreator` = em.`FK_User_ID` THEN ' (Creator)' ELSE '' END AS EventCreator, " .
				"em.`ID`, em.`FK_Event_ID` AS EventID, em.`FK_User_ID` AS UserID " .
				"FROM `Gaming.EventMembers` AS em " .
                                "INNER JOIN `Security.Users` AS u ON em.`FK_User_ID` = u.`ID` " .
				"INNER JOIN `Gaming.Events` AS e ON e.`ID` = em.`FK_Event_ID` " .
                                "WHERE em.`FK_Event_ID` = :eventID " .
				"ORDER BY u.`UserName`;";
        
        $parmEventId = new QueryParameter(':eventID', $eventID, PDO::PARAM_INT);
        $queryParms = array($parmEventId);
	$eventMembers = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getEventMembersQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
			$eventMember = new EventMember($row['ID'], $row['EventID'], $row['UserID'], $row['UserName'], $row['UserName'] . $row['EventCreator']);
			array_push($eventMembers, $eventMember);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve members for event " . $eventID . ". " . $errors);
	}
        
        return $eventMembers;
    }
}
