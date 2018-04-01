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

CONST MIN_TOTAL_TIME=150;
CONST MIN_EXT_TIME=100;
CONST MIN_EXT_COUNT=10;

//####################################################################
cRender::html_header("Snapshot");
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

cDebug::flush();cRender::appdButton($sAppdUrl);
if ($trid=="")	cRender::messagebox("trid is missing");

?>
<!-- ************************************************************** -->
<H2>Snapshot Details for <span class="transaction"><?=$sSnapURL?></a></h2>
<?php
	$oData = cAppDynRestUI::GET_snapshot_segments($sSnapGUID, $sSnapTime);	
	/*
		cDebug::on(true);
		cDebug::vardump($oData,true);
		cDebug::off();
	*/
	$sDate = cAppdynUtil::timestamp_to_date($sSnapTime);
	$trid=$oData->requestSegmentData->businessTransactionId;

	$sClass = cRender::getRowClass();
	?><table class="<?=$sClass?>">
		<tr><th align="right">Business Transaction:</th><td><?=$oData->btName?></td></tr>
		<tr><th align="right">URL:</th><td><?=$sSnapURL?></td></tr>
		<tr><th align="right">Timestamp:</th><td><?=$sDate?></td></tr>
		<tr><th align="right">Number of Segments:</th><td><?=$oData->segmentCount?></td></tr>
	</table>
	<?php
		$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
		$sTransQS = cHttp::build_QS($sTransQS, cRender::TRANS_ID_QS,$trid);
		cRender::button("back to transaction: $trans", "transdetails.php?$sTransQS");
	?>

	
<!-- ************************************************************** -->
<H2>Segment Details</h2>
<?php
	$oSegment = $oData->requestSegmentData;
	$sClass = cRender::getRowClass();
	?><table class="<?=$sClass?>">
		<tr><th align="right">Time Taken:</th><td><?=$oSegment->timeTakenInMilliSecs?> ms</td></tr>
		<tr><th align="right">User Experience:</th><td><?=$oSegment->userExperience?></td></tr>
		<tr><th align="right">Summary:</th><td><?=$oSegment->summary?></td></tr>
		<tr><th align="right">Server:</th><td><?=$oData->requestSegmentData->applicationComponentNodeId?></td></tr>
	</table><?php
?>
<!-- ************************************************************** -->
<H2>Potential Problems</h2>
<?php
	cDebug::flush();
	$aProblems = cAppDynRestUI::GET_snapshot_problems($oApp, $sSnapGUID, $sSnapTime);
	/*
	cDebug::on(true);
	cDebug::vardump($aProblems,true);
	cDebug::off();
	*/

	if (count($aProblems) == 0)
		cRender::messagebox("No problems found");
	else{
		?><div class="<?=cRender::getRowClass()?>"><table border=1 cellspacing=0 cellpadding="3" id="problems">
			<thead><tr>
				<th colspan="2">Type</th>
				<th>Time</th>
				<th width="700">Detail</th>
			</tr></thead>
			<tbody><?php
				foreach ($aProblems as $oProblem){
					if ($oProblem->executionTimeMs < MIN_EXT_TIME) continue;
					?><tr>
						<td><?=$oProblem->subType?></td>
						<td><?=$oProblem->problemType?></td>
						<td><?=$oProblem->executionTimeMs?> ms</td>
						<td><?=htmlspecialchars($oProblem->message)?></td>
					</tr><?php
				}
			?></tbody>
		</table></div>
		<script language="javascript">
			$( function(){ $("#problems").tablesorter();} );
		</script>
		
		<?php
	}
?>
<!-- ************************************************************** -->
<H2>Slow DB and Remote Service Calls - (minimum <?=MIN_TOTAL_TIME?>ms)</h2>
<?php
	cDebug::flush();
	$bError = false;
	try{
		$oData = cAppDynRestUI::GET_snapshot_flow($oApp, $trid, $sSnapGUID, $sSnapTime);
	}catch (Exception $e){
		cRender::errorbox("unable to retrieve snapshot flow, Error was:" . $e->getMessage());
		$bError = true;
	}
	if (!$bError){		
		$aNodes = $oData->nodes;

		foreach ($aNodes as $oNode){
			?><h3><?=$oNode->name?></h3><?php
			$aSegments = $oNode->requestSegmentDataItems;
			if (count($aSegments)==0) {
				cRender::messagebox("No data found");
				continue;
			}
			
			?><div class="<?=cRender::getRowClass()?>"><table border="1" cellspacing="0" id="SLOW<?=$oNode->name?>">
				<thead><tr>
					<th>total time</th>
					<th>Type</th>
					<th>Called By</th>
					<th>Detail</th>
					<th>Count</th>
					<th>Avg</th>
				</tr></thead>
				<tbody><?php
				foreach ($aSegments as $oSegment){
					$aExitCalls = $oSegment->exitCalls;
					foreach ($aExitCalls as $oExitCall){
						if ($oExitCall->timeTakenInMillis < MIN_TOTAL_TIME && $oExitCall->count < MIN_EXT_COUNT) continue;
						
						/*cDebug::on(true);
						cDebug::vardump($oExitCall);
						cDebug::off();
						*/
						$avg = round($oExitCall->timeTakenInMillis/$oExitCall->count,2);
						?><tr>
							<td><?=$oExitCall->timeTakenInMillis?> ms</td>
							<td><?=htmlspecialchars($oExitCall->exitPointName)?></td>
							<td><?=htmlspecialchars($oExitCall->callingMethod)?></td>
							<td><?=htmlspecialchars($oExitCall->detailString)?></td>
							<td><?=$oExitCall->count?></td>
							<td><?=$avg?> ms</td>
						</tr><?php
					}
				}
				?></tbody>
			</table></div>
			<script language="javascript">
				$( function(){ $("#SLOW<?=$oNode->name?>").tablesorter();} );
			</script>
			<?php
		}
	}

// ################################################################################
cRender::html_footer();
?>