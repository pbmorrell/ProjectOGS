<?php
class EventSearchParameters
{
    public $ShowHiddenEvents = false;
    public $StartDateTime = "";
    public $EndDateTime = "";
    public $GameTitles = [];
    public $CustomGameTitle = "";
    public $EventCreators = [];
    public $CustomEventCreatorName = "";
    public $JoinedUsers = [];
    public $CustomJoinedUserName = "";
    public $Platforms = [];
    public $CustomPlatformName = "";
    public $ShowJoinedEvents = false;
    public $ShowCreatedEvents = true;
    public $ShowFullEventsOnly = false;
    public $ShowOpenEventsOnly = false;
    public $NoStartDateRestriction = false;
	
    public function __construct($sh = false, $sdt = "", $edt = "", $gt = [], $ec = [], $ju = [], 
				$p = [], $sje = false, $sce = true, $sfeo = false, $nsd = false, 
				$cecn = "", $cjeu = "", $cpn = "", $soeo = false, $cgt = "")
    {
        $this->ShowHiddenEvents = $sh;
        $this->StartDateTime = $sdt;
        $this->EndDateTime = $edt;
        $this->GameTitles = $gt;
        $this->EventCreators = $ec;
        $this->JoinedUsers = $ju;
        $this->Platforms = $p;
        $this->CustomPlatformName = $cpn;
        $this->ShowJoinedEvents = $sje;
        $this->ShowCreatedEvents = $sce;
        $this->ShowFullEventsOnly = $sfeo;
        $this->ShowOpenEventsOnly = $soeo;
        $this->NoStartDateRestriction = $nsd;
        $this->CustomEventCreatorName = $cecn;
        $this->CustomJoinedUserName = $cjeu;
	$this->CustomGameTitle = $cgt;
    }
}
