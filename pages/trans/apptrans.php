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

require_once("$root/inc/charts.php");

//####################################################################
// common functions
//####################################################################
//get passed in values
$oApp = cRenderObjs::get_current_app();
$gsAppQS = cRenderQS::get_base_app_QS($oApp);

cRenderHtml::header("Transactions for $oApp->name");
cRender::force_login();
//header

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
?><script language="javascript" src="<?=$home?>/js/widgets/tiertrans.js"></script><?php


cRenderCards::card_start();
cRenderCards::body_start();
	cRender::add_filter_box("span[type=tiertrans]","name",".mdl-card");
cRenderCards::body_end();
cRenderCards::action_start();
	cRenderMenus::show_apps_menu("Change Application", "apptrans.php");
	cADCommon::button(cADControllerUI::businessTransactions($oApp));
	$sUrl = cHttp::build_url("config.php", $gsAppQS);
	cRender::button("config", $sUrl);
cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
$aTiers =$oApp->GET_Tiers();
foreach ( $aTiers as $oTier){
	$oTier->app = $oApp;
	
	//get the transaction names for the Tier
	cRenderCards::card_start();
		cRenderCards::body_start();
			?><div 
				type="adtiertrans" 
				home="<?=$home?>" 
				<?=cRender::APP_ID_QS?>="<?=$oApp->id?>" 
				<?=cRender::TIER_ID_QS?>="<?=$oTier->id?>">please wait...</div><?php
		cRenderCards::body_end();
		cRenderCards::action_start();
		cRenderMenus::show_tier_functions($oTier);
			echo "<span type='tiertrans' name='$oTier->name'></span>";
			$sUrl = cHttp::build_url("tiertransgraph.php", $gsAppQS);
			$sUrl = cHttp::build_url($sUrl, cRender::TIER_QS, $oTier->name);
			$sUrl = cHttp::build_url($sUrl, cRender::TIER_ID_QS, $oTier->id);
			cRender::button("show transaction graphs", $sUrl);
		cRenderCards::action_end();
	cRenderCards::card_end();
}
?>

<script language="javascript">
	function init_widget(piIndex, poElement){
		$(poElement).adtiertrans();
	}
	$("div[type=adtiertrans]").each( init_widget);
</script>

<?php
				

cRenderHtml::footer();
?>
