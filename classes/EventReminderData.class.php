<?php
class EventReminderData
{
    public $EventReminderID = -1;
    public $EventID = -1;
    public $EventScheduledForDate = "";
    public $UserTimeZone = "";
    public $UserID = -1;
    public $EmailAddress = "";
    public $TimeInMinsBeforeEventToSend = -1;
    public $GameTitle = "";
    public $PlayersSignedUp = [];
	
    public function __construct($eid, $esd, $uid, $ea, $tts, $eri = -1, $gt = "", $psu = [], $utz = "")
    {
        $this->EventID = $eid;
	$this->EventScheduledForDate = $esd;
	$this->UserTimeZone = $utz;
        $this->UserID = $uid;
        $this->EmailAddress = $ea;
        $this->TimeInMinsBeforeEventToSend = $tts;
        $this->EventReminderID = $eri;
	$this->GameTitle = $gt;
	$this->PlayersSignedUp = $psu;
    }
	
    public static function constructDefaultEventReminderData()
    {
        $instance = new self(-1, "", -1, "", -1);
        return $instance;
    }
}
