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
	
	<script type="text/javascript" src="js/transflow.php"></script>
<?php
cChart::do_header();

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
$oApp = cRender::get_current_app();

$sAppQS = cRender::get_base_app_QS();
$sTierQS = cRender::get_base_tier_QS();
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$trid);

$sFilterTierQS = cFilter::makeTierFilter($tier);
$sFilterTierQS = cHttp::build_QS($sAppQS, $sFilterTierQS);

//**************************************************
$aNodes = cAppdyn::GET_TierAppNodes($app,$tier);
cRender::show_time_options("$app&gt;$app&gt;$tier&gt;$trans"); 
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

$oCred = cRender::get_appd_credentials();
if ($oCred->restricted_login == null){?>
	<select id="showMenu">
		<optgroup label="General">
			<option selected disabled>Show...</option>
			<?php
				if ($node){
					?><option value="transdetails.php?<?=$sTransQS?>">All servers for this transaction</option><?php
				}
			?>
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
		</optgroup>
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
			$("#showMenu").selectmenu({change:common_onListChange});
		}  
	);
	</script><?php
}
$aid = cHeader::get(cRender::APP_ID_QS);
cRender::appdButton(cAppDynControllerUI::transaction($aid,$trid));
cDebug::flush();

?>
<H2>Contents</h2>
<ul>
	<li><a href="#1">Data for (<?=$trans?>) in (<?=$tier?>) tier</a>
	<li><a href="#2">Transaction Map</a>
	<li><a href="#3">Transaction activity for nodes</a>
	<li><a href="#4">Remote Services</a>
	<li><a href="#5">Transaction Snapshots</a>
</ul>
<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2><a name="1">Data for (<?=$trans?>) in (<?=$tier?>) tier </a></h2>
<?php
	$aMetrics = [];
	$aMetrics[] = [cChart::LABEL=>"trans Calls:", cChart::METRIC=>cAppDynMetric::transCallsPerMin($tier, $trans)];
	$aMetrics[] = [cChart::LABEL=>"trans Response:", cChart::METRIC=>cAppDynMetric::transResponseTimes($tier, $trans)];
	$aMetrics[] = [cChart::LABEL=>"trans errors:", cChart::METRIC=>cAppDynMetric::transErrors($tier, $trans)];
	$aMetrics[] = [cChart::LABEL=>"trans cpu used:", cChart::METRIC=>cAppDynMetric::transCpuUsed($tier, $trans)];
	cChart::metrics_table($oApp, $aMetrics, 3, cRender::getRowClass(), cRender::CHART_HEIGHT_SMALL);
	cDebug::flush();
?>

<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2><a name="2">Transaction map</a></h2>
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
cDebug::flush();
if ($node){ ?>
	<h2><a name="3">Data</a> for Transaction: (<?=$trans?>) for node (<?=$node?>)</h2>
	<?php
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"server trans Calls:", cChart::METRIC=>cAppDynMetric::transCallsPerMin($tier, $trans, $node)];
		$aMetrics[] = [cChart::LABEL=>"server trans Response:", cChart::METRIC=>cAppDynMetric::transResponseTimes($tier, $trans, $node)];
		$aMetrics[] = [cChart::LABEL=>"server trans Errors:", cChart::METRIC=>cAppDynMetric::transErrors($tier, $trans, $node)];
		$aMetrics[] = [cChart::LABEL=>"server trans cpu used:", cChart::METRIC=>cAppDynMetric::transCpuUsed($tier, $trans, $node)];
		cChart::metrics_table($oApp, $aMetrics, 2, cRender::getRowClass(), cRender::CHART_HEIGHT_SMALL);
	?>
	<h2>Server Data</h2>
	<?php
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"Overall CPU Busy:", cChart::METRIC=>cAppDynMetric::InfrastructureCpuBusy($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall Java Heap Used:", cChart::METRIC=>cAppDynMetric::InfrastructureJavaHeapUsed($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall Java GC Time:", cChart::METRIC=>cAppDynMetric::InfrastructureJavaGCTime($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall .Net Heap Used:", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetHeapUsed($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall .Net GC Time:", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetGCTime($tier, $node)];
		cChart::metrics_table($oApp, $aMetrics, 3, cRender::getRowClass(), cRender::CHART_HEIGHT_SMALL);
} else{
	?><h2><a name="3">Data</a> for Transaction: (<?=$trans?>) for all nodes</h2><?php
	function sort_nodes($a, $b){
		return strcmp($a->name, $b->name);
	}
	uasort($aNodes , "sort_nodes");
	
	$aMetrics = [];
	foreach ($aNodes as $oNode){ 
		$sNodeName = $oNode->name;
		$sNodeQs = cHttp::build_QS($sTransQS, cRender::NODE_QS, $oNode->name);
		$sUrl = "transdetails.php?$sNodeQs";
						
		$sMetricUrl=cAppDynMetric::transCallsPerMin($tier, $trans, $sNodeName);
		$aMetrics[] = [cChart::LABEL=>"Calls  ($sNodeName)", cChart::METRIC=>$sMetricUrl, cChart::GO_URL=>$sUrl, cChart::GO_HINT=>$oNode->name];
		$sMetricUrl=cAppDynMetric::transResponseTimes($tier, $trans, $sNodeName);
		$aMetrics[] = [cChart::LABEL=>"response ($sNodeName)", cChart::METRIC=>$sMetricUrl];
		$sMetricUrl=cAppDynMetric::transErrors($tier, $trans, $sNodeName);
		$aMetrics[] = [cChart::LABEL=>"Errors ($sNodeName)", cChart::METRIC=>$sMetricUrl];
	}
	$sClass = cRender::getRowClass();
	cChart::metrics_table($oApp, $aMetrics, 3, $sClass, cRender::CHART_HEIGHT_SMALL);
}
?>

<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2><a name="4">Remote</a>Services for - <?=$tier?> - <?=$trans?></h2>
	<?php
		cDebug::flush();
		//******get the external tiers used by this transaction
		$oData = cAppdyn::GET_TransExtTiers($app, $tier, $trans);
		if ($oData){
			foreach ( $oData as $oItem){
				$other = $oItem->name;
				$sClass = cRender::getRowClass();
				
				?><DIV class="<?=$sClass?>">
					<H3><?=$other?></H3><?php
					
					$aMetrics = [];
					$sMetricUrl=cAppDynMetric::transExtCalls($tier, $trans, $other);
					$aMetrics[] = [cChart::LABEL=>"Calls per min to: $other", cChart::METRIC=>$sMetricUrl];
					$sMetricUrl=cAppDynMetric::transExtResponseTimes($tier, $trans, $other);
					$aMetrics[] = [cChart::LABEL=>"response times: $other", cChart::METRIC=>$sMetricUrl];
					cChart::metrics_table($oApp, $aMetrics, 2, $sClass, cRender::CHART_HEIGHT_SMALL);
				?></DIV><?php
			}
		}else
			echo "<h3>This transaction has no external calls</h3>";
	?>
</table>
<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2><a name="5">Transaction Snapshots</a></h2>
<?php
cDebug::flush();
$oTimes = cRender::get_times();
$sAppdUrl = cAppDynControllerUI::transaction_snapshots($aid,$trid, $oTimes);
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
	cRender::appdButton($sAppdUrl, "Goto Transaction Snapshots");
}


// ################################################################################
// ################################################################################
cChart::do_footer();

cRender::html_footer();
?>