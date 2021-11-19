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



const COLUMNS=6;
error_reporting(E_ALL);

//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$totier = cHeader::get(cRender::TO_TIER_QS);
$gsTierQS = cRenderQS::get_base_tier_QS($oTier);
$sTransQs = cHttp::build_qs($gsTierQS, cRender::BACKEND_QS, $totier);

//####################################################################
cRenderHtml::header("External tier calls");
cRender::force_login();
cChart::do_header();

//####################################################################
$title =  "$oApp->name&gt;$oTier->name&gt; to tier $totier";		

cRenderMenus::show_tier_functions($oTier);
cRender::button("back to ($oTier->name) external tiers", cHttp::build_url("tierextgraph.php", $gsTierQS));
?>
<h2>Activity details <?=cRender::show_name(cRender::NAME_TIER,$oTier)?> to (<?=$totier?>)</h2>
<p>
<?php
//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
	cRender::button("see Calling Transactions", "tierextalltrans.php?$sTransQs" );
	$aMetrics=[];
	$sMetricUrl=cADMetricPaths::tierExtCallsPerMin($oTier->name, $totier);
	$aMetrics[] = [cChart::LABEL=>"Calls per min from ($oTier->name) to ($totier)", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cADMetricPaths::tierExtResponseTimes($oTier->name, $totier);
	$aMetrics[] = [cChart::LABEL=>"Response Times in ms from ($oTier->name) to ($totier)", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp,$aMetrics,1,cRender::getRowClass());

//####################################################################
//################ CHART
cChart::do_footer();

cRenderHtml::footer();
?>