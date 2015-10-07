<?php
include_once 'Constants.class.php';
class DataAccess
{		
    // Globals
    private $dbHandler;
    private $errorResult = "";
    private $curStatement;
	
    public function __construct()
    {
        $this->Open();
    }
	
    public function CheckErrors()
    {
        return $this->errorResult;
    }
	
    public function BuildQuery($query, $paramArray = null)
    {
        try{
            $this->curStatement = $this->dbHandler->prepare($query);
        }
        catch(PDOException $e){
            $this->errorResult = 'Could not execute PDO::prepare(). Exception: ' . $e->getMessage();
            return false;
        }
	
        if($paramArray != null){
            try {
                foreach($paramArray as $queryParameter){
                    $paramName = $queryParameter->ParamName;
                    $paramVal = $queryParameter->ParamVal;
                    $paramType = $queryParameter->ParamType;
                    
                    if(is_null($paramType)) {
                        switch(true){
                            case is_int($paramVal):
                                $paramType = PDO::PARAM_INT;
                                break;
                            case is_bool($paramVal):
                                $paramType = PDO::PARAM_BOOL;
                                break;
                            case is_null($paramVal):
                                $paramType = PDO::PARAM_NULL;
                                break;
                            default:
                                $paramType = PDO::PARAM_STR;
                        }
                    }

                    $this->curStatement->bindValue($paramName, $paramVal, $paramType);
                }
            }
            catch(PDOException $e){
                $this->errorResult = 'Could not bind query parameters. Exception: ' . $e->getMessage();
                return false;
            }
        }
	
        return true;
    }
	
    public function GetResultSet()
    {
        try{
            if($this->curStatement->execute()){
                return $this->curStatement->fetchAll(PDO::FETCH_ASSOC);
            }
            else {
                return null;
            }
        }
        catch(PDOException $e){
            $this->errorResult = 'Could not retrieve result set for SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return null;
        }
    }
    
    public function GetResultSetWithPositionalParms($valArray)
    {
        try {
            if($this->curStatement->execute($valArray)) {
                return $this->curStatement->fetchAll(PDO::FETCH_ASSOC);
            }
            else {
                return null;
            }
        }
        catch(PDOException $e){
            $this->errorResult = 'Could not retrieve result set for SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return false;
        }
    }
	
    public function GetSingleResult()
    {
        try{
            if($this->curStatement->execute()){
                return $this->curStatement->fetch(PDO::FETCH_ASSOC);
            }
            else {
                return null;
            }
        }
        catch(PDOException $e){
            $this->errorResult = 'Could not retrieve single-result query for SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return null;
        }
    }
    
    public function GetSingleResultWithPositionalParms($valArray)
    {
        try{
            if($this->curStatement->execute($valArray)){
                return $this->curStatement->fetch(PDO::FETCH_ASSOC);
            }
            else {
                return null;
            }
        }
        catch(PDOException $e){
            $this->errorResult = 'Could not retrieve single-result query for SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return null;
        }
    }
    
    public function ExecuteNonQuery()
    {
        try {
            return $this->curStatement->execute();
        }
        catch(PDOException $e){
            $this->errorResult = 'Could not execute non-query SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return false;
        }
    }
	
    public function ExecuteNonQueryWithPositionalParms($valArray)
    {
        try {
            return $this->curStatement->execute($valArray);
        }
        catch(PDOException $e){
            $this->errorResult = 'Could not execute non-query SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return false;
        }
    }
	
    public function RowCount()
    {
        try {
            return $this->curStatement->rowCount();
        }
        catch(PDOException $e) {
            $this->errorResult = 'Could not get row count for current query "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return -1;
        }
    }
	
    public function GetLastInsertId()
    {
        try {
            return $this->dbHandler->lastInsertId();
        }
        catch(PDOException $e) {
            $this->errorResult = 'Could not get last insert ID for last query. Exception: ' . $e->getMessage();
            return -1;
        }
    }
	
    public function BeginTransaction()
    {
        try {
            return $this->dbHandler->beginTransaction();
        }
        catch(PDOException $e) {
            $this->errorResult = 'Could not begin transaction. Exception: ' . $e->getMessage();
        }
        
        return false;
    }
	
    public function CheckIfInTransaction()
    {
        return $this->dbHandler->inTransaction();
    }
	
    public function CommitTransaction()
    {
        try {
            return $this->dbHandler->commit();
        }
        catch(PDOException $e) {
            $this->errorResult = 'Could not commit transaction. Exception: ' . $e->getMessage();
            return null;
        }
    }
	
    public function RollbackTransaction()
    {
        try {
            return $this->dbHandler->rollBack();
        }
        catch(PDOException $e) {
            $this->errorResult = 'Could not roll back transaction. Exception: ' . $e->getMessage();
            return null;
        }
    }
	
    public function DebugDumpParams()
    {
        try {
            return $this->curStatement->debugDumpParams();
        }
        catch(PDOException $e) {
            $this->errorResult = 'Could not dump debug params. Exception: ' . $e->getMessage();
            return null;
        }
    }
    
    public function Open()
    {
		// Set DSN
		$dsn = Constants::$dbType . ':host=' . Constants::$dbHost . ';dbname=' . Constants::$dbName;
				
		// Set connection options
		$options = array(
			PDO::ATTR_PERSISTENT => true,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
				
		// Attempt to create new PDO instance
		try{
			$this->dbHandler = new PDO($dsn, Constants::$dbUser, Constants::$dbPassword, $options);
		}
		catch(PDOException $e){
			$this->errorResult = $e->getMessage();
		}
    }
    
    public function Close()
    {
        $this->dbHandler = null;
        return true;
    }
}

class QueryParameter{
    public $ParamName;
    public $ParamVal;
    public $ParamType;
	
    public function __construct($paramName, $paramVal, $paramType)
    {
	$this->ParamName = $paramName;
	$this->ParamVal = $paramVal;
	$this->ParamType = $paramType;
    }
}
?>