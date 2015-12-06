<?php
class PayPalUser
{
    public $ID = -1;
    public $UserID = -1;
    public $IsActive = false;
    public $IsRecurring = false;
    public $LastBillDate = "";
    public $MembershipExpDate = "";
    public $ExtendedMembershipDays = 0;
    public $PayerId = "";
    public $SubscriptionType = "";
    public $SubscriptionAmtTotal = 0;
    public $SubscriptionAmtPaidLastCycle = 0;
    public $SubscriptionStartedDate = "";
    public $SubscriptionModifiedDate = "";
    public $SubscriptionID = "";
	
    public function __construct($id, $uid, $ia, $ir, $lbd, $med, $pid, $st, $sat, $sapl, $ssd, $smd, $sid, $emd = 0)
    {
	$this->ID = $id;
        $this->UserID = $uid;
	$this->IsActive = $ia;
	$this->IsRecurring = $ir;
	$this->LastBillDate = $lbd;
	$this->MembershipExpDate = $med;
	$this->PayerId = $pid;
	$this->SubscriptionType = $st;
	$this->SubscriptionAmtTotal = $sat;
	$this->SubscriptionAmtPaidLastCycle = $sapl;
	$this->SubscriptionStartedDate = $ssd;
	$this->SubscriptionModifiedDate = $smd;
	$this->SubscriptionID = $sid;
        $this->ExtendedMembershipDays = $emd;
    }
    
    public static function constructDefaultPayPalUser()
    {
        $instance = new self(-1, -1, false, false, "", "", "", "", 0, 0, "", "", "", 0);
        return $instance;
    }
}
