<?php
include_once 'classes/DataAccess.class.php';

class Logger
{	
    // Logger configuration
    private $logFilePath = "logs/OGS_Error_Log.txt";
	
    // Globals
    private $dataAccess;
	
    public function __construct($database)
    {
	$this->dataAccess = $database;
        //$this->LogInfo("Initializing Logger");
    }

    public function LogInfo($message, $title = 'Information')
    {
        if(!$this->LogToDB($message, $title, LogCategories::INFORMATION)) {
            $this->LogToFile($message, LogCategories::INFORMATION);
        }
    }
    
    public function LogError($message, $title = 'Error')
    {
        if(!$this->LogToDB($message, $title, LogCategories::ERROR)) {
            $this->LogToFile($message, LogCategories::ERROR);
        }
    }
    
    public function LogToDB($message, $title, $category)
    {
        $success = false;
        $logToDBQuery = "INSERT INTO `Administration.Logging`(`Category`, `Title`, `Message`) " .
                        "VALUES(:category, :title, :message);";
	
        $parmCategory = new QueryParameter(':category', $category, PDO::PARAM_STR);
	$parmTitle = new QueryParameter(':title', $title, PDO::PARAM_STR);
	$parmMessage = new QueryParameter(':message', $message, PDO::PARAM_STR);
	$queryParms = array($parmCategory, $parmTitle, $parmMessage);
        
        $errors = $this->dataAccess->CheckErrors();

        if(strlen($errors) == 0) {
            if($this->dataAccess->BuildQuery($logToDBQuery, $queryParms)){
                $results = $this->dataAccess->ExecuteNonQuery();
                if($results != null) {
                    $success = true;
                }
            }
            
            $errors = $this->dataAccess->CheckErrors();
        }
        
        if(!success) {
            $message = "Error occurred when attempting to log message to DB: '" . $errors . "'. Original message: " . $message;
        }
        
        return success;
    }
    
    public function LogToFile($message, $category)
    {
        // No try-catch here: If this call fails, let exception be logged in default PHP error.log
        $formattedMsg = date("Y-m-d h:i:sa") . "  CATEGORY: " . $category . " MESSAGE: '" . $message . "'\n";
        file_put_contents($this->logFilePath, $formattedMsg, FILE_APPEND | LOCK_EX);
    }
}

class LogCategories
{
    const ERROR = 'Error';
    const INFORMATION = 'Info';
}
?>