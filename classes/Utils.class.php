<?php
class Utils
{	    
    public static function ConvertStandardTimeToMilitaryTime($standardTime, $convertSeconds = false)
    {
	$hour = -1;
	$minute = -1;
	$second = 0;
	$amPm = "";
		
	if($convertSeconds) {
            sscanf($standardTime, "%d:%d:%d%s", $hour, $minute, $second, $amPm);
	}
	else {
            sscanf($standardTime, "%d:%d%s", $hour, $minute, $amPm);
	}
		
	$amPm = trim($amPm);
	if(($hour < 12 ) && (strcasecmp($amPm, "pm") === 0)) {
            $hour += 12;
	}
	else if (($hour === 12 ) && (strcasecmp($amPm, "am") === 0)) {
            $hour = 0;
	}
		
	return sprintf("%d:%02d:%02d", $hour, $minute, $second);
    }
	
    public static function ConvertMilitaryTimeToStandardTime($militaryTime, $displaySeconds = false)
    {
	$hour = -1;
	$minute = -1;
	$second = -1;
	$amPm = "am";
		
	sscanf($militaryTime, "%d:%d:%d", $hour, $minute, $second);
		
	if($hour > 12) {
            $hour -= 12;
            $amPm = "pm";
	}
	else if ($hour === 0) {
            $hour = 12;
	}

	$standardTime = "";
	if($displaySeconds) {
            $standardTime = sprintf("%d:%02d:%02d%s", $hour, $minute, $second, $amPm);
	}
	else {
            $standardTime = sprintf("%d:%02d%s", $hour, $minute, $amPm);
	}
		
	return $standardTime;
    }
    
    public static function SearchGameArrayByEventID($gameArray, $id)
    {
	$foundGame = null;
	foreach($gameArray as $game)
	{
            if($game->EventID === $id) {
		$foundGame = $game;
		break;
            }
	}
		
	return $foundGame;
    }
    
    public static function SearchUserArrayByID($userArray, $id)
    {
	$foundUser = null;
	foreach($userArray as $user)
	{
            if($user->UserID === $id) {
		$foundUser = $user;
		break;
            }
	}
		
	return $foundUser;
    }
	
    public static function SearchEventMemberArrayByUserID($eventMemberArray, $id)
    {
        $foundMember = null;
	foreach($eventMemberArray as $member)
	{
            if($member->UserID === $id) {
		$foundMember = $member;
		break;
            }
	}
		
	return $foundMember;
    }
	
    public static function UserIsInEventMemberArray($eventMemberArray, $id)
    {
	return (Utils::SearchEventMemberArrayByUserID($eventMemberArray, $id)) != null;
    }
	
    public static function SearchEventMemberArrayByUserNameText($eventMemberArray, $text)
    {
	$foundMember = null;
	foreach($eventMemberArray as $member)
	{
            if(stripos($member->UserDisplayName, $text) !== false) {
		$foundMember = $member;
		break;
            }
	}
		
	return $foundMember;
    }
	
    public static function SortObjectArrayByMultiMemberProp(&$objArray, $props, $propSortDirections)
    {
	usort($objArray, function($a, $b) use ($props, $propSortDirections) {
            for($i = 0; $i < count($props); $i++) {
                if($a->$props[$i] != $b->$props[$i]) {
                    $aLessThanBVal = -1; // ASC order by default
                    if($propSortDirections != null) {
                        if((isset($propSortDirections[$props[$i]])) && (strcasecmp($propSortDirections[$props[$i]], "desc") == 0)) {
                            $aLessThanBVal = 1;
                        }
                    }
                    return ($a->$props[$i] < $b->$props[$i]) ? $aLessThanBVal : (-$aLessThanBVal);
                }
            }
            return 0;
	});
    }
	
    public static function GenerateUniqueGuid($length)
    {
	$bytes = openssl_random_pseudo_bytes($length);
	return bin2hex($bytes);
    }
}

