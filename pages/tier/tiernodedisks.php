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


//####################################################################
cRenderHtml::$load_google_charts = true;
cRenderHtml::header("tier disks");
cChart::do_header();
cRender::force_login();

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sTierQS = cRenderQS::get_base_tier_QS($oTier);
$sNode = cHeader::get(cRenderQS::NODE_QS);

//********************************************************************
cRenderCards::card_start("Overall Disks Metrics for $oTier->name");
	cRenderCards::body_start();
		$aData = $oTier->GET_DiskMetrics();
		$sBaseMetric = cADInfraMetric::InfrastructureNodeDisks($oTier->name);
		$aMetrics = [];
		foreach ($aData as $oMetric)
			$aMetrics[]= [cChart::LABEL=>$oMetric->name, cChart::METRIC=>$oMetric->name];
		cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cRenderCards::body_end();
	cRenderCards::action_start();
		$oCred = cRenderObjs::get_AD_credentials();
		if (!$oCred->restricted_login) cRenderMenus::show_tier_functions();
		$sDiskUrl = cHttp::build_url("tierdisks.php",$sTierQS);
		cRender::button("Back to Tier Disks", $sDiskUrl);

	cRenderCards::action_end();
cRenderCards::card_end();

//********************************************************************
if ($sNode){
	cRenderCards::card_start("disks for Node..$sNode");
	cRenderCards::body_start();
		$sBaseMetric = cADInfraMetric::InfrastructureNodeDisks($oTier->name, $sNode);
		$aData = $oTier->GET_NodeDisks($sNode);
		if (count($aData)==0)
			cCommon::messagebox("no disks found");
		else{
			$aMetrics = [];
			foreach ($aData as $oDisk){
				$aMetrics[]= [cChart::LABEL=>$oDisk->name, cChart::TYPE=>cChart::LABEL, cChart::WIDTH=>250];

				$sMetric = cADInfraMetric::InfrastructureNodeDiskFree($oTier->name, $sNode, $oDisk->name);
				$aMetrics[]= [cChart::LABEL=>$oDisk->name." free", cChart::METRIC=>$oMetric->name];
				$sMetric = cADInfraMetric::InfrastructureNodeDiskUsed($oTier->name, $sNode, $oDisk->name);
				$aMetrics[]= [cChart::LABEL=>$oDisk->name." used", cChart::METRIC=>$oMetric->name];
			}
			cChart::metrics_table($oApp, $aMetrics, 3, cRender::getRowClass());
		}
		cRenderCards::body_end();
	cRenderCards::card_end();
}

//********************************************************************
cChart::do_footer();
cRenderHtml::footer();
?>
