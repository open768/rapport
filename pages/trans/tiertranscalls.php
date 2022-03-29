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
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/charts.php";

set_time_limit(200); // huge time limit as this takes a long time
//####################################################################
cRenderHtml::header("External tier calls");
cRender::force_login();

//display the results
$oTier = cRenderObjs::get_current_tier();
$gsTierQS = cRenderQS::get_base_tier_QS($oTier);

$title =  "Graphs for transaction calls per minute for transactions for $tier in $oApp->name";

//********************************************************************
if (cAD::is_demo()){
	cCommon::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}
//********************************************************************
//####################################################################
$oResponse =cAD::GET_Tier_transactions($oApp->name, $tier);

$sBaseUrl = cHttp::build_url("transdetails.php", $gsTierQS;) 
$oTimes = cRender::get_times();
foreach ($oResponse as $oDetail){
    $sTrans = $oDetail->name;
	$sTrid = $oDetail->id;
	$oTrans = new cADBT($oTier, $sTrans, $sTrid)
	$sLink = cHttp::build_url($sBaseUrl, cRenderQS::TRANS_QS, $sTrans);
	$sLink = cHttp::build_url($sLink, cRenderQS::TRANS_ID_QS, $sTrid);
	cCommon::flushprint ("<h2><a href='$link'>$sTrans</a></h2>");   
	
	$sMetricpath = cADMetricPaths::transCallsPerMin($oTrans);
	$oResponse = $oApp->GET_MetricData( $sMetricpath, $oTimes, false);
	
	$iTotalRows = count($oResponse);
    $charturl = generate_chart("ttc", "call per min $sTrans",  $oResponse);
	echo "<img src='$charturl'>";
}


//---------------------------------------------------------------
echo "<p>";
cRender::button("see External Calls", cHttp::build_url("tierextcalls.php", $gsTierQS));
cRender::button("just trans stats", cHttp::build_url("tiertrans.php", $gsTierQS));
cRender::button("trans graph", cHttp::build_url("tiertransgraph.php", $gsTierQS));

cRenderHtml::footer();
?>