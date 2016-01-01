<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/Logger.class.php';
include_once 'classes/User.class.php';
include_once 'classes/password.php';

class SecurityHandler
{    
    public function EncryptPassword($password)
    {
        // Use PHP default hashing algorithm -- using this constant will allow
        // automatic upgrade to the latest, strongest algorithms as they are
        // successively set as the default
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function ValidatePassword($password, $hash)
    {
        return password_verify($password, $hash) === TRUE;
    }
    
    // Returns security level of authenticated user, or -1 if user not authenticated
    public function AuthenticateUser($dataAccess, $logger, $userName, $password)
    {
        $objUser = User::constructDefaultUser();

        $authenticateUserQuery = "SELECT u.`ID`, u.`Password`, r.`SecurityLevel`, u.`FK_Timezone_ID`, u.`FirstName`, u.`LastName`, " .
                                 "u.`EmailAddress`, u.`IsPremiumMember`, u.`Gender`, u.`Birthdate`, u.`Autobiography` " .
                                 "FROM `Security.Users` as u " .
                                 "INNER JOIN `Security.UserRoles` ur ON ur.`FK_User_ID` = u.`ID` " .
                                 "INNER JOIN `Security.Roles` as r ON r.`ID` = ur.`FK_Role_ID` " .
                                 "WHERE (u.`UserName` = :userName);";
		
	$parmUserName = new QueryParameter(':userName', $userName, PDO::PARAM_STR);
	$queryParms = array($parmUserName);
	
	$errors = $dataAccess->CheckErrors();
        $success = false;
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($authenticateUserQuery, $queryParms)){
                $results = $dataAccess->GetSingleResult();
                
                if($results != null){
                    if($this->ValidatePassword($password, $results['Password'])) {
                        $userPlatforms = $this->LoadUserPlatforms($dataAccess, $logger, $results['ID']);
						
			$objUser = new User($results['ID'], $results['SecurityLevel'], $results['FK_Timezone_ID'], $userName, 
                                            $results['FirstName'], $results['LastName'], $results['EmailAddress'], $results['IsPremiumMember'], 
                                            $results['Gender'], $results['Birthdate'], $results['Autobiography'], $userPlatforms);
                        $success = true;
                    }
                }
            }
	}
        
        if(!$success) {
            $errors = $dataAccess->CheckErrors();
            $logger->LogError("Could not authenticate user '" . $userName . "'. " . $errors);
        }
        
        return $objUser;
    }
	
    public function LoadUserPlatforms($dataAccess, $logger, $userID)
    {
        $getUserPlatformsQuery = "SELECT `FK_Platform_ID` FROM `Gaming.UserPlatforms` " .
                                 "WHERE `FK_User_ID` = :userID;";
        
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
	$userPlatforms = array();
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($getUserPlatformsQuery, $queryParms)){
		$results = $dataAccess->GetResultSet();
					
		if($results != null){
                    foreach($results as $row) {
                        array_push($userPlatforms, $row['FK_Platform_ID']);
                    }
		}
            }
	}
        
        $errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve user platforms. " . $errors);
	}
        
        return $userPlatforms;
    }
    
    public function LoadUserPlatformNames($dataAccess, $logger, $userID)
    {
        $getUserPlatformsQuery = "SELECT p.`Name` FROM `Gaming.UserPlatforms` as upl " .
                                 "INNER JOIN `Configuration.Platforms` as p ON p.`ID` = upl.`FK_Platform_ID` " .
                                 "WHERE upl.`FK_User_ID` = :userID;";
        
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
	$userPlatforms = array();
        
        if($dataAccess->BuildQuery($getUserPlatformsQuery, $queryParms)){
            $results = $dataAccess->GetResultSet();

            if($results != null){
                foreach($results as $row) {
                    array_push($userPlatforms, $row['Name']);
                }
            }
        }
        
        $errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve user platforms. " . $errors);
	}
        
        return $userPlatforms;
    }
    
    public function UserCanAccessThisPage($dataAccess, $logger, $pageName, $redirectPageOnFailure)
    {
        // Ensure user is logged in
        $redirectToLogin = true;
	$redirectMsg = "";
        
        if(isset($_SESSION['WebUser'])) {
            $objUser = $_SESSION['WebUser'];

            // Check session lifetime -- if 10 minutes or more since last activity, invalidate session
            $timeSinceLastActivity = time() - $_SESSION['lastActivity'];
            
            if($timeSinceLastActivity > 600) {
                session_unset();
                session_destroy();
		$redirectMsg = "Session Inactivity Timeout - Please Log In Again";
            }
            else {
                // Check that user is authorized to view this page
                if($this->UserAuthorizedForThisPage($dataAccess, $logger, $pageName, $objUser->SecurityLevel)) {
                    // For authenticated, authorized users...allow access to this page
                    $redirectToLogin = false;
                }
		else {
                    $redirectMsg = "Not Authorized For [" . $pageName . "] Page";
		}
            }
        }
	else {
            $redirectMsg = "Unauthorized: Must Be Logged In To View [" . $pageName . "] Page";
	}
		
        if($redirectToLogin == true) {
            // Generate query string
            $params = array_merge($_GET, array("redirectMsg" => $redirectMsg));
            $queryString = http_build_query($params);
			
            // Redirect to original page if user not logged in or authorized for this page
            header("HTTP/1.1 401 Unauthorized");
            header("Location: " . $redirectPageOnFailure . "?" . $queryString);
            exit();
        }
		
        return true;
    }
    
    public function LogoutUser()
    {
        if(isset($_SESSION['WebUser'])) {
            session_unset();
            session_destroy();
        }
    }
	
    private function UserAuthorizedForThisPage($dataAccess, $logger, $pageName, $userSecurityLevel)
    {
        $requiredPageSecurityLevel = -1;
        
        $checkPageSecLevelQuery = "SELECT r.`SecurityLevel` FROM `Security.Pages` as p " .
                                  "INNER JOIN `Security.PageRoles` pr ON pr.`FK_Page_ID` = p.`ID` " .
                                  "INNER JOIN `Security.Roles` as r ON r.`ID` = pr.`FK_Role_ID` " .
                                  "WHERE p.`Name` = :pageName;";
        
        $parmPageName = new QueryParameter(':pageName', $pageName, PDO::PARAM_STR);
        $queryParms = array($parmPageName);
        
        $errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($checkPageSecLevelQuery, $queryParms)){
		$results = $dataAccess->GetSingleResult();
                
                if($results != null){
                    $requiredPageSecurityLevel = $results['SecurityLevel'];
                }
            }
	}
        
        if($requiredPageSecurityLevel == -1){
            $errors = $dataAccess->CheckErrors();
            $logger->LogError("Could not check authorization for page '" . $pageName . "'. " . $errors);
        }
        
        return ($userSecurityLevel <= $requiredPageSecurityLevel);
    }
    
    public function InsertBasicUser($dataAccess, $logger, $password, $userEmail, $basicRoleId, $userSecLevel)
    {
        $createdUserId = -1;
	$isPremiumMember = 0;
        $objUser = User::constructDefaultUser();
        
        $insertUserQuery = "INSERT INTO `Security.Users` (`IsPremiumMember`,`Password`,`EmailAddress`) " .
			   "VALUES (:IsPremiumMember, :password, :emailAddress);";
		
	$parmIsPremium = new QueryParameter(':IsPremiumMember', $isPremiumMember, PDO::PARAM_BOOL);
	$parmPassword = new QueryParameter(':password', $password, PDO::PARAM_STR);
	$parmEmail = new QueryParameter(':emailAddress', strtolower($userEmail), PDO::PARAM_STR);
	$queryParms = array($parmIsPremium, $parmPassword, $parmEmail);
			
	$errors = $dataAccess->CheckErrors();
			
	if(strlen($errors) == 0) {
            // Wrap user account creation and user security role assignment inserts into a transaction
            $dataAccess->BeginTransaction();
            $errors = $dataAccess->CheckErrors();
            
            if(strlen($errors) == 0) {
                if($dataAccess->BuildQuery($insertUserQuery, $queryParms)){
                    $results = $dataAccess->ExecuteNonQuery();
                    if($results == true){
                        $createdUserId = $dataAccess->GetLastInsertId();
                    }
                }

                $errors = $dataAccess->CheckErrors();

                if(strlen($errors) == 0) {
                    $userSecLevelIsSet = $this->AssignUserSecurityRole($dataAccess, $logger, $createdUserId, $basicRoleId);

                    if($userSecLevelIsSet) {
                        // Commit transaction -- account is created and security level is set
                        $dataAccess->CommitTransaction();
                        $errors = $dataAccess->CheckErrors();

                        if(strlen($errors) > 0) {
                            $logger->LogError("Could not commit transaction to create user '." . $userEmail . "'. " . $errors);
                        }
                        else {
                            $objUser = new User($createdUserId, $userSecLevel, -1, "", "", "", $userEmail, false, "", "", "", []);
                        }
                    }
                }

                // If we encountered an error on account creation or security role assignment, roll back everything and return failure code
                if($objUser->UserID == -1) {
                    $logger->LogError("Could not insert user '." . $userEmail . "' into Users table. " . $errors);
                    $dataAccess->RollbackTransaction();
                }
            }
            else {
                $logger->LogError("Could not begin transaction to create user '." . $userEmail . "'. " . $errors);
            }
	}
        else {
            $logger->LogError("Could not format query to insert user '." . $userEmail . "' into Users table. " . $errors);
        }
        
	return $objUser;
    }
	
    private function AssignUserSecurityRole($dataAccess, $logger, $userId, $roleId)
    {
        $assignUserRoleQuery = "INSERT INTO `Security.UserRoles` (`FK_Role_ID`,`FK_User_ID`) " .
                               "VALUES (:FKRoleId, :FKUserId);";
		
	$parmRoleId = new QueryParameter(':FKRoleId', $roleId, PDO::PARAM_INT);
	$parmUserId = new QueryParameter(':FKUserId', $userId, PDO::PARAM_INT);
	$queryParms = array($parmRoleId, $parmUserId);
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($assignUserRoleQuery, $queryParms)){
		$dataAccess->ExecuteNonQuery();
            }
				
            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
		return true;
            }
	}
	
        $logger->LogError("Could not assign security role ". $roleId . " to userID " . $userId . ". " . $errors);
	return false;
    }
	
    public function EmailAssociatedWithExistingAccount($dataAccess, $logger, $userEmail)
    {
        $checkExistingAccountsQuery = "SELECT COUNT(`ID`) AS existingAccountCnt FROM `Security.Users` WHERE `EmailAddress` = :userEmail";
        
        $parmUserEmail = new QueryParameter(':userEmail', strtolower($userEmail), PDO::PARAM_STR);
        $queryParms = array($parmUserEmail);
        
        $errors = $dataAccess->CheckErrors();
        $count = -1;
		
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($checkExistingAccountsQuery, $queryParms)){
		$results = $dataAccess->GetSingleResult();
                
                if($results != null){
                    $count = $results['existingAccountCnt'];
                }
            }
        }
        
        if($count == -1){
            $errors = $dataAccess->CheckErrors();
            $logger->LogError("Could not check existing accounts for email address '" . $userEmail . "'. " . $errors);
        }
        
        return ($count != 0);
    }
	
    public function UpdateUserAccount($dataAccess, $logger, $objUserIn, $pwd)
    {        
	$updateSuccess = false;
			
	$parmUserName = new QueryParameter(':userName', $objUserIn->UserName, PDO::PARAM_STR);
	$parmBio = new QueryParameter(':bio', $objUserIn->Autobiography, PDO::PARAM_STR);
	$parmDOB = new QueryParameter(':birthdate', $objUserIn->Birthdate, PDO::PARAM_STR);
	$parmGender = new QueryParameter(':gender', $objUserIn->Gender, PDO::PARAM_STR);
	$parmTimeZone = new QueryParameter(':timeZone', $objUserIn->TimezoneID, PDO::PARAM_INT);
	$parmFirstName = new QueryParameter(':firstName', $objUserIn->FirstName, PDO::PARAM_STR);
	$parmLastName = new QueryParameter(':lastName', $objUserIn->LastName, PDO::PARAM_STR);
        $parmEmail = new QueryParameter(':emailAddress', strtolower($objUserIn->EmailAddress), PDO::PARAM_STR);
	$parmUserId = new QueryParameter(':userId', $objUserIn->UserID, PDO::PARAM_INT);
	$queryParms = array($parmUserName, $parmBio, $parmDOB, $parmGender, $parmTimeZone, $parmFirstName, $parmLastName, $parmEmail, $parmUserId);
	
        $pwdUpdate = " ";
        if(strlen($pwd) > 0) {
            $pwdUpdate = ", `Password` = :password ";
            
            $parmPassword = new QueryParameter(':password', $pwd, PDO::PARAM_STR);
            array_push($queryParms, $parmPassword);
        }
        
	$updateUserQuery = "UPDATE `Security.Users` SET `UserName` = :userName, `Autobiography` = :bio, `Birthdate` = :birthdate, " .
			   "`Gender` = :gender, `FK_Timezone_ID` = :timeZone, `FirstName` = :firstName, `LastName` = :lastName, " .
			   "`EmailAddress` = :emailAddress" . $pwdUpdate .
                           "WHERE `ID` = :userId;";
        
	$errors = $dataAccess->CheckErrors();
		
	if(strlen($errors) == 0) {
            // Wrap user account update and user game platform inserts into a transaction
            try {
		$dataAccess->BeginTransaction();
		$errors = $dataAccess->CheckErrors();
				
		if(strlen($errors) == 0) {
                    if($dataAccess->BuildQuery($updateUserQuery, $queryParms)){
			$dataAccess->ExecuteNonQuery();
                    }

                    $errors = $dataAccess->CheckErrors();
                    if(strlen($errors) == 0) {
			$gamePlatformsAdded = true;

			if(!empty($objUserIn->GamePlatforms)) {
                            $gamePlatformsAdded = $this->InsertUserGamePlatforms($dataAccess, $logger, $objUserIn->UserID, $objUserIn->GamePlatforms);
			}

			if($gamePlatformsAdded) {
                            // Commit transaction -- account is fully updated
                            $dataAccess->CommitTransaction();
                            $errors = $dataAccess->CheckErrors();

                            if(strlen($errors) > 0) {
				$logger->LogError("Could not commit transaction to update user ID '" . $objUserIn->UserID . "'. " . $errors);
                            }
                            else {
				$updateSuccess = true;
                            }
			}
                    }

                    // If we encountered an error on account update or game platform inserts, roll back everything and return failure code
                    if($updateSuccess == false) {
			$logger->LogError("Could not update user ID '" . $objUserIn->UserID . "' with new information from EditProfile page. " . $errors);
			$dataAccess->RollbackTransaction();
                    }
		}
		else {
                    $logger->LogError("Could not begin transaction to update user ID '" . $objUserIn->UserID . "'. " . $errors);
		}
            }
            catch(Exception $e) {
		$logger->LogError("Could not update account for user ID '" . $objUserIn->UserID . "'. Exception: " . $e->getMessage());
            }
	}
	else {
            $logger->LogError("Could not format query to update user ID '" . $objUserIn->UserID . "' with new information from EditProfile page. " . $errors);
	}
			
	return $updateSuccess;
    }
	
    private function InsertUserGamePlatforms($dataAccess, $logger, $userID, $gamePlatforms)
    {
        // Delete existing user platform mappings, if any
        $deleteExistingMappingQuery = "DELETE FROM `Gaming.UserPlatforms` WHERE `FK_User_ID` = :fkUserId";
	$parmUserId = new QueryParameter(':fkUserId', $userID, PDO::PARAM_INT);
	$queryParms = array($parmUserId);
			
	$errors = $dataAccess->CheckErrors();

	if(strlen($errors) == 0) {
            $deleted = false;
            if($dataAccess->BuildQuery($deleteExistingMappingQuery, $queryParms)){
                $deleted = $dataAccess->ExecuteNonQuery();
            }
			
            $errors = $dataAccess->CheckErrors();

            if(($deleted == false) || (strlen($errors) > 0)) {
                $logger->LogError("Could not insert user platforms: unable to clear existing mappings");
                return false;
            }
            
            // Insert current list of user platforms
            $insertUserPlatformPrefix = "INSERT INTO `Gaming.UserPlatforms` (`FK_User_ID`, `FK_Platform_ID`) VALUES ";
            $insertUserPlatformValuesClause = "";
            $queryParms = array();
            $i = 0;

            foreach($gamePlatforms as $platform) {
                $insertUserPlatformValuesClause .= '(:FKUserId' . $i . ', :FKPlatformId' . $i . '), ';
                $parmUserId = new QueryParameter(':FKUserId' . $i, $userID, PDO::PARAM_INT);
                $parmPlatformId = new QueryParameter(':FKPlatformId' . $i, $platform, PDO::PARAM_INT);
                array_push($queryParms, $parmUserId, $parmPlatformId);
                $i = $i + 1;
            }

            // Construct final query
            $insertUserPlatformValuesClause = (substr($insertUserPlatformValuesClause, 0, strlen($insertUserPlatformValuesClause) - 2)) . ";";
            $insertUserPlatformQuery = $insertUserPlatformPrefix . $insertUserPlatformValuesClause;

            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
                if($dataAccess->BuildQuery($insertUserPlatformQuery, $queryParms)){
                    if($dataAccess->ExecuteNonQuery()) {
                        $errors = $dataAccess->CheckErrors();

                        if(strlen($errors) == 0) {
                            return true;
                        }
                    }
                    else {
                        $errors = "No rows updated";
                    }
                }
            }

            $logger->LogError("Could not add platforms for userID " . $userID . ". " . $errors);
            return false;
	}
    }
    
    public function LoadGamerTagsForUser($dataAccess, $logger, $userID, $gamerTagId = -1, $orderBy = "", $paginationEnabled = false, $startIndex = "0", $pageSize = "10")
    {
	$queryParms = [];
	$whereClause = $this->BuildWhereClauseForLoadGamerTagsQuery($userID, $queryParms, $gamerTagId);
		
	$totalRecordCount = $this->GetTotalCountGamerTagsForUser($dataAccess, $logger, $userID, $whereClause, $queryParms);
	$orderByClause = $this->TranslateGamerTagTableOrderByClause($orderBy);
		
	$limitClause = "";
	if($paginationEnabled) {
            $limitClause = "LIMIT " . $startIndex . "," . $pageSize;
	}
		
        $getGamerTagsQuery = "SELECT ugt.`ID`, p.`ID` as PlatformID, ugt.`GamerTagName`, p.`Name` as PlatformName FROM `Gaming.UserGamerTags` as ugt " .
                             "INNER JOIN `Configuration.Platforms` as p ON p.`ID` = ugt.`FK_Platform_ID` " .
                             $whereClause . $orderByClause . $limitClause . ";";
        
	$gamerTags = array();
	$jTableResult = [];
	$jTableStatus = "ERROR";
	$jTableErrorMsg = "Database error: could not retrieve list of gamer tags. Please try again later.";
        
	if($dataAccess->BuildQuery($getGamerTagsQuery, $queryParms)){
            $results = $dataAccess->GetResultSet();
				
            if($results != null){
		foreach($results as $row) {
                    $gamerTag = array (
			"ID" => $row["ID"],
			"GamerTagName" => $row["GamerTagName"],
			"PlatformName" => $row["PlatformName"],
			"PlatformID" => $row["PlatformID"]
                    );
					
                    array_push($gamerTags, $gamerTag);
		}
            }
	}
        
	$errors = $dataAccess->CheckErrors();
	if(strlen($errors) > 0) {
            $logger->LogError("Could not retrieve gamer tags. " . $errors);
	}
	else {
            $jTableStatus = "OK";
            $jTableErrorMsg = "";
	}
		
	$jTableResult['Result'] = $jTableStatus;
	$jTableResult['Message'] = $jTableErrorMsg;
	$jTableResult['TotalRecordCount'] = $totalRecordCount;
        
	$jTableRecordFieldName = "Records";
        $records = $gamerTags;
	if($gamerTagId > -1) {
            $jTableRecordFieldName = "Record";
            $records = $gamerTags[0];
	}
        
	$jTableResult[$jTableRecordFieldName] = $records;
	return json_encode($jTableResult);
    }
	
    public function BuildWhereClauseForLoadGamerTagsQuery($userID, &$queryParms, $gamerTagId)
    {
	$whereClause = "WHERE (ugt.`FK_User_ID` = :userID) ";
        $parmUserId = new QueryParameter(':userID', $userID, PDO::PARAM_INT);
        $queryParms = array($parmUserId);
		
	if($gamerTagId > -1) {
            $whereClause .= "AND (ugt.`ID` = :gamerTagID) ";
            $parmGamerTagId = new QueryParameter(':gamerTagID', $gamerTagId, PDO::PARAM_INT);
            $queryParms[] = $parmGamerTagId;
	}
		
	return $whereClause;
    }
	
    public function TranslateGamerTagTableOrderByClause($orderBy)
    {
        $queryOrderByClause = "";
        $orderByColumn = "";
        $orderByDirection = "";

	if(strlen($orderBy) == 0)  return $queryOrderByClause;
		
        sscanf($orderBy, "%s %s", $orderByColumn, $orderByDirection);
        
        switch($orderByColumn) {
            case "GamerTagName":
                $queryOrderByClause = "ugt.`GamerTagName` " . $orderByDirection;
                break;
            case "PlatformName":
                $queryOrderByClause = "p.`Name` " . $orderByDirection;
                break;
        }
        
        return "ORDER BY " . $queryOrderByClause . " ";
    }
	
    public function GetTotalCountGamerTagsForUser($dataAccess, $logger, $userID, $whereClause, $queryParms)
    {								
        $getGamerTagCntQuery = "SELECT COUNT(ugt.`ID`) as Cnt FROM `Gaming.UserGamerTags` as ugt " .
                               "INNER JOIN `Configuration.Platforms` as p ON p.`ID` = ugt.`FK_Platform_ID` " .
                               $whereClause . ";";
        
	$userGamerTagCnt = 0;
        if($dataAccess->BuildQuery($getGamerTagCntQuery, $queryParms)){
            $results = $dataAccess->GetSingleResult();

            if($results != null){
                $userGamerTagCnt = $results['Cnt'];
            }
        }
		
	return $userGamerTagCnt;
    }
	
    public function AddGamerTagForUser($dataAccess, $logger, $userID, $platformID, $tagName)
    {
        $addNewGamerTagQuery = "INSERT INTO `Gaming.UserGamerTags` (`FK_User_ID`,`FK_Platform_ID`, `GamerTagName`) " .
                               "VALUES (:FKUserId, :FKPlatformId, :tagName);";
		
        $parmUserId = new QueryParameter(':FKUserId', $userID, PDO::PARAM_INT);
	$parmPlatformId = new QueryParameter(':FKPlatformId', $platformID, PDO::PARAM_INT);
        $parmTagName = new QueryParameter(':tagName', $tagName, PDO::PARAM_STR);
        $queryParms = array($parmUserId, $parmPlatformId, $parmTagName);
	$lastInsertId = -1;
        $errorMsg = "Database error: could not add new gamer tag name '" . $tagName . "'. Please try again later.";

        if(strlen(trim($tagName)) > 0) {
            if($dataAccess->BuildQuery($addNewGamerTagQuery, $queryParms)){
                $dataAccess->ExecuteNonQuery();
            }

            $errors = $dataAccess->CheckErrors();

            if(strlen($errors) == 0) {
                $lastInsertId = $dataAccess->GetLastInsertId();
            }
        }
        else {
            $errors = "Tag name must be non-empty";
            $errorMsg = "Input error: tag name must be non-empty. Please fill out the tag name field and try again.";
        }
	
	$jTableResult = [];
	$encodedResult = "";
	if(strlen($errors) > 0) {
            $logger->LogError("Could not add new gamer tag name '". $tagName . "' for userID " . $userID . ". " . $errors);
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $errorMsg;
            $encodedResult = json_encode($jTableResult);
	}
	else {
            $encodedResult = $this->LoadGamerTagsForUser($dataAccess, $logger, $userID, $lastInsertId);
	}
	return $encodedResult;
    }
	
    public function UpdateGamerTagForUser($dataAccess, $logger, $userID, $gamerTagID, $platformID, $tagName)
    {
        $updateGamerTagQuery = "UPDATE `Gaming.UserGamerTags` " . 
                               "SET `FK_Platform_ID` = :FKPlatformId, `GamerTagName` =  :tagName " .
                               "WHERE `ID` = :gamerTagID;";
		
	$parmPlatformId = new QueryParameter(':FKPlatformId', $platformID, PDO::PARAM_INT);
        $parmTagName = new QueryParameter(':tagName', $tagName, PDO::PARAM_STR);
	$parmGamerTagId = new QueryParameter(':gamerTagID', $gamerTagID, PDO::PARAM_INT);
        $queryParms = array($parmPlatformId, $parmTagName, $parmGamerTagId);
        $errorMsg = "Database error: could not update gamer tag '" . $tagName . "'. Please try again later.";

        if(strlen(trim($tagName)) > 0) {
            if($dataAccess->BuildQuery($updateGamerTagQuery, $queryParms)){
                $dataAccess->ExecuteNonQuery();
            }

            $errors = $dataAccess->CheckErrors();
        }
        else {
            $errors = "Tag name must be non-empty";
            $errorMsg = "Input error: tag name must be non-empty. Please fill out the tag name field and try again.";
        }
	
	$jTableResult = [];
	$encodedResult = "";
	if(strlen($errors) > 0) {
            $logger->LogError("Could not add new gamer tag name '". $tagName . "' for userID " . $userID . ". " . $errors);
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $errorMsg;
            $encodedResult = json_encode($jTableResult);
	}
	else {
            $jTableResult['Result'] = "OK";
            $encodedResult = json_encode($jTableResult);
	}
	return $encodedResult;
    }
	
    public function DeleteGamerTagsForUser($dataAccess, $logger, $userID, $gamerTagID)
    {
        $updateGamerTagQuery = "DELETE FROM `Gaming.UserGamerTags` WHERE `ID` = :gamerTagID;";
		
	$parmGamerTagId = new QueryParameter(':gamerTagID', $gamerTagID, PDO::PARAM_INT);
        $queryParms = array($parmGamerTagId);
        $errorMsg = "Database error: could not delete gamer tag. Please try again later.";

	if($dataAccess->BuildQuery($updateGamerTagQuery, $queryParms)){
            $dataAccess->ExecuteNonQuery();
	}

	$errors = $dataAccess->CheckErrors();
	
	$jTableResult = [];
	$encodedResult = "";
	if(strlen($errors) > 0) {
            $logger->LogError("Could not delete gamer tag ID '". $gamerTagID . "' for userID " . $userID . ". " . $errors);
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $errorMsg;
            $encodedResult = json_encode($jTableResult);
	}
	else {
            $jTableResult['Result'] = "OK";
            $encodedResult = json_encode($jTableResult);
	}
	return $encodedResult;
    }
	
    public function UpdateUsername($dataAccess, $logger, $userID)
    {        
	$updateSuccess = false;
	$updateUserQuery = "UPDATE `Security.Users` SET `UserName` = `EmailAddress` WHERE `ID` = :userId;";
			
	$parmUserId = new QueryParameter(':userId', $userID, PDO::PARAM_INT);
	$queryParms = array($parmUserId);
		
	$errors = $dataAccess->CheckErrors();
		
	if(strlen($errors) == 0) {
            try {				
		if($dataAccess->BuildQuery($updateUserQuery, $queryParms)){
                    $updateSuccess = $dataAccess->ExecuteNonQuery();
		}

		$errors = $dataAccess->CheckErrors();
            }
            catch(Exception $e) {
		$logger->LogError("Could not update username to email address for user ID '" . $userID . "'. Exception: " . $e->getMessage());
            }
	}
		
	if($updateSuccess == false) {
            $logger->LogError("Could not update username to email address for user ID '" . $userID . "'. " . $errors);
	}
			
	return $updateSuccess;
    }
	
    public function UsernameIsAvailable($dataAccess, $logger, $userName)
    {        
	$userNameIsAvailable = true;
	$checkUserNameQuery = "SELECT COUNT(ID) AS userNameCnt FROM `Security.Users` WHERE LOWER(`UserName`) = :userName;";
				
	$parmUserName = new QueryParameter(':userName', strtolower($userName), PDO::PARAM_STR);
	$queryParms = array($parmUserName);
			
	$errors = $dataAccess->CheckErrors();
	$count = 0;
		
	if(strlen($errors) == 0) {
            if($dataAccess->BuildQuery($checkUserNameQuery, $queryParms)){
                $results = $dataAccess->GetSingleResult();

                if($results != null){
                    $count = $results['userNameCnt'];
                }
            }

            if($count == -1){
                $errors = $dataAccess->CheckErrors();
                $logger->LogError("Could not check existing accounts for username '" . $userName . "'. " . $errors);
            }
	}
	else {
            $logger->LogError("Could not check existing accounts for username '" . $userName . "'. " . $errors);
	}
				
	return $count == 0;
    }
    
    public function ShowUserProfileDetails($dataAccess, $logger, $userId)
    {
        $objUser = User::constructDefaultUser();

        $getUserProfileDetailsQuery = "SELECT u.`UserName`, t.`Abbreviation`, u.`FirstName`, u.`LastName`, u.`Gender`, u.`Autobiography` " .
                                      "FROM `Security.Users` as u " .
                                      "INNER JOIN `Configuration.TimeZones` as t ON t.`ID` = u.`FK_Timezone_ID`" .
                                      "WHERE (u.`ID` = :userId);";
		
	$parmUserId = new QueryParameter(':userId', $userId, PDO::PARAM_INT);
	$queryParms = array($parmUserId);
	
        $success = false;
        
        if($dataAccess->BuildQuery($getUserProfileDetailsQuery, $queryParms)){
            $results = $dataAccess->GetSingleResult();

            if($results != null){
                $userPlatforms = $this->LoadUserPlatformNames($dataAccess, $logger, $userId);

                $objUser = new User($userId, -1, -1, $results['UserName'], 
                                    $results['FirstName'], $results['LastName'], '', 1, 
                                    $results['Gender'], '', $results['Autobiography'], $userPlatforms);
                $success = true;
            }
        }
        
        if(!$success) {
            $errors = $dataAccess->CheckErrors();
            $logger->LogError("Could not retrieve user profile details for user ID '" . $userId . "'. " . $errors);
        }
        
        // Format user platform name list as scrollable list
        $platformList = "<ul>";
        foreach($objUser->GamePlatforms as $platform) {
            $platformList .= "<li><label>" . $platform . "</label></li>";
        }
        $platformList .= "</ul>";
        
	// Return User Profile Details HTML
	return
            '<article class="box style1">'.
                '<div id="txnDetailsTable" class="detailsTable">' .
                    '<div class="detailsTableRow">' .
                        '<div class="detailsTableCol">Username:</div>' .
                        '<div class="detailsTableCol">' . $objUser->UserName . '</div>' .
                    '</div>' .
                    '<div class="detailsTableRow">' .
                        '<div class="detailsTableCol">First Name:</div>' .
                        '<div class="detailsTableCol">' . $objUser->FirstName . '</div>' .
                    '</div>' .
                    '<div class="detailsTableRow">' .
                        '<div class="detailsTableCol">Last Name:</div>' .
                        '<div class="detailsTableCol">' . $objUser->LastName . '</div>' .
                    '</div>' .
                    '<div class="detailsTableRow">' .
                        '<div class="detailsTableCol">Gender:</div>' .
                        '<div class="detailsTableCol">' . ($objUser->Gender == 'M' ? 'Male' : 'Female') . '</div>' .
                    '</div>' .
                    '<div class="detailsTableRow">' .
                        '<div class="detailsTableCol">Time Zone:</div>' .
                        '<div class="detailsTableCol">' . $results['Abbreviation'] . '</div>' .
                    '</div>' .
                    '<div class="detailsTableRow">' .
                        '<div class="detailsTableCol">Autobiography:</div>' .
                        '<div class="detailsTableCol">' . $objUser->Autobiography . '</div>' .
                    '</div>' .
                    '<div class="detailsTableRow">' .
                        '<div class="detailsTableCol">Platforms:</div>' .
                        '<div class="detailsTableCol">' .
                            '<div class="fixedHeightScrollableContainerNoBorder">' . $platformList . '</div>' .
                        '</div>' .
                    '</div>' .                
                '</div>' .
            '</article>';
    }
}
