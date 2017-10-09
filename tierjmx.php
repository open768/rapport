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
cRender::html_header("tier JMX Database Pools");
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
$tier = cHeader::get(cRender::TIER_QS);
$node = cHeader::get(cRender::NODE_QS);
$gsMetric = cHeader::get(cRender::METRIC_TYPE_QS);

// show time options
$title = "$app&gt;$tier&gt;Infrastructure&gt;JMX";
cRender::show_time_options($title); 
$showlink = cCommon::get_session($LINK_SESS_KEY);
if (!$tier){
	cRender::errorbox("no Tier parameter found");
	exit;
}
if (!$gsMetric){
	cRender::errorbox("no Metric found");
	exit;
}

//stuff for later
$sBaseQS = cRender::get_base_tier_QS();
$sBaseQS = cHttp::build_qs($sBaseQS, cRender::METRIC_TYPE_QS, $gsMetric);
$sBaseUrl = cHttp::build_url("tierjmx.php", $sBaseQS);


//####################################################################
//other buttons
$aNodes = cAppDyn::GET_TierInfraNodes($app,$tier);	

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null)	cRenderMenus::show_tier_functions();

?><select id="menuNodes">
	<option selected disabled>Show Details for</option>
	<optgroup label="tiers"><?php
		$aTiers = cAppdyn::GET_Tiers($app);
		$sBaseTierQS = cRender::get_base_app_QS();
		$sBaseTierQS = cHttp::build_qs($sBaseTierQS, cRender::METRIC_TYPE_QS, $gsMetric);
		$sBaseTierUrl = cHttp::build_url("tierjmx.php", $sBaseTierQS);
		
		foreach ($aTiers as $oTier){
			$sTierUrl = cHttp::build_url($sBaseTierUrl, cRender::METRIC_TYPE_QS, $gsMetric);
			$sTierUrl = cHttp::build_url($sTierUrl, cRender::TIER_QS, $oTier->name);
			$sTierUrl = cHttp::build_url($sTierUrl, cRender::TIER_ID_QS, $oTier->id);
			
			$bDisabled = (($oTier->name == $tier) && ($node==null));
			$sDisabled = ($bDisabled?"disabled":"");
			?><option <?=$sDisabled?> value="<?=$sTierUrl?>">(<?=$oTier->name?>) tier</option><?php
		}
	?></optgroup>
	<optgroup label="Individual Servers"><?php
		foreach ($aNodes as $oNode){
			$sNode = $oNode->name;
			?><option <?=(($sNode == $node)?"disabled":"")?> value="<?=cHttp::build_url($sBaseUrl, cRender::NODE_QS, $sNode)?>"><?=$sNode?></option><?php
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


//####################################################################
$aPools = cAppdyn::GET_JDBC_Pools($app,$tier,$node);
cChart::$width=cRender::CHART_WIDTH_LARGE/2;
?>
<h2>JDBC Pools for (<?=$tier?>) Tier</h2>
<?php
if ($node){
	?><h3>(<?=$node?>) Node</h3><?php
}
?>
<p>
<table class="maintable"><?php
	if (count($aPools) == 0){
		?><tr><td>No Pools Found</td></tr><?php
		return;
	}
		
		
	foreach ($aPools as $oPool){
		$sPool = $oPool->name;
		?><tr class="<?=cRender::getRowClass()?>">
			<td><?=$sPool?></td>
			<td><?php
				$sMetric = cAppDynMetric::InfrastructureJDBCPoolActive($tier,$node, $sPool);
				cChart::add("active connections" , $sMetric, $app, 100);
			?></td>
			<td><?php
				$sMetric = cAppDynMetric::InfrastructureJDBCPoolMax($tier,$node, $sPool);
				cChart::add("Max connections" , $sMetric, $app, 100);
			?></td>
		</tr><?php
	}
?></table>

<?php
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>
