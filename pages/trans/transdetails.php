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
$home = "../..";
$root=realpath($home);
$phpinc = realpath("$root/../phpinc");
$jsinc = "$home/../jsinc";

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
require_once("$root/inc/inc-charts.php");
require_once("$root/inc/inc-secret.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-filter.php");

const COLUMNS=6;
const FLOW_ID = "trflw";
const MIN_TRANS_TIME=150;

//####################################################################
cRender::html_header("Transactions");
cRender::force_login();
?>
	<script type="text/javascript" src="<?=$home?>/js/remote.js"></script>	
	<script type="text/javascript" src="<?=$home?>/js/transflow.js"></script>
<?php
cChart::do_header();

//####################################################
//display the results
$oApp = cRenderObjs::get_current_app();
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


cRender::show_time_options("$oApp->name&gt;$oApp->name&gt;$tier&gt;$trans"); 
//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************
$aNodes = cAppdyn::GET_TierAppNodes($oApp->name,$tier);
function sort_nodes($a, $b){
	return strcmp($a->name, $b->name);
}
uasort($aNodes , "sort_nodes");

$oCred = cRenderObjs::get_appd_credentials();
if ($oCred->restricted_login == null){?>
	<select id="showMenu">
		<optgroup label="General">
			<option selected disabled>Show...</option>
			<?php
				if ($node){
					?><option value="transdetails.php?<?=$sTransQS?>">All servers for this transaction</option><?php
				}
			?>
			<option value="apptrans.php?<?=$sAppQS?>"><?=($oApp->name)?> <i>app</i></option>
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
cRenderMenus::show_tier_functions();
cRender::appdButton(cAppDynControllerUI::transaction($oApp,$trid));
cRender::button("Transaction details for all nodes", "transallnodes.php?$sTransQS");
cDebug::flush();

?>
<H2>Contents</h2>
<ul>
	<li><a href="#1">Data for (<?=$trans?>) in (<?=$tier?>) tier</a>
	<li><a href="#2">Transaction Map</a>
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
	cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
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
		oLoader.home="<?=$home?>";
		oLoader.APP_QS="<?=cRender::APP_QS?>";
		oLoader.TIER_QS="<?=cRender::TIER_QS?>";
		oLoader.TRANS_QS="<?=cRender::TRANS_QS?>";
		oLoader.load("<?=$oApp->name?>", "<?=$tier?>", "<?=$trans?>");
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
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
	?>
	<h2>Server Data</h2>
	<?php
		$aMetrics = [];
		$aMetrics[] = [cChart::LABEL=>"Overall CPU Busy:", cChart::METRIC=>cAppDynMetric::InfrastructureCpuBusy($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall Java Heap Used:", cChart::METRIC=>cAppDynMetric::InfrastructureJavaHeapUsed($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall Java GC Time:", cChart::METRIC=>cAppDynMetric::InfrastructureJavaGCTime($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall .Net Heap Used:", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetHeapUsed($tier, $node)];
		$aMetrics[] = [cChart::LABEL=>"Overall .Net GC Time:", cChart::METRIC=>cAppDynMetric::InfrastructureDotnetGCTime($tier, $node)];
		cChart::render_metrics($oApp, $aMetrics,cChart::CHART_WIDTH_LETTERBOX/3);
}
?>

<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2><a name="4">Remote</a>Services for - <?=$tier?> - <?=$trans?></h2>
	<?php
		cDebug::flush();
		//******get the external tiers used by this transaction
		$oData = cAppdyn::GET_TransExtTiers($oApp->name, $tier, $trans);
		if ($oData){
			$aMetrics = [];
			foreach ( $oData as $oItem){
				$other = $oItem->name;
				$sClass = cRender::getRowClass();
				
					$aMetrics[] = [cChart::TYPE=>cChart::LABEL, cChart::LABEL=>"<DIV style='max-width:200px;overflow-wrap:break-word'>$other</div>"];
					$sMetricUrl=cAppDynMetric::transExtCalls($tier, $trans, $other);
					$aMetrics[] = [cChart::LABEL=>"Calls per min to: $other", cChart::METRIC=>$sMetricUrl];
					$sMetricUrl=cAppDynMetric::transExtResponseTimes($tier, $trans, $other);
					$aMetrics[] = [cChart::LABEL=>"response times: $other", cChart::METRIC=>$sMetricUrl];
			}
			cChart::metrics_table($oApp, $aMetrics, 3, $sClass, cChart::CHART_HEIGHT_SMALL);
		}else
			echo "<h3>This transaction has no external calls</h3>";
	?>
</table>
<p>
<!-- #############################################################################-->
<!-- #############################################################################-->
<h2><a name="5">Transaction Snapshots</a></h2>
Showing snapshots taking over <?=MIN_TRANS_TIME?>ms
<?php
cDebug::flush();

$oTimes = cRender::get_times();
$sAppdUrl = cAppDynControllerUI::transaction_snapshots($oApp,$trid, $oTimes);

$aSnapshots = cAppdyn::GET_snaphot_info($oApp->name, $trid, $oTimes);
if (count($aSnapshots) == 0){
	?><div class="maintable">No Snapshots found</div><?php
}else{
	cRender::button("Analyse top ten slowest transactions", "transanalysis.php?$sTransQS", true);
	?>
		<table class="maintable" id="trans">
			<thead><tr class="tableheader">
				<th width="140">start time</th>
				<th width="10"></th>
				<th width="80">Duration</th>
				<th>Server</th>
				<th>URL</th>
				<th>Summary</th>
				<th width="80"></th>
			</tr></thead>
			<tbody><?php
				foreach ($aSnapshots as $oSnapshot){
					if ($oSnapshot->timeTakenInMilliSecs < MIN_TRANS_TIME) continue;
					$iEpoch = (int) ($oSnapshot->serverStartTime/1000);
					$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);
					$sAppdUrl = cAppDynControllerUI::snapshot($oApp, $trid, $oSnapshot->requestGUID, $oTimes);
					$sImgUrl = cRender::get_trans_speed_colour($oSnapshot->timeTakenInMilliSecs);
					$sSnapQS = cHttp::build_QS($sTransQS, cRender::SNAP_GUID_QS, $oSnapshot->requestGUID);
					$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_URL_QS, $oSnapshot->URL);
					$sSnapQS = cHttp::build_QS($sSnapQS, cRender::SNAP_TIME_QS, $oSnapshot->serverStartTime);
					
					?>
					<tr class="<?=cRender::getRowClass()?>">
						<td><?=$sDate?></td>
						<td><img src="<?=$home?>/<?=$sImgUrl?>"></td>
						<td align="middle"><?=$oSnapshot->timeTakenInMilliSecs?></td>
						<td><?=cAppdynUtil::get_node_name($oApp->id,$oSnapshot->applicationComponentNodeId)?></td>
						<td><a href="snapdetails.php?<?=$sSnapQS?>" target="_blank"><div style="max-width:200px;overflow-wrap:break-word;"><?=$oSnapshot->URL?></div></a></td>
						<td><?=cCommon::fixed_width_div(600, $oSnapshot->summary)?></div></td>
						<td><?=cRender::appdButton($sAppdUrl, "Go")?></td>
					</tr>
				<?php }
			?></tbody>
		</table>
		<script language="javascript">
			$( function(){ 
				$("#trans").tablesorter({
					headers:{
						3:{ sorter: 'digit' }
					}
				});
			});

		</script>
	<?php
	cRender::appdButton($sAppdUrl, "Goto Transaction Snapshots");
}


// ################################################################################
// ################################################################################
cChart::do_footer();

cRender::html_footer();
?>