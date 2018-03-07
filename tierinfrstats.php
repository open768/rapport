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
$root=realpath(".");
$phpinc = realpath("$root/../phpinc");
$jsinc = "../jsinc";

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/http.php");
require_once("$phpinc/ckinc/header.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");

//choose a default duration


$CHART_IGNORE_ZEROS = false;

//####################################################################
cRender::html_header("tier infrastructure");
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE;

//####################################################################
// huge time limit as this takes a long time//display the results
set_time_limit(200); 

//get passed in values
$oApp = cRender::get_current_app();
$tier = cHeader::get(cRender::TIER_QS);
$node = cHeader::get(cRender::NODE_QS);
	

$title = "$oApp->name&gt;$tier&gt;Infrastructure";

//stuff for later

$sAppQs = cRender::get_base_app_QS();
$sTierQs = cRender::get_base_tier_QS();
$sTierInfraUrl = cHttp::build_url("tierinfrstats.php",$sTierQs);
$sAppInfraUrl = cHttp::build_url("appinfra.php",$sAppQs);
$oApp = cRender::get_current_app();

// show time options
cRender::show_time_options($title); 
$showlink = cCommon::get_session($LINK_SESS_KEY);
if (!$tier){
	cRender::errorbox("no Tier parameter found");
	exit;
}

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************


//####################################################################
//other buttons
$aNodes = cAppDyn::GET_TierInfraNodes($oApp->name,$tier);	

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null)	cRenderMenus::show_tier_functions();

?><select id="menuNodes">
	<option selected disabled>Show Infrastructure Details for</option>
	<option <?=($node?"":"disabled")?> value="<?=$sTierInfraUrl?>">(<?=$tier?>) tier</option>
	<option value="<?=$sAppInfraUrl?>">(<?=$oApp->name?>) Application</option>
	<optgroup label="Individual Servers"><?php
		foreach ($aNodes as $oNode){
			$sNode = $oNode->name;
			?><option <?=(($sNode == $node)?"disabled":"")?> value="<?=cHttp::build_url($sTierInfraUrl, cRender::NODE_QS, $sNode)?>"><?=$sNode?></option><?php
		}
	?></optgroup>
</select>

<script language="javascript">
$(  
	function(){
		$("#menuNodes").selectmenu({change:common_onListChange});
	}  
);
</script><?php
if ($node) {
	$sNodeID = cAppdynUtil::get_node_id($oApp->id, $node);
	if ($sNodeID){
		$sUrl = cAppDynControllerUI::nodeDashboard($oApp->id, $sNodeID);
		cRender::appdButton($sUrl);
	}
}
$sAllUrl = cHttp::build_url("tierallnodeinfra.php", $sTierQs);


//####################################################################
?>
<p>
<h2>Overall Statistics for(<?=$tier?>) Tier, <?=($node?"($node) Server":"all Servers")?></h2>
<?php

	$aMetrics = [];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($tier);
	$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, cRender::METRIC_TYPE_ACTIVITY);
	$aMetrics[]= [
		cChart::LABEL=>"Calls per min for ($tier) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See Activity for all nodes in Tier:$tier"
	];
	
	$sMetricUrl=cAppDynMetric::tierResponseTimes($tier);
	$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, cRender::METRIC_TYPE_RESPONSE_TIMES);
	$aMetrics[]= [
		cChart::LABEL=>"Response times in ms for ($tier) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See Response Times for all nodes in Tier:$tier"
	];
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);

//####################################################################
?>
<p>
<h2>Agent Statistics for(<?=$tier?>) Tier, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
//####################################################################
	$aMetricTypes = cAppDynInfraMetric::getInfrastructureAgentMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cAppDynInfraMetric::getInfrastructureMetric($tier,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$tier"
		];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
?>
<p>
<h2>Memory Statistics for(<?=$tier?>) Tier, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
	$aMetricTypes = cAppDynInfraMetric::getInfrastructureMemoryMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cAppDynInfraMetric::getInfrastructureMetric($tier,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$tier"];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
?>
<p>
<h2>Infrastructure Statistics for(<?=$tier?>) Tier, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
	$aMetricTypes = cAppDynInfraMetric::getInfrastructureMiscMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cAppDynInfraMetric::getInfrastructureMetric($tier,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$tier"];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
?>

<?php
cChart::do_footer();
cRender::html_footer();
?>
