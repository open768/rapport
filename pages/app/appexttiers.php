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
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//-----------------------------------------------
$oApp = cRenderObjs::get_current_app();
$sExtSought = cHeader::get(cRenderQS::BACKEND_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);

//####################################################################
$sTitle = "Matching External Calls in $oApp->name";
cRenderHtml::header($sTitle);
cRender::force_login();
cChart::do_header();
$oTimes = cRender::get_times();

//####################################################################
cRenderCards::card_start();
	cRenderCards::body_start();
		?>remote endpoints that match <?=$sExtSought?><?php
		cRender::add_filter_box("a[name]","name",".mdl-card");
	cRenderCards::body_end();
	cRenderCards::action_start();
		cRenderMenus::show_app_functions();
	cRenderCards::action_end();
cRenderCards::card_end();

//####################################################################
//-----------------------------------------------
$aTiers =$oApp->GET_Tiers();


//**********************************************************
//TODO this needs to be asynchonous in a widget for each tier
//**********************************************************
foreach ( $aTiers as $oTier){
	$sTier=$oTier->name;
	
	$aTierExt = cADUtil::get_matching_tier_extcalls($oTier, $sExtSought);
	cRenderCards::card_start("<a name='$sTier'>$sTier</a>");
		cRenderCards::body_start();
			if (count($aTierExt) > 0){
				$aMetrics = [];
					cDebug::vardump($aTierExt);
					foreach ($aTierExt as $sExtFound){
						$aMetrics[] = [cChart::LABEL=>$sExtFound,cChart::TYPE=>cChart::LABEL, cChart::WIDTH=>300];
						$aMetrics[] = [cChart::LABEL=>"Calls per min",cChart::METRIC=>cADTierMetricPaths::toTierCallsPerMin($sTier,$sExtFound)];
						$aMetrics[] = [cChart::LABEL=>"Response time in ms", cChart::METRIC=>cADTierMetricPaths::toTierResponseTimes($sTier,$sExtFound)];
					}
					cChart::metrics_table($oApp, $aMetrics,3,null);
			}else
				cCommon::messagebox("Nothing found");
		cRenderCards::body_end();
		cRenderCards::action_start();
			cRenderMenus::show_tier_functions($oTier);
			if (count($aTierExt) > 0){
				$sUrl = cRenderQS::get_base_tier_QS($oTier);
				$sUrl = cHttp::build_qs($sUrl, cRenderQS::BACKEND_QS, $sExtSought);
				$sUrl = cHttp::build_url("$home/pages/tier/tierextalltrans.php", $sUrl);
				cRender::button("All transactions involving this backend for this tier", $sUrl);	
			}
		cRenderCards::action_end();
	cRenderCards::card_end();
}	
	
cChart::do_footer();

cRenderHtml::footer();
?>
