<?php
class Game
{
    public $EventID = -1;
    public $GameID = -1;
    public $Name = "";
    public $IsExistingTitle = true;
    public $IsGlobalGameTitle = true;
    public $IsPublicGame = true;
    public $ScheduledDate = "1900-01-01";
    public $ScheduledTime = "12:00:00am";
    public $ScheduledDateUTC = "1900-01-01 12:00:00";
    public $ScheduledTimeZoneText = "";
    public $ScheduledTimeZoneID = -1;
    public $RequiredPlayersCount = -1;
    public $SelectedPlatformText = "";
    public $SelectedPlatformID = -1;
    public $Notes = "";
    public $Visible = true;
    public $EventCreatorUserName = "";
    public $EventCreatorUserID = -1;
    public $JoinStatus = "";
    public $FriendsAllowed = [];
    public $EventMembers = [];
	
    public function __construct($gid, $gn, $igg)
    {
        $this->GameID = $gid;
        $this->Name = $gn;
	$this->IsGlobalGameTitle = $igg;
    }
    
    public static function ConstructGameForEvent($gid, $sd, $st, $rpc, $n, $fa, $igg, $tzi, $pid, $gn, $sdu, $spt, $tzt, $eid, $v = true, $ecu = "", $ecid = -1)
    {
        $isPublicGame = count($fa) > 0 ? false : true;
        
        $instance = new self($gid, $gn, $igg);
	$instance->EventID = $eid;
        $instance->IsPublicGame = $isPublicGame;
        $instance->IsExistingTitle = (($gid != NULL) && ($gid > -1));
        $instance->ScheduledDate = $sd;
        $instance->ScheduledTime = $st;
        $instance->RequiredPlayersCount = $rpc;
        $instance->Notes = $n;
        $instance->FriendsAllowed = $fa;
        $instance->ScheduledTimeZoneID = $tzi;
        $instance->SelectedPlatformID = $pid;
	$instance->ScheduledDateUTC = $sdu;
        $instance->SelectedPlatformText = $spt;
        $instance->ScheduledTimeZoneText = $tzt;
	$instance->Visible = $v;
	$instance->EventCreatorUserName = $ecu;
        $instance->EventCreatorUserID = $ecid;
        
        return $instance;
    }
}
