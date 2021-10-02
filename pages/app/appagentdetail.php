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



cRenderHtml::header("Application node detail ");
cRender::force_login();
$oApp = cRenderObjs::get_current_app();
$gsMetricType = cHeader::get(cRender::METRIC_TYPE_QS);
$sAppQS = cRenderQS::get_base_app_QS($oApp);

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
			if (!isset($aTiers[(string)$TierID])) $aTiers[(string)$TierID] = [];
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
	cCommon::errorbox("no application");
	exit;
}
if (!$gsMetricType ){
	cCommon::errorbox("no metric type");
	exit;
}
$oMetric = cADInfraMetric::getInfrastructureMetric($oApp->name,null,$gsMetricType);
$sTitle  = $oMetric->caption;

//####################################################################
$sAppQS = cRenderQS::get_base_app_QS($oApp);

$sDetailRootQS = cHttp::build_url("appagentdetail.php", $sAppQS);

//####################################################################

cRenderMenus::show_app_agent_menu();

cRenderMenus::show_apps_menu("Show detail for", cHttp::build_url("appagentdetail.php",cRender::METRIC_TYPE_QS,$gsMetricType));

cADCommon::button(cADControllerUI::nodes($oApp), "All nodes");
//####################################################################

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

?>
<p>
<h2><?=$sTitle?></h2>

<?php
//####################################################################
$aResponse = $oApp->GET_Nodes();
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
			foreach ($aResponse as $aTierNodes){
				
				$tid = $aTierNodes[0]->tierId;
				$tier = $aTierNodes[0]->tierName;
				$oTier = cRenderObjs::make_tier_obj($oApp, $tier, $tid);
				
				if (cFilter::isTierFilteredOut($oTier)) continue;
				?><hr><?php

				cRenderMenus::show_tier_functions($oTier);
				$sTierQS = cHttp::build_qs($sAppQS, cRender::TIER_QS, $tier);
				$sTierQS = cHttp::build_qs($sTierQS , cRender::TIER_ID_QS, $tid);
				$sTierRootUrl=cHttp::build_url("tierinfrstats.php",$sTierQS);				
				$aMetrics = [];
				foreach ($aTierNodes as $oNode){
					$sNode = $oNode->name;

					$oMetric = cADInfraMetric::getInfrastructureMetric($tier, $sNode ,$gsMetricType );
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
cRenderHtml::footer();
?>
