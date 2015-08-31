<?php
include_once 'classes/DataAccess.class.php';
include_once 'classes/Logger.class.php';

class DBSessionHandler implements SessionHandlerInterface
{
    // Globals
    private $dataAccess;
	
    public function __construct($database)
    {
	// Set data access and log handlers
	$this->dataAccess = $database;
    }

    public function open($savePath, $sessionName)
    {
        if(($this->dataAccess) && (strlen($this->dataAccess->CheckErrors()) == 0)) {
            return true;
        }
        
        return false;
    }
    
    public function close()
    {
	// Commit transaction, releasing lock on the session
	$this->dataAccess->CommitTransaction();
		
        if($this->dataAccess->close()) {
            return true;
        }
        
        return false;
    }
    
    public function read($id)
    {
	// Lock the current session, to serialize access in the event of multiple concurrent user requests
	$this->dataAccess->BeginTransaction();
        $sessionData = "";
        $readSessionDataQuery = "SELECT `SessionData` FROM `Security.UserSessions` " .
                                "WHERE (`ID` = :sessionID) FOR UPDATE;";
		
	$parmSessionID = new QueryParameter(':sessionID', $id, PDO::PARAM_STR);
	$queryParms = array($parmSessionID);
	
	$errors = $this->dataAccess->CheckErrors();
        $success = false;

	if(strlen($errors) == 0) {
            if($this->dataAccess->BuildQuery($readSessionDataQuery, $queryParms)){
		$results = $this->dataAccess->GetSingleResult();
                if($results != null){
                    $sessionData = $results['SessionData'];
                }
            }
	}
        
        // If no data found, must return empty string
        return $sessionData;
    }
    
    public function write($id, $data)
    {
        // Get current timestamp for update
        $accessTime = time();
        
        $writeSessionDataQuery = "REPLACE INTO `Security.UserSessions` (`ID`,`SessionData`, `LastAccess`) " .
                                 "VALUES(:sessionID, :sessionData, :lastAccess);";
	
        $parmSessionID = new QueryParameter(':sessionID', $id, PDO::PARAM_STR);
	$parmSessionData = new QueryParameter(':sessionData', $data, PDO::PARAM_STR);
        $parmLastAccess = new QueryParameter(':lastAccess', $accessTime, PDO::PARAM_STR);
	$queryParms = array($parmSessionID, $parmSessionData, $parmLastAccess);
	
	$errors = $this->dataAccess->CheckErrors();
        $success = false;

	if(strlen($errors) == 0) {
            if($this->dataAccess->BuildQuery($writeSessionDataQuery, $queryParms)){
		$success = $this->dataAccess->ExecuteNonQuery();
            }
	}
        
	// Commit transaction, releasing lock on the session
	$this->dataAccess->CommitTransaction();
        return $success;
    }
    
    public function destroy($session_id) {
        $destroySessionDataQuery = "DELETE FROM `Security.UserSessions` WHERE (`ID` = :sessionID);";
        $parmSessionID = new QueryParameter(':sessionID', $session_id, PDO::PARAM_STR);
	$queryParms = array($parmSessionID);
        
	$errors = $this->dataAccess->CheckErrors();
        $success = false;

	if(strlen($errors) == 0) {
            if($this->dataAccess->BuildQuery($destroySessionDataQuery, $queryParms)){
		$success = $this->dataAccess->ExecuteNonQuery();
            }
	}
        
	// Commit transaction, releasing lock on the session
	$this->dataAccess->CommitTransaction();
        return $success;
    }
    
    public function gc($max_expire_time)
    {
        $staleSessionCutoffDate = time() - $max_expire_time;
        
        $gcSessionQuery = "DELETE FROM `Security.UserSessions` WHERE (`LastAccess` < :staleSessionCutoffDate);";
        $parmStaleSessionDate = new QueryParameter(':staleSessionCutoffDate', $staleSessionCutoffDate, PDO::PARAM_STR);
	$queryParms = array($parmStaleSessionDate);
	
	$errors = $this->dataAccess->CheckErrors();
        $success = false;

	if(strlen($errors) == 0) {
            if($this->dataAccess->BuildQuery($gcSessionQuery, $queryParms)){
		$success = $this->dataAccess->ExecuteNonQuery();
            }
	}
        
        return $success;
    }
}
