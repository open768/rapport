<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

$root=realpath("..");
$phpinc = realpath("$root/../phpinc");
require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/account.php");


set_time_limit(200); // huge time limit as this could takes a long time

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("../inc/inc-render.php");
require_once("../inc/inc-metrics.php");
	
//###################### DATA #############################################
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();


//###################### DATA #############################################
$oApp = cRender::get_current_app();
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
