<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
require_once("$phpinc/ckinc/colour.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
require_once("$ADlib/AD.php");
require_once("$ADlib/core.php");
require_once("$root/inc/filter.php");
require_once("$root/inc/rendermenus.php");
require_once("$root/inc/rendercards.php");
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
	const IGNORE_REF_QS = "igr";
	const LIST_MODE_QS = "list";
	
	//************************************************************
	const LOCATION_QS="loc";
	
	//************************************************************
	const GROUP_TYPE_QS ="gtq";
	const GROUP_TYPE_NODE ="n";
	const GROUP_TYPE_TIER ="t";
	const GROUP_TYPE_IP ="i";

	//************************************************************
	const LOG_ID_QS ="loi";
	const LOG_VERSION_QS = "lov";
	const LOG_CREATED_QS = "loc";
	
	//************************************************************
	const HEALTH_ID_QS ="hi";
	
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
	const SERVER_MQ_MANAGER_QS = "mqm";
	
	
	//**************************************************************************
	const NAME_APP = 1;
	const NAME_TIER = 2;
	const NAME_EXT = 3;
	const NAME_TRANS = 4;
	const NAME_OTHER = 99;
	public static $MAX_ITEMS_PER_PAGE = 20;
	
	//**************************************************************************
	public static function get_times(){
		$sTime = cCommon::get_session(cADCommon::TIME_SESS_KEY);
		if ($sTime == cADCommon::TIME_CUSTOM){
			$epochFrom = cCommon::get_session(cADCommon::TIME_CUSTOM_FROM_KEY);
			$epochTo = cCommon::get_session(cADCommon::TIME_CUSTOM_TO_KEY);
		}else{
			if ($sTime == "") $sTime=60;
			
			$epochTo = time() *1000;
			$epochFrom = $epochTo - ((60 * $sTime)*1000);
		}
		
		$oTimes = new cADTimes();
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
		//cDebug::enter();
		
		$oCred = cRenderObjs::get_appd_credentials();
		if ($oCred == null || !$oCred->logged_in()){
			cCommon::errorbox("not logged in in");
			$sUrl = cHttp::build_url("$home/index.php", cRender::LOCATION_QS, $_SERVER["REQUEST_URI"]);
			self::button("Back to login", "$sUrl", false);
			cDebug::flush();
			exit;
		}
		//cDebug::leave();;
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
	public static function add_filter_box($psSelector,$psAttr,$psParentSelector){
		if 	(self::is_list_mode()) return;

		?><form action="#">
			<div class="mdl-textfield mdl-js-textfield">
				<input class="mdl-textfield__input" type="text" id="filter" disabled>
				<label class="mdl-textfield__label" for="filter">Filter...</label>
			</div>
		</form>
		<script language="javascript">
			
			function onKeyUp( poEvent){
				//look through divs with selectmenu
				var aSelected = $("<?=$psSelector?>");
				var sInput = $("#filter").val().toLowerCase();
				//iterate
				aSelected.each(
					function(index){
						//skip selected that dont have the desired attribute
						var oEl = $(this);
						var sAttr = oEl.attr("<?=$psAttr?>");
						if (sAttr) {
							//check the attribute for a match
							var oParent=$(this).closest("<?=$psParentSelector?>");
							if (sInput.length < 3){	//must be at least 3 chars
								oParent.show();
							}else{
								sAttr = sAttr.toLowerCase();
								if ( sAttr.indexOf(sInput) == -1)
									oParent.hide();
								else
									oParent.show();
							}
						}
					}
				);
			}
			
			$( 			
				function setFilterKeyUp(){
					$(
						function(){
							$("#filter" ).prop( "disabled", false );
							$("#filter" ).keyup(onKeyUp);
						}
					);
				}
			);
		</script>
	<?php	}

	//**************************************************************************
	public static function button ($psCaption, $psUrl, $pbNewWindow =false, $paParams=null, $psTarget=null){
		global $home;
		$bShow = false;
		$oCred = null;
		//cDebug::enter();
		
		if ($psUrl === "$home/index.php") {
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
			echo self::button_code($psCaption, $psUrl, $pbNewWindow, $paParams, $psTarget);
		
		//cDebug::leave();;
	}
	
	//**************************************************************************
	public static function button_code ($psCaption, $psUrl, $pbNewWindow =false, $paParams=null, $psTarget=null){
		$sClass = "blue_button";
		//cDebug::enter();
		
		if ($pbNewWindow){ 
			if ($psTarget !== "")
				$sOnClick = "window.open(\"$psUrl\",\"$psTarget\")";
			else
				$sOnClick = "window.open(\"$psUrl\")";
		}else
			$sOnClick = "document.location.href=\"$psUrl\"";
		
		if ($paParams !== null){
			if (gettype($paParams) !== "array") cDebug::error("expecting an array as the 4th parameter");
			if (isset($paParams["id"])) $sID=" id='".$paParams["id"]."'";
			if (isset($paParams["class"])) $sClass.=" ".$paParams["class"];
		}
		
		///cDebug::leave();;
		$sClass="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect";
  		return "<button  class='$sClass' onclick='$sOnClick;return false;'>$psCaption</button>";
	}
	
	
	//**************************************************************************
	public static function is_list_mode(){
		return (cHeader::get(self::LIST_MODE_QS) !== null);
	}
	
	//**************************************************************************
	public static function show_html_time_options(){
		global $_SERVER,$home;
		
		if (cDebug::is_debugging()) return;

		$sUrl = urlencode($_SERVER['REQUEST_URI']);
		echo cADCommon::get_time_label();
		
		$iDuration = cADCommon::get_duration();
		?>
		<table border=1 cellpadding=0 cellspacing=0><tr><td>
			Time Shown:<br>
			<?php
			foreach ( cADCommon::$TIME_RANGES as $sCaption=>$iValue)
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
	public static function show_time_options(){
		global $_SERVER,$home;
		
		$sUrl = $_SERVER['REQUEST_URI'];
		if (strpos($sUrl,"%")) $sUrl = urldecode($sUrl);
		
		$oCred = cRenderObjs::get_appd_credentials();
		$sAccount = $oCred->account;
		$iDuration = cADCommon::get_duration();
		
		if (cDebug::is_debugging()) return;
		
		//show time options box #### to be moved to material design header ####
		?>
			<form name="frmTime" id="frmTime" action="<?=$home?>/pages/settime.php" method="get">
				<input type="hidden" name="url" value="<?=$sUrl?>">
				<table><tr>
					<td width=90 ><select name="duration" onchange="document.getElementById('frmTime').submit();"><?php
						foreach (cADCommon::$TIME_RANGES as $sCaption=>$iValue){
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
	public static function getRowClass()
	{
		cADCommon::$ROW_TOGGLE = !cADCommon::$ROW_TOGGLE;
		if (cADCommon::$ROW_TOGGLE)
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
