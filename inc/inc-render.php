<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/ckinc/colour.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/core.php");
require_once("$root/inc/inc-filter.php");
require_once("$root/inc/rendermenus.php");
require_once("$root/inc/renderobjs.php");
require_once("$root/inc/renderqs.php");
require_once("$root/inc/renderhtml.php");


function sort_by_app_name($a,$b){
	return strcasecmp($a->name, $b->name);
}

//#######################################################################
//#######################################################################
class cRender{
	//************************************************************
	const APP_QS = "app";
	const APP_ID_QS = "aid";
	
	const DB_QS = "db";
	const IGNORE_REF_QS = "igr";
	
	const TIER_QS = "tier";
	const FROM_TIER_QS = "from";
	const TO_TIER_QS = "to";
	const TIER_ID_QS = "tid";
	const SERVICE_QS = "srv";
	const SERVICE_ID_QS= "sid";
	
	const TRANS_QS = "trans";
	const TRANS_ID_QS = "trid";
	const SNAP_GUID_QS = "snpg";
	const SNAP_URL_QS = "snpu";
	const SNAP_TIME_QS = "snpt";
	
	const NODE_QS = "nd";
	const FILTER_NODE_QS = "fnqs";
	const NODE_ID_QS = "ndid";
	
	const TITLE_QS = "tit";
	const METRIC_QS = "met";
	const METRIC_HEIRARCHY_QS = "meh";
	const BACKEND_QS = "back";
	const LICENSE_QS = "lc";
	
	const USAGE_QS = "us";
	const CSV_QS = "csv";
	const DIV_QS = "div";
	const METRIC_TYPE_QS ="mt";

	const PREVIOUS_QS="prv";
	const LOGIN_TOKEN_QS="lt";

	//************************************************************
	const GROUP_TYPE_QS ="gtq";
	const GROUP_TYPE_NODE ="n";
	const GROUP_TYPE_TIER ="t";
	const GROUP_TYPE_IP ="i";
	
	//**************************************************************************
	const RUM_DETAILS_QS ="rmd";
	const RUM_PAGE_QS = "rpg";
	const RUM_PAGE_ID_QS = "rpid";
	const RUM_TYPE_QS = "rty";
	const RUM_DETAILS_ACTIVITY ="rmda";
	const RUM_DETAILS_RESPONSE ="rmdr";
	const SYNTH_DETAILS_QS = "syd";
		
	//**************************************************************************
	const CHART_METRIC_FIELD = "cmf";
	const CHART_TITLE_FIELD = "ctf";
	const CHART_COUNT_FIELD = "ccf";
	const CHART_APP_FIELD = "caf";
	
	//**************************************************************************
	const NAME_APP = 1;
	const NAME_TIER = 2;
	const NAME_EXT = 3;
	const NAME_TRANS = 4;
	const NAME_OTHER = 99;
	
	//**************************************************************************
	public static function get_times(){
		$sTime = cCommon::get_session(cAppDynCommon::TIME_SESS_KEY);
		if ($sTime == cAppDynCommon::TIME_CUSTOM){
			$epochFrom = cCommon::get_session(cAppDynCommon::TIME_CUSTOM_FROM_KEY);
			$epochTo = cCommon::get_session(cAppDynCommon::TIME_CUSTOM_TO_KEY);
		}else{
			if ($sTime == "") $sTime=60;
			
			$epochTo = time() *1000;
			$epochFrom = $epochTo - ((60 * $sTime)*1000);
		}
		
		$oTimes = new cAppDynTimes();
		$oTimes->start = $epochFrom;
		$oTimes->end = $epochTo;
		
		return $oTimes;
	}
	
	//**************************************************************************
	public static function get_trans_speed_colour($piValue){

		$sImg = "green.png";
		if ($piValue > 5000) $sImg="red.png";
		elseif ($piValue > 1000) $sImg="nearlyred.png";
		elseif ($piValue > 200) $sImg="amber.png";
		
		return "images/$sImg";
	}

	//**************************************************************************
	public static function force_login(){
		global $home;
		cDebug::enter();
		try{
			$oCred = cRenderObjs::get_appd_credentials();
			cDebug::leave();;
			return $oCred->logged_in();
		}
		catch (Exception $e)
		{
			$sMsg = $e->getMessage();
			self::show_top_banner("not logged in "); 
			self::errorbox("there was a problem logging in - $sMsg");
			self::button("Back to login", "$home/index.php", false);
			die;
		}
		cDebug::leave();;
	}
	
	//**************************************************************************
	public static function errorbox($psMessage){
		?>
			<p>
			<div class='errorbox'>
				<h2>Oops there was an error</h2>
				<p>
				<?=$psMessage?>
			</div>
		<?php
	}
	//**************************************************************************
	public static function messagebox($psMessage){
		?>
			<p>
			<div class='errorbox'>
				<?=$psMessage?>
			</div>
		<?php
	}
	



	//*************************************************************
	public static function render_heatmap($paData, $psCaption, $psColCaption, $psRowCaption){
		$aColours = cColour::multigradient([ [0,255,0,7],[255,0,0,5],[255,255,0,7],[255,255,255,0]]);
		$iColours = count($aColours);
		
		$aCols = self::pr__get_cols($paData);
		$aRows = self::pr__get_rows($paData);
		echo "<h2>$psCaption</h2>";
		cColour::showGradient($aColours, "&nbsp;&nbsp;&nbsp;", 40);
		echo "<table border=0 cellspacing=0 class='maintable'><tr><td><table>";
			echo "<tr>";
				echo "<th>$psRowCaption \ $psColCaption</th>";
				foreach ($aCols as $sCol) echo "<th>$sCol</th>";
			echo "</tr>";
			foreach ($aRows as $sRow){
				echo "<tr>";
					echo "<th>$sRow</th>";
					foreach ($aCols as $sCol){
						if (isset($paData[$sCol][$sRow])){
							$iValue = $paData[$sCol][$sRow];
							$iColourValue = round($iValue*($iColours-1));
							$sColour = $aColours[$iColourValue];
							echo "<td bgcolor='$sColour' width=30 height=30 align='middle'>&nbsp;</td>  ";
						} else
							echo "<td>&nbsp;</td>";
					} 
				echo "</tr>";
			}
			

		echo "</table></td></tr></table>";
	}
	
	
	//**************************************************************************
	public static function getTierLinkUrl($psApp,$psAid, $psTier, $psTid)
	{
		return "tier.php?app=$psApp&aid=$psAid&tier=$psTier&tid=$psTid";
	}
	//**************************************************************************
	public static function getTierLink($psApp,$psAid, $psTier, $psTid)
	{
		return "<a href='".self::getTierLinkUrl($psApp,$psAid, $psTier, $psTid)."'>$psTier</a>";
	}
	
	//**************************************************************************
	public static function plain_button ($psCaption, $psUrl){
		
		$oCred = cRenderObjs::get_appd_credentials();
		if ($oCred->restricted_login) return;

		echo  "<button onclick=\"document.location.href='$psUrl';return false;\">$psCaption</button>";
	}

	//**************************************************************************
	public static function button ($psCaption, $psUrl, $pbNewWindow =false, $paParams=null){
		global $home;
		$bShow = false;
		$oCred = null;
		cDebug::enter();
		
		if ($psUrl === "$home/index.php")	{
			cDebug::write("showing login page");
			$bShow = true;
		}
		
		if (! $bShow){
			try{
				$oCred = cRenderObjs::get_appd_credentials();
				$bShow = true;
			}
			catch (Exception $e){
			}
		}

		if ($oCred !== null)
			if ($oCred->restricted_login){
					?><a class='fake_blue_button'><?=$psCaption?></a>&nbsp;<?php
					cDebug::leave();;
					return;
			}
		
		if ($bShow)
			echo self::button_code($psCaption, $psUrl, $pbNewWindow, $paParams);
		
		cDebug::leave();;
	}
	
	public static function button_code ($psCaption, $psUrl, $pbNewWindow =false, $paParams=null){
		$sClass = "blue_button";
		cDebug::enter();
		
		if ($pbNewWindow) 
			$sOnClick = "window.open(\"$psUrl\");";
		else
			$sOnClick = "document.location.href=\"$psUrl\"";
		
		if ($paParams !== null){
			if (gettype($paParams) !== "array") cDebug::error("expecting an array as the 4th parameter");
			if (isset($paParams["id"])) $sID=" id='".$paParams["id"]."'";
			if (isset($paParams["class"])) $sClass.=" ".$paParams["class"];
		}
		
		cDebug::leave();;
		return "<button  class='$sClass' onclick='$sOnClick;return false;'>$psCaption</button>";
	}
	
	//**************************************************************************
	public static function appdButton ($psUrl, $psCaption = "Launch in AppDynamics"){
		?>
			<a class="appd_button" title="<?=$psCaption?>" target='appd' href="<?=$psUrl?>"><?=$psCaption?></a>
		<?php
	}

	
	//**************************************************************************
	public static function show_html_time_options(){
		global $_SERVER,$home;
		
		$sUrl = urlencode($_SERVER['REQUEST_URI']);
		echo cAppDynCommon::get_time_label();
		
		$iDuration = cAppDynCommon::get_duration();
		?>
		<table border=1 cellpadding=0 cellspacing=0><tr><td>
			Time Shown:<br>
			<?php
			foreach ( cAppDynCommon::$TIME_RANGES as $sCaption=>$iValue)
				if ($iValue== $iDuration){
					?><button disabled="disabled"><?=$sCaption?></button><?php
				}else
					self::button($sCaption, "$home/pages/settime.php?duration=$iValue&url=$sUrl");
			?>
			<br><a href="togglelnk.php?url=<?=$sUrl?>">toggle links</a><p>
		</td></tr></table><p>
		<?php
	}
	
	//**************************************************************************
	public static function show_name($piNameType, $pvWhat){
		$sClass = "other_name";
		$sOutput = $pvWhat;
		switch( $piNameType){

			case self::NAME_TRANS: 
				$sClass = "trans_name"; 
				break;
			case self::NAME_APP: 
				$sClass = "app_name"; 
				$sOutput = $pvWhat->name;
				break;
			case self::NAME_TIER: 
				$sClass = "tier_name"; 
				$sOutput = $pvWhat->name;
				break;
			case self::NAME_EXT: 
				$sClass = "external_name"; break;
		}
		?><span class="<?=$sClass?>"><?=$sOutput?></span><?php
	}
	
	
	//**************************************************************************
	public static function show_time_options( $psTitle){
		global $_SERVER,$home;
		
		$sUrl = $_SERVER['REQUEST_URI'];
		if (strpos($sUrl,"%")) $sUrl = urldecode($sUrl);
		
		$oCred = cRenderObjs::get_appd_credentials();
		$sAccount = $oCred->account;
		$iDuration = cAppDynCommon::get_duration();
		
		//show the navigation menu - this updates the material design navigation menu
		cRenderMenus::top_menu();
		
		//show time options box #### to be moved to material design header ####
		?>
			<form name="frmTime" id="frmTime" action="<?=$home?>/pages/settime.php" method="get">
				<input type="hidden" name="url" value="<?=$sUrl?>">
				<table class="timebox"><tr>
					<td >
						<?=$sAccount?><br>
					</td>
					<td ><?=cAppDynCommon::get_time_label()?></td>
					<td width=90 ><select name="duration" onchange="document.getElementById('frmTime').submit();"><?php
						foreach (cAppDynCommon::$TIME_RANGES as $sCaption=>$iValue){
							$sSelected = "";
							if ($iValue== $iDuration)$sSelected =" selected";
							echo "<option value='$iValue' $sSelected>$sCaption</option>";
						}
					?></select></td>
					<td width=80 align="middle"><?php
						$sUrl = urlencode($sUrl);
						self::button("<i>Custom</i>", "$home/pages/customtime.php?url=$sUrl", false);
					?></td>
					<td align="right" class="logoimage"></td>
				</tr></table>
			</form>
		<?php
		cDebug::flush();
	}
	//**************************************************************************
	public static function show_top_banner( $psTitle){
		$bLoggedin = true;
		try{
			$oCred = cRenderObjs::get_appd_credentials();
			$sAccount = $oCred->account;
			$sHost = $oCred->host;
		}catch (Exception $e){
			$sAccount = "unknown";
			$sHost = "unknown";
			$bLoggedin = false;
		}

		?>
			<div class="timebox"><table width="100%"><tr>
				<td width="150">
					<?=($bLoggedin?cRenderMenus::top_menu():"")?>
				</td>
				<td  width="75%">
					<?=$sAccount?><br>
					<?=$sHost?><p>
					<b><?=$psTitle?></b>
				</td>
				<td class="logoimage"></td>
			</tr></table></div>
		<?php
	}
		
	//**************************************************************************
	public static function getRowClass()
	{
		cAppDynCommon::$ROW_TOGGLE = !cAppDynCommon::$ROW_TOGGLE;
		if (cAppDynCommon::$ROW_TOGGLE)
			return "row1";
		else
			return "row2";

	}
	

	//#####################################################################################
	//#####################################################################################
	private static function pr__get_rows($paArray){
		$aRows = [];
		
		foreach ($paArray as $sCol=>$aRows)
			foreach ($aRows as $sRow =>$iValue)
				$aRows[$sRow] = 0;
		
		ksort($aRows, SORT_NUMERIC);
		return (array_keys($aRows));
	}
	
	private static function pr__get_cols($paArray){
		$aCols = [];
		
		foreach ($paArray as $sCol=>$aRows)
			$aCols[$sCol] = 0;
		ksort($aCols, SORT_NUMERIC);
		return (array_keys($aCols));
	}
}
?>
