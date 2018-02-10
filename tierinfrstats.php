<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

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
cChart::$width=cRender::CHART_WIDTH_LARGE;

//####################################################################
// huge time limit as this takes a long time//display the results
set_time_limit(200); 

//get passed in values
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$tier = cHeader::get(cRender::TIER_QS);
$node = cHeader::get(cRender::NODE_QS);
	

$title = "$app&gt;$tier&gt;Infrastructure";

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
$aNodes = cAppDyn::GET_TierInfraNodes($app,$tier);	

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null)	cRenderMenus::show_tier_functions();

?><select id="menuNodes">
	<option selected disabled>Show Infrastructure Details for</option>
	<option <?=($node?"":"disabled")?> value="<?=$sTierInfraUrl?>">(<?=$tier?>) tier</option>
	<option value="<?=$sAppInfraUrl?>">(<?=$app?>) Application</option>
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
	$sNodeID = cAppdynUtil::get_node_id($aid, $node);
	if ($sNodeID){
		$sUrl = cAppDynControllerUI::nodeDashboard($aid, $sNodeID);
		cRender::appdButton($sUrl);
	}
}


//####################################################################
?>
<p>
<h2>Overall Statistics for(<?=$tier?>) Tier, <?=($node?"($node) Server":"all Servers")?></h2>
<?php

	$aMetrics = [];
	$sMetricUrl=cAppDynMetric::tierCallsPerMin($tier);
	$aMetrics[]= [cChart::LABEL=>"Calls per min for ($tier) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass()];
	$sMetricUrl=cAppDynMetric::tierResponseTimes($tier);
	$aMetrics[]= [cChart::LABEL=>"Response times in ms for ($tier) tier", cChart::METRIC=>$sMetricUrl];
	$sClass = cRender::getRowClass();			
	cChart::metrics_table($oApp, $aMetrics, 2, $sClass);

//####################################################################
?>
<p>
<h2>Agent Statistics for(<?=$tier?>) Tier, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
//####################################################################
	$aMetricTypes = cRender::getInfrastructureAgentMetricTypes();
	
	$sAllUrl = cHttp::build_url("tierallnodeinfra.php", $sTierQs);

	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cRender::getInfrastructureMetric($tier,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl];
	}
	$sClass = cRender::getRowClass();			
	cChart::metrics_table($oApp, $aMetrics, 2, $sClass);
?>
<p>
<h2>Infrastructure Statistics for(<?=$tier?>) Tier, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
	$aMetricTypes = cRender::getInfrastructureMetricTypes();
	
	$sAllUrl = cHttp::build_url("tierallnodeinfra.php", $sTierQs);

	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cRender::getInfrastructureMetric($tier,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRender::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, cChart::GO_URL=>$sUrl];
	}
	$sClass = cRender::getRowClass();			
	cChart::metrics_table($oApp, $aMetrics, 2, $sClass);
?>

<?php
cChart::do_footer();

cRender::html_footer();
?>
