<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
$home="..";
require_once "$home/inc/common.php";


set_time_limit(200); // huge time limit as this could takes a long time



//###################### DATA #############################################################
$oApp = cRenderObjs::get_current_app();
$tier = cHeader::get(cRender::TIER_QS);
$trans = cHeader::get(cRender::TRANS_QS);
$index = cHeader::get("id"); //id of HTML div

$aResult = array( "id"=>$index);

$oTimes = cRender::get_times();
$sMetricpath = cADMetricPaths::transResponseTimes($tier, $trans);
$aStats = $oApp->GET_MetricData($sMetricpath, $oTimes,"true",false,true);


if ($aStats){
	$aResult["max"] = $aStats[0];
	$sMetricpath = cADMetricPaths::transErrors($tier, $trans);
	$aErrors = $oApp->GET_MetricData( $sMetricpath, $oTimes,"true",false,true);
	
	if ($aErrors)		$aResult["transErrors"] = $aErrors[0];
}else{
	$aResult["error"] = "no data";
}

echo json_encode($aResult);	
?>
