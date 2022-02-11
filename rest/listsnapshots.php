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
$home="..";
require_once "$home/inc/common.php";


$oTrans = cRenderObjs::get_current_trans();
$oTier = $oTrans->tier;
$sTierQS = cRenderQS::get_base_tier_QS($oTier);
$oApp = $oTier->app;
$sTransQS = cHttp::build_QS($sTierQS, cRenderQS::TRANS_QS,$oTrans->name);
$sTransQS = cHttp::build_QS($sTransQS, cRenderQS::TRANS_ID_QS,$oTrans->id);

class oSnapItem {
	public $startDate,$timetaken,$node,$originalurl,$summary;
	public $guuid, $startTime, $trans;
}

// ################################################################################
$oTimes = cRender::get_times();
$aSnapshots = $oTrans->GET_snapshots($oTimes);
cDebug::extra_debug("number of snapshots: ".count($aSnapshots));
//cDebug::vardump($aSnapshots);
if (count($aSnapshots) == 0)cDebug::error("no snapshots found");


cCommon::write_json($aSnapshots);	
?>