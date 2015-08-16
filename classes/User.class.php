<?php
class User
{
    public $UserID = -1;
    public $SecurityLevel = -1;
    public $TimezoneID = -1;
    public $UserName = "";
    public $FirstName = "";
    public $LastName = "";
    public $EmailAddress = "";
    public $IsPremiumMember = 0;
    public $Gender = "";
    public $Birthdate = "";
    public $Autobiography = "";
    public $GamePlatforms = [];
	
    public function __construct($uid, $sl, $tID, $un, $fn, $ln, $ea, $ipm, $gn, $bd, $ab, $gplatforms)
    {
        $this->UserID = $uid;
        $this->SecurityLevel = $sl;
	$this->TimezoneID = $tID;
        $this->UserName = $un;
        $this->FirstName = $fn;
        $this->LastName = $ln;
        $this->EmailAddress = $ea;
        $this->IsPremiumMember = $ipm;
        $this->Gender = $gn;
        $this->Birthdate = $bd;
        $this->Autobiography = $ab;
	$this->GamePlatforms = $gplatforms;
    }
    
    public static function constructDefaultUser()
    {
        $instance = new self(-1, -1, -1, "", "", "", "", 0, "", "", "", array());
        return $instance;
    }
}
