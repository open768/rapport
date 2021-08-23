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
$tier = cHeader::get(cRender::TIER_QS) ;
$trans = cHeader::get(cRender::TRANS_QS) ;
if ($oApp->name == null)	cDebug::error("App not set");
if ($tier == null)	cDebug::error("Tier not set");
if ($trans == null)	cDebug::error("Trans not set");

//*************************************************************************
$sMetricPath = cAppDynMetric::transExtNames($tier, $trans);
$oWalker = new cAppDynTransFlow();
$oWalker->walk($oApp->name, $tier, $trans);
cCommon::write_json($oWalker);	
?>
