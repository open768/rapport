<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");


const COLUMNS=6;
error_reporting(E_ALL);

//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$totier = cHeader::get(cRender::TO_TIER_QS);
$gsTierQS = cRender::get_base_tier_QS();
$sTransQs = cHttp::build_qs($gsTierQS, cRender::BACKEND_QS, $totier);

//####################################################################
cRender::html_header("External tier calls");
cRender::force_login();
cChart::do_header();

//####################################################################
$title =  "$oApp->name&gt;$oTier->name&gt; to tier $totier";		
cRender::show_time_options($title); 
cRenderMenus::show_tier_functions($oTier);
cRender::button("back to ($oTier->name) external tiers", cHttp::build_url("tierextgraph.php", $gsTierQS));
?>
<h2>Activity details <?=cRender::show_tier_name($oTier)?> to (<?=$totier?>)</h2>
<p>
<?php
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************
	cRender::button("see Calling Transactions", "tierextalltrans.php?$sTransQs" );
	$aMetrics=[];
	$sMetricUrl=cAppDynMetric::tierExtCallsPerMin($oTier->name, $totier);
	$aMetrics[] = [cChart::LABEL=>"Calls per min from ($oTier->name) to ($totier)", cChart::METRIC=>$sMetricUrl];
	$sMetricUrl=cAppDynMetric::tierExtResponseTimes($oTier->name, $totier);
	$aMetrics[] = [cChart::LABEL=>"Response Times in ms from ($oTier->name) to ($totier)", cChart::METRIC=>$sMetricUrl];
	cChart::metrics_table($oApp,$aMetrics,1,cRender::getRowClass());

//####################################################################
//################ CHART
cChart::do_footer();

cRender::html_footer();
?>