<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/User.class.php';
include_once 'classes/Game.class.php';
include_once 'classes/EventMember.class.php';
include_once 'classes/Utils.class.php';
include_once 'classes/EventSearchParameters.class.php';
include_once 'classes/UserSearchParameters.class.php';
include_once 'classes/UserInviteInfo.class.php';

class GamingHandler
{    
    public function LoadUserFriends($dataAccess, $logger, $userID, $eventIDs = [])
    {
	$eventWhereClause = "";
        $eventLeftJoinClause = "";
        $orderByFirstClause = "";
        $extraSelectFields = " ";
	$queryParms = array($userID);
        $loadingAllowedFriendsForEventList = (count($eventIDs) > 0) ? true : false;
		
	if($loadingAllowedFriendsForEventList) {
            $extraSelectFields = ", em.`FK_Event_ID` as EventId ";
            $eventLeftJoinClause = "LEFT JOIN `Gaming.EventAllowedMembers` as em ON u.`ID` = em.`FK_User_ID` ";
            $orderByFirstClause = "em.`FK_Event_ID`, ";
            
            $eventIdParamList = (str_repeat("?,", count($eventIDs) - 1)) . "?";
            $eventWhereClause = "AND (em.`FK_Event_ID` IN (" . $eventIdParamList . ")) ";
            $queryParms = array_merge($queryParms, $eventIDs);
	}
		
        $getUserFriendsQuery = "SELECT uf.`FK_User_ID_Friend` as FriendID, u.`FirstName`, u.`LastName`, u.`UserName`" . $extraSelectFields . 
                               "FROM `Gaming.UserFriends` as uf " . 
                               "INNER JOIN `Security.Users` as u ON uf.`FK_User_ID_Friend` = u.`ID` " . 
                               $eventLeftJoinClause .
                               "WHERE (uf.`FK_User_ID_ThisUser` = ?) " . $eventWhereClause .
                               "ORDER BY " . $orderByFirstClause . "CASE WHEN ((u.`FirstName` IS NOT NULL) AND (LENGTH(u.`FirstName`) > 0)) THEN u.`FirstName` " .
                               "ELSE u.`UserName` END, CASE WHEN ((u.`FirstName` IS NOT NULL) AND (LENGTH(u.`FirstName`) > 0)) THEN u.`LastName` ELSE u.`UserName` END;";
        
	$userFriends = array();
	$userFriendsByEvent = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserFriendsQuery)) {
		$results = $dataAccess->GetResultSetWithPositionalParms($queryParms);
						
		if($results != null){
                    $lastEventId = -1;
                    foreach($results as $row) {
			if($loadingAllowedFriendsForEventList && ($lastEventId <> $row['EventId']) && ($lastEventId > -1)) {
                            $event = new Game(-1, "", false);
                            $event->EventID = $lastEventId;
                            $event->FriendsAllowed = $userFriends;
					
                            $userFriendsByEvent[] = $event;
                            $userFriends = array();
			}
							
			$objUser = User::constructDefaultUser();
			$objUser->UserID = $row['FriendID'];
			$objUser->FirstName = $row['FirstName'];
			$objUser->LastName = $row['LastName'];
			$objUser->UserName = $row['UserName'];
			array_push($userFriends, $objUser);
							
			if($loadingAllowedFriendsForEventList) {
                            $lastEventId = $row['EventId'];
			}
                    }
						
                    if($loadingAllowedFriendsForEventList) {
			$lastEvent = new Game(-1, "", false);
			$lastEvent->EventID = $lastEventId;
			$lastEvent->FriendsAllowed = $userFriends;
			$userFriendsByEvent[] = $lastEvent;
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve user friends. " . $errors);
	}

	return $loadingAllowedFriendsForEventList ? $userFriendsByEvent : $userFriends;
    }
    
    public function LoadFriendListUsers($dataAccess, $logger, $userID, $searchParms, $forAvailableFriendList = true, $orderBy = "UserName ASC",
					$paginationEnabled = false, $startIndex = "0", $pageSize = "10")
    {
	$limitClause = "";
	if($paginationEnabled)
	{
            $limitClause = "LIMIT " . $startIndex . "," . $pageSize;
	}
	
	$queryParms = [$userID, $userID, $userID];
	$orderByClause = "ORDER BY " . $this->TranslateOrderByToUserQueryOrderByClause($orderBy);
	$filterWhereClause = $this->BuildWhereClauseForFriendListQuery($userID, $queryParms, $searchParms, $forAvailableFriendList);
		
        $getAvailableUserQuery = "SELECT DISTINCT u.`ID` as UserID, u.`FirstName`, u.`LastName`, u.`UserName`, u.`Gender`, " . 
				 "(CASE WHEN ufiInvitee.`ID` IS NOT NULL THEN 'Yes' ELSE 'No' END) as InviteFromMe, (CASE WHEN ufiInviter.`ID` IS NOT NULL THEN 'Yes' ELSE 'No' END) as InviteToMe, " .
				 "(CASE WHEN ufiInvitee.`ID` IS NOT NULL THEN ufiInvitee.`FK_User_ID_Invitee` ELSE -1 END) as InviteeID, " .
				 "(CASE WHEN ufiInviter.`ID` IS NOT NULL THEN ufiInviter.`FK_User_ID_Inviter` ELSE -1 END) as InviterID, " .
				 "(CASE WHEN ufiInviter.`IsRejected` IS NULL THEN '' ELSE (CASE WHEN ufiInviter.`IsRejected` = 0 THEN 'Pending' ELSE 'Rejected' END) END) as ReceivedInviteReply, " .
				 "(CASE WHEN ufiInvitee.`IsRejected` IS NULL THEN '' ELSE (CASE WHEN ufiInvitee.`IsRejected` = 0 THEN 'Pending' ELSE 'Rejected' END) END) as SentInviteReply " .
                                 "FROM `Security.Users` as u " .
				 "LEFT JOIN `Gaming.UserFriends` as uf ON (uf.`FK_User_ID_Friend` = u.`ID`) AND (uf.`FK_User_ID_ThisUser` = ?) " .
				 "LEFT JOIN `Gaming.UserFriendInvitations` ufiInviter ON (u.`ID` = ufiInviter.`FK_User_ID_Inviter`) AND (ufiInviter.`FK_User_ID_Invitee` = ?) " .
				 "LEFT JOIN `Gaming.UserFriendInvitations` ufiInvitee ON (u.`ID` = ufiInvitee.`FK_User_ID_Invitee`) AND (ufiInvitee.`FK_User_ID_Inviter` = ?) " .
				 "LEFT JOIN `Payments.PayPalUsers` as ppu ON ppu.`FK_User_ID` = u.`ID` " .
                                 $filterWhereClause . $orderByClause . $limitClause;
        
	$userFriends = array();
        
        if($dataAccess->BuildQuery($getAvailableUserQuery)) {
            $results = $dataAccess->GetResultSetWithPositionalParms($queryParms);

            if($results != null){
                foreach($results as $row) {                        
                    $objUser = User::constructDefaultUser();
                    $objUser->UserID = $row['UserID'];
                    $objUser->FirstName = $row['FirstName'];
                    $objUser->LastName = $row['LastName'];
                    $objUser->UserName = $row['UserName'];
                    $objUser->Gender = $row['Gender'];

                    // Load user invitation data, if present
                    $objUser->UserInviteInfo->InviteFromLoggedInUser = ($row['InviteFromMe'] == 'Yes');
                    $objUser->UserInviteInfo->InviteToLoggedInUser = ($row['InviteToMe'] == 'Yes');
                    $objUser->UserInviteInfo->UserIdInviter = $objUser->UserInviteInfo->InviteFromLoggedInUser ? $userID : $row['InviterID'];
                    $objUser->UserInviteInfo->UserIdInvitee = $objUser->UserInviteInfo->InviteToLoggedInUser ? $userID : $row['InviteeID'];
                    $objUser->UserInviteInfo->ReceivedInviteReply = $row['ReceivedInviteReply'];
                    $objUser->UserInviteInfo->SentInviteReply = $row['SentInviteReply'];
                    array_push($userFriends, $objUser);
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
    
    public function LoadAllGameTitles($dataAccess, $logger)
    {
        $getGameTitlesQuery = "SELECT DISTINCT `Name`, 0 AS IsGlobalGameTitle FROM `Gaming.UserGames` " .
                              "UNION " .
                              "SELECT DISTINCT `Name`, 1 AS IsGlobalGameTitle FROM `Configuration.Games` " .
                              "ORDER BY `Name`;";
        
	$gameTitles = array();
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getGameTitlesQuery)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
			$gameTitle = new Game(-1, $row['Name'], $row['IsGlobalGameTitle'] == 1);
			array_push($gameTitles, $gameTitle);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve game titles. " . $errors);
	}
        
        return $gameTitles;
    }
	
    public function LoadAllActiveUsers($dataAccess, $logger, $curUserID)
    {
        $getActiveUsersQuery = "SELECT DISTINCT `ID`, TRIM(`UserName`) AS UserName FROM `Security.Users` " .
                               "WHERE (`IsActive` = 1) AND (LENGTH(TRIM(`UserName`)) > 0) AND (`ID` <> :userID) " .
                               "ORDER BY `UserName`;";
        
        $parmUserId = new QueryParameter(':userID', $curUserID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
    	$activeUsers = array();
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getActiveUsersQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
                        $user = User::constructDefaultUser();
                        $user->UserID = $row['ID'];
                        $user->UserName = $row['UserName'];
                        array_push($activeUsers, $user);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve active users. " . $errors);
	}
        
        return $activeUsers;
    }
	
    public function EventEditorLoad($dataAccess, $logger, $user, $eventId)
    {
        // If this is called for existing event, append eventID to each control ID for uniqueness
        $formName = "eventCreateForm";
        $formButtonName = "createEventBtn";
        $formButtonText = "Create Event!";
        $editEventButtons = "";
        $gameDateValue = "";
        $gameTimeValue = "";
        $eventInfo = null;
        $userDefaultTimeZoneId = -1;
        $userDefaultPlatformId = -1;
        
        if(strlen($eventId) > 0) {
            $formName = "eventEditForm"  . $eventId;
            $formButtonName = "editEventBtn" . $eventId;
            $formButtonText = "Update Event";
            
            // Load information about this event
            $showHiddenEvents = true;
            $showJoinedEvents = true;
            $showOpenEvents = true;
            $showUnjoinedFullEvents = true;
            $noStartDateRestriction = true;
            $searchParms = new EventSearchParameters($showHiddenEvents, "", "", [], [], [], [], $showJoinedEvents, $showOpenEvents, 
                                                     $showUnjoinedFullEvents, $noStartDateRestriction);
            $userAllowedFriendsByEvent = $this->LoadUserFriends($dataAccess, $logger, $user->UserID, [$eventId]);
            $eventArray = $this->GetScheduledGames($dataAccess, $logger, $user->UserID, "DisplayDate ASC", false, "0", "10", $eventId, 
                                                   $searchParms, [], [], $userAllowedFriendsByEvent);
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
        else {
            $userDefaultTimeZoneId = $user->TimezoneID;
            $userDefaultPlatformId = (count($user->GamePlatforms) > 1) ? -1 : (reset($user->GamePlatforms));
        }
        
	// Build game-players-needed selector
	$gamePlayersNeededSelect = '<select id="gamePlayersNeeded' . $eventId . '" name="gamePlayersNeeded' . $eventId . '" >';
	$selected = '';
		
	for($i = 2; $i < 65; $i++) {
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
	$userFriendList = $this->LoadUserFriends($dataAccess, $logger, $user->UserID);
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
                            $this->ConstructGameTitleSelectorHTML($dataAccess, $logger, $user->UserID, $eventId, (($eventInfo != null) ? $eventInfo->Name : '')) .
			'</div><br />'.
			'<p><i class="fa fa-calendar-o"></i> &nbsp; Tell us a date<br/>'.
                            '<input id="gameDate' . $eventId . '" name="gameDate' . $eventId . '" ' . $gameDateValue .
                            'type="text" maxlength="50" placeholder=" Date"></p>'.
			'<p><i class="fa fa-clock-o"></i> &nbsp; Time you want to play<br/>'.
                            '<input id="gameTime' . $eventId . '" name="gameTime' . $eventId . '" ' . $gameTimeValue .
                            'type="text" maxlength="9" placeholder=" Time"><br />' .
                            $this->GetTimezoneList($dataAccess, (($eventInfo != null) ? $eventInfo->ScheduledTimeZoneID : $userDefaultTimeZoneId), $eventId) .
                        '</p>'.
			'<p><i class="fa fa-user"></i> &nbsp; Total number of players needed<br/>'. 
                            $gamePlayersNeededSelect .
                        '<p><i class="fa fa-gamepad"></i> &nbsp; Choose a platform for this game<br/>'. 
                            $this->GetPlatformDropdownList($dataAccess, (($eventInfo != null) ? $eventInfo->SelectedPlatformID : $userDefaultPlatformId), $eventId) .
                        '</p>'.
			'<p><i class="fa fa-comments-o"></i> &nbsp; Notes about your event<br/>'.
                            '<textarea name="message' . $eventId . '" id="message' . $eventId .
                            '" placeholder=" exp: Looking for some new team mates to play through Rocket Leagues 3v3 mode. Must have a mic!" rows="6" required>'.
                            (($eventInfo != null) ? ($eventInfo->Notes) : '') . '</textarea>'.
                        '</p>'. 
			($user->IsPremiumMember ?
			(
                            '<p><i class="fa fa-lock"></i> &nbsp; Only allow friends to join this event</p>'.
                            '<input type="checkbox" id="privateEvent' . $eventId . '" name="privateEvent' . $eventId . '" value="private" ' . 
                            $privateEventOptionChecked . ' ' . $privateEventOptionDisabled . 
                            '>Private Event</input>&nbsp;<input class="selectAllCheckbox" type="checkbox" id="selectAllFriends' . $eventId . '" value="all" ' . 
                            ((($eventId === 0) || (!$privateEventOptionChecked)) ? 'disabled ' : '') . '>Select all</input>'.
                            '<div class="fixedHeightScrollableContainer">'. 
				$friendListSelect .
                            '</div><br /><br />'
			) 
			: 
                            '<br />'
			).
                        '<div id="eventDialogToolbar' . $eventId . '" class="dlgToolbarContainer">'.
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
    
    public function ConstructGameTitleMultiSelector($dataAccess, $logger)
    {
        $gameSelector = '';
        $userGames = $this->LoadAllGameTitles($dataAccess, $logger);
        
        foreach($userGames as $userGame) {
            $gameSelector .= ('<label class="overlayPanelLabelMultiSelector"><input type="checkbox" name="filterGameTitles[]" value="' . 
                                $userGame->Name . '" class="overlayPanelElement">' . $userGame->Name . '</label><br />');
        }
        
        return $gameSelector;
    }
	
    public function ConstructUserMultiSelector($dataAccess, $logger, $groupId, $userID)
    {
        $userSelector = '';
        $users = $this->LoadAllActiveUsers($dataAccess, $logger, $userID);
        
        foreach($users as $user) {
            $userSelector .= ('<label class="overlayPanelLabelMultiSelector"><input type="checkbox" name="' . $groupId . '" value="' . 
                                $user->UserID . '" class="overlayPanelElement">' . $user->UserName . '</label><br />');
        }
        
        return $userSelector;
    }
	
    public function JTableAvailableUsersViewerLoad($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, $pageSize, $searchParms)
    {
        $forAvailableFriendList = true;
	$totalAvailUserCnt = $this->GetTotalCountFriendListUsers($dataAccess, $logger, $userID, $searchParms, $forAvailableFriendList);
	$availUsers = $this->LoadFriendListUsers($dataAccess, $logger, $userID, $searchParms, $forAvailableFriendList, $orderBy, $paginationEnabled, 
                                                 $startIndex, $pageSize);
		
        $rows = [];
        foreach($availUsers as $user) {
            $row = array (
                "ID" => $user->UserID,
                "UserName" => $user->UserName,
                "FirstName" => $user->FirstName,
		"LastName" => $user->LastName,
		"Gender" => $user->Gender,
                "GamerTags" => '',
		"SendInvite" => ''
            );

            array_push($rows, $row);
        }

        $jTableResult = [];
        $jTableResult['Result'] = 'OK';
        $jTableResult['TotalRecordCount'] = $totalAvailUserCnt;
        $jTableResult['Records'] = $rows;
        return json_encode($jTableResult);
    }
    
    public function JTableCurrentFriendsListViewerLoad($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, $pageSize, $searchParms)
    {
        $forAvailableFriendList = false;
	$totalUserCnt = $this->GetTotalCountFriendListUsers($dataAccess, $logger, $userID, $searchParms, $forAvailableFriendList);
	$friends = $this->LoadFriendListUsers($dataAccess, $logger, $userID, $searchParms, $forAvailableFriendList, $orderBy, $paginationEnabled, 
                                              $startIndex, $pageSize);
		
        $rows = [];
        foreach($friends as $user) {
            $row = array (
                "ID" => $user->UserID,
                "UserName" => $user->UserName,
                "FirstName" => $user->FirstName,
		"LastName" => $user->LastName,
                "FullName" => $user->FirstName . " " . $user->LastName,
		"InviteType" => ($user->UserInviteInfo->InviteFromLoggedInUser ? 'From Me' : 
                                    ($user->UserInviteInfo->InviteToLoggedInUser ? 'To Me' : 'No')),
                "InviteReply" => ($user->UserInviteInfo->InviteFromLoggedInUser ? $user->UserInviteInfo->SentInviteReply : 
                                    ($user->UserInviteInfo->InviteToLoggedInUser ? $user->UserInviteInfo->ReceivedInviteReply : 'N/A')),
                "AnswerInvite" => '',
                "GamerTags" => ''
            );

            array_push($rows, $row);
        }

        $jTableResult = [];
        $jTableResult['Result'] = 'OK';
        $jTableResult['TotalRecordCount'] = $totalUserCnt;
        $jTableResult['Records'] = $rows;
        return json_encode($jTableResult);
    }
	
    public function JTableEventManagerLoad($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, $pageSize, $searchParms)
    {
        $eventIDs = [];
        $scheduledGames = [];
	$totalScheduledGameCnt = -1;
		
	// If filtering on joined users, must run two additional queries (passes) to return the requested events while maintaining proper paging and ordering
	if(((is_array($searchParms->JoinedUsers)) && (count($searchParms->JoinedUsers) > 0)) || (strlen($searchParms->CustomJoinedUserName) > 0)) {
            // In this case, we must get the complete list of distinct event IDs for the search criteria, without a LIMIT clause, because we need to filter
            // the results further in a later step before applying the LIMIT
            $eventIDs = $this->GetScheduledGameIDList($dataAccess, $logger, $userID, $searchParms, $orderBy, false, 
                                                      $startIndex, $pageSize);
			
            if(count($eventIDs) > 0) {
		$joinedMemberEventIds = $this->GetEventsJoinedByUsersInList($dataAccess, $logger, $searchParms->JoinedUsers, $searchParms->CustomJoinedUserName, $eventIDs);
		$eventIDs = $this->GetScheduledGameIDList($dataAccess, $logger, $userID, $searchParms, $orderBy, $paginationEnabled, 
														  $startIndex, $pageSize, $joinedMemberEventIds);
		$filterCountByJoinedUsers = true;
		$totalScheduledGameCnt = $this->GetTotalCountScheduledGames($dataAccess, $logger, $userID, $searchParms, $filterCountByJoinedUsers);
            }
	}
	else {
            $eventIDs = $this->GetScheduledGameIDList($dataAccess, $logger, $userID, $searchParms, $orderBy, $paginationEnabled, 
                                                      $startIndex, $pageSize);
	}
			
	if(count($eventIDs) > 0) {
            $eventMembersByEvent = $this->GetEventMembers($dataAccess, $logger, $eventIDs);
            $userAllowedFriendsByEvent = $this->LoadUserFriends($dataAccess, $logger, $userID, $eventIDs);
            $scheduledGames = $this->GetScheduledGames($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, $pageSize, -1, 
                                                       $searchParms, $eventIDs, $eventMembersByEvent, $userAllowedFriendsByEvent);
	}

	if($totalScheduledGameCnt < 0)  $totalScheduledGameCnt = $this->GetTotalCountScheduledGames($dataAccess, $logger, $userID, $searchParms);
		
        $rows = [];
        foreach($scheduledGames as $game) {
            $playersSignedUp = $game->EventMembers;
			
            $playersSignedUpData = "";
            foreach($playersSignedUp as $player) {
		$playersSignedUpData .= ($player->EventMemberId . "|" . $player->UserDisplayName . "|" . $player->UserID . ",");
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
                "EventCreator" => (($game->EventCreatorUserID === $userID) ? 'ME' : $game->EventCreatorUserName),
                "EventCreatorUserID" => $game->EventCreatorUserID,
                "Actions" => $game->JoinStatus,
		"Hidden" => !$game->Visible ? 'Yes' : 'No',
		"EventScheduledForDate" => $game->ScheduledDateUTC
            );

            array_push($rows, $row);
        }

        $jTableResult = [];
        $jTableResult['Result'] = 'OK';
        $jTableResult['TotalRecordCount'] = $totalScheduledGameCnt;
        $jTableResult['Records'] = $rows;
        return json_encode($jTableResult);
    }
	
    public function JTableCurrentEventViewerLoad($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, $pageSize, $searchParms)
    {												  
        $eventIDs = [];
        $scheduledGames = [];
        $totalScheduledGameCnt = -1;

        // If filtering on joined users, must run two additional queries (passes) to return the requested events while maintaining proper paging and ordering
        if(((is_array($searchParms->JoinedUsers)) && (count($searchParms->JoinedUsers) > 0)) || (strlen($searchParms->CustomJoinedUserName) > 0)) {
            // In this case, we must get the complete list of distinct event IDs for the search criteria, without a LIMIT clause, because we need to filter
            // the results further in a later step before applying the LIMIT
            $eventIDs = $this->GetScheduledGameIDList($dataAccess, $logger, $userID, $searchParms, $orderBy, false, 
                                                      $startIndex, $pageSize);

            if(count($eventIDs) > 0) {
                $joinedMemberEventIds = $this->GetEventsJoinedByUsersInList($dataAccess, $logger, $searchParms->JoinedUsers, $searchParms->CustomJoinedUserName, $eventIDs);
                $eventIDs = $this->GetScheduledGameIDList($dataAccess, $logger, $userID, $searchParms, $orderBy, $paginationEnabled, 
                                                          $startIndex, $pageSize, $joinedMemberEventIds);
                $filterCountByJoinedUsers = true;
                $totalScheduledGameCnt = $this->GetTotalCountScheduledGames($dataAccess, $logger, $userID, $searchParms, $filterCountByJoinedUsers);
            }
        }
        else {
            $eventIDs = $this->GetScheduledGameIDList($dataAccess, $logger, $userID, $searchParms, $orderBy, $paginationEnabled, 
                                                      $startIndex, $pageSize);
        }

        if(count($eventIDs) > 0) {
            $eventMembersByEvent = $this->GetEventMembers($dataAccess, $logger, $eventIDs);
            $userAllowedFriendsByEvent = [];

            $scheduledGames = $this->GetScheduledGames($dataAccess, $logger, $userID, $orderBy, $paginationEnabled, $startIndex, $pageSize, -1, 
                                                       $searchParms, $eventIDs, $eventMembersByEvent, $userAllowedFriendsByEvent);
        }
		
	if($totalScheduledGameCnt < 0)  $totalScheduledGameCnt = $this->GetTotalCountScheduledGames($dataAccess, $logger, $userID, $searchParms);
	$totalGameCntNotJoined = $this->GetTotalCountUnjoinedScheduledGames($dataAccess, $logger, $userID);

        $rows = [];
        foreach($scheduledGames as $game) {
            $playersSignedUp = $game->EventMembers;
			
            $playersSignedUpData = "";
            foreach($playersSignedUp as $player) {
		$playersSignedUpData .= ($player->EventMemberId . "|" . $player->UserDisplayName . "|" . $player->UserID . ",");
            }
            $playersSignedUpData = rtrim($playersSignedUpData, ",");

            $row = array (
                "ID" => $game->EventID,
		"TotalGamesToJoinCount" => $totalGameCntNotJoined,
		"UserName" => $game->EventCreatorUserName,
                "UserID" => $game->EventCreatorUserID,
                "GameTitle" => $game->Name,
                "Platform" => $game->SelectedPlatformText,
                "DisplayDate" => $game->ScheduledDate,
                "DisplayTime" => $game->ScheduledTime . ' ' . $game->ScheduledTimeZoneText,
                "Notes" => $game->Notes,
                "PlayersSignedUp" => sprintf("%d (of %d)", count($playersSignedUp), $game->RequiredPlayersCount),
		"PlayersSignedUpData" => $playersSignedUpData,
		"Actions" => $game->JoinStatus,
		"EventScheduledForDate" => $game->ScheduledDateUTC
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
        $playersSignedUp = $this->GetEventMembers($dataAccess, $logger, [$eventId]);

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
        $gameIDColToReset = "`FK_Game_ID`";
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
            $gameIDColToReset = "`FK_UserGames_ID`";
            
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
                            "`FK_User_ID_EventCreator`=:FKUserEventCreator," . $gameIDCol . "=:FKGameId," . $gameIDColToReset . "=NULL," .
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
        $addNewUserGameQuery = "INSERT INTO `Gaming.UserGames` (`FK_User_ID`,`Name`, `CreatedDate`, `ModifiedDate`) " .
                               "VALUES (:FKUserId, :gameName, UTC_TIMESTAMP(), UTC_TIMESTAMP()) ". 
                               "ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), `ModifiedDate` = UTC_TIMESTAMP();";
		
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
                    $positionalParms = array_merge($positionalParms, $eventIDs);
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
    
    public function EventViewerRemoveMemberFromEvent($dataAccess, $logger, $evtMemberId, $curUserId, $curUserIsPremium)
    {
        $validationMsg = $this->ValidateThisMemberCanBeRemoved($dataAccess, $logger, $evtMemberId, $curUserId, $curUserIsPremium);
        if($validationMsg === "success") {
            $deleteSuccess = false;
            $removeUserFromEventsQuery = "DELETE FROM `Gaming.EventMembers` WHERE (`ID` = :evtMemberId);";
            $parmEvtMemberId = new QueryParameter(':evtMemberId', $evtMemberId, PDO::PARAM_INT);
            $queryParms = array($parmEvtMemberId);

            $errors = "";

            try {				
                if($dataAccess->BuildQuery($removeUserFromEventsQuery, $queryParms)){
                    $deleteSuccess = $dataAccess->ExecuteNonQuery();
                }

                $errors = $dataAccess->CheckErrors();
            }
            catch(Exception $e) {
                $logger->LogError("Could not remove event member " . $evtMemberId . " from event. Exception: " . $e->getMessage());
            }

            if(!$deleteSuccess) {
                $logger->LogError("Could not remove event member " . $evtMemberId . " from event. " . $errors);
            }

            return ($deleteSuccess === true) ? ("SUCCESS: Removed player from event") : ("SYSTEM ERROR: Could not remove player from event. Please try again later.");
        }
        else {
            return "SYSTEM ERROR: Could not remove player from event. " . $validationMsg;
        }
    }
    
    private function ValidateThisMemberCanBeRemoved($dataAccess, $logger, $evtMemberId, $curUserId, $curUserIsPremium)
    {
        $validationMsg = "success";
        if(!$curUserIsPremium)  $validationMsg = "Only premium members are allowed to remove joined members from their events.";
        else {
            $lookUpEventMemberInfoQuery = "SELECT em.`FK_User_ID` AS EventMemberUserId, e.`FK_User_ID_EventCreator` AS EventCreatorUserId " .
                                          "FROM `Gaming.EventMembers` AS em " .
                                          "INNER JOIN `Gaming.Events` AS e ON e.`ID` = em.`FK_Event_ID` " .
                                          "WHERE (em.`ID` = :evtMemberId);";

            $parmEvtMemberId = new QueryParameter(':evtMemberId', $evtMemberId, PDO::PARAM_INT);
            $queryParms = array($parmEvtMemberId);
            $results = null;
            
            if($dataAccess->BuildQuery($lookUpEventMemberInfoQuery, $queryParms)){
                $results = $dataAccess->GetSingleResult();

                if($results != null){
                    $eventMemberUserId = $results['EventMemberUserId'];
                    $eventCreatorUserId = $results['EventCreatorUserId'];
                    
                    if($eventCreatorUserId != $curUserId)      $validationMsg = "Can only remove members from events that you've created.";
                    else if($eventMemberUserId == $curUserId)  $validationMsg = "The event creator (you) cannot be removed from event.";
                }
            }

            $errors = $dataAccess->CheckErrors();
            if((strlen($errors) > 0) || ($results == null)) {
                $logger->LogError("Could not retrieve validation info for event member removal request (evtMember: " . $evtMemberId . 
                                  "; requesting user ID: " . $curUserId . "). " . $errors);
                $validationMsg = "Unable to confirm your permissions to perform this action.";
            }
        }
        
        return $validationMsg;
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
	
    public function GetPlatformCheckboxList($dataAccess, $selectedPlatforms, $groupId = 'platforms[]', $encloseInOverlayLabel = false)
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
						
                        $curLine = "<input type='checkbox' name='" . $groupId . "' " . $selected . " value='" . $row['ID'] . "'>" . 
                                   $row['Name'] . "<br />";

                        if($encloseInOverlayLabel) {
                            $curLine = '<label class="overlayPanelLabelMultiSelector"><input type="checkbox" class="overlayPanelElement" name="' . 
                                       $groupId . '" ' . $selected . ' value="' . $row['ID'] . '">' . $row['Name'] . '</label><br />';
                        }
                        $ddlPlatformsHTML = $ddlPlatformsHTML . $curLine;
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
    
    public function GetPlatformDropdownList($dataAccess, $selectedPlatform, $eventId = '', $platformListName = '')
    {
        $platformListId = ((strlen($eventId) == 0) ? 'ddlPlatforms' : ('ddlPlatforms' . $eventId));
        $platformListName = ((strlen($platformListName) > 0) ? $platformListName : $platformListId);
        
        $platformQuery = "SELECT `ID`, `Name` FROM `Configuration.Platforms` ORDER BY `Name`;";
        $ddlPlatformsHTML = "";
        $ddlPlatformsErrorHTML = "<select id='" . $platformListId . "' name='" . $platformListName . 
                                 "'><option value='-1'>Cannot load console list, please try later</option></select><br/><br/>";

        $errors = $dataAccess->CheckErrors();
        
        if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($platformQuery)){
                $results = $dataAccess->GetResultSet();
                if($results != null){
                    $ddlPlatformsHTML .= "<select id='" . $platformListId . "' name='" . $platformListName . "'>";
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
    
    private function TranslateOrderByToUserQueryOrderByClause($orderBy)
    {
        $queryOrderByClause = "";
        $orderByColumn = "";
        $orderByDirection = "";

        sscanf($orderBy, "%s %s", $orderByColumn, $orderByDirection);
        
        // Always sort by username at minimum, or after other specified columns when they have equal values
        switch($orderByColumn) {
            case "FirstName":
                $queryOrderByClause = "IFNULL(u.`FirstName`, '') " . $orderByDirection . ", u.`UserName`";
                break;
            case "LastName":
                $queryOrderByClause = "IFNULL(u.`LastName`, '') " . $orderByDirection . ", u.`UserName`";
                break;
            case "FullName":
                $queryOrderByClause = "(CONCAT((IFNULL(u.`FirstName`, '')), ' ', (IFNULL(u.`LastName`, '')))) " . $orderByDirection . ", u.`UserName`";
                break;
            case "Gender":
                $queryOrderByClause = "u.`Gender` " . $orderByDirection . ", u.`UserName`";
                break;
            case "UserName":
                $queryOrderByClause = "u.`UserName` " . $orderByDirection;
                break;
            case "InviteType":
                $queryOrderByClause = "(CASE WHEN ufiInvitee.`ID` IS NOT NULL THEN 'From Me' ELSE " . 
                                        "(CASE WHEN ufiInviter.`ID` IS NOT NULL THEN 'To Me' ELSE 'No' END) END) " . $orderByDirection;
                break;
            case "InviteReply":
                $queryOrderByClause = "(CASE WHEN ((IFNULL(ufiInvitee.`IsRejected`, -1) = -1) AND (IFNULL(ufiInviter.`IsRejected`, -1) = -1)) THEN 'N/A' " .
                                      "ELSE (CASE WHEN ((IFNULL(ufiInvitee.`IsRejected`, -1) = 0) OR (IFNULL(ufiInviter.`IsRejected`, -1) = 0)) THEN 'Pending' ELSE 'Rejected' END) " .
                                      "END) "  . $orderByDirection;
                break;
        }
        
        return $queryOrderByClause . " ";
    }
	
    private function TranslateOrderByToEventQueryOrderByClause($orderBy)
    {
        $queryOrderByClause = "";
        $orderByColumn = "";
        $orderByDirection = "";

        sscanf($orderBy, "%s %s", $orderByColumn, $orderByDirection);
        
        // Always sort by event scheduled date at minimum, or after other specified 
        //  columns when they have equal values
        switch($orderByColumn) {
            case "GameTitle":
                $queryOrderByClause = "(COALESCE(cg.`Name`, ug.`Name`)) " . $orderByDirection . ", e.`EventScheduledForDate`";
                break;
            case "Platform":
                $queryOrderByClause = "p.`Name` " . $orderByDirection . ", e.`EventScheduledForDate`";
                break;
            case "Hidden":
		// If sorting by "Hidden", need to reverse requested sort direction because "IsActive" is the opposite of "Hidden"
                $queryOrderByClause = "(CASE WHEN e.`IsActive` = 0 THEN 1 ELSE 0 END) " . $orderByDirection . ", e.`EventScheduledForDate`";
                break;
            case "DisplayDate":
                $queryOrderByClause = "e.`EventScheduledForDate` " . $orderByDirection;
                break;
            case "UserName":
                $queryOrderByClause = "u.`UserName` " . $orderByDirection . ", e.`EventScheduledForDate`";
                break;
            case "Actions":
                $queryOrderByClause = "(CASE WHEN em2.`ID` IS NOT NULL THEN (CASE WHEN (e.`EventScheduledForDate` < UTC_TIMESTAMP()) THEN 'JOINED' ELSE 'LEAVE' END) ELSE " .
                                      "(CASE WHEN membersByEvent.`JoinedCnt` >= e.`RequiredMemberCount` THEN 'FULL' ELSE (CASE WHEN (e.`EventScheduledForDate` < UTC_TIMESTAMP()) " .
                                        "THEN 'UNJOINED' ELSE 'JOIN' END) END) END) " . 
                                      $orderByDirection . ", e.`EventScheduledForDate`";
		break;
            case "EventCreator":
                $queryOrderByClause = "(CASE WHEN ((em2.`ID` IS NOT NULL) AND (em2.`ID` = u.`ID`)) THEN 'ME' ELSE u.`UserName` END) " . 
                                      $orderByDirection . ", e.`EventScheduledForDate`";
                break;
        }
        
        return $queryOrderByClause . " ";
    }

    public function BuildWhereClauseForFriendListQuery($userID, &$queryParms, $searchParms = null, $forAvailableFriendList = true)
    {
	// Want to exclude current user from displayed user list, no matter what
        $userLeftJoinClause = "";
        $userWhereClause = "WHERE (u.`ID` <> ?) ";
	array_push($queryParms, $userID);
		
	// Only show premium members: basic members are not allowed to have a friends list, or to be on someone else's friend list
	if(Constants::$isPayPalTest) {
            $userWhereClause .= "AND (u.`IsPremiumMember` = 1) AND (IFNULL(ppu.`IsActive`, 1) = 1) ";
	}
	else {
            $userWhereClause .= ("AND ((u.`IsPremiumMember` = 1) AND (IFNULL(ppu.`MembershipExpirationDate`, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 DAY)) > UTC_TIMESTAMP()) " .
                                 "AND (IFNULL(ppu.`IsActive`, 0) = 1)) ");
	}
        
	if($forAvailableFriendList) {
            $userWhereClause .= "AND (uf.`FK_User_ID_ThisUser` IS NULL) ";
	}
        else {
            if (!$searchParms->ShowCurrentFriendsForUser) {
		$userWhereClause .= "AND (uf.`FK_User_ID_ThisUser` IS NULL) ";
            }
			
            $userWhereClause .= ("AND ((IFNULL(uf.`FK_User_ID_ThisUser`, -1) = ?) OR " . 
                                "(ufiInviter.`FK_User_ID_Invitee` IS NOT NULL) OR (ufiInvitee.`FK_User_ID_Inviter` IS NOT NULL)) ");
            array_push($queryParms, $userID);
        }
		
	// Include current friends, as well as received & sent invitations, in Friends List table by default...only exclude if specifically filtered out by user
	if(!$forAvailableFriendList) {
            if(!$searchParms->ShowInvitesForUser) {
		$userWhereClause .= "AND (ufiInviter.`FK_User_ID_Invitee` IS NULL) ";
            }
            else if(!$searchParms->ShowRejectedInvites) {
                $userWhereClause .= "AND ((IFNULL(ufiInviter.`IsRejected`, 0)) = 0) ";
            }
            
            if(!$searchParms->ShowInvitesSentByUser) {
		$userWhereClause .= "AND (ufiInvitee.`FK_User_ID_Inviter` IS NULL) ";
            }
            else if(!$searchParms->ShowRejectedInvites) {
                $userWhereClause .= "AND ((IFNULL(ufiInvitee.`IsRejected`, 0)) = 0) ";
            }
	}
	else {
            // Do not include users with active invitations between them and current user in Available Users list
            $userWhereClause .= "AND (((ufiInviter.`ID` IS NULL) OR (ufiInviter.`IsRejected` = 1)) AND ((ufiInvitee.`ID` IS NULL) OR (ufiInvitee.`IsRejected` = 1))) ";
	}
        
	// Filter by users who have the specified platforms on their profile
        if((is_array($searchParms->Platforms)) && (count($searchParms->Platforms) > 0)) {
            $userLeftJoinClause .= "LEFT JOIN `Gaming.UserPlatforms` AS upl ON upl.`FK_User_ID` = u.`ID` ";
            $platformListForQuery = (str_repeat("?,", count($searchParms->Platforms) - 1)) . "?";
            $userWhereClause .= "AND ((IFNULL(upl.`FK_Platform_ID`, -1)) IN (" . $platformListForQuery . ")) ";
            $queryParms = array_merge($queryParms, $searchParms->Platforms);
        }
        
        // Filter by users who have the specified text in their username
        if(strlen($searchParms->UserName) > 0) {
            $userWhereClause .= "AND (u.`UserName` LIKE ?) ";
            array_push($queryParms, "%" . $searchParms->UserName . "%");
        }
        
        // Filter by users who have the specified text in their first name
        if(strlen($searchParms->FirstName) > 0) {
            $userWhereClause .= "AND (u.`FirstName` LIKE ?) ";
            array_push($queryParms, "%" . $searchParms->FirstName . "%");
        }

        // Filter by users who have the specified text in their last name
        if(strlen($searchParms->LastName) > 0) {
            $userWhereClause .= "AND (u.`LastName` LIKE ?) ";
            array_push($queryParms, "%" . $searchParms->LastName . "%");
        }
        
        // Filter by users who have the specified text in at least one of their gamer tags
        if(strlen($searchParms->GamerTag) > 0) {
            $userLeftJoinClause .= "LEFT JOIN `Gaming.UserGamerTags` AS ugt ON ugt.`FK_User_ID` = u.`ID` ";
            $userWhereClause .= "AND (ugt.`GamerTagName` LIKE ?) ";
            array_push($queryParms, "%" . $searchParms->GamerTag . "%");
        }
        
        // Filter by users who have the specified gender
        if(strlen($searchParms->Gender) > 0) {
            $userWhereClause .= "AND (u.`Gender` = ?) ";
            array_push($queryParms, $searchParms->Gender);
        }
		
        return ($userLeftJoinClause . $userWhereClause);
    }
    
    public function BuildWhereClauseForScheduledGameQuery($userID, &$queryParms, $eventId = -1, $isCountOnly = false, $searchParms = null, 
							  $eventIDs = [], $orderBy = "", $joinedMemberEventIds = [])
    {
        $eventWhereClause = "";
        
        // Determine if current user is member of the given event
        $eventMemberLeftJoinClause = "LEFT JOIN `Gaming.EventMembers` AS em2 ON (em2.`FK_Event_ID` = e.`ID`) AND (em2.`FK_User_ID` = ?) ";
        array_push($queryParms, $userID);
        
        if($searchParms->ShowFullEventsOnly || $searchParms->ShowOpenEventsOnly || (strripos($orderBy, "Actions") !== false)) {
            // Get count of members joined to each event
            $eventMemberLeftJoinClause .= "LEFT JOIN (SELECT COUNT(`ID`) AS JoinedCnt, `FK_Event_ID` FROM `Gaming.EventMembers` " . 
                                          "GROUP BY `FK_Event_ID`) AS membersByEvent ON membersByEvent.`FK_Event_ID` = e.`ID` ";            
        }
        
	if($eventId > -1) {
            // If filtering on a specific event, no need to check further filters -- go ahead and return data for this event
            $eventWhereClause .= "WHERE (e.`ID` = ?) ";
            array_push($queryParms, $eventId);
            return $eventMemberLeftJoinClause . $eventWhereClause;
        }
		
        if(count($eventIDs) > 0) {
            // Don't need to repeat the same work -- event ID list should already be properly filtered
            $eventIDListForQuery = (str_repeat("?,", count($eventIDs) - 1)) . "?";
            $eventWhereClause = "WHERE (e.`ID` IN (" . $eventIDListForQuery . ")) ";
            $queryParms = array_merge($queryParms, $eventIDs);
            return $eventMemberLeftJoinClause . $eventWhereClause;
        }
        
        if($searchParms->ShowFullEventsOnly) {            
            $eventWhereClause .= "AND (membersByEvent.`JoinedCnt` >= e.`RequiredMemberCount`) ";
        }
        else if($searchParms->ShowOpenEventsOnly) {            
            $eventWhereClause .= "AND (membersByEvent.`JoinedCnt` < e.`RequiredMemberCount`) ";
        }
        
	// Filter by events that have the specified members
        if(count($joinedMemberEventIds) > 0) {
            $joinedEventIDListForQuery = (str_repeat("?,", count($joinedMemberEventIds) - 1)) . "?";
            $eventWhereClause = "AND (e.`ID` IN (" . $joinedEventIDListForQuery . ")) ";
            $queryParms = array_merge($queryParms, $joinedMemberEventIds);
        }
        
        if(($userID > -1) && !$searchParms->ShowJoinedEvents && !$searchParms->ShowCreatedEvents) {
            // Exclude events created or joined by current user
            $eventWhereClause .= "AND (em2.`ID` IS NULL) AND (e.`FK_User_ID_EventCreator` <> ?) ";
            array_push($queryParms, $userID);
			
            // Filter out any private events for which the current user is not an allowed member, if viewing Current Events list (not seeing joined or created events)
            $eventWhereClause .= "AND ((e.`IsPublic` = 1) OR (e.`ID` IN ".
                                 "(SELECT `FK_Event_ID` FROM `Gaming.EventAllowedMembers`".
                                 " WHERE (`FK_User_ID` = ?) AND (`FK_Event_ID` = e.`ID`))".
                                 ")) ";
            
            array_push($queryParms, $userID);
        }
        
	// If searching for events from certain event creators, filter on them
	if(((is_array($searchParms->EventCreators)) && (count($searchParms->EventCreators) > 0)) && (strlen($searchParms->CustomEventCreatorName) > 0)) {
            $eventCreatorListForQuery = (str_repeat("?,", count($searchParms->EventCreators) - 1)) . "?";
            $eventWhereClause .= "AND ((e.`FK_User_ID_EventCreator` IN (" . $eventCreatorListForQuery . ")) ";
            $queryParms = array_merge($queryParms, $searchParms->EventCreators);
			
            $eventWhereClause .= "OR (u.`UserName` LIKE ?)) ";
            array_push($queryParms, "%" . $searchParms->CustomEventCreatorName . "%");
	}
	else if(((is_array($searchParms->EventCreators)) && (count($searchParms->EventCreators) > 0)) || (strlen($searchParms->CustomEventCreatorName) > 0)) {
            if(((is_array($searchParms->EventCreators)) && (count($searchParms->EventCreators) > 0))) {
		$eventCreatorListForQuery = (str_repeat("?,", count($searchParms->EventCreators) - 1)) . "?";
		$eventWhereClause .= "AND (e.`FK_User_ID_EventCreator` IN (" . $eventCreatorListForQuery . ")) ";
		$queryParms = array_merge($queryParms, $searchParms->EventCreators);
            }
            if(strlen($searchParms->CustomEventCreatorName) > 0) {
		$eventWhereClause .= "AND (u.`UserName` LIKE ?) ";
		array_push($queryParms, "%" . $searchParms->CustomEventCreatorName . "%");
            }
	}
		
        if(!$searchParms->ShowHiddenEvents) {
            $eventWhereClause .= "AND (e.`IsActive` = 1) ";
        }
        
	if($searchParms->ShowJoinedEvents && !$searchParms->ShowCreatedEvents) {
            $eventWhereClause .= "AND (em2.`ID` IS NOT NULL) AND (e.`FK_User_ID_EventCreator` <> ?) ";
            array_push($queryParms, $userID);
	}
	else if(!$searchParms->ShowJoinedEvents && $searchParms->ShowCreatedEvents) {
            $eventWhereClause .= "AND (e.`FK_User_ID_EventCreator` = ?) ";
            array_push($queryParms, $userID);			
	}
	else if($searchParms->ShowJoinedEvents && $searchParms->ShowCreatedEvents) {
            $eventWhereClause .= "AND (em2.`ID` IS NOT NULL) ";
	}	
		     
	if(!$searchParms->NoStartDateRestriction) {
            if(strlen($searchParms->StartDateTime) > 0) {            
		$eventWhereClause .= "AND (e.`EventScheduledForDate` >= ?) ";
		array_push($queryParms, $searchParms->StartDateTime);
								
		$eventWhereClause .= "AND (e.`EventScheduledForDate` <= ?) ";
		array_push($queryParms, $searchParms->EndDateTime);
            }
            else {
		$eventWhereClause .= "AND (e.`EventScheduledForDate` > UTC_TIMESTAMP()) "; // By default, only show future events
            }
	}
        
        // Filter on game titles, if any
        if(((is_array($searchParms->GameTitles)) && (count($searchParms->GameTitles) > 0)) && (strlen($searchParms->CustomGameTitle) > 0)) {
            $gameTitleListForQuery = (str_repeat("?,", count($searchParms->GameTitles) - 1)) . "?";
            $eventWhereClause .= "AND ((COALESCE(cg.`Name`, ug.`Name`) IN (" . $gameTitleListForQuery . ")) ";
            $queryParms = array_merge($queryParms, $searchParms->GameTitles);
			
            $eventWhereClause .= "OR (COALESCE(cg.`Name`, ug.`Name`) LIKE ?)) ";
            array_push($queryParms, "%" . $searchParms->CustomGameTitle . "%");
        }
        else if(((is_array($searchParms->GameTitles)) && (count($searchParms->GameTitles) > 0)) || (strlen($searchParms->CustomGameTitle) > 0)) {
            if(((is_array($searchParms->GameTitles)) && (count($searchParms->GameTitles) > 0))) {
                $gameTitleListForQuery = (str_repeat("?,", count($searchParms->GameTitles) - 1)) . "?";
                $eventWhereClause .= "AND (COALESCE(cg.`Name`, ug.`Name`) IN (" . $gameTitleListForQuery . ")) ";
                $queryParms = array_merge($queryParms, $searchParms->GameTitles);
            }
            if(strlen($searchParms->CustomGameTitle) > 0) {
                $eventWhereClause .= "AND (COALESCE(cg.`Name`, ug.`Name`) LIKE ?) ";
                array_push($queryParms, "%" . $searchParms->CustomGameTitle . "%");
            }
        }

        // Filter on platforms, if any
        if(((is_array($searchParms->Platforms)) && (count($searchParms->Platforms) > 0)) && (strlen($searchParms->CustomPlatformName) > 0)) {
            $platformListForQuery = (str_repeat("?,", count($searchParms->Platforms) - 1)) . "?";
            $eventWhereClause .= "AND ((p.`ID` IN (" . $platformListForQuery . ")) ";
            $queryParms = array_merge($queryParms, $searchParms->Platforms);
			
            $eventWhereClause .= "OR (p.`Name` LIKE ?)) ";
            array_push($queryParms, "%" . $searchParms->CustomPlatformName . "%");
        }
        else if(((is_array($searchParms->Platforms)) && (count($searchParms->Platforms) > 0)) || (strlen($searchParms->CustomPlatformName) > 0)) {
            if(((is_array($searchParms->Platforms)) && (count($searchParms->Platforms) > 0))) {
                $platformListForQuery = (str_repeat("?,", count($searchParms->Platforms) - 1)) . "?";
                $eventWhereClause .= "AND (p.`ID` IN (" . $platformListForQuery . ")) ";
                $queryParms = array_merge($queryParms, $searchParms->Platforms);
            }
            if(strlen($searchParms->CustomPlatformName) > 0) {
                $eventWhereClause .= "AND (p.`Name` LIKE ?) ";
                array_push($queryParms, "%" . $searchParms->CustomPlatformName . "%");
            }
        }

	// Replace first "AND" with a "WHERE"
	$firstAndPos = stripos($eventWhereClause, "and");
	if($firstAndPos !== false) {
            $eventWhereClause = "WHERE " . (substr($eventWhereClause, ($firstAndPos + 4)));
	}
        
        return $eventMemberLeftJoinClause . $eventWhereClause;
    }
	
    public function GetScheduledGames($dataAccess, $logger, $userID, $orderBy = "DisplayDate ASC", $paginationEnabled = false, $startIndex = "0", 
                                      $pageSize = "10", $eventId = -1, $searchParms = null, $eventIDs = [], $eventMembersByEvent = [], $userAllowedFriendsByEvent = [])
    {
	$limitClause = "";
        
	if($paginationEnabled && (count($eventIDs) == 0)) {
            $limitClause = "LIMIT " . $startIndex . "," . $pageSize;
	}
	
        $queryParms = [];
        $eventWhereClause = $this->BuildWhereClauseForScheduledGameQuery($userID, $queryParms, $eventId, false, $searchParms, $eventIDs, $orderBy);
        $orderByClause = "ORDER BY " . $this->TranslateOrderByToEventQueryOrderByClause($orderBy);
        
        $getUserGamesQuery = "SELECT e.`ID`, COALESCE(cg.`Name`, ug.`Name`) AS GameTitle, COALESCE(tz.`Abbreviation`, tz.`Description`) AS TimeZone, " .
                             "e.`EventScheduledForDate`, e.`DisplayDate`, e.`DisplayTime`, e.`RequiredMemberCount`, p.`Name` AS Platform, e.`Notes`, " . 
                             "e.`FK_Game_ID` AS GameID, tz.`ID` AS TimezoneID, p.`ID` AS PlatformID, e.`IsActive`, " .
                             "u.`UserName` AS EventCreatorUserName, u.`ID` AS EventCreatorUserID, ".
                             "(CASE WHEN em2.`ID` IS NULL THEN 0 ELSE 1 END) AS thisUserIsJoined " .
                             "FROM `Gaming.Events` AS e ".
                             "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
                             "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
                             "INNER JOIN `Security.Users` AS u ON u.`ID` = e.`FK_User_ID_EventCreator` ".
                             "LEFT JOIN `Configuration.Games` AS cg ON cg.`ID` = e.`FK_Game_ID` ".
                             "LEFT JOIN `Gaming.UserGames` AS ug ON ug.`ID` = e.`FK_UserGames_ID` ".
                             $eventWhereClause . $orderByClause . $limitClause . ";";
        
	$userGames = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserGamesQuery)){
		$results = $dataAccess->GetResultSetWithPositionalParms($queryParms);
					
		if($results != null){
                    foreach($results as $row) {
                        $evtMembers = [];
                        $curEvent = Utils::SearchGameArrayByEventID($eventMembersByEvent, $row['ID']);
                        if($curEvent != null) {
                            $evtMembers = $curEvent->EventMembers;
                        }
                        
                        $eventAllowedFriends = [];
                        $curEvent = Utils::SearchGameArrayByEventID($userAllowedFriendsByEvent, $row['ID']);
                        if($curEvent != null) {
                            $eventAllowedFriends = $curEvent->FriendsAllowed;
                        }
                        
                        // Show event scheduled date to user in standard time format, with no seconds shown, and AM/PM indicator
                        $displayTime = Utils::ConvertMilitaryTimeToStandardTime($row['DisplayTime']);

                        $userGame = Game::ConstructGameForEvent($row['GameID'], $row['DisplayDate'], $displayTime, $row['RequiredMemberCount'], $row['Notes'], $eventAllowedFriends, 
                                                                false, $row['TimezoneID'], $row['PlatformID'], $row['GameTitle'], $row['EventScheduledForDate'], $row['Platform'], 
                                                                $row['TimeZone'], $row['ID'], $row['IsActive'] == '1' ? true : false, $row['EventCreatorUserName'],
                                                                $row['EventCreatorUserID']);
                        $userGame->EventMembers = $evtMembers;
                        $this->SetEventJoinStatus($userGame, $evtMembers, $row['RequiredMemberCount'], $row['thisUserIsJoined'], 
                                                  $row['EventScheduledForDate'], $userID);
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
	
    private function SetEventJoinStatus($userGame, $evtMembers, $requiredMemberCount, $thisUserIsJoined, $eventScheduledDate, $userID)
    {
        // Calculate join status of this game: full (all required members signed up), join (open for current user to join), leave (current user is joined already),
	// or past (event was scheduled for a past time, so join status cannot be changed)
	$eventDate = date_create_from_format("Y-m-d H:i:s", $eventScheduledDate, new DateTimeZone("UTC"));
	$curUTCDate = new DateTime(null, new DateTimeZone("UTC"));
	$isPastEvent = ($curUTCDate > $eventDate);
		
        $joinStatus = $isPastEvent ? "UNJOINED" : "JOIN";
        if($userGame->EventCreatorUserID == $userID) {
            $joinStatus = $isPastEvent ? "OLD" : "EDIT";
        }
        else if($thisUserIsJoined == 1) {
            $joinStatus = $isPastEvent ? "JOINED" : "LEAVE";
        }
        else if(count($evtMembers) >= $requiredMemberCount) {
            $joinStatus = "FULL";
        }

        $userGame->JoinStatus = $joinStatus;
    }

    private function GetTotalCountFriendListUsers($dataAccess, $logger, $userID,  $searchParms = null, $forAvailableFriendList = true)
    {
	$totalUserCnt = 0;
		
	$queryParms = [$userID, $userID, $userID];
	$filterWhereClause = $this->BuildWhereClauseForFriendListQuery($userID, $queryParms, $searchParms, $forAvailableFriendList);
		
        $getAvailableUserQuery = "SELECT COUNT(DISTINCT u.`ID`) AS Cnt " .
                                 "FROM `Security.Users` as u " .
				 "LEFT JOIN `Gaming.UserFriends` as uf ON (uf.`FK_User_ID_Friend` = u.`ID`) AND (uf.`FK_User_ID_ThisUser` = ?) " .
				 "LEFT JOIN `Gaming.UserFriendInvitations` ufiInviter ON (u.`ID` = ufiInviter.`FK_User_ID_Inviter`) AND (ufiInviter.`FK_User_ID_Invitee` = ?) " .
				 "LEFT JOIN `Gaming.UserFriendInvitations` ufiInvitee ON (u.`ID` = ufiInvitee.`FK_User_ID_Invitee`) AND (ufiInvitee.`FK_User_ID_Inviter` = ?) " .
				 "LEFT JOIN `Payments.PayPalUsers` as ppu ON ppu.`FK_User_ID` = u.`ID` " .
                                 $filterWhereClause;
        
        if($dataAccess->BuildQuery($getAvailableUserQuery)) {
            $results = $dataAccess->GetSingleResultWithPositionalParms($queryParms);

            if($results != null){
                $totalUserCnt = $results['Cnt'];
            }
        }
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve count of user friends. " . $errors);
	}
		
	return $totalUserCnt;
    }
	
    private function GetTotalCountScheduledGames($dataAccess, $logger, $userID, $searchParms = null, $filterCountByJoinedUsers = false)
    {
	$userTotalScheduledGamesCnt = 0;
	
        $queryParms = [];
        $eventWhereClause = $this->BuildWhereClauseForScheduledGameQuery($userID, $queryParms, -1, true, $searchParms);
		
	if(!$filterCountByJoinedUsers) {
            $getUserGamesQuery = "SELECT COUNT(e.`ID`) AS Cnt " .
				 "FROM `Gaming.Events` AS e ".
				 "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
				 "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
				 "INNER JOIN `Security.Users` AS u ON u.`ID` = e.`FK_User_ID_EventCreator` ".
				 "LEFT JOIN `Configuration.Games` AS cg ON cg.`ID` = e.`FK_Game_ID` ".
				 "LEFT JOIN `Gaming.UserGames` AS ug ON ug.`ID` = e.`FK_UserGames_ID` ".
				 $eventWhereClause . ";";
			
            $errors = $dataAccess->CheckErrors();
			
            if(strlen($errors) == 0) {
		if($dataAccess->BuildQuery($getUserGamesQuery)){
                    $results = $dataAccess->GetSingleResultWithPositionalParms($queryParms);
						
                    if($results != null){
			$userTotalScheduledGamesCnt = $results['Cnt'];
                    }
		}
            }
	}
	else {
            $getUserGamesQuery = "SELECT e.`ID` AS EventID, em.`FK_User_ID` AS UserID, LOWER(u2.`UserName`) AS UserName " .
				 "FROM `Gaming.Events` AS e ".
				 "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
				 "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
				 "INNER JOIN `Security.Users` AS u ON u.`ID` = e.`FK_User_ID_EventCreator` ".
				 "LEFT JOIN `Configuration.Games` AS cg ON cg.`ID` = e.`FK_Game_ID` ".
				 "LEFT JOIN `Gaming.UserGames` AS ug ON ug.`ID` = e.`FK_UserGames_ID` ".
				 "LEFT JOIN `Gaming.EventMembers` AS em ON (em.`FK_Event_ID` = e.`ID`) " .
				 "LEFT JOIN `Security.Users` AS u2 ON (u2.`ID` = em.`FK_User_ID`) " .
				 $eventWhereClause . ";";
			
            $errors = $dataAccess->CheckErrors();
			
            if(strlen($errors) == 0) {
		if($dataAccess->BuildQuery($getUserGamesQuery)){
                    $results = $dataAccess->GetResultSetWithPositionalParms($queryParms);
						
                    if($results != null){
			$eventIdList = [];
                        foreach($results as $row) {
                            if((in_array($row['UserID'], $searchParms->JoinedUsers)) || 
                               (strtolower($searchParms->CustomJoinedUserName) == $row['UserName'])) {
                                $eventIdList[] = $row['EventID'];
                            }
                        }
						
                        $eventsWithJoinedMembers = array_unique($eventIdList, SORT_NUMERIC);
                        $userTotalScheduledGamesCnt = count($eventsWithJoinedMembers);
                    }
                }
            }
        }
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve count of user scheduled games. " . $errors);
	}
        
        return $userTotalScheduledGamesCnt;
    }
    
    private function GetScheduledGameIDList($dataAccess, $logger, $userID, $searchParms = null, $orderBy = "", $paginationEnabled = false, 
                                            $startIndex = 0, $pageSize = 10, $joinedMemberEventIds = [])
    {
        $queryParms = [];
	$limitClause = "";
	if($paginationEnabled) {
            $limitClause = "LIMIT " . $startIndex . "," . $pageSize;
	}
        $eventWhereClause = $this->BuildWhereClauseForScheduledGameQuery($userID, $queryParms, -1, false, $searchParms, [], $orderBy, $joinedMemberEventIds);
	$orderByClause = "ORDER BY " . $this->TranslateOrderByToEventQueryOrderByClause($orderBy);	
        
        $getUserGamesQuery = "SELECT DISTINCT e.`ID` FROM `Gaming.Events` AS e ".
                             "INNER JOIN `Configuration.TimeZones` AS tz ON tz.`ID` = e.`FK_Timezone_ID` ".
                             "INNER JOIN `Configuration.Platforms` AS p ON p.`ID` = e.`FK_Platform_ID` ".
                             "INNER JOIN `Security.Users` AS u ON u.`ID` = e.`FK_User_ID_EventCreator` ".
                             "LEFT JOIN `Configuration.Games` AS cg ON cg.`ID` = e.`FK_Game_ID` ".
                             "LEFT JOIN `Gaming.UserGames` AS ug ON ug.`ID` = e.`FK_UserGames_ID` ".
                             $eventWhereClause . $orderByClause . $limitClause . ";";
        
        $errors = $dataAccess->CheckErrors();
        $eventIds = [];
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserGamesQuery)){
		$results = $dataAccess->GetResultSetWithPositionalParms($queryParms);
					
		if($results != null){
                    foreach($results as $row) {
                        array_push($eventIds, $row["ID"]);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve distinct list of event IDs for current table page. " . $errors);
	}
        
        return $eventIds;
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
    
    public function GetEventMembers($dataAccess, $logger, $eventIDs)
    {
        $eventIdParamList = (str_repeat("?,", count($eventIDs) - 1)) . "?";
        
        $getEventMembersQuery = "SELECT u.`UserName`, CASE WHEN e.`FK_User_ID_EventCreator` = em.`FK_User_ID` THEN ' (Creator)' ELSE '' END AS EventCreator, " .
				"em.`ID`, em.`FK_Event_ID` AS EventID, em.`FK_User_ID` AS UserID " .
				"FROM `Gaming.EventMembers` AS em " .
                                "INNER JOIN `Security.Users` AS u ON em.`FK_User_ID` = u.`ID` " .
				"INNER JOIN `Gaming.Events` AS e ON e.`ID` = em.`FK_Event_ID` " .
                                "WHERE (em.`FK_Event_ID` IN (" . $eventIdParamList . ")) " .
				"ORDER BY e.`ID`, u.`UserName`;";
        
        $eventMembers = array();
	$eventMembersByEvent = array();
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getEventMembersQuery)){
		$results = $dataAccess->GetResultSetWithPositionalParms($eventIDs);
					
		if($results != null){
                    $lastEventId = -1;
                    foreach($results as $row) {
                        if(($lastEventId <> $row['EventID']) && ($lastEventId > -1)) {
                            $event = new Game(-1, "", false);
                            $event->EventID = $lastEventId;
                            $event->EventMembers = $eventMembers;
                            
                            $eventMembersByEvent[] = $event;
                            $eventMembers = array();
                        }
                        
			$eventMember = new EventMember($row['ID'], $row['EventID'], $row['UserID'], $row['UserName'], $row['UserName'] . $row['EventCreator']);
			array_push($eventMembers, $eventMember);
                        $lastEventId = $row['EventID'];
                    }
                    
                    $lastEvent = new Game(-1, "", false);
                    $lastEvent->EventID = $lastEventId;
                    $lastEvent->EventMembers = $eventMembers;
                    $eventMembersByEvent[] = $lastEvent;
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve members for events. " . $errors);
	}
        
        return $eventMembersByEvent;
    }
	
    public function GetEventsJoinedByUsersInList($dataAccess, $logger, $filterUsers, $filterUserCustomName, $eventIDs)
    {
        $eventIdParamList = (str_repeat("?,", count($eventIDs) - 1)) . "?";
	$whereClause = "WHERE (em.`FK_Event_ID` IN (" . $eventIdParamList . ")) ";
	$queryParms = $eventIDs;
		
	if(((is_array($filterUsers)) && (count($filterUsers) > 0)) && (strlen($filterUserCustomName) > 0)) {
            $joinedUserListForQuery = (str_repeat("?,", count($filterUsers) - 1)) . "?";
            $whereClause .= "AND ((em.`FK_User_ID` IN (" . $joinedUserListForQuery . ")) ";
            $queryParms = array_merge($queryParms, $filterUsers);

            $whereClause .= "OR (u.`UserName` LIKE ?)) ";
            array_push($queryParms, "%" . $filterUserCustomName . "%");
	}
	else if(((is_array($filterUsers)) && (count($filterUsers) > 0)) || (strlen($filterUserCustomName) > 0)) {
            if(((is_array($filterUsers)) && (count($filterUsers) > 0))) {
		$joinedUserListForQuery = (str_repeat("?,", count($filterUsers) - 1)) . "?";
		$whereClause .= "AND (em.`FK_User_ID` IN (" . $joinedUserListForQuery . ")) ";
		$queryParms = array_merge($queryParms, $filterUsers);
            }
            if(strlen($filterUserCustomName) > 0) {
		$whereClause .= "AND (u.`UserName` LIKE ?) ";
		array_push($queryParms, "%" . $filterUserCustomName . "%");
            }
	}
        
        $getEventsJoinedByUsersQuery = "SELECT DISTINCT em.`FK_Event_ID` as ID FROM `Gaming.EventMembers` AS em " .
                                       "INNER JOIN `Security.Users` AS u ON em.`FK_User_ID` = u.`ID` " . $whereClause . ";";
        
        $joinedEventIDs = array();
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getEventsJoinedByUsersQuery)){
		$results = $dataAccess->GetResultSetWithPositionalParms($queryParms);
					
		if($results != null){
                    foreach($results as $row) {                        
			array_push($joinedEventIDs, $row['ID']);
                    }
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve events joined by specified users. " . $errors);
	}
        
        return $joinedEventIDs;
    }
    
    public function SendFriendInviteToUsers($dataAccess, $logger, $userID, $userIds)
    {
	// Remove any existing invitations between current user and the current set of invitees
        if($this->DeleteInvitationsBetweenUsers($dataAccess, $userID, $userIds)) {
            // Insert current friend invitations
            $sendInviteToUsersQuery = "INSERT INTO `Gaming.UserFriendInvitations` (`FK_User_ID_Invitee`,`FK_User_ID_Inviter`) VALUES ";

            $valuesClauseFormat = "(%s, %s)";
            $inviteeParmNameFormat = ":userIdInvitee%d";
            $inviterParmNameFormat = ":userIdInviter%d";
            $queryParms = [];

            // Build insert statement
            for($i = 0; $i < count($userIds); $i++) {
                $valuesClauseSuffix = ", ";
                if($i === (count($userIds) - 1)) {
                    $valuesClauseSuffix = ";";
                }

                $inviteeParmName = sprintf($inviteeParmNameFormat, $i);
                $inviterParmName = sprintf($inviterParmNameFormat, $i);
                $sendInviteToUsersQuery .= (sprintf($valuesClauseFormat, $inviteeParmName, $inviterParmName) . $valuesClauseSuffix);

                $parmInviteeUserId = new QueryParameter($inviteeParmName, $userIds[$i], PDO::PARAM_INT);
                $parmInviterUserId = new QueryParameter($inviterParmName, $userID, PDO::PARAM_INT);
                array_push($queryParms, $parmInviteeUserId, $parmInviterUserId);
            }

            if($dataAccess->BuildQuery($sendInviteToUsersQuery, $queryParms)){
                $dataAccess->ExecuteNonQuery();
            }

            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
                return 'SUCCESS: All selected users have been sent a friend invitation.';
            }
        }
	
        $logger->LogError(sprintf("SendFriendInviteToUsers(): Could not send invite to user IDs [%s] for current user ID [%d]. %s", 
                                    implode(',', $userIds), $userID, $errors));
	return 'SYSTEM ERROR: Could not send invitation to selected users. Please try again later.';
    }
    
    public function RemoveUsersFromFriendList($dataAccess, $logger, $userID, $userIds)
    {
        // Reject any pending invitations from selected users
        $userIdParamList = (str_repeat("?,", count($userIds) - 1)) . "?";
	$queryParms = $userIds;
	array_push($queryParms, $userID);
        
        $rejectPendingInvitesQuery = "UPDATE `Gaming.UserFriendInvitations` SET `IsRejected` = 1 " . 
                                     "WHERE (`FK_User_ID_Inviter` IN (" . $userIdParamList . ")) AND (`FK_User_ID_Invitee` = ?) AND (`IsRejected` = 0);";

        if($dataAccess->BuildQuery($rejectPendingInvitesQuery)){
            $dataAccess->ExecuteNonQueryWithPositionalParms($queryParms);
        }
        
        // Cancel any pending invitations to selected users from current user
        $errors = $dataAccess->CheckErrors();
        if(strlen($errors) == 0) {        
            $deleteQuery = "DELETE FROM `Gaming.UserFriendInvitations` WHERE " . 
                           "(`FK_User_ID_Invitee` IN (" . $userIdParamList . ")) AND (`FK_User_ID_Inviter` = ?) AND (`IsRejected` = 0);";

            if($dataAccess->BuildQuery($deleteQuery)){
                $dataAccess->ExecuteNonQueryWithPositionalParms($queryParms);

                // Remove any of the selected users who are currently friends of the logged-in user
                $errors = $dataAccess->CheckErrors();
                if(strlen($errors) == 0) {
                    $deleteQuery = "DELETE FROM `Gaming.UserFriends` WHERE " . 
                                   "((`FK_User_ID_Friend` IN (" . $userIdParamList . ")) AND (`FK_User_ID_ThisUser` = ?)) OR " .
                                   "((`FK_User_ID_ThisUser` IN (" . $userIdParamList . ")) AND (`FK_User_ID_Friend` = ?));";

                    $queryParms = array_merge($queryParms, $userIds);
                    array_push($queryParms, $userID);
                    if($dataAccess->BuildQuery($deleteQuery)){
                        $dataAccess->ExecuteNonQueryWithPositionalParms($queryParms);

                        $errors = $dataAccess->CheckErrors();
                        if(strlen($errors) == 0) {
                            return 'SUCCESS: Removed all selected users from friends list, including any pending invitations.';
                        }
                    }
                }
            }
        }
        
        $logger->LogError(sprintf("RemoveUsersFromFriendList(): Could not reject invite from user IDs [%s] for current user ID [%d]. %s", 
                                    implode(',', $userIds), $userID, $errors));
        return 'SYSTEM ERROR: Could not reject invitations from selected users. Please try again later.';
    }
    
    public function AcceptUserFriendInvites($dataAccess, $logger, $userID, $userIds)
    {
	// Wrap UserFriends insert and UserFriendInvitations update in transaction: both must succeed, or must roll back both
	if(!$dataAccess->BeginTransaction()) {
            $errors = "Could not begin transaction.";
            $logger->LogError(sprintf("Could not accept invite from user IDs [%s] for current user ID [%d]. %s", implode(',', $userIds), $userID, $errors));
            return 'SYSTEM ERROR: Could not accept invitations from selected users. Please try again later.';
	}
		
        $acceptInviteFromUsersQuery = "INSERT INTO `Gaming.UserFriends` (`FK_User_ID_Friend`,`FK_User_ID_ThisUser`) VALUES ";
		
        $valuesClauseFormat = "(%s, %s)";
        $friendParmNameFormat = ":userIdFriend%d";
        $thisUserParmNameFormat = ":userIdThisUser%d";
        $queryParms = [];
	$insertSuccess = false;
        $deleteSuccess = false;

        // Build insert statement
        for($i = 0, $j = 0; $i < count($userIds); $i++, $j += 2) {
            $valuesClauseSuffix = ", ";
            if($i === (count($userIds) - 1)) {
                $valuesClauseSuffix = ";";
            }
            
            // Add this user as current (logged-in) user's friend
            $friendParmName = sprintf($friendParmNameFormat, $j);
            $thisUserParmName = sprintf($thisUserParmNameFormat, $j);
            $acceptInviteFromUsersQuery .= (sprintf($valuesClauseFormat, $friendParmName, $thisUserParmName) . ', ');

            $parmFriendUserId = new QueryParameter($friendParmName, $userIds[$i], PDO::PARAM_INT);
            $parmThisUserId = new QueryParameter($thisUserParmName, $userID, PDO::PARAM_INT);
            array_push($queryParms, $parmFriendUserId, $parmThisUserId);
            
            // Add current (logged-in) user as this user's friend
            $friendParmName = sprintf($friendParmNameFormat, ($j + 1));
            $thisUserParmName = sprintf($thisUserParmNameFormat, ($j + 1));
            $acceptInviteFromUsersQuery .= (sprintf($valuesClauseFormat, $friendParmName, $thisUserParmName) . $valuesClauseSuffix);

            $parmFriendUserId = new QueryParameter($friendParmName, $userID, PDO::PARAM_INT);
            $parmThisUserId = new QueryParameter($thisUserParmName, $userIds[$i], PDO::PARAM_INT);
            array_push($queryParms, $parmFriendUserId, $parmThisUserId);            
        }
		
	if($dataAccess->BuildQuery($acceptInviteFromUsersQuery, $queryParms)){
            $insertSuccess = $dataAccess->ExecuteNonQuery();
	}
		
	$errors = $dataAccess->CheckErrors();
	if($insertSuccess && (strlen($errors) == 0)) {
            // If friends successfully added to this user's friend list, remove invitations
            $deleteSuccess = $this->DeleteInvitationsBetweenUsers($dataAccess, $userID, $userIds);

            if($deleteSuccess) {
		if($dataAccess->CommitTransaction())  return "SUCCESS: Accepted friend invitations from all selected users";
		else                                  $errors = "Could not commit transaction...rolling back";
            }
	}
		
	if($dataAccess->CheckIfInTransaction())
	{
            $dataAccess->RollbackTransaction();
	}
		
	$logger->LogError(sprintf("AcceptUserFriendInvites(): Could not accept invite from user IDs [%s] for current user ID [%d]. %s", 
                                    implode(',', $userIds), $userID, $errors));
	return 'SYSTEM ERROR: Could not accept invitations from selected users. Please try again later.';
    }
    
    public function DeleteInvitationsBetweenUsers($dataAccess, $pivotUser, $selectedUsers)
    {
        $deleteSuccess = false;
	$userIdParamList = (str_repeat("?,", count($selectedUsers) - 1)) . "?";
	$deleteQuery = "DELETE FROM `Gaming.UserFriendInvitations` WHERE " . 
                       "(((`FK_User_ID_Invitee` IN (" . $userIdParamList . ")) AND (`FK_User_ID_Inviter` = ?)) OR " .
                       "((`FK_User_ID_Inviter` IN (" . $userIdParamList . ")) AND (`FK_User_ID_Invitee` = ?)));";
        
	$queryParms = $selectedUsers;
	array_push($queryParms, $pivotUser);
        $queryParms = array_merge($queryParms, $selectedUsers);
        array_push($queryParms, $pivotUser);
		
	if($dataAccess->BuildQuery($deleteQuery)){
            $deleteSuccess = $dataAccess->ExecuteNonQueryWithPositionalParms($queryParms);
	}

        return ($deleteSuccess && (strlen($dataAccess->CheckErrors()) == 0));
    }
}
