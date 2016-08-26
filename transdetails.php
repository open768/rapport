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
require_once("$phpinc/appdynamics/common.php");
require_once("inc/inc-charts.php");
require_once("inc/inc-secret.php");
require_once("inc/inc-render.php");
require_once("inc/inc-filter.php");

const COLUMNS=6;
const FLOW_ID = "trflw";

//####################################################################
cRender::html_header("Transactions");
cRender::force_login();
?>
	<script type="text/javascript" src="js/remote.js"></script>
	<script type="text/javascript" src="js/chart.php"></script>
	<script type="text/javascript" src="js/transflow.php"></script>
<?php
cChart::do_header();
cChart::$json_data_fn = "chart_getUrl";
cChart::$json_callback_fn = "chart_jsonCallBack";
cChart::$csv_url = "rest/getMetric.php";
cChart::$zoom_url = "metriczoom.php";
cChart::$save_fn = "save_fave_chart";
cChart::$compare_url = "compare.php";

cChart::$metric_qs = cRender::METRIC_QS;
cChart::$title_qs = cRender::TITLE_QS;
cChart::$app_qs = cRender::APP_QS;

//####################################################
//display the results
$app = cHeader::get(cRender::APP_QS);
$aid = cHeader::get(cRender::APP_ID_QS);
$tier = cHeader::get(cRender::TIER_QS);
$tid = cHeader::get(cRender::TIER_ID_QS);
$trans = cHeader::get(cRender::TRANS_QS);
$trid = cHeader::get(cRender::TRANS_ID_QS);
$node= cHeader::get(cRender::NODE_QS);
$sExtraCaption = ($node?"($node) node":"");

$sAppQS = cRender::get_base_app_QS();
$sTierQS = cRender::get_base_tier_QS();
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$trid);

$sFilterTierQS = cFilter::makeTierFilter($tier);
$sFilterTierQS = cHttp::build_QS($sAppQS, $sFilterTierQS);

//**************************************************
$aNodes = cAppdyn::GET_TierAppNodes($app,$tier);
cRender::show_time_options("$app&gt;$app&gt;$tier&gt;$trans"); 

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){?>
	<select id="backMenu">
		<option selected disabled>Back to...</option>
		<option value="apptrans.php?<?=$sAppQS?>"><?=($app)?> <i>app</i></option>
		<option value="apptrans.php?<?=$sFilterTierQS?>"><?=($tier)?> <i>tier</i></option>
		<?php
			if ($node){ 
				$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $node);
				?><option value="tiertransgraph.php?<?=$sNodeQs?>">
					(<?=($node)?>) server
				</option><?php 
			}
		?>
	</select>
	<select id="showMenu">
		<option selected disabled>Show...</option>
		<?php
			if ($node){
				?><option value="transdetails.php?<?=$sTransQS?>">All servers for this transaction</option><?php
			}
		?>
		<optgroup label="Servers for this transaction">
		<?php
			foreach ($aNodes as $oNode){
				$sDisabled = ($oNode->name==$node?"disabled":"");
				$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $oNode->name);
				$sUrl = "transdetails.php?$sNodeQs";
				?>
					<option <?=$sDisabled?> value="<?=$sUrl?>"><?=$oNode->name?></option>
				<?php
			}
		?>
		</optgroup>
	</select>
	<script language="javascript">
	$(  
		function(){
			$("#backMenu").selectmenu({change:common_onListChange});
			$("#showMenu").selectmenu({change:common_onListChange});
		}  
	);
	</script><?php
}
$aid = cHeader::get(cRender::APP_ID_QS);
cRender::appdButton(cAppDynControllerUI::transaction($aid,$trid));

?>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2>Data for (<?=$trans?>) in (<?=$tier?>) tier </h2>
<?php
	$aMetrics = [];
	$aMetrics[] = ["trans Calls:", cAppDynMetric::transCallsPerMin($tier, $trans)];
	$aMetrics[] = ["trans Response:", cAppDynMetric::transResponseTimes($tier, $trans)];
	$aMetrics[] = ["trans errors:", cAppDynMetric::transErrors($tier, $trans)];
	$aMetrics[] = ["trans cpu used:", cAppDynMetric::transCpuUsed($tier, $trans)];
	cRender::render_metrics_table($app, $aMetrics, 3, cRender::getRowClass(), cRender::CHART_HEIGHT_SMALL);
?>

<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2>Transaction map</h2>
<div class="transactionflow" id="<?=FLOW_ID?>">
	Please wait...
</div>
<script>
	function load_trans_flow(){
		var oLoader = new cTransFlow("<?=FLOW_ID?>");
		oLoader.load("<?=$app?>", "<?=$tier?>", "<?=$trans?>");
	}
	$(load_trans_flow);	
</script>

<?php

// ################################################################################
// ################################################################################
if ($node){ ?>
	<h2>Transaction Data for (<?=$node?>) server</h2>
	<?php
		$aMetrics = [];
		$aMetrics[] = ["server trans Calls:", cAppDynMetric::transCallsPerMin($tier, $trans, $node)];
		$aMetrics[] = ["server trans Response:", cAppDynMetric::transResponseTimes($tier, $trans, $node)];
		$aMetrics[] = ["server trans Errors:", cAppDynMetric::transErrors($tier, $trans, $node)];
		$aMetrics[] = ["server trans cpu used:", cAppDynMetric::transCpuUsed($tier, $trans, $node)];
		cRender::render_metrics_table($app, $aMetrics, 3, cRender::getRowClass(), cRender::CHART_HEIGHT_SMALL);
	?>
	<h2>Server Data</h2>
	<?php
		$aMetrics = [];
		$aMetrics[] = ["Overall CPU Busy:", cAppDynMetric::InfrastructureCpuBusy($tier, $node)];
		$aMetrics[] = ["Overall Java Heap Used:", cAppDynMetric::InfrastructureJavaHeapUsed($tier, $node)];
		$aMetrics[] = ["Overall Java GC Time:", cAppDynMetric::InfrastructureJavaGCTime($tier, $node)];
		$aMetrics[] = ["Overall .Net Heap Used:", cAppDynMetric::InfrastructureDotnetHeapUsed($tier, $node)];
		$aMetrics[] = ["Overall .Net GC Time:", cAppDynMetric::InfrastructureDotnetGCTime($tier, $node)];
		cRender::render_metrics_table($app, $aMetrics, 3, cRender::getRowClass(), cRender::CHART_HEIGHT_SMALL);
} else{
	?><h2>Transaction information for individual Servers</?>
	<table class='maintable'>
		<tr class="tableheader">
			<th>Calls</th>
			<th>Response times</th>
			<th>Errors</th>
		</tr>
		<?php
			function sort_nodes($a, $b){
				return strcmp($a->name, $b->name);
			}
			uasort($aNodes , "sort_nodes");
			
			cChart::$width = cRender::CHART_WIDTH_LETTERBOX / 3;
			foreach ($aNodes as $oNode){ 
				$sNodeName = $oNode->name;
				$sClass = cRender::getRowClass();
				?>
				<tr class="<?=$sClass?>">
					<td colspan="4">&nbsp;<br><span class="tableheader">Server:</span> <?=$sNodeName?></td>
				</tr><tr class="<?=$sClass?>">
					<td><?php
						$sMetricUrl=cAppDynMetric::transCallsPerMin($tier, $trans, $sNodeName);
						cChart::add("Calls  ($sNodeName)", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::transResponseTimes($tier, $trans, $sNodeName);
						cChart::add("response ($sNodeName)", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::transErrors($tier, $trans, $sNodeName);
						cChart::add("Errors ($sNodeName)", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td width="30"><?php
						$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $oNode->name);
						$sUrl = "transdetails.php?$sNodeQs";
						cRender::button("Go", $sUrl);
					?></td>
				</tr>
			<?php }
	?></table><?php
}
?>

<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2>Remote Services for - <?=$tier?> - <?=$trans?></h2>
	<?php
		//******get the external tiers used by this transaction
		$oData = cAppdyn::GET_TransExtTiers($app, $tier, $trans);
		cChart::$width = cRender::CHART_WIDTH_LETTERBOX / 3;
		if ($oData){
			?><table class='maintable'>
			<tr class="tableheader">
				<th>Name</th>
				<th>Activity</th>
				<th>Response Times (ms)</th>
			</tr>
			
			<?php
			foreach ( $oData as $oItem){
				$other = $oItem->name;
				$sClass = cRender::getRowClass();
				
				?><tr class="<?=$sClass?>">
					<td><?=$other?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::transExtCalls($tier, $trans, $other);
						cChart::add("Calls per min to: $other", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
					<td><?php
						$sMetricUrl=cAppDynMetric::transExtResponseTimes($tier, $trans, $other);
						cChart::add("response times: $other", $sMetricUrl, $app, cRender::CHART_HEIGHT_SMALL);
					?></td>
				</tr><?php
			}
			?></table><?php
		}else
			echo "<h3>This transaction has no external calls</h3>";
	?>
</table>
<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<?php
$oTimes = cRender::get_times();
$sAppdUrl = cAppDynControllerUI::transaction_snapshots($aid,$trid, $oTimes);
cRender::appdButton($sAppdUrl, "Transaction Snapshots");
$aSnapshots = cAppdyn::GET_snaphot_info($app, $trid, $oTimes);
if (count($aSnapshots) == 0){
	?><div class="maintable">No Snapshots found</div><?php
}else{
	cDebug::vardump($aSnapshots[0],true);
	?>
	<table class="maintable" id="trans">
		<thead><tr class="tableheader">
			<th width="150">start time</th>
			<th width="10"></th>
			<th width="80">Duration</th>
			<th>Server</th>
			<th>Summary</th>
			<th width="100"></th>
		</tr></thead>
		<tbody><?php
			foreach ($aSnapshots as $oSnapshot){
				$iEpoch = (int) ($oSnapshot->serverStartTime/1000);
				$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
				$sAppdUrl = cAppDynControllerUI::snapshot($aid, $trid, $oSnapshot->requestGUID, $oTimes);
				$sImgUrl = cRender::get_trans_speed_colour($oSnapshot->timeTakenInMilliSecs);
				?>
				<tr class="<?=cRender::getRowClass()?>">
					<td><?=$sDate?></td>
					<td><img src="<?=$sImgUrl?>"></td>
					<td align="middle"><?=$oSnapshot->timeTakenInMilliSecs?></td>
					<td><?=cAppdynUtil::get_node_name($aid,$oSnapshot->applicationComponentNodeId)?></td>
					<td><?=$oSnapshot->summary?></td>
					<td><?=cRender::appdButton($sAppdUrl, "Go")?></td>
				</tr>
			<?php }
		?></tbody>
	</table>
	<script language="javascript">
		$( function(){ $("#trans").tablesorter();} );
	</script>
<?php
}


// ################################################################################
// ################################################################################
cChart::do_footer("chart_getUrl", "chart_jsonCallBack");

cRender::html_footer();
?>