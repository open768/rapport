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


//###################### DATA #############################################
$oApp = cRenderObjs::get_current_app();
if (!$oApp->name) $oApp->name = "no application set";
$psMetric = cHeader::get(cRenderQS::METRIC_QS);
$psDiv = cHeader::get(cRenderQS::DIV_QS); 
$psCSV=cHeader::get(cRenderQS::CSV_QS);
$psPrevious = cHeader::get(cRenderQS::PREVIOUS_QS);
$psHeirarchy = cHeader::get(cRenderQS::METRIC_HEIRARCHY_QS);

//*************************************************************************
cDebug::write("getting metric - $psMetric");
if ($psHeirarchy)
	$oResult = $oApp->GET_Metric_heirarchy($psMetric, false);
else
	$oResult = cMetricGetter::get_metric($oApp, $psMetric, ($psPrevious != null));
cDebug::write("got metric - $psMetric");
if ($oResult && $psDiv) $oResult->div = $psDiv;

//*************************************************************************
//* output
//*************************************************************************
if (!$oResult){ //error
	$oResult = ['error'=>"no data", 'div' =>$psDiv];
	cCommon::write_json($oResult);	
	return;
}

if (!$psCSV){ //got something
	cDebug::write("outputting json");
	cCommon::write_json($oResult);	
	return;
}else{
	//***CSV*******************************************************************
	$sFilename = str_replace("/","_",$psMetric);
	cHeader::set_download_filename("$sFilename.csv");

	cCommon::do_echo("controller,". cADCore::GET_controller());
	cCommon::do_echo("Application,$oApp->name");
	cCommon::do_echo("Date now,".date(DateTime::W3C,time()));
	cCommon::do_echo("metric,$psMetric");
	cCommon::do_echo("");

	cCommon::do_echo("Date,Average Value, Max");
	foreach ($oResult->data as $oItem){
		//reformat the date
		$oDate = DateTime::createFromFormat(DateTime::W3C, $oItem->date);
		$sDate = $oDate->format(cCommon::EXCEL_DATE_FORMAT);
		cCommon::do_echo("$sDate,$oItem->value,$oItem->max");
	}
}
?>
