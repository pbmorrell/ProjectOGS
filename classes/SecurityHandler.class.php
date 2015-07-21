<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/Logger.class.php';

class SecurityHandler
{
    // Globals
    public $userId = -1;
    public $userSecurityLevel = -1;
    
    public function EncryptPassword($password)
    {
        // Use PHP default hashing algorithm -- using this constant will allow
        // automatic upgrade to the latest, strongest algorithms as they are
        // successively set as the default
        return password_hash($password, PASSWORD_DEFAULT);
        
//        $salt = sprintf('$2a$%02d$', 15);
//        $bytes = $this->GetRandomBytes(16);
//        $salt .= $this->EncodeBytes($bytes);
//        
//        $hashedPassword = crypt($password, $salt);
//        
//        if(strlen($hashedPassword) > 13) {
//            return $hashedPassword;
//        }
//        
//        return null;
    }
    
    public function ValidatePassword($password, $hash)
    {
        return password_verify($password, $hash) === TRUE;
    }
    
    // Returns security level of authenticated user, or -1 if user not authenticated
    public function AuthenticateUser($dataAccess, $logger, $userName, $password)
    {
        $userSecurityLevel = -1;
        $authenticateUserQuery = "SELECT r.`SecurityLevel`, u.`Password` FROM `Security.Users` as u " .
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
                        $userSecurityLevel = $results['SecurityLevel'];
                        $success = true;
                    }
                }
            }
	}
        
        if(!success) {
            $errors = $dataAccess->CheckErrors();
            $logger->LogError("Could not authenticate user '" . $userName . "'. Exception: " . $errors);
        }
        
        return $userSecurityLevel;
    }
    
    public function UserAuthorizedForThisPage($dataAccess, $logger, $pageName, $userSecurityLevel)
    {
        $minPageSecurityLevel = -1;
        
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
                    $minPageSecurityLevel = $results['SecurityLevel'];
                }
            }
	}
        
        if($minPageSecurityLevel == -1){
            $errors = $dataAccess->CheckErrors();
            $logger->LogError("Could not check authorization for page '" . $pageName . "'. Exception: " . $errors);
        }
        
        return ($minPageSecurityLevel == -1) || ($userSecurityLevel >= $minPageSecurityLevel);
    }
    
    public function InsertBasicUser($dataAccess, $logger, $userName, $password, $userEmail, $basicRoleId)
    {
        $createdUserId = -1;
	$isPremiumMember = 0;
        
        $insertUserQuery = "INSERT INTO `Security.Users` (`UserName`,`IsPremiumMember`,`Password`,`EmailAddress`) " .
			   "VALUES (:userName, :IsPremiumMember, :password, :emailAddress);";
		
	$parmUserName = new QueryParameter(':userName', $userName, PDO::PARAM_STR);
	$parmIsPremium = new QueryParameter(':IsPremiumMember', $isPremiumMember, PDO::PARAM_BOOL);
	$parmPassword = new QueryParameter(':password', $password, PDO::PARAM_STR);
	$parmEmail = new QueryParameter(':emailAddress', $userEmail, PDO::PARAM_STR);
	$queryParms = array($parmUserName, $parmIsPremium, $parmPassword, $parmEmail);
		
	$errors = $dataAccess->CheckErrors();
        
	if(strlen($errors) == 0) {
            // Wrap user account creation and user security role assignment inserts into a transaction
            $dataAccess->BeginTransaction();
            $errors = $dataAccess->CheckErrors();
            
            if(strlen($errors) == 0) {
                if($dataAccess->BuildQuery($insertUserQuery, $queryParms)){
                    $results = $dataAccess->ExecuteNonQuery();
                    if($results != null){
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
                            $logger->LogError("Could not commit transaction to create user '." . $userName . "'. Exception: " . $errors);
                        }
                    }
                    else {
                        $createdUserId = -1;
                    }
                }

                // If we encountered an error on account creation or security role assignment, roll back everything and return failure code
                if($createdUserId == -1) {
                    $logger->LogError("Could not insert user '." . $userName . "' into Users table. Exception: " . $errors);
                    $dataAccess->RollbackTransaction();
                }
            }
            else {
                $logger->LogError("Could not begin transaction to create user '." . $userName . "'. Exception: " . $errors);
            }
	}
        else {
            $logger->LogError("Could not format query to insert user '." . $userName . "' into Users table. Exception: " . $errors);
        }
        
	return $createdUserId;
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
	
        $logger->LogError("Could assign security role ". $roleId . " to userID " . $userId . ". Exception: " . $errors);
	return false;
    }
}
?>