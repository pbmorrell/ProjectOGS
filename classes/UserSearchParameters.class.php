<?php
class UserSearchParameters
{
    public $GamerTag = "";
    public $UserName = "";
    public $FirstName = "";
    public $LastName = "";
    public $Platforms = [];
    public $Gender = "";
    public $ShowInvitesForUser = true;
    public $ShowInvitesSentByUser = false;
    public $ShowRejectedInvites = true;
    public $ShowCurrentFriendsForUser = true;
	
    public function __construct($gt = "", $un = "", $fn = "", $ln = "", $p = [], $g = "", $sifu = true, $sibu = false, $sri = true, $scf = true)
    {
        $this->GamerTag = $gt;
        $this->UserName = $un;
		$this->FirstName = $fn;
		$this->LastName = $ln;
        $this->Platforms = $p;
		$this->Gender = $g;
		$this->ShowInvitesForUser = $sifu;
		$this->ShowInvitesSentByUser = $sibu;
		$this->ShowRejectedInvites = $sri;
		$this->ShowCurrentFriendsForUser = $scf;
    }
}
