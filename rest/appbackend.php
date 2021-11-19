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


//###################### DATA #############################################
$oApp = cRenderObjs::get_current_app();

//*************************************************************************
cDebug::write("getting backends for $oApp->name");

class cAppBackendOutput {
	public $name;
	public $metric;
}

$aData = $oApp->GET_Backends();

$aOut = [];
foreach ($aData as $oItem){
	$oBackend = new cAppBackendOutput;
	$oBackend->name = $oItem->name;
	$oBackend->metric = cADMetricPaths::backendResponseTimes($oItem->name);
	$aOut[] = $oBackend;
}


//*************************************************************************
//* output
//*************************************************************************

cDebug::write("outputting json");
cCommon::write_json($aOut);	
return;
?>
