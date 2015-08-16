<?php
class Game
{
    public $GameID = -1;
    public $Name = "";
    public $IsGlobalGameTitle = true;
    public $IsPublicGame = true;
    public $ScheduledDate = "1900-01-01";
    public $ScheduledTime = "12:00:00 am";
    public $RequiredPlayersCount = -1;
    public $Notes = "";
    public $FriendsAllowed = [];
	
    public function __construct($gid, $gn, $igg)
    {
        $this->GameID = $gid;
        $this->Name = $gn;
	$this->IsGlobalGameTitle = $igg;
    }
    
    public static function ConstructGameForEvent($gn, $sd, $st, $rpc, $n, $fa, $igg)
    {
        $isPublicGame = count($fa) > 0 ? true : false;
        
        $instance = new self(-1, $gn, $igg);
        $instance->IsPublicGame = $isPublicGame;
        $instance->ScheduledDate = $sd;
        $instance->ScheduledTime = $st;
        $instance->RequiredPlayersCount = $rpc;
        $instance->Notes = $n;
        $instance->FriendsAllowed = $fa;
        
        return $instance;
    }
}
