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
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$gsMetricType = cHeader::get(cRender::METRIC_TYPE_QS);

//####################################################################
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
<?php
cChart::do_header();
cChart::$width = cRender::CHART_WIDTH_LARGE;
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";

cChart::$compare_url = "compare.php";
cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

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
if (!$app ){
	cRender::errorbox("no application");
	exit;
}
if (!$gsMetricType ){
	cRender::errorbox("no metric type");
	exit;
}
$oMetric = cRender::getInfrastructureMetric($app,null,$gsMetricType);
$sTitle  = $oMetric->caption;

//####################################################################
$sAppQS = cRender::get_base_app_QS();

$sDetailRootQS = cHttp::build_url("allnodedetail.php", $sAppQS);

//####################################################################
cRender::show_time_options($sTitle); 
cRender::show_apps_menu("Show detail for", "allnodedetail.php","&".cRender::METRIC_TYPE_QS."=$gsMetricType");

$aMetrics = cRender::getInfrastructureMetricTypes();
?>
<select id="MetricMenu">
	<option selected disabled>Infrastructure details for all Servers:</option>
	<?php
		foreach ($aMetrics as $sMetricType){
			$oMetric = cRender::getInfrastructureMetric($app,null,$sMetricType);
			$sMetricUrl = cHttp::build_url($sDetailRootQS, cRender::METRIC_TYPE_QS, $sMetricType);
			?>
				<option <?=($sMetricType == $gsMetricType?"disabled":"")?> value="<?=$sMetricUrl?>"><?=$oMetric->short?></option>
			<?php
		}
	?>
</select>
<script language="javascript">
$(  
	function(){
		$("#MetricMenu").selectmenu({change:common_onListChange});
	}  
);
</script>
<?php
cRender::appdButton(cAppDynControllerUI::nodes($aid), "All nodes");
//####################################################################
?>
<p>
<h2><?=$sTitle?></h2>

<?php
//####################################################################
$aResponse = cAppDyn::GET_AppNodes($aid);
$aResponse= group_by_tier($aResponse);
uasort($aResponse, "pr__sort_nodes");
$iNodes = count_nodes($aResponse);

if ($iNodes==0){
	?>
		<div class="maintable"><h2>No nodes found</h2></div>
	<?php
}else{
?>
	<script language="javascript">
		var iTotalNodes=<?=count_nodes($aResponse)?>;
		var iNoData = 0;
		
		function hide_chart(poData){ //override
			var sDivID = poData.oItem.chart;
			var sCaption = poData.oItem.caption;
			$("#"+sDivID).closest("TABLE").closest("TR").empty(); //the whole row
			iTotalNodes--;
			$("#count").html(iTotalNodes);
			iNoData++;
			$("#nodata").html(iNoData);
			$("#nodatabody").append(sCaption).append("<BR>");
		}
		bean.on(cChartBean,CHART__NODATA_EVENT,hide_chart);
	</script>
	<p>
	<h2>There are <span id="count"><?=count_nodes($aResponse);?></span> nodes . <a href="#no_data_anchor">An additional (<span id="nodata">0</span> reported no data)</a></h2>
	<p>
	<table class="maintable" border="1" cellspacing="0" cellpadding="2">
		<tr>
			<th>Tier</th>
			<th width="200">Node Name</th>
			<th></th>
		</tr>
		<?php
			foreach ($aResponse as $aTierNodes){
				if (cFilter::isTierFilteredOut($aTierNodes[0]->tierName)) continue;
				
				$tid = $aTierNodes[0]->tierId;
				$tier = $aTierNodes[0]->tierName;
				
				$class=cRender::getRowClass();
				$sTierQS = cHttp::build_qs($sAppQS, cRender::TIER_QS, $tier);
				$sTierQS = cHttp::build_qs($sTierQS , cRender::TIER_ID_QS, $tid);
				$sTierRootUrl=cHttp::build_url("tierinfrstats.php",$sTierQS);
				$sDiskRootUrl=cHttp::build_url("nodedisks.php", $sTierQS);
				
				//-- figure out how many rows to span
				$iRowSpan=1;
				foreach ($aTierNodes as $oNode){
					$sNode = $oNode->name;
					if (cFilter::isNodeFilteredOut($sNode)) continue;
					$iRowSpan++;
				}

				?><tr class="<?=$class?>">
					<td rowspan="<?=$iRowSpan?>"><?=cRender::button($tier,$sTierRootUrl)?></td>
				</tr><?php
				
				sort ($aTierNodes);
				foreach ($aTierNodes as $oNode){
					$sNode = $oNode->name;
					if (cFilter::isNodeFilteredOut($sNode)) continue;
					$oMetric = cRender::getInfrastructureMetric($tier, $sNode ,$gsMetricType );
					
					$sDetailUrl = cHttp::build_url($sTierRootUrl,cRender::NODE_QS,$sNode);
					$sDiskUrl = cHttp::build_url($sDiskRootUrl,cRender::NODE_QS,$sNode);
					
					?><tr class="<?=$class?>">
						<td><?php cChart::add($oMetric->caption, $oMetric->metric, $app, 100);?></td>
						<td>
							<?=cRender::button("go",$sDetailUrl)?>
							<?=($gsMetricType==cRender::METRIC_TYPE_INFR_DISK_FREE?cRender::button("disks", $sDiskUrl):"")?>
						</td>
					</tr><?php
				}
			}
		?>
	</table>
	<p>
	<h2><a name="no_data_anchor">No data reported for the following</a></h2>
	<table class="maintable"><tr><td id="nodatabody">
	</td></tr></table>
<?php
}
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");
cRender::html_footer();
?>
