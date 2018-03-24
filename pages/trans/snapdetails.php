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

$sTierQS = cRender::get_base_tier_QS();
$sTransQS = cHttp::build_QS($sTierQS, cRender::TRANS_QS,$trans);
cAppDynRestUI::$oTimes = cRender::get_times();

cRender::show_time_options("snapshot detail: $oApp->name&gt;$oApp->name&gt;$oTier->name&gt;$trans&gt;$sSnapURL"); 
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

?>
<H2>Snapshot Details for <?=$sSnapURL?></h2>
<?php
	$oData = cAppDynRestUI::GET_snapshot_segments($sSnapGUID);

	$sClass = cRender::getRowClass();
	?><table class="<?=$sClass?>">
		<th align="right">Business Transaction:</th><td><?=$oData->btName?></td></tr>
		<th align="right">Number of Segments:</th><td><?=$oData->segmentCount?></td></tr>
	</table><?php
	
	$oSegment = $oData->requestSegmentData;
	$sClass = cRender::getRowClass();
	?><table class="<?=$sClass?>">
		<tr><th align="right">thread</th><td><?=$oSegment->threadName?></td></tr>
		<tr><th align="right">timeTaken:</th><td><?=$oSegment->timeTakenInMilliSecs?></td></tr>
		<tr><th align="right">User Experience:</th><td><?=$oSegment->userExperience?></td></tr>
		<tr><th align="right">Summary:</th><td><?=$oSegment->summary?></td></tr>
		<tr><th align="right">Archived:</th><td><?=$oSegment->archived?></td></tr>
	</table><?php

	
// ################################################################################
// ################################################################################
cRender::html_footer();
?>