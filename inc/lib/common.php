<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/ckinc/common.php");

class cAppDynCommon{
	const TIME_SESS_KEY= "tsk";
	const TIME_CUSTOM_FROM_KEY="tcfk";
	const TIME_CUSTOM_TO_KEY="tctk";
	const TIME_CUSTOM = "custom";
	const LINK_SESS_KEY="lsk";
	public static $ROW_TOGGLE = false;
	public static $TIME_RANGES = 
		array(
			"5 mins"=>5,
			"10 mins"=>10,
			"15 mins"=>15,
			"30 mins"=>30,
			"45 mins"=>45,
			"Hour"=>60,
			"1.5 Hr" => 90, 
			"2 hr"=>120, 
			"4 hr"=>240 , 
			"6 hr"=>360, 
			"12 hr"=>720,
			"1 day"=> cCommon::MINS_IN_DAY,
			"2 days"=>cCommon::MINS_IN_DAY * 2,
			"3 days" => cCommon::MINS_IN_DAY * 3,
			"4 days" => cCommon::MINS_IN_DAY * 4, 
			"1 Week" => cCommon::MINS_IN_WEEK,
			"2 Week" => cCommon::MINS_IN_WEEK *2,
			"3 Week" => cCommon::MINS_IN_WEEK *3,
			"1 month" => cCommon::MINS_IN_MONTH,
			"2 month" => cCommon::MINS_IN_MONTH *2,
			"3 month" => cCommon::MINS_IN_MONTH *3,
			"4 month" => cCommon::MINS_IN_MONTH *4,
			"5 month" => cCommon::MINS_IN_MONTH *5,
			"6 month" => cCommon::MINS_IN_MONTH *6
		);
		
	//**************************************************************************
	public static function get_duration(){
		global $_SESSION;
		
		$duration = cCommon::get_session(cAppDynCommon::TIME_SESS_KEY);
		if ($duration == cAppDynCommon::TIME_CUSTOM){
			$hh1 = cCommon::get_session("fromh");
			$mm1 = cCommon::get_session("fromm");
			$hh2 = cCommon::get_session("toh");
			$mm2 = cCommon::get_session("tom");
			$fromd = cCommon::get_session("fromd");
			
			$duration = "between $hh1:$mm1 to $hh2:$mm2 on $fromd"; 
		}
		elseif ($duration == "")
			$duration = 60;
			
		return $duration;
	}

	//**************************************************************************
	public static function get_time_label() {
		global $LINK_SESS_KEY;
		global $_SESSION, $_SERVER;

		$sess_key = cCommon::get_session(cAppDynCommon::TIME_SESS_KEY);
		if ($sess_key == cAppDynCommon::TIME_CUSTOM){ 
			$fromtime= $_SESSION[cAppDynCommon::TIME_CUSTOM_FROM_KEY];
			$totime =  $_SESSION[cAppDynCommon::TIME_CUSTOM_TO_KEY];
			$fromDate = date("d/m/y H:i", $fromtime/1000);
			$toDate = date("d/m/y H:i", $totime/1000);
			return "<b>Custom $fromDate to $toDate</b>";
		}
		else
		{
			$duration = self::get_duration();
			return "<b>last $duration mins up-to ".date("F j, Y, g:i a"). "</b>";
		}
	}
}

