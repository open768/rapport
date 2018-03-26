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
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
cAppDynRestUI::$oTimes = cRender::get_times();

cRender::show_top_banner("snapshot detail: $oApp->name&gt;$oApp->name&gt;$oTier->name&gt;$trans&gt;$sSnapURL"); 

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not support ed for Demo");
	cRender::html_footer();
	exit;
}
//********************************************************************

$oCred = cRenderObjs::get_appd_credentials();
cRenderMenus::show_tier_functions();
cRender::button("back to transaction: $trans", "transdetails.php?$sTransQS");
cDebug::flush();

$sAppdUrl = cAppDynControllerUI::snapshot($oApp, $trid, $sSnapGUID, cAppDynRestUI::$oTimes);
cRender::appdButton($sAppdUrl);


?>
<!-- ************************************************************** -->
<H2>Snapshot Details for <?=$sSnapURL?></h2>
<?php
	$oData = cAppDynRestUI::GET_snapshot_segments($sSnapGUID, $sSnapTime);
	$iEpoch = (int) ($sSnapTime/1000);
	$sDate = date(cCommon::ENGLISH_DATE_FORMAT, $iEpoch);

	$sClass = cRender::getRowClass();
	?><table class="<?=$sClass?>">
		<th align="right">Business Transaction:</th><td><?=$oData->btName?></td></tr>
		<th align="right">URL:</th><td><?=$sSnapURL?></td></tr>
		<th align="right">Timestamp:</th><td><?=$sDate?></td></tr>
		<th align="right">Number of Segments:</th><td><?=$oData->segmentCount?></td></tr>
		<th align="right">Server:</th><td><?=$oData->requestSegmentData->applicationComponentNodeId?></td></tr>
	</table>

	
<!-- ************************************************************** -->
<H2>Segment Details</h2>
<?php
	$oSegment = $oData->requestSegmentData;
	$sClass = cRender::getRowClass();
	?><table class="<?=$sClass?>">
		<tr><th align="right">Time Taken:</th><td><?=$oSegment->timeTakenInMilliSecs?> ms</td></tr>
		<tr><th align="right">User Experience:</th><td><?=$oSegment->userExperience?></td></tr>
		<tr><th align="right">Summary:</th><td><?=$oSegment->summary?></td></tr>
	</table><?php
?>
<!-- ************************************************************** -->
<H2>Potential Problems</h2>
<?php
	cDebug::flush();
	$aProblems = cAppDynRestUI::GET_snapshot_problems($oApp, $sSnapGUID, $sSnapTime);

	if (count($aProblems) == 0)
		cRender::messagebox("No problems found");
	else{
		?><div class="<?=cRender::getRowClass()?>"><table border=1 cellspacing=0 cellpadding="3" id="problems">
			<thead><tr>
				<th>Type</th>
				<th>Time</th>
				<th width="700">Detail</th>
			</tr></thead>
			<tbody><?php
				foreach ($aProblems as $oProblem){
					?><tr>
						<td><?=$oProblem->subType?> <?=$oProblem->problemType?></td>
						<td><?=$oProblem->executionTimeMs?> ms</td>
						<td><?=$oProblem->message?></td>
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
<H2>Slow DB and Remote Service Calls</h2>
<?php
	cDebug::flush();
	$aProblems = cAppDynRestUI::GET_snapshot_slow_db_and_remote($oApp, $sSnapGUID, $sSnapTime);
	if (count($aProblems) == 0)
		cRender::messagebox("not implemented: No slow DB or remote service calls found");
	else{
	}
?>
<!-- ************************************************************** -->
<H2>Slow Methods</h2>
<?php
	cDebug::flush();
	$aProblems = cAppDynRestUI::GET_snapshot_slow_methods($oApp, $sSnapGUID, $sSnapTime);
	if (count($aProblems) == 0)
		cRender::messagebox("not implemented: No slow methods found");
	else{
	}

// ################################################################################
cRender::html_footer();
?>