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


//####################################################################
//get passed in values
$moTier = cRenderObjs::get_current_tier();
if ($moTier)
	$moApp = $moTier->app;
else
	$moApp = cRenderObjs::get_current_app();

$gsAppQS = cRenderQS::get_base_app_QS($moApp);


cRenderHtml::$load_google_charts = true;
cRenderHtml::header("Transactions for $moApp->name");
cRender::force_login();

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

if (!$moApp) cDebug::error("no app");


//####################################################################
function tier_card($poTier){
	global $moApp, $gsAppQS, $home;

	//get the transaction names for the Tier
	cRenderCards::card_start();
		cRenderCards::body_start();
			?><div
				type="adtiertrans"
				home="<?=$home?>"
				<?=cRenderQS::APP_ID_QS?>="<?=$moApp->id?>"
				<?=cRenderQS::TIER_ID_QS?>="<?=$poTier->id?>">please wait...</div><?php
		cRenderCards::body_end();
		cRenderCards::action_start();
		cRenderMenus::show_tier_functions($poTier);
			echo "<span type='tiertrans' name='$poTier->name'></span>";
			$sUrl = cHttp::build_url("tiertransgraph.php", $gsAppQS);
			$sUrl = cHttp::build_url($sUrl, cRenderQS::TIER_QS, $poTier->name);
			$sUrl = cHttp::build_url($sUrl, cRenderQS::TIER_ID_QS, $poTier->id);
			cRender::button("show transaction graphs", $sUrl);
		cRenderCards::action_end();
	cRenderCards::card_end();
}
//####################################################################

?><script src="<?=$jsWidgets?>/tiertrans.js"></script><?php

//*********************** header panel ***********************************
cRenderCards::card_start();
cRenderCards::body_start();
	cRender::add_filter_box("span[type=tiertrans]","name",".mdl-card");
cRenderCards::body_end();
cRenderCards::action_start();
	cRenderMenus::show_app_change_menu("Change Application");
	cADCommon::button(cADControllerUI::businessTransactions($moApp));
	$sUrl = cHttp::build_url("config.php", $gsAppQS);
	cRender::button("config", $sUrl);

	$sUrl = cHttp::build_url("$home/pages/app/datacollectors.php", $gsAppQS);
	cRender::button("data collectors", $sUrl);

	$sUrl = cHttp::build_url("allother.php", $gsAppQS);
	cRender::button("All Other Transactions", $sUrl);
cRenderCards::action_end();
cRenderCards::card_end();

if ($moTier){
	tier_card($moTier);
}else{
	$aTiers =$moApp->GET_Tiers();
	foreach ( $aTiers as $oTier){
		$oTier->app = $moApp;
		tier_card($oTier);
	}
}

?>

<script>
	function init_widget(piIndex, poElement){
		$(poElement).adtiertrans();
	}
	$("div[type=adtiertrans]").each( init_widget);
</script>

<?php
cRenderHtml::footer();
?>
