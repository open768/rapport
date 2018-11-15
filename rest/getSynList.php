<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

require_once("../inc/root.php");
cRoot::set_root("..");

require_once("$phpinc/appdynamics/appdynamics.php");
require_once("$phpinc/appdynamics/common.php");
require_once("$phpinc/appdynamics/metrics.php");
require_once("$phpinc/appdynamics/account.php");


//set_time_limit(200); // huge time limit as this could takes a long time

require_once("$phpinc/ckinc/debug.php");
require_once("$phpinc/ckinc/session.php");
require_once("$phpinc/ckinc/common.php");
require_once("$phpinc/ckinc/header.php");
require_once("$root/inc/inc-render.php");
require_once("$root/inc/inc-metrics.php");
	
//###################### DATA #############################################
cSession::set_folder();
session_start();
cDebug::check_GET_or_POST();


//###################### DATA #############################################
$oApp = cRenderObjs::get_current_app();
if (!$oApp->name) $oApp->name = "no application set";

//*************************************************************************
cDebug::write("getting synthetics list - $oApp->name");
$oResult = ["demo"=>"hello"];


//*************************************************************************
//* output
//*************************************************************************
cCommon::write_json($oResult);	
?>
