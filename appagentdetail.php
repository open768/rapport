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
require_once("$phpinc/ckinc/header.php");
require_once("$phpinc/ckinc/http.php");
	
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();

//####################################################################

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");


cRender::html_header("Application node detail ");
cRender::force_login();
$oApp = cRender::get_current_app();
$gsMetricType = cHeader::get(cRender::METRIC_TYPE_QS);

//####################################################################
?>
	<script type="text/javascript" src="js/remote.js"></script>
	
<?php
cChart::do_header();
cChart::$width = cChart::CHART_WIDTH_LARGE;
//####################################################################
function pr__sort_nodes($a,$b){
	return strcmp($a[0]->tierName, $b[0]->tierName);
}

function group_by_tier($paNodes){
	$aTiers = [];
	
	foreach ($paNodes as $aTierNodes)
		foreach ($aTierNodes as $oNode){
			$TierID = $oNode->tierId;
			if (!array_key_exists((string)$TierID, $aTiers)) $aTiers[(string)$TierID] = [];
			$aTiers[(string)$TierID][] = $oNode;
		}
		
	return $aTiers;
}

function count_nodes($paData){
	$iCount = 0;
	foreach ($paData as $aTierNodes)
		$iCount += count($aTierNodes);
		
	return $iCount;
}

//####################################################################
if (!$oApp->name ){
	cRender::errorbox("no application");
	exit;
}
if (!$gsMetricType ){
	cRender::errorbox("no metric type");
	exit;
}
$oMetric = cAppDynInfraMetric::getInfrastructureMetric($oApp->name,null,$gsMetricType);
$sTitle  = $oMetric->caption;

//####################################################################
$sAppQS = cRender::get_base_app_QS();

$sDetailRootQS = cHttp::build_url("appagentdetail.php", $sAppQS);

//####################################################################
cRender::show_time_options($sTitle); 
cRenderMenus::show_app_agent_menu();

cRenderMenus::show_apps_menu("Show detail for", cHttp::build_url("appagentdetail.php",cRender::METRIC_TYPE_QS,$gsMetricType));

cRender::appdButton(cAppDynControllerUI::nodes($oApp->id), "All nodes");
//####################################################################

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

?>
<p>
<h2><?=$sTitle?></h2>

<?php
//####################################################################
$aResponse = cAppDyn::GET_AppNodes($oApp->id);
$aResponse= group_by_tier($aResponse);
uasort($aResponse, "pr__sort_nodes");
$iNodes = count_nodes($aResponse);

if ($iNodes==0){
	?>
		<div class="maintable"><h2>No nodes found</h2></div>
	<?php
}else{
?>
	<p>
		<?php
			$sAppQS = cRender::get_base_app_QS();
			foreach ($aResponse as $aTierNodes){
				if (cFilter::isTierFilteredOut($aTierNodes[0]->tierName)) continue;
				
				?><hr><?php
				$tid = $aTierNodes[0]->tierId;
				$tier = $aTierNodes[0]->tierName;

				cRenderMenus::show_tier_functions($tier, $tid);
				$sTierQS = cHttp::build_qs($sAppQS, cRender::TIER_QS, $tier);
				$sTierQS = cHttp::build_qs($sTierQS , cRender::TIER_ID_QS, $tid);
				$sTierRootUrl=cHttp::build_url("tierinfrstats.php",$sTierQS);				
				$aMetrics = [];
				foreach ($aTierNodes as $oNode){
					$sNode = $oNode->name;

					$oMetric = cAppDynInfraMetric::getInfrastructureMetric($tier, $sNode ,$gsMetricType );
					$sDetailUrl = cHttp::build_url($sTierRootUrl,cRender::NODE_QS,$sNode);
					
					$aMetrics[] = [cChart::LABEL=>$oMetric->caption, cChart::METRIC=>$oMetric->metric];
					$aMetrics[] = [cChart::TYPE=>cChart::LABEL,cChart::LABEL=>cRender::button_code("go",$sDetailUrl)];
					
				}
				cChart::metrics_table($oApp, $aMetrics,6,cRender::getRowClass(),null,cChart::CHART_WIDTH_LETTERBOX/3);
			}
		?>
	<p>
<?php
}
cChart::do_footer();
cRender::html_footer();
?>
