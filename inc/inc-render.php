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
require_once("$phpinc/appdynamics/core.php");
require_once("$root/inc/inc-filter.php");


//#######################################################################
//#######################################################################
class cRenderMenus{
	//******************************************************************************************
	public static function show_app_functions($poApp=null){
		global $home;
		$oCred = cRenderObjs::get_appd_credentials();
		if ($oCred->restricted_login) {
			cRender::button($poApp->name,null);
			return;
		}
		
		if ($poApp == null) $poApp = cRenderObjs::get_current_app();
		?>
			<span 
				type="appdmenus" menu="appfunctions" 
				home="<?=$home?>"
				appname="<?=$poApp->name?>" appid="<?=$poApp->id?>">
			</span>
		<?php
	}
	//******************************************************************************************
	public static function show_app_agent_menu($poApp = null){
		global $home;

		$oCred = cRenderObjs::get_appd_credentials();
		if ($oCred->restricted_login) 
			return;
		
		if ($poApp == null) $poApp = cRenderObjs::get_current_app();
		?>
			<span 
				type="appdmenus" menu="appagents" 				
				home="<?=$home?>"
				appname="<?=$poApp->name?>" appid="<?=$poApp->id?>">
			</span>
		<?php
	}

	//******************************************************************************************
	public static function show_apps_menu($psCaption, $psURLFragment, $psExtraQS=""){
		global $home;
	
		$oCred = cRenderObjs::get_appd_credentials();
		if ($oCred->restricted_login) {
			cRender::button($psCaption,null);
			return;
		}
		
		$sApps_fragment = self::get_apps_fragment();

		?>
			<span 
				type="appdmenus" menu="appsmenu" 
				home="<?=$home?>"
				caption="<?=$psCaption?>" url="<?=$psURLFragment?>" 
				extra="<?=$psExtraQS?>" <?=$sApps_fragment?>>
			</span>
		<?php
		self::show_app_functions();
	}
	
	//******************************************************************************************
	public static function show_tier_menu($psTier, $psURLFragment, $psExtraQS=""){
		global $home;
		$oCred = cRenderObjs::get_appd_credentials();
		if ($oCred->restricted_login)	return;

		$oApp = cRenderObjs::get_current_app();
		
		try{
			$oTiers = cAppDyn::GET_Tiers($oApp);
		}
		catch (Exception $e)
		{
			cRender::errorbox("Oops unable to get tier data from controller");
			exit;
		}
		
		$sFragment = "";
		$iCount = 1;
		foreach ($oTiers as $oTier){
			$sFragment .= " tname.$iCount=\"$oTier->name\" tid.$iCount=\"$oTier->id\" ";
			$iCount++;
		}
		
		?>
			<span 
				type="appdmenus" menu="tiermenu" 
				home="<?=$home?>"
				caption="<?=$psTier?>" url="<?=$psURLFragment?>" 
				extra="<?=$psExtraQS?>" <?=$sFragment?>>
			</span>
		<?php
	}

	//******************************************************************************************
	public static function 	show_tiernodes_menu($psCaption, $psUrl){
		global $home;
		$oApp = cRenderObjs::get_current_app();
		$tier = cHeader::get(cRender::TIER_QS);
		$aNodes = cAppDyn::GET_TierInfraNodes($oApp->name,$tier);	
		$sFragment = "";
		
		$iCount = 1;
		foreach ($aNodes as $oNode){
			$sFragment .= " node.$iCount=\"$oNode->name\"";
			$iCount++;
		}
		
		?>
			<span 
				type="appdmenus" menu="tiernodesmenu"  
				home="<?=$home?>"
				caption="<?=$psCaption?>" url="<?=$psUrl?>" 
				<?=$sFragment?>>
			</span>
		<?php
	}
	
	//******************************************************************************************
	public static function top_menu(){
		global $home;
		$oCred = cRenderObjs::get_appd_credentials();
		if ($oCred->restricted_login){
			cRender::button("Back to Login", "$home/index.php");
			return;
		}
		
		$sApps_fragment = self::get_apps_fragment();

		?>
			<span 
				type="appdmenus" menu="topmenu" 
				home="<?=$home?>"
				<?=$sApps_fragment?>>
			</span>
		<?php
	}
	
	//******************************************************************************************
	public static function show_tier_functions($poTier = null, $psNode=null){
		global $home;
		$oCred = cRenderObjs::get_appd_credentials();
	
		if ($oCred->restricted_login) {
			cRender::button($psTier,null);
			return;
		}
		if ($poTier == null){
			$poTier = cRenderObjs::get_current_tier();
		}
		?>
			<span 
				type="appdmenus" menu="tierfunctions"  
				home="<?=$home?>"
				tier="<?=$poTier->name?>" tid="<?=$poTier->id?>" node="<?=$psNode?>">
			</span>
		<?php
	}
	
	//******************************************************************************************
	public static function get_apps_fragment(){
		try{
			$aApps = cAppDyn::GET_Applications();
		}
		catch (Exception $e)
		{
			cRender::errorbox("Oops unable to get application data from controller");
			exit;
		}
		uasort($aApps,"sort_by_app_name" );
		$iCount=0;
		$sApps_fragment = "";
		foreach ($aApps as $oApp){
			$iCount++;
			$sApps_fragment.= "appname.$iCount =\"$oApp->name\" appid.$iCount=\"$oApp->id\" ";
		}
		
		return $sApps_fragment;
	}

}

function sort_by_app_name($a,$b){
	return strcasecmp($a->name, $b->name);
}
//#######################################################################
//#######################################################################
class cRenderObjs{
	//**************************************************************************
	private static $oAppDCredentials = null;
	
	//**************************************************************************
	public static function get_appd_credentials(){
		cDebug::enter();
		$oCred = self::$oAppDCredentials;
		if (!$oCred){
			cDebug::extra_debug("got credentials");
			$oCred = new cAppDynCredentials;
			$oCred->check();
			self::$oAppDCredentials = $oCred;
		}
		cDebug::leave();
		return $oCred;
	}
	
	public static function make_app_obj($psApp, $psAID){
		return new cAppDApp($psApp, $psAID);		
	}
	
	public static function get_current_app(){
		$sApp = cHeader::get(cRender::APP_QS);
		$sAID = cHeader::get(cRender::APP_ID_QS);
		return self::make_app_obj($sApp, $sAID);
	}

	public static function make_tier_obj($poApp, $psTier, $psTID){
		return new cAppDTier($poApp, $psTier, $psTID);
	}
	
	public static function get_current_tier(){
		$oApp = self::get_current_app();
		$sTier = cHeader::get(cRender::TIER_QS);
		$sTID = cHeader::get(cRender::TIER_ID_QS);
		return self::make_tier_obj($oApp, $sTier, $sTID);
	}
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
	const BACKEND_QS = "back";
	const LICENSE_QS = "lc";
	
	const USAGE_QS = "us";
	const CSV_QS = "csv";
	const DIV_QS = "div";
	
	const PREVIOUS_QS="prv";
	const LOGIN_TOKEN_QS="lt";

	//************************************************************
	const GROUP_TYPE_QS ="gtq";
	const GROUP_TYPE_NODE ="n";
	const GROUP_TYPE_TIER ="t";
	const GROUP_TYPE_IP ="i";
	
	//************************************************************
	const METRIC_TYPE_QS ="mt";
	
	const METRIC_TYPE_RUMCALLS = "mrc";
	const METRIC_TYPE_RUMRESPONSE = "mrr";
	const METRIC_TYPE_TRANSRESPONSE = "mtr";
	const METRIC_TYPE_DATABASE_TIME = "mdt";
	const METRIC_TYPE_ACTIVITY = "mac";
	const METRIC_TYPE_RESPONSE_TIMES = "mrt";
	const METRIC_TYPE_BACKEND_RESPONSE = "mbr";
	const METRIC_TYPE_BACKEND_ACTIVITY = "mba";
	
	
	const METRIC_TYPE_JMX_DBPOOLS = "mtjdbp";
	
	
	//**************************************************************************
	const RUM_DETAILS_QS ="rmd";
	const RUM_PAGE_QS = "rpg";
	const RUM_TYPE_QS = "rty";
	const RUM_DETAILS_ACTIVITY ="rmda";
	const RUM_DETAILS_RESPONSE ="rmdr";
	
	//**************************************************************************
	const CLEAN_BASE_APP_PARAMS = [self::APP_QS, self::APP_ID_QS];
	const CLEAN_BASE_TIER_PARAMS = [self::APP_QS, self::APP_ID_QS, self::TIER_QS, self::TIER_ID_QS];
	const CLEAN_BASE_NODE_PARAMS = [self::APP_QS, self::APP_ID_QS, self::TIER_QS, self::TIER_ID_QS, self::NODE_QS, self::NODE_ID_QS];
	
	const BASE_APP_PARAMS = [self::APP_QS, self::APP_ID_QS, cFilter::FILTER_APP_QS, cFilter::FILTER_TIER_QS ,cFilter::FILTER_NODE_QS];
	const BASE_TIER_PARAMS = [self::APP_QS, self::APP_ID_QS, self::TIER_QS, self::TIER_ID_QS,  cFilter::FILTER_APP_QS, cFilter::FILTER_TIER_QS , cFilter::FILTER_NODE_QS];
	const BASE_NODE_PARAMS = [self::APP_QS, self::APP_ID_QS, self::TIER_QS, self::TIER_ID_QS, self::NODE_QS, self::NODE_ID_QS , cFilter::FILTER_APP_QS, cFilter::FILTER_TIER_QS ,cFilter::FILTER_NODE_QS ];
	
	//**************************************************************************
	const CHART_METRIC_FIELD = "cmf";
	const CHART_TITLE_FIELD = "ctf";
	const CHART_COUNT_FIELD = "ccf";
	const CHART_APP_FIELD = "caf";
	
	
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
			cDebug::leave();
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
		cDebug::leave();
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
						if (array_key_exists($sRow, $paData[$sCol])){
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
					cDebug::leave();
					return;
			}
		
		if ($bShow)
			echo self::button_code($psCaption, $psUrl, $pbNewWindow, $paParams);
		
		cDebug::leave();
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
			if (array_key_exists("id", $paParams )) $sID=" id='".$paParams["id"]."'";
			if (array_key_exists("class", $paParams )) $sClass.=" ".$paParams["class"];
		}
		
		cDebug::leave();
		return "<button  class='$sClass' onclick='$sOnClick;return false;'>$psCaption</button>";
	}
	
	//**************************************************************************
	public static function appdButton ($psUrl, $psCaption = "Launch in AppDynamics"){
		?>
			<a class="appd_button" title="<?=$psCaption?>" target='appd' href="<?=$psUrl?>"><?=$psCaption?></a>
		<?php
	}

	//**************************************************************************
	public static function html_header ($psTitle){
		global $jsinc, $home;
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title><?=$psTitle?></title>
			<LINK rel="stylesheet" type="text/css" href="<?=$home?>/css/reporter.css" >
			<link rel="stylesheet" type="text/css" href="<?=$home?>/css/jquery-ui/jquery-ui.min.css">
			<link rel="stylesheet" href="<?=$jsinc?>/jquery-spinner/css/gspinner.min.css">			
			
			<!-- Global site tag (gtag.js) - Google Analytics -->
			<script async src="https://www.googletagmanager.com/gtag/js?id=UA-51550338-2"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag('js', new Date());

			  gtag('config', 'UA-51550338-2');
			</script>			
			<!-- End Google Tag Manager -->
			<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
			
			<script src="<?=$jsinc?>/ck-inc/debug.js"></script>
			<script src="<?=$jsinc?>/jquery/jquery-3.2.1.min.js"></script>
			<script src="<?=$jsinc?>/jquery-ui/jquery-ui.min.js"></script>
			<script src="<?=$jsinc?>/tablesorter/jquery.tablesorter.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-inview/jquery.inview.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-visible/jquery.visible.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/jquery-spinner/g-spinner.min.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/bean/bean.js"></script>

			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/debug.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/common.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/http.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/httpqueue.js"></script>
			<script type="text/javascript" src="<?=$jsinc?>/ck-inc/jquery/jquery.inviewport.js"></script>
			
			<script src="<?=$home?>/js/widgets/chart.js"></script>
			<script src="<?=$home?>/js/widgets/menus.js"></script>
			<script src="<?=$home?>/js/common.js"></script>
		
		</head>
		<BODY>
		<?php
		cDebug::flush();
		//error_reporting(E_ALL & ~E_WARNING);
	}
	
	//**************************************************************************
	public static function html_footer (){
		?>
		<script language="javascript">
			$(
				function(){
				$("button.blue_button").removeAttr("class").button();
				cMenus.renderMenus();
				}
			);
		</script>
		<table border=0 width="100%" class="footer"><tr><td>
			<div class="licenseBox">
				Copyright (c) 2013-2018 <a target="katsu" href="https://www.chickenkatsu.co.uk/">ChickenKatsu</a>
				<p>
				This software is protected by copyright under the terms of the 
				Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
				http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode
				<p>
				USE AT YOUR OWN RISK - NO GUARANTEES OF ANY FORM ARE EITHER EXPRESSED OR IMPLIED.
				<p>
				For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk.<br>
				You may commercially evaluate this software for no more than 1 calendar month<br>
				We're on <a href="https://github.com/open768/appdynamics-reporter">Github</a>
			</div>
			<div class="paidLicenseBox">
				Licensed to : <?=cSecret::LICENSED_TO?><!-- <?=cSecret::LICENSE_COMMENT?>-->
			</div>
			<small>
				Charts built using <a target="new" href="https://developers.google.com/chart/">Google Charts</a> licensed under the Creative Commons Attribution license.<br>
				uses <a href="http://tablesorter.com/">tablesorter</a> by Christian Bach licensed under the MIT license<br>
				uses <a href="https://gist.github.com/umidjons/8396981">pub sub pattern</a> by Baylor Rae licensed under the GNU General Public license<br>
				No passwords are stored by this application.<br>
				AppDynamics is a registered trademark of <a href="http://www.appdynamics.com/">AppDynamics, Inc</a>
			</small>				
		</td></tr></table>
		</BODY></HTML>
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
	public static function show_extcall_name($psExt){
		?><span class="external_name"><?=$psExt?></span><?php
	}
	
	//**************************************************************************
	public static function show_tier_name($poTier){
		?><span class="tier_name"><?=$poTier->name?></span><?php
	}
	
	//**************************************************************************
	public static function show_app_name($poApp){
		?><span class="app_name"><?=$poApp->name?></span><?php
	}
	
	//**************************************************************************
	public static function show_time_options( $psTitle){
		global $_SERVER,$home;
		
		$sUrl = $_SERVER['REQUEST_URI'];
		if (strpos($sUrl,"%")) $sUrl = urldecode($sUrl);
		cDebug::write("return URL is $sUrl");
		
		$oCred = cRenderObjs::get_appd_credentials();
		$sAccount = $oCred->account;
		$sHost = $oCred->host;
		$iDuration = cAppDynCommon::get_duration();
		
		?>
			<form name="frmTime" id="frmTime" action="<?=$home?>/pages/settime.php" method="get">
				<input type="hidden" name="url" value="<?=$sUrl?>">
				<table class="timebox"><tr>
					<td>
						<?=cRenderMenus::top_menu()?>
					</td>
					<td >
						<?=$sAccount?><br>
						<?=$sHost?><p>
						<b><?=$psTitle?></b>
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
	public static function build_app_qs( $poApp){
		$appQS = cHttp::build_qs(null, self::APP_QS, $poApp->name);
		$appQS = cHttp::build_qs($appQS, self::APP_ID_QS, $poApp->id);
		return $appQS;
	}

	//******************************************************************************************
	public static function build_tier_qs( $poTier){
		$sAppQs = self::build_app_qs($poTier->app);
		$sTierQs = cHttp::build_qs($sAppQs, self::TIER_QS, $poTier->name);
		$sTierQs = cHttp::build_qs($sTierQs, self::TIER_ID_QS, $poTier->id);
		return $sTierQs;
	}
	//#####################################################################################
	//#####################################################################################
	public static function get_clean_base_app_QS(){ return self::get_base_QS(self::CLEAN_BASE_APP_PARAMS);}
	public static function get_base_app_QS(){ return self::get_base_QS(self::BASE_APP_PARAMS);}
	public static function get_base_tier_QS(){return self::get_base_QS(self::BASE_TIER_PARAMS);}
	public static function get_base_node_QS(){return self::get_base_QS(self::BASE_NODE_PARAMS);}
	
	public static function get_base_QS($paParams){
		$sBaseUrl = "";
		
		foreach ($paParams as $sParam){
			$sValue = cHeader::get($sParam);
			if ($sValue) $sBaseUrl = cHttp::build_qs($sBaseUrl, $sParam, $sValue );
		}
		return $sBaseUrl;
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
