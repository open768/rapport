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
require_once("../../inc/root.php");
cRoot::set_root("../..");
require_once("$root/inc/common.php");
require_once("$root/inc/inc-charts.php");
require_once("$phpinc/appdynamics/metrics.php");

//####################################################################
cRenderHtml::header("tier disks");
cChart::do_header();
cRender::force_login();

//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$sTierQS = cRenderQS::get_base_tier_QS($oTier);

// show time options
$title = "$oApp->name&gt;$oTier->name&gt;Tier Infrastructure&gt;disks";
cRender::show_time_options($title); 
$showlink = cCommon::get_session($LINK_SESS_KEY);

//other buttons
$oCred = cRenderObjs::get_appd_credentials();
if (!$oCred->restricted_login) cRenderMenus::show_tier_functions();

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


//data for the page
	
//####################################################################
?>
<h2>Overall Disks Metrics for <?=cRender::show_name(cRender::NAME_TIER,$oTier)?></h2>
<?php
	$aData = $oTier->GET_DiskMetrics();
	$sBaseMetric = cAppdynMetric::InfrastructureNodeDisks($oTier->name);
	$aMetrics = [];
	foreach ($aData as $oMetric)
		$aMetrics[]= [cChart::LABEL=>$oMetric->name, cChart::METRIC=>$oMetric->name];
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);

?>
<p>
<h2>Show disks for Node..</h2>
<?php
$aNodes = $oTier->GET_Nodes();
$sBaseUrl = cHttp::build_url("tiernodedisks.php",$sTierQS);
foreach ($aNodes as $oNode){
	$sUrl = cHttp::build_url($sBaseUrl, cRender::NODE_QS, $oNode->name);
	cRender::button($oNode->name, $sUrl);
	echo " ";
}

cChart::do_footer();
cRenderHtml::footer();
?>
