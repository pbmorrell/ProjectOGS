<?php
class EventMember
{
	public $EventMemberId = -1;
    public $EventID = -1;
    public $UserID = -1;
	public $UserName = "";
	public $UserDisplayName = "";
	
    public function __construct($emid, $eid, $uid, $un, $udn)
    {
        $this->EventMemberId = $emid;
        $this->EventID = $eid;
		$this->UserID = $uid;
		$this->UserName = $un;
		$this->UserDisplayName = $udn;
    }
}
