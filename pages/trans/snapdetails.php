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

CONST MIN_TOTAL_TIME_REMOTE=150;
CONST MIN_TOTAL_TIME_METHOD=40;
CONST MIN_EXT_TIME=100;
CONST MIN_EXT_COUNT=20;

function sort_by_count($a,$b){
	return strnatcmp($b->count, $a->count);
}
function sort_by_time($a,$b){
	return strnatcmp($b->timeTakenInMillis, $a->timeTakenInMillis);
}

//####################################################################
$trans = cHeader::get(cRender::TRANS_QS);
cRender::html_header("Snapshot - $trans");
cRender::force_login();

//####################################################
//display the results
$oTier = cRenderObjs::get_current_tier();
$oApp = $oTier->app;

$trans = cHeader::get(cRender::TRANS_QS);
$trid = cHeader::get(cRender::TRANS_ID_QS);
$sSnapGUID = cHeader::get(cRender::SNAP_GUID_QS);
$sSnapURL = cHeader::get(cRender::SNAP_URL_QS);
$sSnapTime = cHeader::get(cRender::SNAP_TIME_QS);

$sTierQS = cRender::get_base_tier_QS();

cRender::show_top_banner("snapshot detail: $oApp->name&gt;$oApp->name&gt;$oTier->name&gt;$trans&gt;$sSnapURL"); 

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

$oCred = cRenderObjs::get_appd_credentials();
cDebug::flush();

$oTime = cAppdynUtil::make_time_obj($sSnapTime);
cAppDynRestUI::$oTimes = cRender::get_times();
$sAppdUrl = cAppDynControllerUI::snapshot($oApp, $trid, $sSnapGUID, $oTime);

cRender::appdButton($sAppdUrl);
if ($trid=="")	cRender::messagebox("trid is missing");

?>
<!-- ************************************************************** -->
<H2>Snapshot Details for <span class="transaction"><?=$sSnapURL?></span></h2>
<?php
	cDebug::flush();
	$oSnapshot = cAppDynRestUI::GET_snapshot_segments($sSnapGUID, $sSnapTime);	
	cDebug::vardump($oSnapshot);
	$sDate = cAppdynUtil::timestamp_to_date($sSnapTime);
	$trid=$oSnapshot->requestSegmentData->businessTransactionId;

	$sClass = cRender::getRowClass();
	?><table class="<?=$sClass?>">
		<tr><th align="right">Business Transaction:</th><td><?=cRender::show_name(cRender::NAME_TRANS,$oSnapshot->btName)?></td></tr>
		<tr><th align="right">URL:</th><td><?=$sSnapURL?></td></tr>
		<tr><th align="right">Timestamp:</th><td><?=$sDate?></td></tr>
		<tr><th align="right">Number of Segments:</th><td><?=$oSnapshot->segmentCount?></td></tr>
	</table>
	<?php
		$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
		$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$trid);
		cRender::button("back to transaction: $trans", "transdetails.php?$sTransQS");
	?>
<!-- ************************************************************** -->
<H2>Segment Details</h2>
<?php
	cDebug::flush();
	$oSegment = $oSnapshot->requestSegmentData;
	?><table class="<?=cRender::getRowClass();?>">
		<tr><th align="right">Time Taken:</th><td><?=$oSegment->timeTakenInMilliSecs?> ms</td></tr>
		<tr><th align="right">User Experience:</th><td><?=$oSegment->userExperience?></td></tr>
		<tr><th align="right">Summary:</th><td><?=$oSegment->summary?></td></tr>
		<tr><th align="right">Server:</th><td><?=cAppdynUtil::get_node_name($oApp->id,$oSnapshot->requestSegmentData->applicationComponentNodeId)?></td></tr>
	</table>
	
<!-- ************************************************************** -->
<h2>Http Parameters</h2>
<?php
	cDebug::flush();
	$bProceed = true;
	if (count($oSegment->httpParameters)==0){
		cRender::messagebox("no http parameters captured");
		$bProceed = false;
	}
	if ($bProceed){
		?><table border="1" cellpadding="3" cellspacing="0" class="<?=cRender::getRowClass();?>">
			<tr>
				<th>Name</th>
				<th>Value</th>
			</tr><?php
			foreach ($oSegment->httpParameters as $oParam){
				?><tr>
					<td align="right"><?=$oParam->name?></td>
					<td><?=$oParam->value?></td>
				</tr><?php
			}
		?></table><?php
	}
?>

<!-- ************************************************************** -->
<H2>All External Calls Made</h2>
<?php
	cDebug::flush();
	$oFlow = null;
	$bProceed = true;
	try{
		$oFlow = cAppDynRestUI::GET_snapshot_flow($oSegment);
	}catch (Exception $e){
		cRender::errorbox("unable to retrieve snapshot flow, Error was:" . $e->getMessage());
		$bProceed = false;
	}
	
	if ($bProceed){
		$oExtCalls = cAppDynUtil::count_flow_ext_calls($oFlow);
		if ($oExtCalls == null) 
			cDebug::error("Unable to count external calls");
		else{
			?><table border="1" cellpadding="3" cellspacing="0" class="<?=cRender::getRowClass();?>">
				<tr>
					<th>External Call</th>
					<th>Count</th>
					<th>total elapsed time</th>
				</tr><?php
				$aKeys = $oExtCalls->keys();
				foreach ($aKeys as $sKey){
					$oCounter = $oExtCalls->get($sKey);
					?><tr>
						<td><?=$sKey?></td>
						<td><?=$oCounter->count?></td>
						<td><?=$oCounter->totalTime?> ms</td>
					</tr><?php
				}
			?></table><?php
		}
	}
?>
<!-- ************************************************************** -->
<H2>Slow methods - (minimum <?=MIN_TOTAL_TIME_METHOD?>ms)</h2>
<?php
	cDebug::flush();
	$bProceed = true;
	try{
		$aData = cAppDynRestUI::GET_snapshot_expensive_methods($sSnapGUID, $sSnapTime);
	}catch (Exception $e){
		cRender::errorbox("unable to retrieve slow methods, try refreshing the page:" . $e->getMessage());
		$bProceed = false;
	}

	if (!$bProceed || (count($aData) == 0)){
		cRender::messagebox("no data found");
	}else{
		?><div class="<?=cRender::getRowClass()?>"><table border="1" cellspacing="0" id="SLOW__METHODS" >
			<thead><tr>
				<th width="50">Total time (ms)</th>
				<th width="400">Class</th>
				<th width="400">Method</th>
				<th width="50">Count</th>
				<th width="50">Avg time (ms)</th>
			</tr></thead>
			<tbody><?php
				foreach ($aData as $oDetail){
					if ($oDetail->timeSpentInMilliSec < MIN_TOTAL_TIME_METHOD  && $oDetail->callCount < MIN_EXT_COUNT) continue;
					$avg = round($oDetail->timeSpentInMilliSec/$oDetail->callCount,0);
					
					?><tr>
						<td><?=$oDetail->timeSpentInMilliSec?></td>
						<td><?=$oDetail->className?></td>
						<td><?=$oDetail->methodName?></td>
						<td><?=$oDetail->callCount?></td>
						<td><?=$avg?></td>
					</tr><?php				
				}
			?></tbody>
		</table></div>
		<script language="javascript">
			$( function(){ 
				$("#SLOW__METHODS").tablesorter({
					headers:{
						1:{ sorter: 'digit' },
						4:{ sorter: 'digit' },
						5:{ sorter: 'digit' }
					}
				});
			});
		</script><?php
	}
?>
<!-- ************************************************************** -->
<?php
	cDebug::flush();
	$bError = false;
	try{
		$oFlow = cAppDynRestUI::GET_snapshot_flow($oSegment);
	}catch (Exception $e){
		cRender::errorbox("unable to retrieve snapshot flow, Error was:" . $e->getMessage());
		$bError = true;
	}
	if (!$bError){		
		$aNodes = $oFlow->nodes;

		foreach ($aNodes as $oNode){
			?><h2><?=$oNode->name?></h2><?php
			$aSegments = $oNode->requestSegmentDataItems;
			if (count($aSegments)==0) {
				cRender::messagebox("No data found");
				continue;
			}
			
			?><h3>Slow DB and Remote Service Calls - (minimum <?=MIN_TOTAL_TIME_REMOTE?>ms)</h3><?php
				//extract the slow calls
				$iElapsed = 0;
				$iElapsedAll = 0;
				
				$aExitCalls = [];
				foreach ($aSegments as $oSegment)
					foreach ($oSegment->exitCalls as $oExitCall){
						$iElapsedAll += $oExitCall->timeTakenInMillis;
						if ($oExitCall->timeTakenInMillis < MIN_TOTAL_TIME_REMOTE) continue;
						$aExitCalls[] = $oExitCall;
					}
				if (count($aExitCalls) == 0)
					cRender::messagebox("no Slow remote calls found");
				else{
					uasort($aExitCalls, "sort_by_time");
					
					//render
					?><div class="<?=cRender::getRowClass()?>">
						<table border="1" cellspacing="0" id="SLOW<?=$oNode->name?>" width="100%">
							<thead><tr>
								<th width="50">Total time (ms)</th>
								<th width="50">Type</th>
								<th width="300">Called By</th>
								<th >Detail</th>
								<th width="50">Count</th>
								<th width="50">Avg time (ms)</th>
							</tr></thead>
							<tbody><?php
								foreach ($aExitCalls as $oExitCall){
									$avg = round($oExitCall->timeTakenInMillis/$oExitCall->count,0);
									$iElapsed += $oExitCall->timeTakenInMillis;
									?><tr>
										<td><b><?=$oExitCall->timeTakenInMillis?></b></td>
										<td><?=htmlspecialchars($oExitCall->exitPointName)?></td>
										<td><?=cCommon::fixed_width_div(300,htmlspecialchars($oExitCall->callingMethod))?></td>
										<td><?=htmlspecialchars($oExitCall->detailString)?></td>
										<td><?=$oExitCall->count?></td>
										<td><?=$avg?></td>
									</tr><?php
								}
							?></tbody>
						</table>
						<b>Total time taken for all remote calls: <?=$iElapsedAll?> ms, 
						of which slow calls account for: <?=$iElapsed?> ms</b>
					</div>
					<script language="javascript">
						$( function(){ 
							$("#SLOW<?=$oNode->name?>").tablesorter({
								headers:{
									1:{ sorter: 'digit' },
									5:{ sorter: 'digit' },
									6:{ sorter: 'digit' }
								}
							});
						});
					</script><?php
				}
				
			//***********************************************************************
			?><h3>High Frequency Calls  (minimum <?=MIN_EXT_COUNT?> calls)</h3><?php
				$iElapsed = 0;
				$iElapsedAll = 0;
				
				//extract the exit calls we're interested in
				$aExitCalls = [];
				foreach ($aSegments as $oSegment)
					foreach ($oSegment->exitCalls as $oExitCall){
						$iElapsedAll += $oExitCall->timeTakenInMillis;
						if ($oExitCall->count < MIN_EXT_COUNT) continue;
						$aExitCalls[] = $oExitCall;
					}
				if (count($aExitCalls) == 0)
					cRender::messagebox("No High Frequency Calls found");
				else{
					uasort($aExitCalls, "sort_by_count");
					$iCount = 0;
					//render
					?><div class="<?=cRender::getRowClass()?>">
						<table border="1" cellspacing="0" id="REPT<?=$oNode->name?>" width="100%">
							<thead><tr>
								<th width="50">Type</th>
								<th width="50">Count</th>
								<th width="50">Total time (ms)</th>
								<th width="50">Avg time (ms)</th>
								<th width="300">Called By</th>
								<th >Detail</th>
							</tr></thead>
							<tbody><?php
							foreach ($aExitCalls as $oExitCall){
								$iElapsed += $oExitCall->timeTakenInMillis;
								if (stripos($oExitCall->detailString,"pooled")) continue;
								$iCount+=$oExitCall->count;
								
								$avg = round($oExitCall->timeTakenInMillis/$oExitCall->count,0);
								?><tr>
									<td><?=htmlspecialchars($oExitCall->exitPointName)?></td>
									<td><b><?=$oExitCall->count?></b></td>
									<td><?=$oExitCall->timeTakenInMillis?></td>
									<td><?=$avg?></td>
									<td><?=cCommon::fixed_width_div(300,htmlspecialchars($oExitCall->callingMethod))?></td>
									<td><?=htmlspecialchars($oExitCall->detailString)?></td>
								</tr><?php
							}
							?></tbody>
						</table>
						<h3>Total time taken for all external calls: <?=$iElapsedAll?> ms, 
						of which <?=$iCount?> high frequency calls account for: <?=$iElapsed?> ms</h3>
					</div>
					<script language="javascript">
						$( function(){ 
							$("#REPT<?=$oNode->name?>").tablesorter({
								headers:{
									1:{ sorter: 'digit' },
									5:{ sorter: 'digit' },
									6:{ sorter: 'digit' }
								}
							});
						});
					</script><?php
				}
		}
	}
?>
<?php

		
// ################################################################################
cRender::html_footer();
?>