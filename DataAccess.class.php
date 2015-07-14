<?php
class DataAccess{	
    // Database configuration
    private $dbType = "mysql";
    private $dbHost = "localhost";
    private $dbUser = "raiden01_webuser";
    private $dbPassword = "t3\$t*useR";
    private $dbName = "raiden01_ProjectOGS";
	
    // Globals
    private $dbHandler;
    private $errorResult = "";
    private $curStatement;
	
    public function __construct()
    {
	// Set DSN
	$dsn = $this->dbType . ':host=' . $this->dbHost . ';dbname=' . $this->dbName;
		
	// Set connection options
	$options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
	);
		
	// Attempt to create new PDO instance
	try{
            $this->dbHandler = new PDO($dsn, $this->dbUser, $this->dbPassword, $options);
	}
	catch(PDOException $e){
            $this->errorResult = $e->getMessage();
	}
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
        catch(Exception $e){
            $this->errorResult = 'Could not execute PDO::prepare(). Exception: ' . $e->getMessage();
            return false;
        }
		
        if($paramArray != null){
            try{
                foreach($paramArray as $queryParameter){
                    $paramName = $queryParameter->ParamName;
                    $paramVal = $queryParameter->ParamVal;
                    $paramType = $queryParameter->ParamType;

                    if(is_null($paramType)){
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
            catch(Exception $e){
                $this->errorResult = 'Could not bind query parameters. Exception: ' . $e->getMessage();
                return false;
            }
        }
		
        return true;
    }
	
    public function ExecuteResultSet()
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
            $this->errorResult = 'Could not execute result set for SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return null;
        }
    }
	
    public function ExecuteSingle()
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
            $this->errorResult = 'Could not execute single-result query for SQL: "' . $this->curStatement->queryString . '". Exception: ' . $e->getMessage();
            return null;
        }
    }
	
    public function RowCount()
    {
        return $this->curStatement->rowCount();
    }
	
    public function GetLastInsertId()
    {
        return $this->dbHandler->lastInsertId();
    }
	
    public function BeginTransaction()
    {
        return $this->dbHandler->beginTransaction();
    }
	
    public function CommitTransaction()
    {
        return $this->dbHandler->commit();
    }
	
    public function RollbackTransaction()
    {
        return $this->dbHandler->rollBack();
    }
	
    public function DebugDumpParams()
    {
        return $this->curStatement->debugDumpParams();
    }
}

class QueryParameter{
    public $ParamName;
    public $ParamVal;
    public $ParamType;
}
?>