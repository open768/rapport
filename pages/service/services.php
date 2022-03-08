<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

set_time_limit(200); 

//####################################################################
cRenderHtml::header("Service End Points");
cRender::force_login();
cChart::do_header();
?>
	<script language="javascript" src="<?=$jsWidgets?>/tierserviceendpoints.js"></script>
<?php

//####################################################################
//get passed in values
$oApp = cRenderObjs::get_current_app();
$oTimes = cRender::get_times();
$oTier = null;
if (cHeader::get(cRenderQS::TIER_ID_QS))$oTier = cRenderObjs::get_current_tier();

//********************************************************************
//display a summary
cRenderCards::card_start();
	cRenderCards::action_start();
		cADCommon::button(cADControllerUI::serviceEndPoints($oApp,$oTimes));
		if ($oTier){
			$sAppQS = cRenderQS::get_base_app_QS($oApp);
			$sUrl = cHttp::build_url(cCommon::filename(), $sAppQS);
			cRender::button("Service End points for App: $oApp->name", $sUrl);
			cRenderMenus::show_tier_menu("Show Service EndPoints for");
			cRenderMenus::show_tier_functions($oTier);
		}else
			cRenderMenus::show_apps_menu("Show Service EndPoints for");

	cRenderCards::action_end();
cRenderCards::card_end();


//********************************************************************
if ($oTier){
	?>
		<div 
			id="tierwidget" 
			<?=cRenderQS::APP_ID_QS?>="<?=$oApp->id?>" 
			<?=cRenderQS::TIER_ID_QS?>="<?=$oTier->id?>" 
			<?=cRenderQS::TIER_QS?>="<?=$oTier->name?>" 
			<?=cRenderQS::HOME_QS?>="<?=$home?>" 
		>
			Please Wait..
		</div>
		<script language="javascript">
			$(function(){
				$("#tierwidget").adserviceendpoints();
			});
		</script>
	<?php
}else{
	//TBD make these widgets that hide  tiers that dont have service end points 
	$aTiers = $oApp->GET_Tiers();
	cRenderCards::card_start("Select a Tier");
		cRenderCards::body_start();
		?><DIV style="column-count:3"><?php
		$sLastCh = "";
		
		foreach ($aTiers as $oTier){
			$sTier = $oTier->name;
			$sCh = strtoupper($sTier[0]);
			if ($sCh !== $sLastCh){
				echo "<h3>$sCh</h3>";
				$sLastCh = $sCh;
			}
			
			$sTierQS = cRenderQS::get_base_tier_QS($oTier);
			$sUrl = cHttp::build_url(cCommon::filename(), $sTierQS);
			cRender::button($oTier->name, $sUrl);
			echo "<br>";
		}	
		?></DIV><?php
		cRenderCards::body_end();
	cRenderCards::card_end();
}

//####################################################################
cChart::do_footer();

cRenderHtml::footer();
?>
