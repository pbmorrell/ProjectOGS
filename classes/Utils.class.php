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
}

