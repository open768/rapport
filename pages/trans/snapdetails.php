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
//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";


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
$oSnap = cRenderObjs::get_current_snapshot();
cDebug::vardump($oSnap);
$oTrans = $oSnap->trans;
$oTier = $oTrans->tier;
$oApp = $oTier->app;
cRenderHtml::header("Snapshot - $oTrans->name");
cRender::force_login();

//####################################################
//display the results
$sSnapURL = cHeader::get(cRenderQS::SNAP_URL_QS);

$sTierQS = cRenderQS::get_base_tier_QS($oTier);

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************

$oCred = cRenderObjs::get_AD_credentials();
cDebug::flush();

$oTime = new cADTimes($oSnap->starttime);
$sAppdUrl = cADControllerUI::snapshot($oSnap);

try{
	$oSegments = $oSnap->GET_segments();	
	cDebug::vardump($oSegments);
}catch (Exception $e){
	cCommon::messagebox("no segments found");
	cRenderHtml::footer();
	exit;
}
//###############################################################################################
cRenderCards::card_start("Snapshot Details for $sSnapURL");
cRenderCards::body_start();
	$sDate = cADTime::timestamp_to_date($oSnap->starttime);

	?><table border="1" cellspacing="0">
		<tr><th align="right">Business Transaction:</th><td><?=cRender::show_name(cRender::NAME_TRANS,$oSegments->btName)?></td></tr>
		<tr><th align="right">URL:</th><td><?=$sSnapURL?></td></tr>
		<tr><th align="right">Timestamp:</th><td><?=$sDate?></td></tr>
		<tr><th align="right">Number of Segments:</th><td><?=$oSegments->segmentCount?></td></tr>
	</table><?php
cRenderCards::body_end();
cRenderCards::action_start();
	cADCommon::button($sAppdUrl);
	$sTransQS = cHttp::build_QS($sTierQS, cRenderQS::TRANS_QS,$oTrans->name);
	$sTransQS = cHttp::build_QS($sTransQS, cRenderQS::TRANS_ID_QS,$oTrans->id);
	cRender::button("back to transaction: $oTrans->name", "transdetails.php?$sTransQS");
cRenderCards::action_end();
cRenderCards::card_end();

//###############################################################################################
cRenderCards::card_start("Segment Details");
cRenderCards::body_start();
	$oSegment = $oSegments->requestSegmentData;
	?><table border="1" cellspacing="0">
		<tr><th align="right">Time Taken:</th><td><?=$oSegment->timeTakenInMilliSecs?> ms</td></tr>
		<tr><th align="right">User Experience:</th><td><?=$oSegment->userExperience?></td></tr>
		<tr><th align="right">Summary:</th><td><?=$oSegment->summary?></td></tr>
		<tr><th align="right">Server:</th><td><?=cADUtil::get_node_name($oApp,$oSegments->requestSegmentData->applicationComponentNodeId)?></td></tr>
	</table><?php
cRenderCards::body_end();
cRenderCards::card_end();
	
//###############################################################################################
cRenderCards::card_start("http parameters");
cRenderCards::body_start();
	cDebug::flush();
	$bProceed = true;
	if (count($oSegment->httpParameters)==0){
		cCommon::messagebox("no http parameters captured");
		$bProceed = false;
	}
	if ($bProceed){
		?><table border="1" cellpadding="3" cellspacing="0" >
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
cRenderCards::body_end();
cRenderCards::card_end();

//###############################################################################################
cRenderCards::card_start("All External Calls Made");
cRenderCards::body_start();
	cDebug::flush();
	$oFlow = null;
	$bProceed = true;
	try{
		$oFlow = $oSnap->GET_segments_flow($oSegment);
	}catch (Exception $e){
		cCommon::errorbox("unable to retrieve snapshot flow, Error was:" . $e->getMessage());
		$bProceed = false;
	}
	
	if ($bProceed){
		$oExtCalls = cADUtil::count_flow_ext_calls($oFlow);
		if ($oExtCalls == null) 
			cDebug::error("Unable to count external calls");
		else{
			?><table border="1"cellspacing="0" >
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
cRenderCards::body_end();
cRenderCards::card_end();

//###############################################################################################
cRenderCards::card_start("Slow methods - (minimum ".MIN_TOTAL_TIME_METHOD."ms)");
cRenderCards::body_start();
	cDebug::flush();
	$bProceed = true;
	try{
		$aData = $oSnap->GET_expensive_methods();
	}catch (Exception $e){
		cCommon::errorbox("unable to retrieve slow methods, try refreshing the page:" . $e->getMessage());
		$bProceed = false;
	}

	if (!$bProceed || (count($aData) == 0)){
		cCommon::messagebox("no data found");
	}else{
		?><table border="1" cellspacing="0" id="SLOW__METHODS" >
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
		</table>
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
cRenderCards::body_end();
cRenderCards::card_end();


//###############################################################################################
	$bError = false;
	try{
		$oFlow = $oSnap->GET_segments_flow($oSegment);
	}catch (Exception $e){
		cCommon::errorbox("unable to retrieve snapshot flow, Error was:" . $e->getMessage());
		$bError = true;
	}
	if (!$bError){		
		$aNodes = $oFlow->nodes;

		foreach ($aNodes as $oNode){
			cRenderCards::card_start("Node: $oNode->name");
			cRenderCards::body_start();
				$aSegments = $oNode->requestSegmentDataItems;
				if ($aSegments == null || count($aSegments)==0) {
					cCommon::messagebox("No data found");
					cRenderCards::body_end();
					cRenderCards::card_end();
					continue;
				}
			
				//***************************************************************************************************
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
					
				//***************************************************************************************************
				cRenderCards::card_start("Slow DB and Remote Service Calls - (minimum ".MIN_TOTAL_TIME_REMOTE."ms)");
				cRenderCards::body_start();
					if (count($aExitCalls) == 0)
						cCommon::messagebox("no Slow remote calls found");
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
				cRenderCards::body_end();
				cRenderCards::card_end();

				//***************************************************************************************************
				cRenderCards::card_start("High Frequency Calls  (minimum ".MIN_EXT_COUNT." calls)");
				cRenderCards::body_start();
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
						cCommon::messagebox("No High Frequency Calls found");
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
				cRenderCards::body_end();
				cRenderCards::card_end();
			cRenderCards::body_end();
			cRenderCards::card_end();
		}
	}
?>
<?php

		
// ################################################################################
cRenderHtml::footer();
?>