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
//# TODO make asynchronous as it can take a long time to load
//####################################################################

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";



//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();

//####################################################################
$title ="$oApp->name Application Errors and Exceptions";
cRenderHtml::header("$title");
cRender::force_login();
?><script language="javascript" src="<?=$jsWidgets?>/tiererrors.js"></script><?php

$oTimes = cRender::get_times();

$oCred = cRenderObjs::get_AD_credentials();
if ($oCred->restricted_login == null){
	cRenderCards::card_start();
	cRenderCards::body_start();
		cRender::add_filter_box("span[type=tiername]","tiername",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_app_functions($oApp);
	cRenderCards::action_end();
	cRenderCards::card_end();		
}
//#############################################################
function sort_metric_names($poRow1, $poRow2){
	return strnatcasecmp($poRow1->metricPath, $poRow2->metricPath);
}

$gsTABLE_ID = 0;

//*****************************************************************************
function render_tier_errors($poTier){
	global $oApp, $oTimes, $home, $gsTABLE_ID;
	
	$tierQS = cRenderQS::get_base_tier_QS( $poTier);
	$sGraphUrl = cHttp::build_url("../tier/tiererrorgraphs.php", $tierQS);
	
	cRenderCards::card_start("<span type=\"tiername\" tiername=\"$poTier->name\">$poTier->name</span>");
	cRenderCards::body_start();
		?><div type="tiererrors" home="<?=$home?>" 
				<?=cRender::TIER_QS?>="<?=$poTier->name?>"
				<?=cRender::APP_ID_QS?>="<?=$oApp->id?>">
					Loading Errors for: <?=$poTier->name?>
		</div><?php
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_tier_functions($poTier);
		cADCommon::button(cADControllerUI::tier_errors($oApp, $poTier));
		cRender::button("Show Error Graphs", $sGraphUrl);	
	cRenderCards::action_end();
	cRenderCards::card_end();		
}

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


//#############################################################
//get the page metrics
$aResponse =$oApp->GET_Tiers();
if ( count($aResponse) == 0)
	cCommon::messagebox("Nothing found");
else
	foreach ( $aResponse as $oTier)
		render_tier_errors($oTier);
?>
<script language="javascript">
	function init_widget(piIndex, poElement){
		$(poElement).adtiererrors();
	}
	$("div[type=tiererrors]").each( init_widget);
</script>

<?php
cRenderHtml::footer();
?>
