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


//choose a default duration


$CHART_IGNORE_ZEROS = false;

//####################################################################
cRenderHtml::header("tier infrastructure");
cRender::force_login();
cChart::do_header();
cChart::$width=cChart::CHART_WIDTH_LARGE;

//####################################################################
// huge time limit as this takes a long time//display the results
set_time_limit(200); 

//get passed in values
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;
$node = cHeader::get(cRenderQS::NODE_QS);
	

$title = "$oApp->name&gt;$oTier->name&gt;Infrastructure";

//stuff for later

$sAppQs = cRenderQS::get_base_app_QS($oApp);
$sTierQs = cRenderQS::get_base_tier_QS($oTier);
$sTierInfraUrl = cHttp::build_url(cCommon::filename(),$sTierQs);
$sAppInfraUrl = cHttp::build_url("appinfra.php",$sAppQs);
$oApp = cRenderObjs::get_current_app();

// show time options

$showlink = cCommon::get_session($LINK_SESS_KEY);
if (!$oTier->name){
	cCommon::errorbox("no Tier parameter found");
	exit;
}

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************


//####################################################################
//other buttons
$aNodes = $oTier->GET_Nodes();	

$oCred = cRenderObjs::get_AD_credentials();
if ($oCred->restricted_login == null)	cRenderMenus::show_tier_functions();

?><select id="menuNodes">
	<option selected disabled>Show Infrastructure Details for</option>
	<option <?=($node?"":"disabled")?> value="<?=$sTierInfraUrl?>">(<?=$oTier->name?>) tier</option>
	<option value="<?=$sAppInfraUrl?>"><?=$oApp->name?> Application</option>
	<optgroup label="Individual Servers"><?php
		foreach ($aNodes as $oNode){
			$sNode = $oNode->name;
			?><option <?=(($sNode == $node)?"disabled":"")?> value="<?=cHttp::build_url($sTierInfraUrl, cRenderQS::NODE_QS, $sNode)?>"><?=$sNode?></option><?php
		}
	?></optgroup>
</select>

<script>
$(  
	function(){
		$("#menuNodes").selectmenu({change:common_onListChange});
	}  
);
</script><?php
if ($node) {
	$sNodeID = cADUtil::get_node_id($oApp, $node);
	if ($sNodeID){
		$sUrl = cADControllerUI::nodeDashboard($oApp, $sNodeID);
		cADCommon::button($sUrl);
	}
}
$sDiskUrl = cHttp::build_url("tierdisks.php", $sTierQs);
cRender::button("disk statistics", $sDiskUrl);

cDebug::flush();
$sAllUrl = cHttp::build_url("tierallnodeinfra.php", $sTierQs);

//####################################################################
?>
<p>
<h2>Overall Statistics for <?=$oTier->name?>, <?=($node?"($node) Server":"all Servers")?></h2>
<?php

	$aMetrics = [];
	$sMetricUrl=cADTierMetricPaths::tierCallsPerMin($oTier->name);
	$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_ACTIVITY);
	$aMetrics[]= [
		cChart::LABEL=>"Calls per min for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,cChart::STYLE=>cRender::getRowClass(),
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See Activity for all nodes in Tier:$oTier->name"
	];
	
	$sMetricUrl=cADTierMetricPaths::tierResponseTimes($oTier->name);
	$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, cADMetricPaths::METRIC_TYPE_RESPONSE_TIMES);
	$aMetrics[]= [
		cChart::LABEL=>"Response times in ms for ($oTier->name) tier", cChart::METRIC=>$sMetricUrl,
		cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See Response Times for all nodes in Tier:$oTier->name"
	];
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cDebug::flush();

//####################################################################
?>
<p>
<h2>Agent Statistics for <?=$oTier->name?>, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
//####################################################################
	$aMetricTypes = cADInfraMetric::getInfrastructureAgentMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::HIDEIFNODATA=>true,
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$oTier->name"
		];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cDebug::flush();
?>
<p>
<h2>Memory Statistics for <?=$oTier->name?>, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
	$aMetricTypes = cADInfraMetric::getInfrastructureMemoryMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::HIDEIFNODATA=>true,
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$oTier->name"
		];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cDebug::flush();
?>
<p>
<h2>Infrastructure Statistics for <?=$oTier->name?>, <?=($node?"($node) Server":"all Servers")?></h2>
<?php
	$aMetricTypes = cADInfraMetric::getInfrastructureMiscMetricTypes();
	
	$aMetrics = [];
	foreach ($aMetricTypes as $sMetricType){
		$oMetric = cADInfraMetric::getInfrastructureMetric($oTier->name,$node,$sMetricType);
		$sUrl = cHttp::build_url($sAllUrl, cRenderQS::METRIC_TYPE_QS, $sMetricType);
		$aMetrics[]= [
			cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric, 
			cChart::HIDEIFNODATA=>true,
			cChart::GO_URL=>$sUrl, cChart::GO_HINT=>"See $oMetric->caption for all nodes in Tier:$oTier->name"
		];
	}
	cChart::render_metrics($oApp, $aMetrics, cChart::CHART_WIDTH_LETTERBOX/3);
	cDebug::flush();

	
cChart::do_footer();
cRenderHtml::footer();
?>
