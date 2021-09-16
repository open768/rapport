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
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();


//###################### DATA #############################################
$oApp = cRenderObjs::get_current_app();
if (!$oApp->name) $oApp->name = "no application set";
$oTimes = cRender::get_times();
//*************************************************************************
cDebug::write("getting synthetics list - $oApp->name");
$sGetDetails = cHeader::get(cRender::SYNTH_DETAILS_QS);
if ($sGetDetails)
	$oResult = cAD_RestUI::GET_Synthetic_jobs($oApp, $oTimes, true);
else
	$oResult = cAD_RestUI::GET_Synthetic_jobs($oApp, $oTimes, false);	


//*************************************************************************
//* output
//*************************************************************************
cCommon::write_json($oResult);	
?>
