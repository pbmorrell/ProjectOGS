<?php
class SearchParameters
{
    public $ShowHiddenEvents = false;
    public $StartDateTime = "";
    public $EndDateTime = "";
    public $GameTitles = [];
    public $EventCreators = [];
	public $CustomEventCreatorName = "";
    public $JoinedUsers = [];
    public $Platforms = [];
    public $ShowJoinedEvents = false;
    public $ShowOpenEvents = true;
    public $ShowUnjoinedFullEvents = false;
    public $NoStartDateRestriction = false;
	
    public function __construct($sh = false, $sdt = "", $edt = "", $gt = [], $ec = [], $ju = [], 
								$p = [], $sje = false, $soe = true, $sufe = false, $nsd = false, $cecn = "")
    {
        $this->ShowHiddenEvents = $sh;
		$this->StartDateTime = $sdt;
		$this->EndDateTime = $edt;
		$this->GameTitles = $gt;
		$this->EventCreators = $ec;
		$this->JoinedUsers = $ju;
		$this->Platforms = $p;
		$this->ShowJoinedEvents = $sje;
		$this->ShowOpenEvents = $soe;
		$this->ShowUnjoinedFullEvents = $sufe;
		$this->NoStartDateRestriction = $nsd;
		$this->CustomEventCreatorName = $cecn;
    }
}
