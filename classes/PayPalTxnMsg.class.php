<?php
class PayPalTxnMsg
{
    public $TxnId = "";
    public $TxnType = "";
    public $PayerId = "-1";
    public $UserId = -1;
    public $NotificationType = "";
    public $PDTOperation = "";
    public $NotificationDate = "";
    public $PaymentStatus = "";
    public $PaymentPendingReason = "";
    public $SelectedSubscriptionOption = "";
    public $SubscriptionAmtPaid = "0";
    public $SubscriptionAmtTotal = "0";
    public $SubscriptionIsRecurring = false;
    public $SubscriptionModifyDate = "";
    public $SubscriptionID = "";
    public $UserMessage = "";
    public $UserUpgradedPremium = false;
    public $UserSubscriptionRenewed = false;
    public $UserSubscriptionCancelledPending = false;
    public $UserSubscriptionCancelledImmediate = false;
    public $IsValidBusiness = true;
    public $IsValidated = false;
    public $UpdateUserMembershipStatus = false;
	
    public function __construct($tid, $tt, $pid, $uid, $nt, $ps, $sso, $ssp, $sst, $smd, $um, $nd, $pdto, $sid)
    {
        $this->TxnId = $tid;
        $this->TxnType = $tt;
        $this->PayerId = $pid;
        $this->UserId = $uid;
        $this->NotificationType = $nt;
        $this->PaymentStatus = $ps;
        $this->SelectedSubscriptionOption = $sso;
        $this->SubscriptionAmtPaid = $ssp;
        $this->SubscriptionAmtTotal = $sst;
	$this->SubscriptionModifyDate = $smd;
        $this->UserMessage = $um;
	$this->NotificationDate = $nd;
	$this->PDTOperation = $pdto;
	$this->SubscriptionID = $sid;
    }
    
    public static function ConstructDefaultMsg()
    {   
        $instance = new self("", "", "", -1, "", "", "", "", "", "", "", "", "", "");
        return $instance;
    }
}
